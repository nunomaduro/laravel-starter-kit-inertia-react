<?php

return [
    'panel' => [
        'group' => 'Settings',
        'label' => 'Manage Features',
        'title' => 'Manage Features & Segments',
    ],

    'form' => [
        'feature' => 'Feature',
        'scope' => 'Scope',
        'status' => 'Status',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'unique_validation' => 'Feature segmentation already exists. Each feature scope can only have one activated segment and one deactivated segment. To continue, please modify the existing segment, or remove it and create a new one.',
    ],

    'table' => [
        'title' => 'Title',
        'segment' => 'Segment',
        'status' => 'Status',
        'activated' => 'ACTIVATED',
        'deactivated' => 'DEACTIVATED',
    ],

    'actions' => [
        'create_segment' => 'Create Feature Segment',
        'segment_feature' => 'Segment Feature',
        'activate' => 'Activate',
        'activate_description' => 'This action will activate the selected feature for users.',
        'deactivate' => 'Deactivate',
        'deactivate_for_all' => 'Deactivate for All',
        'deactivate_description' => 'This action will deactivate this feature for users.',
        'purge' => 'Purge',
        'purge_description' => 'This action will purge resolved features from storage.',
        'all_features' => 'All Features',
        'modify' => 'Modify',
        'modify_heading' => 'Modify Feature Segment',
        'remove' => 'Remove',
        'remove_heading' => 'Removing this feature segment cannot be undone!',
    ],

    'notifications' => [
        'done' => 'Done!',
        'activated_for_all' => ':feature activated for users.',
        'deactivated_for_all' => ':feature deactivated for users.',
        'all_features_purged' => 'All features successfully purged from storage.',
        'feature_purged' => ':feature feature successfully purged from storage.',
    ],
];
