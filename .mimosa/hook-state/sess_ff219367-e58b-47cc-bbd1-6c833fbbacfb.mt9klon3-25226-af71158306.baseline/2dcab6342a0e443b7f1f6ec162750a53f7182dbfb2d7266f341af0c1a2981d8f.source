<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemSettingsService
{
    private const CACHE_PREFIX = 'system_settings_';
    private const CACHE_TTL = 3600; // 1 hour
    
    public function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = SystemSetting::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return $this->castValue($setting->value, $setting->type);
        });
    }
    
    public function set(string $key, $value, string $type = 'string', string $description = null): SystemSetting
    {
        return DB::transaction(function () use ($key, $value, $type, $description) {
            $setting = SystemSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $this->serializeValue($value, $type),
                    'type' => $type,
                    'description' => $description,
                ]
            );
            
            $this->clearCache($key);
            
            return $setting;
        });
    }
    
    public function delete(string $key): bool
    {
        $result = SystemSetting::where('key', $key)->delete();
        
        if ($result) {
            $this->clearCache($key);
        }
        
        return (bool) $result;
    }
    
    public function getAll(): array
    {
        $settings = SystemSetting::all();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting->key] = $this->castValue($setting->value, $setting->type);
        }
        
        return $result;
    }
    
    public function getByModule(string $module): array
    {
        $settings = SystemSetting::where('key', 'LIKE', $module . '.%')->get();
        $result = [];
        
        foreach ($settings as $setting) {
            $key = str_replace($module . '.', '', $setting->key);
            $result[$key] = $this->castValue($setting->value, $setting->type);
        }
        
        return $result;
    }
    
    public function bulkUpdate(array $settings): void
    {
        DB::transaction(function () use ($settings) {
            foreach ($settings as $key => $data) {
                if (is_array($data)) {
                    $this->set($key, $data['value'], $data['type'] ?? 'string', $data['description'] ?? null);
                } else {
                    $this->set($key, $data);
                }
            }
        });
    }
    
    private function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'array':
            case 'json':
                return json_decode($value, true) ?? [];
            case 'string':
            default:
                return (string) $value;
        }
    }
    
    private function serializeValue($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
            case 'integer':
            case 'float':
            case 'string':
                return (string) $value;
            case 'array':
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }
    
    private function clearCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }
}