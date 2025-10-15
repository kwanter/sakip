<?php

namespace App\Services;

use App\Models\User;
use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\Target;
use App\Models\EvidenceDocument;
use App\Models\Report;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Carbon\Carbon;
use Exception;

class SakipNotificationService
{
    protected $cacheTimeout = 3600; // 1 hour
    
    protected $notificationTypes = [
        'target_due' => 'Target Setting Due',
        'data_submission_due' => 'Data Submission Due',
        'assessment_due' => 'Assessment Due',
        'report_generation_due' => 'Report Generation Due',
        'data_validated' => 'Data Validated',
        'assessment_completed' => 'Assessment Completed',
        'target_approved' => 'Target Approved',
        'evidence_validated' => 'Evidence Validated',
        'report_generated' => 'Report Generated',
        'deadline_approaching' => 'Deadline Approaching',
        'deadline_overdue' => 'Deadline Overdue',
        'system_alert' => 'System Alert',
        'achievement_threshold' => 'Achievement Threshold Alert',
    ];

    protected $notificationChannels = [
        'database' => 'Database',
        'email' => 'Email',
        'sms' => 'SMS',
        'push' => 'Push Notification',
    ];

    /**
     * Send notification
     */
    public function sendNotification(array $data): Notification
    {
        return DB::transaction(function () use ($data) {
            // Validate data
            $validator = $this->validateNotificationData($data);
            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . $validator->errors()->first());
            }

            // Get recipients
            $recipients = $this->getRecipients($data);
            if (empty($recipients)) {
                throw new Exception('No recipients found for notification');
            }

            // Get notification template
            $template = $this->getNotificationTemplate($data['type']);
            if (!$template) {
                throw new Exception('Notification template not found');
            }

            // Generate notification content
            $content = $this->generateNotificationContent($template, $data);

            // Create notification record
            $notification = Notification::create([
                'type' => $data['type'],
                'title' => $content['title'],
                'message' => $content['message'],
                'data' => json_encode($data['data'] ?? []),
                'priority' => $data['priority'] ?? 'normal',
                'channels' => json_encode($data['channels'] ?? ['database']),
                'recipients_count' => count($recipients),
                'sent_by' => auth()->id(),
                'sent_at' => now(),
                'expires_at' => $data['expires_at'] ?? Carbon::now()->addDays(30),
                'is_read' => false,
            ]);

            // Send to recipients
            $this->sendToRecipients($notification, $recipients, $data['channels'] ?? ['database']);

            // Log activity
            $this->logActivity('send_notification', $notification, 'Notification sent');

            return $notification;
        });
    }

    /**
     * Send deadline reminder
     */
    public function sendDeadlineReminder(string $type, $instansiId = null, Carbon $deadline = null): array
    {
        $results = [];
        
        switch ($type) {
            case 'target_setting':
                $results = $this->sendTargetSettingReminder($instansiId, $deadline);
                break;
            case 'data_submission':
                $results = $this->sendDataSubmissionReminder($instansiId, $deadline);
                break;
            case 'assessment':
                $results = $this->sendAssessmentReminder($instansiId, $deadline);
                break;
            case 'report_generation':
                $results = $this->sendReportGenerationReminder($instansiId, $deadline);
                break;
        }

        return $results;
    }

    /**
     * Send achievement alert
     */
    public function sendAchievementAlert(PerformanceData $performanceData, string $thresholdType = 'below'): Notification
    {
        $achievement = $performanceData->achievement_percentage;
        $indicator = $performanceData->performanceIndicator;
        $instansi = $indicator->instansi;

        $data = [
            'type' => 'achievement_threshold',
            'title' => "Achievement Alert: {$indicator->name}",
            'message' => "Achievement for {$indicator->name} is {$achievement}% ({$thresholdType} threshold)",
            'data' => [
                'performance_data_id' => $performanceData->id,
                'indicator_id' => $indicator->id,
                'achievement' => $achievement,
                'threshold_type' => $thresholdType,
                'instansi_id' => $instansi->id,
            ],
            'priority' => 'high',
            'channels' => ['database', 'email'],
            'recipients' => $this->getAchievementAlertRecipients($instansi->id),
        ];

        return $this->sendNotification($data);
    }

    /**
     * Send validation notification
     */
    public function sendValidationNotification(string $type, $entity, string $status): Notification
    {
        $data = $this->prepareValidationNotificationData($type, $entity, $status);
        return $this->sendNotification($data);
    }

    /**
     * Send system alert
     */
    public function sendSystemAlert(string $alertType, string $message, array $details = []): Notification
    {
        $data = [
            'type' => 'system_alert',
            'title' => "System Alert: {$alertType}",
            'message' => $message,
            'data' => array_merge($details, [
                'alert_type' => $alertType,
                'timestamp' => now()->toDateTimeString(),
            ]),
            'priority' => 'high',
            'channels' => ['database', 'email'],
            'recipients' => $this->getSystemAlertRecipients(),
        ];

        return $this->sendNotification($data);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, array $filters = [], $perPage = 15)
    {
        $query = Notification::where('recipients', 'like', '%"' . $userId . '"%')
            ->orWhere('recipients', 'like', '%"all"%')
            ->orWhere('recipients', 'like', '%"role:' . $this->getUserRole($userId) . '"%');

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['is_read']) && $filters['is_read'] !== null) {
            $query->where('is_read', $filters['is_read']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('sent_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('sent_at', '<=', $filters['date_to']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'sent_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId): bool
    {
        $notification = Notification::find($notificationId);
        if (!$notification) {
            return false;
        }

        // Check if user is recipient
        if (!$this->isUserRecipient($notification, $userId)) {
            return false;
        }

        $notification->update(['is_read' => true]);
        
        $this->logActivity('mark_read', $notification, 'Notification marked as read');
        
        return true;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead($userId): int
    {
        $notifications = Notification::where('recipients', 'like', '%"' . $userId . '"%')
            ->orWhere('recipients', 'like', '%"all"%')
            ->orWhere('recipients', 'like', '%"role:' . $this->getUserRole($userId) . '"%')
            ->where('is_read', false)
            ->get();

        $count = $notifications->count();
        
        foreach ($notifications as $notification) {
            $notification->update(['is_read' => true]);
        }

        $this->logActivity('mark_all_read', null, "All notifications marked as read for user {$userId}");
        
        return $count;
    }

    /**
     * Delete notification
     */
    public function deleteNotification($notificationId, $userId): bool
    {
        $notification = Notification::find($notificationId);
        if (!$notification) {
            return false;
        }

        // Check if user has permission to delete
        if (!$this->canUserDeleteNotification($notification, $userId)) {
            return false;
        }

        $result = $notification->delete();
        
        $this->logActivity('delete_notification', $notification, 'Notification deleted');
        
        return $result;
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStatistics($userId = null): array
    {
        $cacheKey = $userId ? "notification_stats_user_{$userId}" : 'notification_stats_global';
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($userId) {
            $query = Notification::query();
            
            if ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('recipients', 'like', '%"' . $userId . '"%')
                        ->orWhere('recipients', 'like', '%"all"%')
                        ->orWhere('recipients', 'like', '%"role:' . $this->getUserRole($userId) . '"%');
                });
            }

            $total = $query->count();
            $unread = $query->where('is_read', false)->count();
            
            return [
                'total' => $total,
                'unread' => $unread,
                'read' => $total - $unread,
                'by_type' => $query->select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
                'by_priority' => $query->select('priority', DB::raw('count(*) as count'))
                    ->groupBy('priority')
                    ->pluck('count', 'priority')
                    ->toArray(),
            ];
        });
    }

    /**
     * Send target setting reminder
     */
    protected function sendTargetSettingReminder($instansiId = null, Carbon $deadline = null): array
    {
        $results = [];
        $instansis = $this->getInstansisWithPendingTargets($instansiId);

        foreach ($instansis as $instansi) {
            $data = [
                'type' => 'target_due',
                'title' => 'Target Setting Due',
                'message' => "Target setting is due for {$instansi->name}. Please complete target setting by {$deadline->format('Y-m-d')}",
                'data' => [
                    'instansi_id' => $instansi->id,
                    'deadline' => $deadline->toDateTimeString(),
                ],
                'priority' => 'high',
                'channels' => ['database', 'email'],
                'recipients' => $this->getTargetSettingRecipients($instansi->id),
            ];

            $results[] = $this->sendNotification($data);
        }

        return $results;
    }

    /**
     * Send data submission reminder
     */
    protected function sendDataSubmissionReminder($instansiId = null, Carbon $deadline = null): array
    {
        $results = [];
        $instansis = $this->getInstansisWithMissingData($instansiId);

        foreach ($instansis as $instansi) {
            $data = [
                'type' => 'data_submission_due',
                'title' => 'Data Submission Due',
                'message' => "Performance data submission is due for {$instansi->name}. Please submit data by {$deadline->format('Y-m-d')}",
                'data' => [
                    'instansi_id' => $instansi->id,
                    'deadline' => $deadline->toDateTimeString(),
                ],
                'priority' => 'high',
                'channels' => ['database', 'email'],
                'recipients' => $this->getDataSubmissionRecipients($instansi->id),
            ];

            $results[] = $this->sendNotification($data);
        }

        return $results;
    }

    /**
     * Send assessment reminder
     */
    protected function sendAssessmentReminder($instansiId = null, Carbon $deadline = null): array
    {
        $results = [];
        $assessments = $this->getPendingAssessments($instansiId);

        foreach ($assessments as $assessment) {
            $data = [
                'type' => 'assessment_due',
                'title' => 'Assessment Due',
                'message' => "Assessment is due for {$assessment->performanceIndicator->name}. Please complete assessment by {$deadline->format('Y-m-d')}",
                'data' => [
                    'assessment_id' => $assessment->id,
                    'instansi_id' => $assessment->instansi_id,
                    'deadline' => $deadline->toDateTimeString(),
                ],
                'priority' => 'high',
                'channels' => ['database', 'email'],
                'recipients' => $this->getAssessmentRecipients($assessment->instansi_id),
            ];

            $results[] = $this->sendNotification($data);
        }

        return $results;
    }

    /**
     * Send report generation reminder
     */
    protected function sendReportGenerationReminder($instansiId = null, Carbon $deadline = null): array
    {
        $results = [];
        $instansis = $this->getInstansisWithIncompleteReports($instansiId);

        foreach ($instansis as $instansi) {
            $data = [
                'type' => 'report_generation_due',
                'title' => 'Report Generation Due',
                'message' => "Report generation is due for {$instansi->name}. Please generate reports by {$deadline->format('Y-m-d')}",
                'data' => [
                    'instansi_id' => $instansi->id,
                    'deadline' => $deadline->toDateTimeString(),
                ],
                'priority' => 'high',
                'channels' => ['database', 'email'],
                'recipients' => $this->getReportGenerationRecipients($instansi->id),
            ];

            $results[] = $this->sendNotification($data);
        }

        return $results;
    }

    /**
     * Prepare validation notification data
     */
    protected function prepareValidationNotificationData(string $type, $entity, string $status): array
    {
        $data = [
            'type' => $this->getValidationNotificationType($type, $status),
            'priority' => $status === 'rejected' ? 'high' : 'normal',
            'channels' => ['database', 'email'],
        ];

        switch ($type) {
            case 'performance_data':
                $data['title'] = "Data {$status}";
                $data['message'] = "Performance data for {$entity->performanceIndicator->name} has been {$status}";
                $data['data'] = [
                    'performance_data_id' => $entity->id,
                    'indicator_id' => $entity->performance_indicator_id,
                    'validation_status' => $status,
                ];
                $data['recipients'] = $this->getDataValidationRecipients($entity->instansi_id);
                break;

            case 'assessment':
                $data['title'] = "Assessment {$status}";
                $data['message'] = "Assessment for {$entity->performanceIndicator->name} has been {$status}";
                $data['data'] = [
                    'assessment_id' => $entity->id,
                    'indicator_id' => $entity->performance_indicator_id,
                    'status' => $status,
                ];
                $data['recipients'] = $this->getAssessmentValidationRecipients($entity->instansi_id);
                break;

            case 'evidence_document':
                $data['title'] = "Evidence {$status}";
                $data['message'] = "Evidence document {$entity->file_name} has been {$status}";
                $data['data'] = [
                    'evidence_id' => $entity->id,
                    'validation_status' => $status,
                ];
                $data['recipients'] = $this->getEvidenceValidationRecipients($entity->instansi_id);
                break;

            case 'target':
                $data['title'] = "Target {$status}";
                $data['message'] = "Target for {$entity->performanceIndicator->name} has been {$status}";
                $data['data'] = [
                    'target_id' => $entity->id,
                    'indicator_id' => $entity->performance_indicator_id,
                    'approval_status' => $status,
                ];
                $data['recipients'] = $this->getTargetValidationRecipients($entity->instansi_id);
                break;
        }

        return $data;
    }

    /**
     * Get recipients based on notification data
     */
    protected function getRecipients(array $data): array
    {
        $recipients = [];

        if (isset($data['recipients'])) {
            if (is_array($data['recipients'])) {
                $recipients = $data['recipients'];
            } else {
                $recipients = [$data['recipients']];
            }
        }

        // Add recipients based on type and context
        if (isset($data['instansi_id'])) {
            $instansiRecipients = $this->getInstansiRecipients($data['instansi_id'], $data['type']);
            $recipients = array_merge($recipients, $instansiRecipients);
        }

        // Remove duplicates
        return array_unique($recipients);
    }

    /**
     * Get notification template
     */
    protected function getNotificationTemplate(string $type): ?NotificationTemplate
    {
        return NotificationTemplate::where('type', $type)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Generate notification content
     */
    protected function generateNotificationContent(NotificationTemplate $template, array $data): array
    {
        $placeholders = $data['data'] ?? [];
        
        return [
            'title' => $this->replacePlaceholders($template->title_template, $placeholders),
            'message' => $this->replacePlaceholders($template->message_template, $placeholders),
        ];
    }

    /**
     * Replace placeholders in template
     */
    protected function replacePlaceholders(string $template, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        
        return $template;
    }

    /**
     * Send notification to recipients
     */
    protected function sendToRecipients(Notification $notification, array $recipients, array $channels): void
    {
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'database':
                    $this->sendDatabaseNotification($notification, $recipients);
                    break;
                case 'email':
                    $this->sendEmailNotification($notification, $recipients);
                    break;
                case 'sms':
                    $this->sendSmsNotification($notification, $recipients);
                    break;
                case 'push':
                    $this->sendPushNotification($notification, $recipients);
                    break;
            }
        }
    }

    /**
     * Send database notification
     */
    protected function sendDatabaseNotification(Notification $notification, array $recipients): void
    {
        // Store notification for each recipient
        foreach ($recipients as $recipient) {
            // This would typically create a user_notification pivot record
            // For now, we'll just log it
            Log::info('Database notification sent', [
                'notification_id' => $notification->id,
                'recipient' => $recipient,
            ]);
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmailNotification(Notification $notification, array $recipients): void
    {
        // Get user emails
        $emails = User::whereIn('id', $recipients)->pluck('email')->toArray();
        
        if (!empty($emails)) {
            // This would typically use Laravel's Mail facade
            // For now, we'll just log it
            Log::info('Email notification sent', [
                'notification_id' => $notification->id,
                'recipients' => $emails,
                'subject' => $notification->title,
                'body' => $notification->message,
            ]);
        }
    }

    /**
     * Send SMS notification
     */
    protected function sendSmsNotification(Notification $notification, array $recipients): void
    {
        // Get user phone numbers
        $phones = User::whereIn('id', $recipients)
            ->whereNotNull('phone')
            ->pluck('phone')
            ->toArray();
        
        if (!empty($phones)) {
            // This would typically use an SMS service
            // For now, we'll just log it
            Log::info('SMS notification sent', [
                'notification_id' => $notification->id,
                'recipients' => $phones,
                'message' => $notification->message,
            ]);
        }
    }

    /**
     * Send push notification
     */
    protected function sendPushNotification(Notification $notification, array $recipients): void
    {
        // This would typically use a push notification service
        Log::info('Push notification sent', [
            'notification_id' => $notification->id,
            'recipients' => $recipients,
            'title' => $notification->title,
            'message' => $notification->message,
        ]);
    }

    /**
     * Get user role
     */
    protected function getUserRole($userId): string
    {
        $user = User::find($userId);
        return $user ? $user->role : 'user';
    }

    /**
     * Check if user is recipient
     */
    protected function isUserRecipient(Notification $notification, $userId): bool
    {
        $recipients = json_decode($notification->recipients, true) ?? [];
        
        return in_array($userId, $recipients) ||
               in_array('all', $recipients) ||
               in_array('role:' . $this->getUserRole($userId), $recipients);
    }

    /**
     * Check if user can delete notification
     */
    protected function canUserDeleteNotification(Notification $notification, $userId): bool
    {
        return $notification->sent_by === $userId || $this->isUserAdmin($userId);
    }

    /**
     * Check if user is admin
     */
    protected function isUserAdmin($userId): bool
    {
        $user = User::find($userId);
        return $user && in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Get validation notification type
     */
    protected function getValidationNotificationType(string $type, string $status): string
    {
        $typeMap = [
            'performance_data' => 'data_validated',
            'assessment' => 'assessment_completed',
            'evidence_document' => 'evidence_validated',
            'target' => 'target_approved',
        ];

        return $typeMap[$type] ?? 'system_alert';
    }

    /**
     * Get instansi recipients
     */
    protected function getInstansiRecipients($instansiId, $notificationType): array
    {
        // This would return users associated with the instansi based on notification type
        return User::where('instansi_id', $instansiId)->pluck('id')->toArray();
    }

    /**
     * Get target setting recipients
     */
    protected function getTargetSettingRecipients($instansiId): array
    {
        return User::where('instansi_id', $instansiId)
            ->whereIn('role', ['manager', 'admin'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get data submission recipients
     */
    protected function getDataSubmissionRecipients($instansiId): array
    {
        return User::where('instansi_id', $instansiId)
            ->whereIn('role', ['data_collector', 'manager'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get assessment recipients
     */
    protected function getAssessmentRecipients($instansiId): array
    {
        return User::where('instansi_id', $instansiId)
            ->whereIn('role', ['assessor', 'manager'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get report generation recipients
     */
    protected function getReportGenerationRecipients($instansiId): array
    {
        return User::where('instansi_id', $instansiId)
            ->whereIn('role', ['manager', 'admin'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get achievement alert recipients
     */
    protected function getAchievementAlertRecipients($instansiId): array
    {
        return User::where('instansi_id', $instansiId)
            ->whereIn('role', ['manager', 'assessor', 'admin'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get system alert recipients
     */
    protected function getSystemAlertRecipients(): array
    {
        return User::whereIn('role', ['admin', 'super_admin'])->pluck('id')->toArray();
    }

    /**
     * Get data validation recipients
     */
    protected function getDataValidationRecipients($instansiId): array
    {
        return User::where('instansi_id', $instansiId)
            ->whereIn('role', ['data_collector', 'manager'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get assessment validation recipients
     */
    protected function getAssessmentValidationRecipients($instansiId): array
    {
        return User::where('instansi_id', $instansiId)
            ->whereIn('role', ['assessor', 'manager'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get evidence validation recipients
     */
    protected function getEvidenceValidationRecipients($instansiId): array
    {
        return User::where('instansi_id', $instansiId)
            ->whereIn('role', ['data_collector', 'manager'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get target validation recipients
     */
    protected function getTargetValidationRecipients($instansiId): array
    {
        return User::where('instansi_id', $instansiId)
            ->whereIn('role', ['manager', 'admin'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get instansis with pending targets
     */
    protected function getInstansisWithPendingTargets($instansiId = null)
    {
        return Instansi::when($instansiId, fn($q) => $q->where('id', $instansiId))
            ->whereHas('performanceIndicators', function ($query) {
                $query->whereDoesntHave('targets', function ($q) {
                    $q->where('target_year', date('Y'))
                        ->where('approval_status', 'approved');
                });
            })
            ->get();
    }

    /**
     * Get instansis with missing data
     */
    protected function getInstansisWithMissingData($instansiId = null)
    {
        return Instansi::when($instansiId, fn($q) => $q->where('id', $instansiId))
            ->whereHas('performanceIndicators', function ($query) {
                $query->whereDoesntHave('performanceData', function ($q) {
                    $q->where('period_year', date('Y'))
                        ->where('period_month', date('n'))
                        ->where('validation_status', 'validated');
                });
            })
            ->get();
    }

    /**
     * Get pending assessments
     */
    protected function getPendingAssessments($instansiId = null)
    {
        return Assessment::when($instansiId, fn($q) => $q->where('instansi_id', $instansiId))
            ->where('status', 'pending')
            ->get();
    }

    /**
     * Get instansis with incomplete reports
     */
    protected function getInstansisWithIncompleteReports($instansiId = null)
    {
        return Instansi::when($instansiId, fn($q) => $q->where('id', $instansiId))
            ->whereDoesntHave('reports', function ($query) {
                $query->where('report_year', date('Y'))
                    ->where('report_period', $this->getCurrentPeriod())
                    ->where('status', 'completed');
            })
            ->get();
    }

    /**
     * Get current period
     */
    protected function getCurrentPeriod(): string
    {
        $month = date('n');
        return $month <= 6 ? 'first_semester' : 'second_semester';
    }

    /**
     * Validate notification data
     */
    protected function validateNotificationData(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'type' => 'required|string|in:' . implode(',', array_keys($this->notificationTypes)),
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'data' => 'nullable|array',
            'priority' => 'nullable|string|in:low,normal,high,critical',
            'channels' => 'nullable|array',
            'channels.*' => 'string|in:database,email,sms,push',
            'recipients' => 'required|array',
            'recipients.*' => 'string',
            'expires_at' => 'nullable|date',
        ]);
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, $notification, string $description): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'instansi_id' => null,
            'module' => 'notifications',
            'activity' => $action,
            'description' => $description,
            'old_values' => null,
            'new_values' => $notification ? $notification->toArray() : null,
        ]);
    }
}