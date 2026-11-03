<?php

namespace App\Services;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Database-driven business settings with config/kisan.php defaults.
 * Values are cached as one array and the cache is flushed on every save.
 */
class SettingsService
{
    private const CACHE_KEY = 'kisan.settings';

    private ?array $loaded = null;

    public function all(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }

        $defaults = config('kisan.settings', []);

        try {
            $stored = Cache::rememberForever(self::CACHE_KEY, function () {
                if (! Schema::hasTable('business_settings')) {
                    return [];
                }

                return BusinessSetting::query()->pluck('value', 'key')->all();
            });
        } catch (Throwable) {
            // Database not reachable yet (e.g. during installation) — fall back to defaults.
            $stored = [];
        }

        return $this->loaded = array_merge($defaults, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public function bool(string $key): bool
    {
        return in_array((string) $this->get($key, '0'), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Persist several settings and return the [old, new] values that actually changed.
     *
     * @param  array<string,mixed>  $values
     * @return array{0: array, 1: array}
     */
    public function setMany(array $values, ?int $userId = null): array
    {
        $current = $this->all();
        $old = [];
        $new = [];

        foreach ($values as $key => $value) {
            $value = is_bool($value) ? ($value ? '1' : '0') : ($value === null ? null : (string) $value);
            if (($current[$key] ?? null) === $value) {
                continue;
            }
            BusinessSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => explode('.', $key)[0], 'updated_by' => $userId]
            );
            $old[$key] = $current[$key] ?? null;
            $new[$key] = $value;
        }

        $this->flush();

        return [$old, $new];
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->loaded = null;
    }
}
