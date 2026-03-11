<?php

return [
    'label' => '活动日志',
    'plural_label' => '活动日志',
    'table' => [
        'column' => [
            'log_name' => '日志名称',
            'event' => '事件',
            'subject_id' => '主体 ID',
            'subject_type' => '主体类型',
            'causer_id' => '致因 ID',
            'causer_type' => '致因类型',
            'properties' => '属性',
            'created_at' => '创建于',
            'updated_at' => '更新于',
            'description' => '描述',
            'subject' => '主体',
            'causer' => '致因',
            'ip_address' => 'IP 地址',
            'browser' => '浏览器',
            'id' => 'ID',
        ],
        'filter' => [
            'event' => '事件',
            'created_at' => '创建于',
            'created_from' => '创建自',
            'created_until' => '创建至',
            'causer' => '致因',
            'subject_type' => '主体类型',
            'batch' => '批次 UUID',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => '活动详情',
        ],
        'tab' => [
            'overview' => '概览',
            'changes' => '变更',
            'raw_data' => '原始数据',
            'old' => '旧值',
            'new' => '新值',
        ],
        'entry' => [
            'log_name' => '日志名称',
            'event' => '事件',
            'created_at' => '创建于',
            'description' => '描述',
            'subject' => '主体',
            'causer' => '致因',
            'ip_address' => 'IP 地址',
            'browser' => '浏览器',
            'attributes' => '属性',
            'old' => '旧值',
            'key' => '键',
            'value' => '值',
            'properties' => '属性',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => '时间轴',
            'empty_state_title' => '未找到活动日志',
            'empty_state_description' => '此记录尚无活动日志。',
        ],
        'delete' => [
            'confirmation' => '您确定要删除此活动日志吗？此操作无法撤销。',
            'heading' => '删除活动日志',
            'button' => '删除',
        ],
        'revert' => [
            'heading' => '撤销更改',
            'confirmation' => '您确定要撤销此更改吗？这将恢复旧值。',
            'button' => '撤销',
            'success' => '更改已成功撤销',
            'no_old_data' => '无旧数据可用于撤销',
            'subject_not_found' => '未找到主体模型',
            'label' => '撤销',
            'nothing_selected' => '未选择要撤销的属性。',
            'helper_text' => '将 \':old\' 恢复为 \':new\'',
        ],
        'export' => [
            'filename' => '活动日志',
            'notification' => [
                'completed' => '您的活动日志导出已完成，已导出 :successful_rows :rows_label。',
                'failed_rows' => ':count 条:rows导出失败。',
            ],
        ],
        'restore' => [
            'label' => '恢复',
            'heading' => '恢复记录',
            'confirmation' => '您确定要恢复此已删除的记录吗？',
            'success' => '记录已成功恢复。',
        ],
        'prune' => [
            'label' => '清理日志',
            'heading' => '清理活动日志',
            'confirmation' => '您确定要删除早于所选日期的日志吗？此操作无法撤销。',
            'success' => '已成功清理 :count 条活动日志。',
            'date' => '清理早于此日期的日志',
        ],
        'batch' => [
            'label' => '批次',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => '您确定要删除所选的活动日志吗？',
            ],
            'restore' => [
                'label' => '恢复所选项',
                'confirmation' => '您确定要恢复所选的已删除记录吗？',
                'success' => '已成功恢复 :count 条记录。',
            ],
            'revert' => [
                'label' => '批量撤销',
                'confirmation' => '您确定要撤销所有选定日志的更改吗？仅处理具有旧数据的日志。',
                'success' => '已成功撤销 :count 条日志。',
            ],
        ],
    ],
    'filters' => '筛选',
    'pages' => [
        'user_activities' => [
            'title' => '用户活动',
            'heading' => '用户活动',
            'description_title' => '追踪用户行为',
            'description' => '查看用户在应用程序中执行的所有活动。按用户、事件类型或主体筛选，以查看完整的操作时间轴。',
        ],
        'audit_dashboard' => [
            'title' => '审计仪表板',
        ],
    ],
    'event' => [
        'created' => '已创建',
        'updated' => '已更新',
        'deleted' => '已删除',
        'restored' => '已恢复',
    ],
    'filter' => [
        'causer' => '用户',
        'event' => '事件类型',
        'subject_type' => '主体类型',
    ],
    'widgets' => [
        'latest_activity' => '最新活动',
        'activity_chart' => [
            'heading' => '活动趋势',
            'label' => '活动',
        ],
        'heatmap' => [
            'heading' => 'Activity Heatmap',
            'less' => 'Less',
            'more' => 'More',
            'tooltip' => ':count activities on :date',
        ],
        'stats' => [
            'total_activities' => '总活动数',
            'total_description' => '系统中总日志数',
            'top_causer' => '活跃致因',
            'top_causer_description' => ':count 次活动',
            'top_subject' => '常用主体',
            'top_subject_description' => ':count 次修改',
            'no_data' => '暂无数据',
        ],
    ],
    'dashboard' => [
        'title' => 'Audit Dashboard',
    ],
    'system' => '系统',
    'row' => '行',
    'rows' => '行',
];
