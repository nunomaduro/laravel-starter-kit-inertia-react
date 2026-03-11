<?php

return [
    'label' => 'Aktivitätsprotokoll',
    'plural_label' => 'Aktivitätsprotokolle',
    'table' => [
        'column' => [
            'log_name' => 'Protokollname',
            'event' => 'Ereignis',
            'subject_id' => 'Subjekt ID',
            'subject_type' => 'Subjekt Typ',
            'causer_id' => 'Verursacher ID',
            'causer_type' => 'Verursacher Typ',
            'properties' => 'Eigenschaften',
            'created_at' => 'Erstellt am',
            'updated_at' => 'Aktualisiert am',
            'description' => 'Beschreibung',
            'subject' => 'Subjekt',
            'causer' => 'Verursacher',
            'ip_address' => 'IP Adresse',
            'browser' => 'Browser',
            'id' => 'ID',
        ],
        'filter' => [
            'event' => 'Ereignis',
            'created_at' => 'Erstellt am',
            'created_from' => 'Erstellt von',
            'created_until' => 'Erstellt bis',
            'causer' => 'Verursacher',
            'subject_type' => 'Subjekt Typ',
            'batch' => 'Batch UUID',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Aktivitätsdetails',
        ],
        'tab' => [
            'overview' => 'Übersicht',
            'changes' => 'Änderungen',
            'raw_data' => 'Rohdaten',
            'old' => 'Alt',
            'new' => 'Neu',
        ],
        'entry' => [
            'log_name' => 'Protokollname',
            'event' => 'Ereignis',
            'created_at' => 'Erstellt am',
            'description' => 'Beschreibung',
            'subject' => 'Subjekt',
            'causer' => 'Verursacher',
            'ip_address' => 'IP Adresse',
            'browser' => 'Browser',
            'attributes' => 'Attribute',
            'old' => 'Alt',
            'key' => 'Schlüssel',
            'value' => 'Wert',
            'properties' => 'Eigenschaften',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Zeitstrahl',
            'empty_state_title' => 'Keine Aktivitätsprotokolle gefunden',
            'empty_state_description' => 'Es wurden noch keine Aktivitäten für diesen Datensatz aufgezeichnet.',
        ],
        'delete' => [
            'confirmation' => 'Sind Sie sicher, dass Sie dieses Aktivitätsprotokoll löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.',
            'heading' => 'Aktivitätsprotokoll löschen',
            'button' => 'Löschen',
        ],
        'revert' => [
            'heading' => 'Änderungen rückgängig machen',
            'confirmation' => 'Sind Sie sicher, dass Sie diese Änderung rückgängig machen möchten? Dies wird die alten Werte wiederherstellen.',
            'button' => 'Rückgängig machen',
            'success' => 'Änderungen erfolgreich rückgängig gemacht',
            'no_old_data' => 'Keine alten Daten verfügbar zum Rückgängigmachen',
            'subject_not_found' => 'Subjekt-Modell nicht gefunden',
            'label' => 'Rückgängig machen',
            'nothing_selected' => 'Keine Attribute zum Rückgängigmachen ausgewählt.',
            'helper_text' => 'Ändere von \':old\' zurück zu \':new\'',
        ],
        'export' => [
            'filename' => 'aktivitaetsprotokolle',
            'notification' => [
                'completed' => 'Ihr Aktivitätsprotokoll-Export wurde abgeschlossen und :successful_rows :rows_label wurden exportiert.',
                'failed_rows' => ':count :rows konnten nicht exportiert werden.',
            ],
        ],
        'restore' => [
            'label' => 'Wiederherstellen',
            'heading' => 'Datensatz wiederherstellen',
            'confirmation' => 'Sind Sie sicher, dass Sie diesen gelöschten Datensatz wiederherstellen möchten?',
            'success' => 'Datensatz erfolgreich wiederhergestellt.',
        ],
        'prune' => [
            'label' => 'Protokolle bereinigen',
            'heading' => 'Aktivitätsprotokolle bereinigen',
            'confirmation' => 'Sind Sie sicher, dass Sie Protokolle löschen möchten, die älter als das ausgewählte Datum sind? Diese Aktion kann nicht rückgängig gemacht werden.',
            'success' => ':count Aktivitätsprotokolle erfolgreich bereinigt.',
            'date' => 'Protokolle bereinigen, die älter sind als',
        ],
        'batch' => [
            'label' => 'Batch',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => 'Sind Sie sicher, dass Sie die ausgewählten Aktivitätsprotokolle löschen möchten?',
            ],
            'restore' => [
                'label' => 'Ausgewählte wiederherstellen',
                'confirmation' => 'Sind Sie sicher, dass Sie die ausgewählten gelöschten Datensätze wiederherstellen möchten?',
                'success' => ':count Datensätze erfolgreich wiederhergestellt.',
            ],
            'revert' => [
                'label' => 'Ausgewählte rückgängig machen',
                'confirmation' => 'Sind Sie sicher, dass Sie die Änderungen für alle ausgewählten Protokolle rückgängig machen möchten? Nur Protokolle mit alten Daten werden verarbeitet.',
                'success' => ':count Protokolle erfolgreich rückgängig gemacht.',
            ],
        ],
    ],
    'filters' => 'Filter',
    'pages' => [
        'user_activities' => [
            'title' => 'Benutzeraktivitäten',
            'heading' => 'Benutzeraktivitäten',
            'description_title' => 'Benutzeraktionen verfolgen',
            'description' => 'Sehen Sie alle Aktivitäten an, die von Benutzern in Ihrer Anwendung durchgeführt wurden. Filtern Sie nach Benutzer, Ereignistyp oder Subjekt, um eine vollständige Zeitleiste der Aktionen zu sehen.',
        ],
        'audit_dashboard' => [
            'title' => 'Audit Dashboard',
        ],
    ],
    'event' => [
        'created' => 'Erstellt',
        'updated' => 'Aktualisiert',
        'deleted' => 'Gelöscht',
        'restored' => 'Wiederhergestellt',
    ],
    'filter' => [
        'causer' => 'Benutzer',
        'event' => 'Ereignistyp',
        'subject_type' => 'Subjekttyp',
    ],
    'widgets' => [
        'latest_activity' => 'Neueste Aktivität',
        'activity_chart' => [
            'heading' => 'Aktivität im Zeitverlauf',
            'label' => 'Aktivitäten',
        ],
        'heatmap' => [
            'heading' => 'Aktivitäts-Heatmap',
            'less' => 'Weniger',
            'more' => 'Mehr',
            'tooltip' => ':count Aktivitäten am :date',
        ],
        'stats' => [
            'total_activities' => 'Gesamte Aktivitäten',
            'total_description' => 'Gesamte Protokolle im System',
            'top_causer' => 'Hauptverursacher',
            'top_causer_description' => ':count Aktivitäten',
            'top_subject' => 'Hauptsubjekt',
            'top_subject_description' => ':count Änderungen',
            'no_data' => 'Keine Daten',
        ],
    ],
    'dashboard' => [
        'title' => 'Audit Dashboard',
    ],
    'system' => 'System',
    'row' => 'Zeile',
    'rows' => 'Zeilen',
];
