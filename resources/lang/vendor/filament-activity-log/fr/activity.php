<?php

return [
    'label' => 'Journal d\'activité',
    'plural_label' => 'Journaux d\'activité',
    'table' => [
        'column' => [
            'log_name' => 'Nom du journal',
            'event' => 'Événement',
            'subject_id' => 'ID du sujet',
            'subject_type' => 'Type de sujet',
            'causer_id' => 'ID de cause',
            'causer_type' => 'Type de cause',
            'properties' => 'Propriétés',
            'created_at' => 'Créé le',
            'updated_at' => 'Mis à jour le',
            'description' => 'Description',
            'subject' => 'Sujet',
            'causer' => 'Cause',
            'id' => 'ID',
            'ip_address' => 'Adresse IP',
            'browser' => 'Navigateur',
        ],
        'filter' => [
            'event' => 'Événement',
            'created_at' => 'Créé le',
            'created_from' => 'Créé depuis',
            'created_until' => 'Créé jusqu\'à',
            'causer' => 'Cause',
            'subject_type' => 'Type de sujet',
            'batch' => 'UUID du lot',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Détails de l\'activité',
        ],
        'tab' => [
            'overview' => 'Aperçu',
            'changes' => 'Changements',
            'raw_data' => 'Données brutes',
            'old' => 'Ancien',
            'new' => 'Nouveau',
        ],
        'entry' => [
            'log_name' => 'Nom du journal',
            'event' => 'Événement',
            'created_at' => 'Créé le',
            'description' => 'Description',
            'subject' => 'Sujet',
            'causer' => 'Cause',
            'ip_address' => 'Adresse IP',
            'browser' => 'Navigateur',
            'attributes' => 'Attributs',
            'old' => 'Ancien',
            'key' => 'Clé',
            'value' => 'Valeur',
            'properties' => 'Propriétés',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Chronologie',
            'empty_state_title' => 'Aucun journal d\'activité trouvé',
            'empty_state_description' => 'Il n\'y a pas encore d\'activités enregistrées pour cet enregistrement.',
        ],
        'delete' => [
            'confirmation' => 'Êtes-vous sûr de vouloir supprimer ce journal d\'activité ? Cette action est irréversible.',
            'heading' => 'Supprimer le journal d\'activité',
            'button' => 'Supprimer',
        ],
        'revert' => [
            'heading' => 'Annuler les modifications',
            'confirmation' => 'Êtes-vous sûr de vouloir annuler cette modification ? Cela restaurera les anciennes valeurs.',
            'button' => 'Annuler',
            'success' => 'Modifications annulées avec succès',
            'no_old_data' => 'Aucune ancienne donnée disponible pour annuler',
            'subject_not_found' => 'Modèle de sujet introuvable',
            'label' => 'Annuler',
            'nothing_selected' => 'Aucun attribut sélectionné pour l\'annulation.',
            'helper_text' => 'Passer de \':old\' à \':new\'',
        ],
        'export' => [
            'filename' => 'journaux_d_activite',
            'notification' => [
                'completed' => 'L\'exportation de votre journal d\'activité est terminée et :successful_rows :rows_label ont été exportées.',
                'failed_rows' => ':count :rows n\'ont pas pu être exportées.',
            ],
        ],
        'restore' => [
            'label' => 'Restaurer',
            'heading' => 'Restaurer l\'enregistrement',
            'confirmation' => 'Êtes-vous sûr de vouloir restaurer cet enregistrement supprimé ?',
            'success' => 'Enregistrement restauré avec succès.',
        ],
        'prune' => [
            'label' => 'Nettoyer les journaux',
            'heading' => 'Nettoyer les journaux d\'activité',
            'confirmation' => 'Êtes-vous sûr de vouloir supprimer les journaux antérieurs à la date sélectionnée ? Cette action est irréversible.',
            'success' => ':count journaux d\'activité nettoyés avec succès.',
            'date' => 'Nettoyer les journaux plus anciens que',
        ],
        'batch' => [
            'label' => 'Lot',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => 'Êtes-vous sûr de vouloir supprimer les journaux d\'activité sélectionnés ?',
            ],
            'restore' => [
                'label' => 'Restaurer la sélection',
                'confirmation' => 'Êtes-vous sûr de vouloir restaurer les enregistrements supprimés sélectionnés ?',
                'success' => ':count enregistrements restaurés avec succès.',
            ],
            'revert' => [
                'label' => 'Annuler la sélection',
                'confirmation' => 'Êtes-vous sûr de vouloir annuler les modifications pour tous les journaux sélectionnés ? Seuls les journaux avec d\'anciennes données seront traités.',
                'success' => ':count journaux annulés avec succès.',
            ],
        ],
    ],
    'filters' => 'Filtres',
    'pages' => [
        'user_activities' => [
            'title' => 'Activités de l\'Utilisateur',
            'heading' => 'Activités de l\'Utilisateur',
            'description_title' => 'Suivre les Actions de l\'Utilisateur',
            'description' => 'Consultez toutes les activités effectuées par les utilisateurs dans votre application. Filtrez par utilisateur, type d\'événement ou sujet pour voir une chronologie complète des actions.',
        ],
        'audit_dashboard' => [
            'title' => 'Tableau de bord d\'audit',
        ],
    ],
    'event' => [
        'created' => 'Créé',
        'updated' => 'Mis à jour',
        'deleted' => 'Supprimé',
        'restored' => 'Restauré',
    ],
    'filter' => [
        'causer' => 'Utilisateur',
        'event' => 'Type d\'Événement',
        'subject_type' => 'Type de Sujet',
    ],
    'widgets' => [
        'latest_activity' => 'Activité récente',
        'activity_chart' => [
            'heading' => 'Activité au fil du temps',
            'label' => 'Activités',
        ],
        'heatmap' => [
            'heading' => 'Carte thermique d\'activité',
            'less' => 'Moins',
            'more' => 'Plus',
            'tooltip' => ':count activités le :date',
        ],
        'stats' => [
            'total_activities' => 'Activités totales',
            'total_description' => 'Total des journaux dans le système',
            'top_causer' => 'Principal auteur',
            'top_causer_description' => ':count activités',
            'top_subject' => 'Sujet principal',
            'top_subject_description' => ':count modifications',
            'no_data' => 'Pas de données',
        ],
    ],
    'dashboard' => [
        'title' => 'Tableau de bord d\'audit',
    ],
    'system' => 'Système',
    'row' => 'ligne',
    'rows' => 'lignes',
];
