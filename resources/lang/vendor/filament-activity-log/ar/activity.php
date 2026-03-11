<?php

return [
    'label' => 'سجل النشاط',
    'plural_label' => 'سجلات النشاط',
    'table' => [
        'column' => [
            'log_name' => 'اسم السجل',
            'event' => 'الحدث',
            'subject_id' => 'معرف الموضوع',
            'subject_type' => 'نوع الموضوع',
            'causer_id' => 'معرف المتسبب',
            'causer_type' => 'نوع المتسبب',
            'properties' => 'الخصائص',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
            'description' => 'الوصف',
            'subject' => 'الموضوع',
            'causer' => 'المتسبب',
            'id' => 'المعرف',
            'ip_address' => 'عنوان IP',
            'browser' => 'المتصفح',
        ],
        'filter' => [
            'event' => 'الحدث',
            'created_at' => 'تاريخ الإنشاء',
            'created_from' => 'من تاريخ',
            'created_until' => 'إلى تاريخ',
            'causer' => 'المتسبب',
            'subject_type' => 'نوع الموضوع',
            'batch' => 'معرف الدفعة',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'تفاصيل النشاط',
        ],
        'tab' => [
            'overview' => 'نظرة عامة',
            'changes' => 'التغييرات',
            'raw_data' => 'البيانات الخام',
            'old' => 'القديم',
            'new' => 'الجديد',
        ],
        'entry' => [
            'log_name' => 'اسم السجل',
            'event' => 'الحدث',
            'created_at' => 'تاريخ الإنشاء',
            'description' => 'الوصف',
            'subject' => 'الموضوع',
            'causer' => 'المتسبب',
            'ip_address' => 'عنوان IP',
            'browser' => 'المتصفح',
            'attributes' => 'السمات',
            'old' => 'القديم',
            'key' => 'المفتاح',
            'value' => 'القيمة',
            'properties' => 'الخصائص',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'الجدول الزمني',
            'empty_state_title' => 'لم يتم العثور على سجلات نشاط',
            'empty_state_description' => 'لا توجد أنشطة مسجلة لهذا السجل حتى الآن.',
        ],
        'delete' => [
            'confirmation' => 'هل أنت متأكد أنك تريد حذف سجل النشاط هذا؟ لا يمكن التراجع عن هذا الإجراء.',
            'heading' => 'حذف سجل النشاط',
            'button' => 'حذف',
        ],
        'revert' => [
            'heading' => 'التراجع عن التغييرات',
            'confirmation' => 'هل أنت متأكد أنك تريد التراجع عن هذا التغيير؟ سيؤدي هذا إلى استعادة القيم القديمة.',
            'button' => 'تراجع',
            'success' => 'تم التراجع عن التغييرات بنجاح',
            'no_old_data' => 'لا توجد بيانات قديمة متاحة للتراجع',
            'subject_not_found' => 'لم يتم العثور على نموذج الموضوع',
            'label' => 'تراجع',
            'nothing_selected' => 'لم يتم تحديد أي سمات للتراجع عنها.',
            'helper_text' => 'تغيير من \':old\' إلى \':new\'',
        ],
        'export' => [
            'filename' => 'سجلات_النشاط',
            'notification' => [
                'completed' => 'اكتمل تصدير سجل النشاط وتم تصدير :successful_rows :rows_label.',
                'failed_rows' => 'فشل تصدير :count :rows.',
            ],
        ],
        'restore' => [
            'label' => 'استعادة',
            'heading' => 'استعادة السجل',
            'confirmation' => 'هل أنت متأكد أنك تريد استعادة هذا السجل المحذوف؟',
            'success' => 'تم استعادة السجل بنجاح.',
        ],
        'prune' => [
            'label' => 'تنظيف السجلات',
            'heading' => 'تنظيف سجلات النشاط',
            'confirmation' => 'هل أنت متأكد أنك تريد حذف السجلات الأقدم من التاريخ المحدد؟ لا يمكن التراجع عن هذا الإجراء.',
            'success' => 'تم تنظيف :count من سجلات النشاط بنجاح.',
            'date' => 'تنظيف السجلات الأقدم من',
        ],
        'batch' => [
            'label' => 'دفعة',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => 'هل أنت متأكد أنك تريد حذف سجلات النشاط المحددة؟',
            ],
            'restore' => [
                'label' => 'استعادة المحدد',
                'confirmation' => 'هل أنت متأكد أنك تريد استعادة السجلات المحذوفة المحددة؟',
                'success' => 'تم استعادة :count من السجلات بنجاح.',
            ],
            'revert' => [
                'label' => 'تراجع عن المحدد',
                'confirmation' => 'هل أنت متأكد أنك تريد التراجع عن التغييرات لجميع السجلات المحددة؟ سيتم معالجة السجلات التي تحتوي على بيانات قديمة فقط.',
                'success' => 'تم التراجع عن :count من السجلات بنجاح.',
            ],
        ],
    ],
    'filters' => 'تصفية',
    'pages' => [
        'user_activities' => [
            'title' => 'أنشطة المستخدم',
            'heading' => 'أنشطة المستخدم',
            'description_title' => 'تتبع إجراءات المستخدم',
            'description' => 'عرض جميع الأنشطة التي قام بها المستخدمون في تطبيقك. تصفية حسب المستخدم، نوع الحدث، أو الموضوع لرؤية جدول زمني كامل للإجراءات.',
        ],
        'audit_dashboard' => [
            'title' => 'لوحة مراقبة التدقيق',
        ],
    ],
    'event' => [
        'created' => 'تم الإنشاء',
        'updated' => 'تم التحديث',
        'deleted' => 'تم الحذف',
        'restored' => 'تمت الاستعادة',
    ],
    'filter' => [
        'causer' => 'المستخدم',
        'event' => 'نوع الحدث',
        'subject_type' => 'نوع الموضوع',
    ],
    'widgets' => [
        'latest_activity' => 'النشاط الأخير',
        'activity_chart' => [
            'heading' => 'النشاط بمرور الوقت',
            'label' => 'الأنشطة',
        ],
        'heatmap' => [
            'heading' => 'خريطة حرارية للنشاط',
            'less' => 'أقل',
            'more' => 'أكثر',
            'tooltip' => ':count أنشطة في :date',
        ],
        'stats' => [
            'total_description' => 'Total logs in system',
            'top_causer' => 'Top Causer',
            'top_causer_description' => ':count activities',
            'top_subject' => 'Top Subject',
            'top_subject_description' => ':count modifications',
            'no_data' => 'No data',
        ],
    ],
    'dashboard' => [
        'title' => 'Audit Dashboard',
    ],
    'system' => 'System',
    'row' => 'row',
    'rows' => 'rows',
];
