<?php

return [
    'title' => 'System Audit & Security Logs',
    'subtitle' => 'Immutable forensic trail of all staff activities, system changes, authentication events, and data mutations.',

    // Banner Chips
    'banner_total' => ':count Audit Records',
    'banner_today' => ':count Today',
    'banner_users' => ':count Staff Tracked',
    'banner_immutable' => 'Append-Only Ledger',

    // Action shortcuts
    'btn_users' => 'Users & Staff',
    'btn_roles' => 'Roles & Security',
    'btn_settings' => 'System Settings',
    'btn_reports' => 'Activity Reports',

    // 4 KPI Metric Cards
    'kpi_total' => 'Total Audit Events',
    'kpi_total_sub' => 'Immutable compliance trail',
    'kpi_today' => "Today's Events",
    'kpi_today_sub' => 'Recorded since midnight',
    'kpi_users' => 'Active Operators',
    'kpi_users_sub' => 'Staff with logged activity',
    'kpi_mutations' => 'State Mutations',
    'kpi_mutations_sub' => 'Changes with payload diff',

    // Tabs
    'tab_all' => 'All Activity',
    'tab_today' => "Today's Trail",
    'tab_mutations' => 'Data Changes',
    'tab_security' => 'Security & System',

    // Filters
    'filter_from' => 'From Date',
    'filter_to' => 'To Date',
    'filter_user' => 'Staff User',
    'filter_all_users' => 'All Users',
    'filter_action' => 'Action Type',
    'filter_all_actions' => 'All Actions',
    'btn_filter' => 'Filter',
    'btn_reset' => 'Reset',

    // Table Columns
    'col_timestamp' => 'Timestamp',
    'col_user' => 'Staff User',
    'col_action' => 'Action Event',
    'col_subject' => 'Subject Entity',
    'col_description' => 'Activity Description',
    'col_ip' => 'IP & Device',
    'col_details' => 'Action',
    'system_user' => 'System / Anonymous',
    'empty_title' => 'No audit records found',
    'empty_text' => 'No system activities matched the selected filter criteria.',

    // Show View
    'show_title' => 'Audit Log Record #:id',
    'show_subtitle' => 'Forensic view of logged system action and immutable property differences.',
    'btn_back' => 'Back to Audit Logs',
    'card_metadata' => 'Action Metadata',
    'card_payload' => 'State Changes / Payload Diff',
    'field_log_id' => 'Log Entry ID',
    'field_timestamp' => 'Exact Timestamp',
    'field_user' => 'Acting User',
    'field_action' => 'Action Event',
    'field_subject' => 'Auditable Subject',
    'field_desc' => 'Description',
    'field_ip' => 'IP Address',
    'field_ua' => 'User Agent / Device',
    'diff_before' => 'Previous State (Before)',
    'diff_after' => 'New State (After)',
    'no_diff' => 'This event did not modify recorded data attributes.',
];

