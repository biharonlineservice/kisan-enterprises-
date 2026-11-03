<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WebsiteContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'section',
        'title',
        'content',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("website.content.{$key}", function () use ($key, $default) {
            $row = static::where('key', $key)->first();

            return $row ? $row->content : $default;
        });
    }

    public static function getTitle(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("website.title.{$key}", function () use ($key, $default) {
            $row = static::where('key', $key)->first();

            return $row ? $row->title : $default;
        });
    }

    public static function set(string $key, string $section, ?string $title, ?string $content): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['section' => $section, 'title' => $title, 'content' => $content]
        );

        Cache::forget("website.content.{$key}");
        Cache::forget("website.title.{$key}");
    }
}
