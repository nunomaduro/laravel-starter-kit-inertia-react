<?php

return [
    'panel' => [
        'group' => 'Einstellungen',
        'label' => 'Funktionen verwalten',
        'title' => 'Funktionen & Segmente verwalten',
    ],

    'form' => [
        'feature' => 'Funktion',
        'scope' => 'Bereich',
        'status' => 'Status',
        'activate' => 'Aktivieren',
        'deactivate' => 'Deaktivieren',
        'unique_validation' => 'Funktionssegmentierung existiert bereits. Jeder Funktionsbereich kann nur ein aktiviertes Segment und ein deaktiviertes Segment haben. Um fortzufahren, ändern Sie bitte das vorhandene Segment oder entfernen Sie es und erstellen Sie ein neues.',
    ],

    'table' => [
        'title' => 'Titel',
        'segment' => 'Segment',
        'status' => 'Status',
        'activated' => 'AKTIVIERT',
        'deactivated' => 'DEAKTIVIERT',
    ],

    'actions' => [
        'create_segment' => 'Funktionssegment erstellen',
        'segment_feature' => 'Funktion segmentieren',
        'activate' => 'Aktivieren',
        'activate_description' => 'Diese Aktion aktiviert die ausgewählte Funktion für Benutzer.',
        'deactivate' => 'Deaktivieren',
        'deactivate_for_all' => 'Für alle deaktivieren',
        'deactivate_description' => 'Diese Aktion deaktiviert diese Funktion für Benutzer.',
        'purge' => 'Bereinigen',
        'purge_description' => 'Diese Aktion bereinigt aufgelöste Funktionen aus dem Speicher.',
        'all_features' => 'Alle Funktionen',
        'modify' => 'Ändern',
        'modify_heading' => 'Funktionssegment ändern',
        'remove' => 'Entfernen',
        'remove_heading' => 'Das Entfernen dieses Funktionssegments kann nicht rückgängig gemacht werden!',
    ],

    'notifications' => [
        'done' => 'Fertig!',
        'activated_for_all' => ':feature wurde für Benutzer aktiviert.',
        'deactivated_for_all' => ':feature wurde für Benutzer deaktiviert.',
        'all_features_purged' => 'Alle Funktionen wurden erfolgreich aus dem Speicher bereinigt.',
        'feature_purged' => 'Funktion :feature wurde erfolgreich aus dem Speicher bereinigt.',
    ],
];
