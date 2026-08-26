<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Models\Assessment;
use App\Models\Report;
use App\Models\PerformanceData;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Notification Service
 * 
 * Handles notification management for SAKIP module including email notifications,
 * system notifications, and user alerts.
 */
class NotificationService
{
    /**
     * Send notification to user
     */
    public function sendNotification($userId, $type, $title, $message, $data = null)
    {
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data ? json_encode($data) : null,
                'is_read' => false,
                'created_at' => now(),
            ]);

            // Send email notification if user has email notifications enabled
            $user = User::find($userId);
            if ($user && $user->email_notifications_enabled) {
                $this->sendEmailNotification($user, $notification);
            }

            return $notification;

        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification($user, $notification)
    {
        try {
            Mail::raw($notification->message, function ($message) use ($user, $notification) {
                $message->to($user->email)
                        ->subject($notification->title);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send email notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify assessment submission
     */
    public function notifyAssessmentSubmission($assessment)
    {
        try {
            // Notify assessor
            $this->sendNotification(
                $assessment->assessor_id,
                'assessment_submitted',
                'Penilaian Dikirim',
                "Penilaian untuk indikator {$assessment->indicator->name} telah berhasil dikirim.",
                ['assessment_id' => $assessment->id]
            );

            // Notify approvers
            $approvers = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'superadmin']);
            })->where('instansi_id', $assessment->instansi_id)->get();

            foreach ($approvers as $approver) {
                $this->sendNotification(
                    $approver->id,
                    'assessment_submitted',
                    'Penilaian Baru Menunggu Persetujuan',
                    "Penilaian baru untuk indikator {$assessment->indicator->name} menunggu persetujuan Anda.",
                    ['assessment_id' => $assessment->id]
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to notify assessment submission: ' . $e->getMessage());
        }
    }

    /**
     * Notify assessment review
     */
    public function notifyAssessmentReview($assessment, $decision, $notes = null)
    {
        try {
            $status = $decision === 'approved' ? 'Disetujui' : 'Ditolak';
            $message = "Penilaian untuk indikator {$assessment->indicator->name} telah {$status}.";
            
            if ($notes) {
                $message .= " Catatan: {$notes}";
            }

            // Notify assessor
            $this->sendNotification(
                $assessment->assessor_id,
                'assessment_reviewed',
                "Penilaian {$status}",
                $message,
                ['assessment_id' => $assessment->id, 'decision' => $decision]
            );

            // Notify creator if different from assessor
            if ($assessment->created_by && $assessment->created_by !== $assessment->assessor_id) {
                $this->sendNotification(
                    $assessment->created_by,
                    'assessment_reviewed',
                    "Penilaian {$status}",
                    $message,
                    ['assessment_id' => $assessment->id, 'decision' => $decision]
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to notify assessment review: ' . $e->getMessage());
        }
    }

    /**
     * Notify report submission
     */
    public function notifyReportSubmission($report)
    {
        try {
            // Notify report creator
            $this->sendNotification(
                $report->created_by,
                'report_submitted',
                'Laporan Dikirim',
                "Laporan {$report->title} telah berhasil dikirim untuk persetujuan.",
                ['report_id' => $report->id]
            );

            // Notify approvers
            $approvers = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'superadmin']);
            })->where('instansi_id', $report->instansi_id)->get();

            foreach ($approvers as $approver) {
                $this->sendNotification(
                    $approver->id,
                    'report_submitted',
                    'Laporan Baru Menunggu Persetujuan',
                    "Laporan baru {$report->title} menunggu persetujuan Anda.",
                    ['report_id' => $report->id]
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to notify report submission: ' . $e->getMessage());
        }
    }

    /**
     * Notify report approval
     */
    public function notifyReportApproval($report, $decision, $notes = null)
    {
        try {
            $status = $decision === 'approved' ? 'Disetujui' : 'Ditolak';
            $message = "Laporan {$report->title} telah {$status}.";
            
            if ($notes) {
                $message .= " Catatan: {$notes}";
            }

            // Notify report creator
            $this->sendNotification(
                $report->created_by,
                'report_approved',
                "Laporan {$status}",
                $message,
                ['report_id' => $report->id, 'decision' => $decision]
            );

        } catch (\Exception $e) {
            Log::error('Failed to notify report approval: ' . $e->getMessage());
        }
    }

    /**
     * Notify data collection reminder
     */
    public function notifyDataCollectionReminder($indicator, $userId)
    {
        try {
            $this->sendNotification(
                $userId,
                'data_collection_reminder',
                'Pengingat Pengumpulan Data',
                "Jangan lupa untuk mengumpulkan data untuk indikator {$indicator->name}.",
                ['indicator_id' => $indicator->id]
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify data collection reminder: ' . $e->getMessage());
        }
    }

    /**
     * Notify compliance issue
     */
    public function notifyComplianceIssue($instansiId, $issue, $severity = 'medium')
    {
        try {
            $users = User::where('instansi_id', $instansiId)
                ->whereHas('roles', function($query) {
                    $query->whereIn('name', ['admin', 'superadmin']);
                })->get();

            foreach ($users as $user) {
                $this->sendNotification(
                    $user->id,
                    'compliance_issue',
                    'Masalah Kepatuhan',
                    $issue,
                    ['severity' => $severity, 'instansi_id' => $instansiId]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify compliance issue: ' . $e->getMessage());
        }
    }

    /**
     * Notify deadline approaching
     */
    public function notifyDeadlineApproaching($type, $item, $daysRemaining)
    {
        try {
            $users = $this->getResponsibleUsers($type, $item);

            foreach ($users as $user) {
                $message = "Batas waktu untuk {$type} akan berakhir dalam {$daysRemaining} hari.";
                
                $this->sendNotification(
                    $user->id,
                    'deadline_approaching',
                    'Peringatan Batas Waktu',
                    $message,
                    ['type' => $type, 'item_id' => $item->id, 'days_remaining' => $daysRemaining]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify deadline approaching: ' . $e->getMessage());
        }
    }

    /**
     * Get responsible users for different types
     */
    private function getResponsibleUsers($type, $item)
    {
        switch ($type) {
            case 'target':
                return User::where('instansi_id', $item->instansi_id)
                    ->where(function($q) use ($item) {
                        $q->where('id', $item->created_by)
                          ->orWhereHas('roles', function($q) {
                              $q->whereIn('name', ['admin', 'superadmin']);
                          });
                    })->get();

            case 'assessment':
                return User::where('id', $item->assessor_id)->get();

            case 'report':
                return User::where('id', $item->created_by)->get();

            default:
                return collect();
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        try {
            $notification = Notification::find($notificationId);
            
            if ($notification && $notification->user_id === auth()->id()) {
                $notification->update(['is_read' => true]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}