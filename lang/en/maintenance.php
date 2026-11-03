<?php

return [
    'title' => 'Database Backup & System Maintenance',
    'subtitle' => 'Generate on-demand database snapshots, download SQL dumps, and inspect server environment.',

    // Banner Chips
    'banner_backups' => ':count Backups Stored',
    'banner_size' => 'Archive: :size',
    'banner_env' => 'PHP :php • :db',
    'banner_disk' => 'Free Disk: :space',

    // Quick action buttons
    'btn_create_backup' => 'Create Database Backup Now',
    'btn_settings' => 'System Settings',
    'btn_audit_logs' => 'Audit Logs',
    'btn_reports' => 'Activity Reports',

    // 4 KPI Metric Cards
    'kpi_backups' => 'Stored SQL Snapshots',
    'kpi_backups_sub' => 'Total archive files',
    'kpi_size' => 'Backup Storage Footprint',
    'kpi_size_sub' => 'Inside storage/app/backups',
    'kpi_database' => 'Active Database',
    'kpi_database_sub' => 'Engine: :driver',
    'kpi_disk' => 'Server Free Space',
    'kpi_disk_sub' => 'Hosting disk capacity',

    // Tabs
    'tab_backups' => 'Database Backups',
    'tab_recovery' => 'Restoration Guide',
    'tab_automation' => 'Cron Automation',
    'tab_telemetry' => 'Server Telemetry',
    'tab_all' => 'All Modules (Overview)',

    // Backups Table
    'table_title' => 'Stored Database Backups (:count)',
    'col_filename' => 'Backup Filename',
    'col_size' => 'File Size',
    'col_created' => 'Created Timestamp',
    'col_actions' => 'Actions',
    'btn_download' => 'Download',
    'btn_delete' => 'Delete',
    'confirm_create' => 'Generate an on-demand SQL backup now?',
    'confirm_delete' => 'Delete this backup snapshot permanently?',
    'empty_title' => 'No database backup files found',
    'empty_text' => 'Click "Create Database Backup Now" to generate your first SQL snapshot.',

    // Restoration Section
    'restore_title' => 'Disaster Recovery & Database Restoration Guide',
    'restore_desc' => 'In the unlikely event of data corruption, server migration, or disaster recovery, follow these standard commands to restore any generated SQL snapshot:',
    'restore_caution' => 'Restoring an older SQL backup replaces all existing database tables with the contents of that backup snapshot. Always create a fresh backup before restoring an older state.',

    // Automation Section
    'cron_title' => 'Automated Nightly Backups',
    'cron_desc' => 'To automate daily midnight backups on Hostinger or any cPanel shared hosting, add a Cron Job in your hosting control panel:',
    'cron_schedule' => 'Hostinger / cPanel Cron Schedule:',
    'cron_schedule_sub' => 'Runs automatically at 2:00 AM every night during off-peak trading hours.',
    'cron_command' => 'Hostinger / cPanel Command:',
    'retention_title' => 'Recommended Retention Policy:',
    'retention_daily' => 'Daily backups kept for 30 days.',
    'retention_monthly' => 'End-of-month backups archived for 1 year.',
    'retention_offsite' => 'Download monthly backups to your local office computer for offsite redundancy.',
    'audit_notice' => 'All backup creation and deletion actions are recorded in the system audit log with the acting admin credentials and timestamp.',

    // Telemetry Section
    'telemetry_title' => 'Server Environment & Telemetry',
    'field_php' => 'PHP Version',
    'field_laravel' => 'Laravel Framework',
    'field_os' => 'Operating System',
    'field_server' => 'Web Server Software',
    'field_db_driver' => 'Database Driver',
    'field_db_name' => 'Database Name',
    'field_disk_free' => 'Available Disk Capacity',
];

