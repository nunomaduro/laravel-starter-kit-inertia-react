<?php

return [
    'label' => 'Activity Log',
    'plural_label' => 'Activity Logs',
    'table' => [
        'column' => [
            'id' => 'ID',
            'log_name' => 'Log Name',
            'event' => 'Event',
            'subject_id' => 'Subject ID',
            'subject_type' => 'Subject Type',
            'causer_id' => 'Causer ID',
            'causer_type' => 'Causer Type',
            'properties' => 'Properties',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'description' => 'Description',
            'subject' => 'Subject',
            'causer' => 'Causer',
            'ip_address' => 'IP Address',
            'browser' => 'Browser',
        ],
        'filter' => [
            'event' => 'Event',
            'created_at' => 'Created At',
            'created_from' => 'Created From',
            'created_until' => 'Created Until',
            'causer' => 'Causer',
            'subject_type' => 'Subject Type',
            'batch' => 'Batch UUID',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Activity Details',
        ],
        'tab' => [
            'overview' => 'Overview',
            'changes' => 'Changes',
            'raw_data' => 'Raw Data',
            'old' => 'Old',
            'new' => 'New',
        ],
        'entry' => [
            'log_name' => 'Log Name',
            'event' => 'Event',
            'created_at' => 'Created At',
            'description' => 'Description',
            'subject' => 'Subject',
            'causer' => 'Causer',
            'ip_address' => 'IP Address',
            'browser' => 'Browser',
            'attributes' => 'Attributes',
            'old' => 'Old',
            'key' => 'Key',
            'value' => 'Value',
            'properties' => 'Properties',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Timeline',
            'empty_state_title' => 'No activity logs found',
            'empty_state_description' => 'There are no activities recorded for this record yet.',
        ],
        'delete' => [
            'confirmation' => 'Are you sure you want to delete this activity log? This action cannot be undone.',
            'heading' => 'Delete Activity Log',
            'button' => 'Delete',
        ],
        'revert' => [
            'label' => 'Revert',
            'heading' => 'Revert Changes',
            'confirmation' => 'Are you sure you want to revert this change? This will restore the old values.',
            'button' => 'Revert',
            'success' => 'Changes reverted successfully',
            'no_old_data' => 'No old data available to revert',
            'nothing_selected' => 'No attributes selected to revert.',
            'subject_not_found' => 'Subject model not found',
            'helper_text' => 'Change from \':old\' back to \':new\'',
        ],
        'restore' => [
            'label' => 'Restore',
            'heading' => 'Restore Record',
            'confirmation' => 'Are you sure you want to restore this deleted record?',
            'success' => 'Record restored successfully.',
        ],
        'prune' => [
            'label' => 'Prune Logs',
            'heading' => 'Prune Activity Logs',
            'confirmation' => 'Are you sure you want to delete logs older than the selected date? This action cannot be undone.',
            'success' => ':count activity logs pruned successfully.',
            'date' => 'Prune logs older than',
        ],
        'export' => [
            'filename' => 'activity_logs',
            'notification' => [
                'completed' => 'Your activity log export has completed and :successful_rows :rows_label exported.',
                'failed_rows' => ':count :rows failed to export.',
            ],
        ],
        'batch' => [
            'label' => 'Batch',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => 'Are you sure you want to delete the selected activity logs?',
            ],
            'restore' => [
                'label' => 'Restore Selected',
                'confirmation' => 'Are you sure you want to restore the selected deleted records?',
                'success' => ':count records restored successfully.',
            ],
            'revert' => [
                'label' => 'Revert Selected',
                'confirmation' => 'Are you sure you want to revert changes for all selected logs? Only logs with old data will be processed.',
                'success' => ':count logs reverted successfully.',
            ],
        ],
    ],
    'widgets' => [
        'latest_activity' => 'Latest Activity',
        'activity_chart' => [
            'heading' => 'Activity Over Time',
            'label' => 'Activities',
        ],
        'heatmap' => [
            'heading' => 'Activity Heatmap',
            'less' => 'Less',
            'more' => 'More',
            'tooltip' => ':count activities on :date',
        ],
        'stats' => [
            'total_activities' => 'Total Activities',
            'total_description' => 'Total logs in system',
            'top_causer' => 'Top Causer',
            'top_causer_description' => ':count activities',
            'top_subject' => 'Top Subject',
            'top_subject_description' => ':count modifications',
            'no_data' => 'No data',
        ],
    ],
    'pages' => [
        'user_activities' => [
            'title' => 'User Activities',
            'heading' => 'User Activities',
            'description_title' => 'Track User Actions',
            'description' => 'View all activities performed by users in your application. Filter by user, event type, or subject to see a complete timeline of actions.',
        ],
        'audit_dashboard' => [
            'title' => 'Audit Dashboard',
        ],
    ],
    'event' => [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'restored' => 'Restored',
    ],
    'filter' => [
        'causer' => 'User',
        'event' => 'Event Type',
        'subject_type' => 'Subject Type',
    ],
    'dashboard' => [
        'title' => 'Audit Dashboard',
    ],
    'filters' => 'Filters',
    'system' => 'System',
    'row' => 'row',
    'rows' => 'rows',
];
