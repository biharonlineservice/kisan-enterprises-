<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Throwable;

class MailConfigService
{
    /**
     * Apply database-driven mail settings to the active Laravel runtime config.
     */
    public static function apply(): void
    {
        try {
            /** @var SettingsService $settings */
            $settings = app(SettingsService::class);
            $s = $settings->all();

            $mailer = $s['mail.mailer'] ?? config('mail.default', 'log');
            $host = $s['mail.host'] ?? config('mail.mailers.smtp.host', '127.0.0.1');
            $port = (int) ($s['mail.port'] ?? config('mail.mailers.smtp.port', 465));
            $encryption = strtolower((string) ($s['mail.encryption'] ?? 'ssl'));
            $username = $s['mail.username'] ?? config('mail.mailers.smtp.username');
            $password = $s['mail.password'] ?? config('mail.mailers.smtp.password');
            $fromAddress = $s['mail.from_address'] ?? config('mail.from.address', 'noreply@kisanenterprises.in');
            $fromName = $s['mail.from_name'] ?? config('mail.from.name', 'Kisan Enterprises');

            // Determine mail scheme for Symfony Mailer in Laravel 11/12
            $scheme = match ($encryption) {
                'ssl', 'smtps' => 'smtps',
                default => null,
            };

            config([
                'mail.default' => $mailer,
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
                'mail.mailers.smtp.scheme' => $scheme,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName,
            ]);

            // Purge cached transport so changes take immediate effect
            Mail::purge();
        } catch (Throwable) {
            // Keep existing environment configuration if settings are unavailable
        }
    }
}

