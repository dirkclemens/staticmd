<?php

return [
    'lang_code' => 'en',
    'admin' => [
        'brand' => 'StaticMD Admin',
        'common' => [
            'dashboard' => 'Dashboard',
            'files' => 'Files',
            'new_page' => 'New page',
            'editor' => 'Editor',
            'settings' => 'Settings',
            'view_site' => 'View site',
            'logout' => 'Logout',
            'session' => 'Session',
            'cancel' => 'Cancel',
            'save' => 'Save',
            'delete' => 'Delete',
            'back_to_dashboard' => 'Back to Dashboard',
        ],
        'login' => [
            'title' => 'Please sign in',
            'username' => 'Username',
            'password' => 'Password',
            'signin' => 'Sign in',
            'back_to_site' => 'Back to website',
            'logged_out' => 'You have been logged out successfully.',
            'invalid' => 'Invalid credentials or CSRF token.'
        ],
        'alerts' => [
            'saved' => 'File saved successfully.',
            'deleted' => 'File deleted successfully.',
            'success' => 'Action completed successfully.',
            'settings_saved' => 'Settings have been saved successfully.'
        ],
        'errors' => [
            'save_failed' => 'Error saving file.',
            'delete_failed' => 'Error deleting file.',
            'no_file' => 'No file specified.',
            'file_not_found' => 'File not found.',
            'no_permission' => 'No permission to delete.',
            'invalid_file' => 'Invalid filename.',
            'csrf_invalid' => 'Invalid security token.',
            'invalid_request' => 'Invalid request.',
            'settings_save_failed' => 'Error saving settings.',
            'generic' => 'An error occurred.'
        ],
        'dashboard' => [
            'stats' => [
                'total_pages' => 'Total pages',
                'disk_usage' => 'Disk usage',
                'php_version' => 'PHP version',
                'memory_limit' => 'Memory limit'
            ],
            'recent' => 'Recently edited',
            'columns' => [
                'file' => 'File',
                'route' => 'Route',
                'modified' => 'Modified',
                'actions' => 'Actions'
            ],
            'buttons' => [
                'new_page' => 'New page',
                'files' => 'Files',
                'edit' => 'Edit',
                'view' => 'View',
                'delete' => 'Delete'
            ],
            'empty' => 'No files yet.'
        ],
        'delete_modal' => [
            'title' => 'Delete file',
            'question' => 'Are you sure you want to delete this file?',
            'warning' => 'This action cannot be undone!',
            'cancel' => 'Cancel',
            'confirm' => 'Yes, delete'
        ],
        'session' => [
            'expired_alert' => 'Your session has expired. You will be redirected to the login page.'
        ],
        'settings' => [
            'title' => 'System settings',
            'website' => 'Website settings',
            'website_name' => 'Website name',
            'logo_url' => 'Logo URL',
            'frontend_theme' => 'Website theme',
            'theme_preview' => 'Theme preview',
            'visit_frontend' => 'Visit the frontend to see the selected theme',
            'dashboard' => 'Dashboard settings',
            'recent_files_count' => 'Number of recently edited files',
            'show_file_stats' => 'Show file statistics on dashboard',
            'editor' => 'Editor settings',
            'editor_theme' => 'Editor theme',
            'auto_save_interval' => 'Auto-save interval',
            'navigation' => 'Navigation ordering',
            'navigation_hint' => 'Define the order of the main navigation',
            'navigation_order' => 'Navigation order',
            'actions_saved_in' => 'Settings are stored in',
            'save_settings' => 'Save settings',
            'language' => 'Language',
            'language_help' => 'Choose the language for the admin interface'
        ]
    ]
];
