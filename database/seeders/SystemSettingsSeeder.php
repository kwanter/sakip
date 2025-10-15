<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\SystemSettingsService;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = app(SystemSettingsService::class);

        // Seed default application name
        $existingName = \App\Models\SystemSetting::where('key', 'app.name')->first();
        if (!$existingName) {
            $service->set('app.name', 'SAKIP System', 'string', 'Application name');
        }

        // Seed default application description
        $existingDesc = \App\Models\SystemSetting::where('key', 'app.description')->first();
        if (!$existingDesc) {
            $service->set('app.description', 'Sistem Akuntabilitas Kinerja Instansi Pemerintah', 'string', 'Application description');
        }
    }
}