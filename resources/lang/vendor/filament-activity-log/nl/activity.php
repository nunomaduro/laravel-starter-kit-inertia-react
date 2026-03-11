<?php

return [
    'label' => 'Activiteitenlogboek',
    'plural_label' => 'Activiteitenlogboeken',
    'table' => [
        'column' => [
            'log_name' => 'Logboeknaam',
            'event' => 'Gebeurtenis',
            'subject_id' => 'Onderwerp ID',
            'subject_type' => 'Onderwerp Type',
            'causer_id' => 'Veroorzaker ID',
            'causer_type' => 'Veroorzaker Type',
            'properties' => 'Eigenschappen',
            'created_at' => 'Aangemaakt Op',
            'updated_at' => 'Bijgewerkt Op',
            'description' => 'Beschrijving',
            'subject' => 'Onderwerp',
            'causer' => 'Veroorzaker',
            'id' => 'ID',
            'ip_address' => 'IP-adres',
            'browser' => 'Browser',
        ],
        'filter' => [
            'event' => 'Gebeurtenis',
            'created_at' => 'Aangemaakt Op',
            'created_from' => 'Aangemaakt Van',
            'created_until' => 'Aangemaakt Tot',
            'causer' => 'Veroorzaker',
            'subject_type' => 'Onderwerp Type',
            'batch' => 'Batch UUID',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Activiteit Details',
        ],
        'tab' => [
            'overview' => 'Overzicht',
            'changes' => 'Wijzigingen',
            'raw_data' => 'Ruwe Data',
            'old' => 'Oud',
            'new' => 'Nieuw',
        ],
        'entry' => [
            'log_name' => 'Logboeknaam',
            'event' => 'Gebeurtenis',
            'created_at' => 'Aangemaakt Op',
            'description' => 'Beschrijving',
            'subject' => 'Onderwerp',
            'causer' => 'Veroorzaker',
            'ip_address' => 'IP Adres',
            'browser' => 'Browser',
            'attributes' => 'Attributen',
            'old' => 'Oud',
            'key' => 'Sleutel',
            'value' => 'Waarde',
            'properties' => 'Eigenschappen',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Tijdlijn',
            'empty_state_title' => 'Geen activiteitenlogboeken gevonden',
            'empty_state_description' => 'Er zijn nog geen activiteiten geregistreerd voor dit item.',
        ],
        'delete' => [
            'confirmation' => 'Weet u zeker dat u dit activiteitenlogboek wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.',
            'heading' => 'Activiteitenlogboek Verwijderen',
            'button' => 'Verwijderen',
        ],
        'revert' => [
            'heading' => 'Wijzigingen Ongedaan Maken',
            'confirmation' => 'Weet u zeker dat u deze wijziging ongedaan wilt maken? Hiermee worden de oude waarden hersteld.',
            'button' => 'Ongedaan Maken',
            'success' => 'Wijzigingen succesvol ongedaan gemaakt',
            'no_old_data' => 'Geen oude gegevens beschikbaar om te herstellen',
            'subject_not_found' => 'Onderwerp model niet gevonden',
            'label' => 'Terugdraaien',
            'nothing_selected' => 'Geen attributen geselecteerd om terug te draaien.',
            'helper_text' => 'Wijzig van \':old\' terug naar \':new\'',
        ],
        'export' => [
            'filename' => 'activiteitenlogboeken',
            'notification' => [
                'completed' => 'Uw export van het activiteitenlogboek is voltooid en :successful_rows :rows_label zijn geëxporteerd.',
                'failed_rows' => ':count :rows konden niet worden geëxporteerd.',
            ],
        ],
        'restore' => [
            'label' => 'Herstellen',
            'heading' => 'Record Herstellen',
            'confirmation' => 'Weet u zeker dat u dit verwijderde record wilt herstellen?',
            'success' => 'Record succesvol hersteld.',
        ],
        'prune' => [
            'label' => 'Logboeken opschonen',
            'heading' => 'Activiteitenlogboeken opschonen',
            'confirmation' => 'Weet u zeker dat u logboeken ouder dan de geselecteerde datum wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.',
            'success' => ':count activiteitenlogboeken succesvol opgeschoond.',
            'date' => 'Schoon logboeken op ouder dan',
        ],
        'batch' => [
            'label' => 'Batch',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => 'Weet u zeker dat u de geselecteerde activiteitenlogboeken wilt verwijderen?',
            ],
            'restore' => [
                'label' => 'Herstel geselecteerde',
                'confirmation' => 'Weet u zeker dat u de geselecteerde verwijderde records wilt herstellen?',
                'success' => ':count records succesvol hersteld.',
            ],
            'revert' => [
                'label' => 'Draai geselecteerde terug',
                'confirmation' => 'Weet u zeker dat u de wijzigingen voor alle geselecteerde logboeken ongedaan wilt maken? Alleen logboeken met oude gegevens worden verwerkt.',
                'success' => ':count logboeken succesvol teruggedraaid.',
            ],
        ],
    ],
    'filters' => 'Filters',
    'pages' => [
        'user_activities' => [
            'title' => 'Gebruikersactiviteiten',
            'heading' => 'Gebruikersactiviteiten',
            'description_title' => 'Volg Gebruikersacties',
            'description' => 'Bekijk alle activiteiten die door gebruikers in uw applicatie zijn uitgevoerd. Filter op gebruiker, gebeurtenistype of onderwerp om een volledige tijdlijn van acties te zien.',
        ],
        'audit_dashboard' => [
            'title' => 'Audit Dashboard',
        ],
    ],
    'event' => [
        'created' => 'Aangemaakt',
        'updated' => 'Bijgewerkt',
        'deleted' => 'Verwijderd',
        'restored' => 'Hersteld',
    ],
    'filter' => [
        'causer' => 'Gebruiker',
        'event' => 'Gebeurtenis Type',
        'subject_type' => 'Onderwerp Type',
    ],
    'widgets' => [
        'latest_activity' => 'Laatste Activiteit',
        'activity_chart' => [
            'heading' => 'Activiteit door de tijd heen',
            'label' => 'Activiteiten',
        ],
        'heatmap' => [
            'heading' => 'Activity Heatmap',
            'less' => 'Minder',
            'more' => 'Meer',
            'tooltip' => ':count activiteiten op :date',
        ],
        'stats' => [
            'total_activities' => 'Totaal aantal activiteiten',
            'total_description' => 'Totaal aantal logboeken in systeem',
            'top_causer' => 'Meeste veroorzaker',
            'top_causer_description' => ':count activiteiten',
            'top_subject' => 'Meeste onderwerp',
            'top_subject_description' => ':count wijzigingen',
            'no_data' => 'Geen gegevens',
        ],
    ],
    'dashboard' => [
        'title' => 'Audit Dashboard',
    ],
    'system' => 'Systeem',
    'row' => 'rij',
    'rows' => 'rijen',
];
