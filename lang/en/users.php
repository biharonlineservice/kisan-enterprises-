<?php

return [
    'title' => 'User & Staff Management',
    'subtitle' => 'Create and manage internal staff accounts, roles, permission scopes, and system access states.',
    'banner_total' => ':count Staff Accounts',
    'banner_active' => ':count Active',
    'banner_admins' => ':count Admins',
    'banner_recent_login' => ':count Signed In (7d)',

    // Action buttons & shortcuts
    'btn_add_user' => 'Add New User',
    'btn_roles' => 'Roles & Permissions',
    'btn_audit_logs' => 'Audit Trail Logs',
    'btn_settings' => 'System Settings',
    'btn_export_csv' => 'Export CSV',

    // 4 KPI Metric Cards
    'kpi_total_users' => 'Total Staff Users',
    'kpi_total_users_sub' => 'Enterprise employee accounts',
    'kpi_active_users' => 'Active Staff',
    'kpi_active_users_sub' => 'Authorized system access',
    'kpi_admins' => 'System Administrators',
    'kpi_admins_sub' => 'Full administrative authority',
    'kpi_temp_passwords' => 'Temporary Passwords',
    'kpi_temp_passwords_sub' => 'Pending initial password change',

    // Filter Tabs
    'tab_all' => 'All Staff',
    'tab_active' => 'Active Accounts',
    'tab_inactive' => 'Inactive',
    'tab_admins' => 'Administrators',
    'tab_temp_pwd' => 'Temp Password',

    // Search & Filters
    'filter_search' => 'Search Staff',
    'search_placeholder' => 'Search name, mobile, email, or designation...',
    'filter_role' => 'System Role',
    'role_all' => 'All Roles',
    'filter_status' => 'Status',
    'status_all' => 'All Statuses',
    'status_active' => 'Active Only',
    'status_inactive' => 'Inactive Only',
    'btn_filter' => 'Filter',
    'btn_reset' => 'Reset',

    // Table Columns
    'col_user' => 'Staff Member / Contact',
    'col_role' => 'System Role',
    'col_designation' => 'Designation',
    'col_status' => 'Account Status',
    'col_last_active' => 'Last Activity',
    'col_actions' => 'Actions',

    // Badges & Labels
    'status_active_pill' => 'Active',
    'status_inactive_pill' => 'Inactive',
    'temp_pwd_pill' => 'Temp Pwd',
    'never_logged_in' => 'Never logged in',
    'btn_edit' => 'Edit Staff',
    'btn_activate' => 'Activate',
    'btn_deactivate' => 'Deactivate',
    'confirm_toggle' => 'Are you sure you want to change the status of this user account?',
    'empty_title' => 'No user accounts found',
    'empty_text' => 'No staff members matched your query or filter criteria.',
    'btn_create_first' => 'Create First User',

    // Form labels
    'form_create_title' => 'Create Staff Account',
    'form_edit_title' => 'Edit Staff Account: :name',
    'form_create_subtitle' => 'Register a new team member with phone login credentials and role permissions.',
    'form_edit_subtitle' => 'Update staff profile details, department designation, and access permissions.',
    'label_name' => 'Full Name',
    'placeholder_name' => 'e.g. Ramesh Kumar',
    'label_mobile' => 'Mobile Number (Login ID)',
    'placeholder_mobile' => '10-digit mobile number',
    'help_mobile' => 'Primary 10-digit mobile number used by staff to sign into ERP.',
    'label_email' => 'Email Address',
    'placeholder_email' => 'Optional — e.g. ramesh@kisan.local',
    'help_email' => 'Optional. Used for communications and account notices.',
    'label_designation' => 'Designation / Title',
    'placeholder_designation' => 'e.g. Accounts Manager, Mandi In-Charge',
    'label_role' => 'System Role',
    'help_role' => 'Controls module access, financial authority, and data visibility.',
    'label_is_active' => 'Account is Active',
    'help_is_active' => 'Inactive users are immediately blocked from logging in.',
    'section_password' => 'Security & Password Setup',
    'label_password' => 'Password',
    'placeholder_password' => 'Minimum 8 characters',
    'label_password_confirmation' => 'Confirm Password',
    'placeholder_password_confirmation' => 'Repeat password',
    'help_password_create' => 'Temporary password. Staff will be required to change this upon first login.',
    'help_password_edit' => 'Leave blank to keep the current password.',
    'btn_save' => 'Save Changes',
    'btn_create' => 'Create Staff User',
    'btn_cancel' => 'Cancel',
    'btn_back' => 'Back to Users',
];

