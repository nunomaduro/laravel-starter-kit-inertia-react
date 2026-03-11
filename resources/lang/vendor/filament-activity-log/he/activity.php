<?php

return [
    'label' => 'יומן פעילות',
    'plural_label' => 'יומני פעילות',
    'table' => [
        'column' => [
            'log_name' => 'שם היומן',
            'event' => 'אירוע',
            'subject_id' => 'מזהה נושא',
            'subject_type' => 'סוג נושא',
            'causer_id' => 'מזהה גורם',
            'causer_type' => 'סוג גורם',
            'properties' => 'מאפיינים',
            'created_at' => 'נוצר ב',
            'updated_at' => 'עודכן ב',
            'description' => 'תיאור',
            'subject' => 'נושא',
            'causer' => 'גורם',
            'id' => 'מזהה',
            'ip_address' => 'כתובת IP',
            'browser' => 'דפדפן',
        ],
        'filter' => [
            'event' => 'אירוע',
            'created_at' => 'נוצר ב',
            'created_from' => 'נוצר מ',
            'created_until' => 'נוצר עד',
            'causer' => 'גורם',
            'subject_type' => 'סוג נושא',
            'batch' => 'מזהה אצווה',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'פרטי פעילות',
        ],
        'tab' => [
            'overview' => 'סקירה כללית',
            'changes' => 'שינויים',
            'raw_data' => 'נתונים גולמיים',
            'old' => 'ישן',
            'new' => 'חדש',
        ],
        'entry' => [
            'log_name' => 'שם היומן',
            'event' => 'אירוע',
            'created_at' => 'נוצר ב',
            'description' => 'תיאור',
            'subject' => 'נושא',
            'causer' => 'גורם',
            'ip_address' => 'כתובת IP',
            'browser' => 'דפדפן',
            'attributes' => 'תכונות',
            'old' => 'ישן',
            'key' => 'מפתח',
            'value' => 'ערך',
            'properties' => 'מאפיינים',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'ציר זמן',
            'empty_state_title' => 'לא נמצאו יומני פעילות',
            'empty_state_description' => 'אין עדיין פעילויות מתועדות עבור רשומה זו.',
        ],
        'delete' => [
            'confirmation' => 'האם אתה בטוח שברצונך למחוק יומן פעילות זה? פעולה זו אינה הפיכה.',
            'heading' => 'מחק יומן פעילות',
            'button' => 'מחק',
        ],
        'revert' => [
            'heading' => 'בטל שינויים',
            'confirmation' => 'האם אתה בטוח שברצונך לבטל שינוי זה? פעולה זו תשחזר את הערכים הישנים.',
            'button' => 'בטל',
            'success' => 'השינויים בוטלו בהצלחה',
            'no_old_data' => 'אין נתונים ישנים זמינים לביטול',
            'subject_not_found' => 'מודל הנושא לא נמצא',
            'label' => 'שחזור',
            'nothing_selected' => 'לא נבחרו תכונות לשחזור.',
            'helper_text' => 'שנה מ-\':old\' חזרה ל-\':new\'',
        ],
        'export' => [
            'filename' => 'יומני_פעילות',
            'notification' => [
                'completed' => 'ייצוא יומן הפעילות שלך הושלם ו-:successful_rows :rows_label יוצאו.',
                'failed_rows' => ':count :rows נכשלו בייצוא.',
            ],
        ],
        'restore' => [
            'label' => 'שחזור',
            'heading' => 'שחזור רשומה',
            'confirmation' => 'האם אתה בטוח שברצונך לשחזר רשומה זו שנמחקה?',
            'success' => 'הרשומה שוחזרה בהצלחה.',
        ],
        'prune' => [
            'label' => 'ניקוי יומנים',
            'heading' => 'ניקוי יומני פעילות',
            'confirmation' => 'האם אתה בטוח שברצונך למחוק יומנים ישנים יותר מהתאריך שנבחר? פעולה זו אינה הפיכה.',
            'success' => ':count יומני פעילות נוקו בהצלחה.',
            'date' => 'נקה יומנים ישנים מ-',
        ],
        'batch' => [
            'label' => 'אצווה',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => 'האם אתה בטוח שברצונך למחוק את יומני הפעילות שנבחרו?',
            ],
            'restore' => [
                'label' => 'שחזר את הנבחרים',
                'confirmation' => 'האם אתה בטוח שברצונך לשחזר את הרשומות שנמחקו שנבחרו?',
                'success' => ':count רשומות שוחזרו בהצלחה.',
            ],
            'revert' => [
                'label' => 'שחזר את הנבחרים',
                'confirmation' => 'האם אתה בטוח שברצונך לבטל שינויים עבור כל היומנים שנבחרו? רק יומנים עם נתונים ישנים יעובדו.',
                'success' => ':count יומנים שוחזרו בהצלחה.',
            ],
        ],
    ],
    'filters' => 'מסננים',
    'pages' => [
        'user_activities' => [
            'title' => 'פעילות משתמש',
            'heading' => 'פעילות משתמש',
            'description_title' => 'עקוב אחר פעולות משתמש',
            'description' => 'צפה בכל הפעילויות שבוצעו על ידי משתמשים באפליקציה שלך. סנן לפי משתמש, סוג אירוע או נושא כדי לראות ציר זמן מלא של פעולות.',
        ],
        'audit_dashboard' => [
            'title' => 'לוח בקרת ביקורת',
        ],
    ],
    'event' => [
        'created' => 'נוצר',
        'updated' => 'עודכן',
        'deleted' => 'נמחק',
        'restored' => 'שוחזר',
    ],
    'filter' => [
        'causer' => 'משתמש',
        'event' => 'סוג אירוע',
        'subject_type' => 'סוג נושא',
    ],
    'widgets' => [
        'latest_activity' => 'פעילות אחרונה',
        'activity_chart' => [
            'heading' => 'פעילות לאורך זמן',
            'label' => 'פעילויות',
        ],
        'heatmap' => [
            'heading' => 'מפת חום של פעילות',
            'less' => 'פחות',
            'more' => 'יותר',
            'tooltip' => ':count פעילויות ב-:date',
        ],
        'stats' => [
            'total_activities' => 'סה"כ פעילויות',
            'total_description' => 'סה"כ יומנים במערכת',
            'top_causer' => 'גורם ראשי',
            'top_causer_description' => ':count פעילויות',
            'top_subject' => 'נושא ראשי',
            'top_subject_description' => ':count עדכונים',
            'no_data' => 'אין נתונים',
        ],
    ],
    'dashboard' => [
        'title' => 'Audit Dashboard',
    ],
    'system' => 'מערכת',
    'row' => 'שורה',
    'rows' => 'שורות',
];
