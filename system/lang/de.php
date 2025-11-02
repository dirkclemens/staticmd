<?php

return [
    'lang_code' => 'de',
    'admin' => [
        'brand' => 'StaticMD Admin',
        'common' => [
            'dashboard' => 'Dashboard',
            'files' => 'Dateien',
            'new_page' => 'Neue Seite',
            'editor' => 'Editor',
            'settings' => 'Einstellungen',
            'view_site' => 'Zur Website',
            'logout' => 'Abmelden',
            'session' => 'Session',
            'cancel' => 'Abbrechen',
            'save' => 'Speichern',
            'delete' => 'Löschen',
            'back_to_dashboard' => 'Zurück zum Dashboard',
        ],
        'login' => [
            'title' => 'Bitte melden Sie sich an',
            'username' => 'Benutzername',
            'password' => 'Passwort',
            'signin' => 'Anmelden',
            'back_to_site' => 'Zurück zur Website',
            'logged_out' => 'Sie wurden erfolgreich abgemeldet.',
            'invalid' => 'Ungültige Anmeldedaten oder CSRF-Token.'
        ],
        'alerts' => [
            'saved' => 'Datei wurde erfolgreich gespeichert.',
            'deleted' => 'Datei wurde erfolgreich gelöscht.',
            'success' => 'Aktion wurde erfolgreich ausgeführt.',
            'settings_saved' => 'Einstellungen wurden erfolgreich gespeichert.'
        ],
        'errors' => [
            'save_failed' => 'Fehler beim Speichern der Datei.',
            'delete_failed' => 'Fehler beim Löschen der Datei.',
            'no_file' => 'Keine Datei angegeben.',
            'file_not_found' => 'Datei wurde nicht gefunden.',
            'no_permission' => 'Keine Berechtigung zum Löschen.',
            'invalid_file' => 'Ungültiger Dateiname.',
            'csrf_invalid' => 'Sicherheitstoken ungültig.',
            'invalid_request' => 'Ungültige Anfrage.',
            'settings_save_failed' => 'Fehler beim Speichern der Einstellungen.',
            'generic' => 'Ein Fehler ist aufgetreten.'
        ],
        'dashboard' => [
            'stats' => [
                'total_pages' => 'Gesamt Seiten',
                'disk_usage' => 'Speicherplatz',
                'php_version' => 'PHP Version',
                'memory_limit' => 'Memory Limit'
            ],
            'recent' => 'Zuletzt bearbeitet',
            'columns' => [
                'file' => 'Datei',
                'route' => 'Route',
                'modified' => 'Geändert',
                'actions' => 'Aktionen'
            ],
            'buttons' => [
                'new_page' => 'Neue Seite',
                'files' => 'Dateien',
                'edit' => 'Bearbeiten',
                'view' => 'Ansehen',
                'delete' => 'Löschen'
            ],
            'empty' => 'Noch keine Dateien vorhanden.'
        ],
        'delete_modal' => [
            'title' => 'Datei löschen',
            'question' => 'Sind Sie sicher, dass Sie diese Datei löschen möchten?',
            'warning' => 'Diese Aktion kann nicht rückgängig gemacht werden!',
            'cancel' => 'Abbrechen',
            'confirm' => 'Ja, löschen'
        ],
        'session' => [
            'expired_alert' => 'Ihre Session ist abgelaufen. Sie werden zur Login-Seite weitergeleitet.'
        ],
        'settings' => [
            'title' => 'System-Einstellungen',
            'website' => 'Website-Einstellungen',
            'website_name' => 'Website-Name',
            'logo_url' => 'Logo-URL',
            'frontend_theme' => 'Website-Theme',
            'theme_preview' => 'Theme-Vorschau',
            'visit_frontend' => 'Besuchen Sie die Frontend-Seite um das gewählte Theme zu sehen',
            'dashboard' => 'Dashboard-Einstellungen',
            'recent_files_count' => 'Anzahl zuletzt bearbeiteter Dateien',
            'show_file_stats' => 'Datei-Statistiken im Dashboard anzeigen',
            'editor' => 'Editor-Einstellungen',
            'editor_theme' => 'Editor-Theme',
            'auto_save_interval' => 'Auto-Save Intervall',
            'navigation' => 'Navigation-Sortierung',
            'navigation_hint' => 'Bestimme die Reihenfolge der Hauptnavigation',
            'navigation_order' => 'Navigation-Reihenfolge',
            'actions_saved_in' => 'Einstellungen werden gespeichert in',
            'save_settings' => 'Einstellungen speichern',
            'language' => 'Sprache',
            'language_help' => 'Sprache für die Admin-Oberfläche wählen'
        ]
    ]
];
