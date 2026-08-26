<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemSettingsService;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    protected $settingsService;

    public function __construct(SystemSettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        $this->middleware('can:manage-high-level-settings');
    }

    /**
     * Display system settings page
     */
    public function index(Request $request)
    {
        $this->authorize('manage-high-level-settings');

        $settings = SystemSetting::all();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        $this->authorize('manage-high-level-settings');

        $rules = [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'settings.*.type' => 'required|string|in:string,integer,float,boolean,array,json',
            'settings.*.description' => 'nullable|string',
            // Specific application settings constraints
            'settings.app\.name.value' => 'sometimes|required|string|max:150',
            'settings.app\.description.value' => 'sometimes|nullable|string|max:500',
        ];

        $validated = $request->validate($rules);

        foreach ($validated['settings'] as $setting) {
            $this->settingsService->set(
                $setting['key'],
                $setting['value'],
                $setting['type'],
                $setting['description'] ?? null,
            );
        }

        // Log the settings update
        \Log::info('System settings updated', [
            'user' => auth()->user()->name,
            'settings' => array_column($validated['settings'], 'key'),
        ]);

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
