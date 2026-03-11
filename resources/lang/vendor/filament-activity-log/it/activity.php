<?php

return [
    'label' => 'Registro Attività',
    'plural_label' => 'Registri Attività',
    'table' => [
        'column' => [
            'log_name' => 'Nome Registro',
            'event' => 'Evento',
            'subject_id' => 'ID Soggetto',
            'subject_type' => 'Tipo Soggetto',
            'causer_id' => 'ID Autore',
            'causer_type' => 'Tipo Autore',
            'properties' => 'Proprietà',
            'created_at' => 'Creato Il',
            'updated_at' => 'Aggiornato Il',
            'description' => 'Descrizione',
            'subject' => 'Soggetto',
            'causer' => 'Autore',
            'ip_address' => 'Indirizzo IP',
            'browser' => 'Browser',
            'id' => 'ID',
        ],
        'filter' => [
            'event' => 'Evento',
            'created_at' => 'Creato Il',
            'created_from' => 'Creato Dal',
            'created_until' => 'Creato Fino Al',
            'causer' => 'Autore',
            'subject_type' => 'Tipo Soggetto',
            'batch' => 'UUID Batch',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Dettagli Attività',
        ],
        'tab' => [
            'overview' => 'Panoramica',
            'changes' => 'Modifiche',
            'raw_data' => 'Dati Grezzi',
            'old' => 'Vecchio',
            'new' => 'Nuovo',
        ],
        'entry' => [
            'log_name' => 'Nome Registro',
            'event' => 'Evento',
            'created_at' => 'Creato Il',
            'description' => 'Descrizione',
            'subject' => 'Soggetto',
            'causer' => 'Autore',
            'ip_address' => 'Indirizzo IP',
            'browser' => 'Browser',
            'attributes' => 'Attributi',
            'old' => 'Vecchio',
            'key' => 'Chiave',
            'value' => 'Valore',
            'properties' => 'Proprietà',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Cronologia',
            'empty_state_title' => 'Nessun registro attività trovato',
            'empty_state_description' => 'Non ci sono ancora attività registrate per questo record.',
        ],
        'delete' => [
            'confirmation' => 'Sei sicuro di voler eliminare questo registro attività? Questa azione non può essere annullata.',
            'heading' => 'Elimina Registro Attività',
            'button' => 'Elimina',
        ],
        'revert' => [
            'heading' => 'Ripristina Modifiche',
            'confirmation' => 'Sei sicuro di voler ripristinare questa modifica? Questo ripristinerà i vecchi valori.',
            'button' => 'Ripristina',
            'success' => 'Modifiche ripristinate con successo',
            'no_old_data' => 'Nessun dato vecchio disponibile per il ripristino',
            'subject_not_found' => 'Modello del soggetto non trovato',
            'label' => 'Ripristina',
            'nothing_selected' => 'Nessun attributo selezionato per il ripristino.',
            'helper_text' => 'Passa da \':old\' a \':new\'',
        ],
        'export' => [
            'filename' => 'registri_attivita',
            'notification' => [
                'completed' => 'L\'esportazione del registro attività è stata completata e :successful_rows :rows_label sono state esportate.',
                'failed_rows' => ':count :rows non sono state esportate.',
            ],
        ],
        'restore' => [
            'label' => 'Ripristina',
            'heading' => 'Ripristina Record',
            'confirmation' => 'Sei sicuro di voler ripristinare questo record eliminato?',
            'success' => 'Record ripristinato con successo.',
        ],
        'prune' => [
            'label' => 'Pulisci Registri',
            'heading' => 'Pulisci Registri Attività',
            'confirmation' => 'Sei sicuro di voler eliminare i registri più vecchi della data selezionata? Questa azione non può essere annullata.',
            'success' => ':count registri attività puliti con successo.',
            'date' => 'Pulisci registri più vecchi di',
        ],
        'batch' => [
            'label' => 'Batch',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => 'Sei sicuro di voler eliminare i registri attività selezionati?',
            ],
            'restore' => [
                'label' => 'Ripristina Selezionati',
                'confirmation' => 'Sei sicuro di voler ripristinare i record eliminati selezionati?',
                'success' => ':count record ripristinati con successo.',
            ],
            'revert' => [
                'label' => 'Ripristina Selezionati',
                'confirmation' => 'Sei sicuro di voler annullare le modifiche per tutti i registri selezionati? Verranno elaborati solo i registri con dati vecchi.',
                'success' => ':count registri ripristinati con successo.',
            ],
        ],
    ],
    'filters' => 'Filtri',
    'pages' => [
        'user_activities' => [
            'title' => 'Attività Utente',
            'heading' => 'Attività Utente',
            'description_title' => 'Traccia Azioni Utente',
            'description' => 'Visualizza tutte le attività svolte dagli utenti nella tua applicazione. Filtra per utente, tipo di evento o soggetto per vedere una cronologia completa delle azioni.',
        ],
        'audit_dashboard' => [
            'title' => 'Dashboard di Audit',
        ],
    ],
    'event' => [
        'created' => 'Creato',
        'updated' => 'Aggiornato',
        'deleted' => 'Eliminato',
        'restored' => 'Ripristinato',
    ],
    'filter' => [
        'causer' => 'Utente',
        'event' => 'Tipo Evento',
        'subject_type' => 'Tipo Soggetto',
    ],
    'widgets' => [
        'latest_activity' => 'Attività Recente',
        'activity_chart' => [
            'heading' => 'Attività nel tempo',
            'label' => 'Attività',
        ],
        'heatmap' => [
            'heading' => 'Activity Heatmap',
            'less' => 'Meno',
            'more' => 'Più',
            'tooltip' => ':count attività il :date',
        ],
        'stats' => [
            'total_activities' => 'Attività totali',
            'total_description' => 'Registri totali nel sistema',
            'top_causer' => 'Autore principale',
            'top_causer_description' => ':count attività',
            'top_subject' => 'Soggetto principale',
            'top_subject_description' => ':count modifiche',
            'no_data' => 'Nessun dato',
        ],
    ],
    'dashboard' => [
        'title' => 'Audit Dashboard',
    ],
    'system' => 'Sistema',
    'row' => 'riga',
    'rows' => 'righe',
];
