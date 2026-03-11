<?php

return [
    'label' => 'Registro de actividad',
    'plural_label' => 'Registros de actividad',
    'table' => [
        'column' => [
            'log_name' => 'Nombre del registro',
            'event' => 'Evento',
            'subject_id' => 'ID del sujeto',
            'subject_type' => 'Tipo de sujeto',
            'causer_id' => 'ID del causante',
            'causer_type' => 'Tipo de causante',
            'properties' => 'Propiedades',
            'created_at' => 'Creado el',
            'updated_at' => 'Actualizado el',
            'description' => 'Descripción',
            'subject' => 'Sujeto',
            'causer' => 'Causante',
            'id' => 'ID',
            'ip_address' => 'Dirección IP',
            'browser' => 'Navegador',
        ],
        'filter' => [
            'event' => 'Evento',
            'created_at' => 'Creado el',
            'created_from' => 'Creado desde',
            'created_until' => 'Creado hasta',
            'causer' => 'Causante',
            'subject_type' => 'Tipo de sujeto',
            'batch' => 'UUID del lote',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Detalles de la actividad',
        ],
        'tab' => [
            'overview' => 'Resumen',
            'changes' => 'Cambios',
            'raw_data' => 'Datos brutos',
            'old' => 'Antiguo',
            'new' => 'Nuevo',
        ],
        'entry' => [
            'log_name' => 'Nombre del registro',
            'event' => 'Evento',
            'created_at' => 'Creado el',
            'description' => 'Descripción',
            'subject' => 'Sujeto',
            'causer' => 'Causante',
            'ip_address' => 'Dirección IP',
            'browser' => 'Navegador',
            'attributes' => 'Atributos',
            'old' => 'Antiguo',
            'key' => 'Clave',
            'value' => 'Valor',
            'properties' => 'Propiedades',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Línea de tiempo',
            'empty_state_title' => 'No se encontraron registros de actividad',
            'empty_state_description' => 'No hay actividades registradas para este registro todavía.',
        ],
        'delete' => [
            'confirmation' => '¿Está seguro de que desea eliminar este registro de actividad? Esta acción no se puede deshacer.',
            'heading' => 'Eliminar registro de actividad',
            'button' => 'Eliminar',
        ],
        'revert' => [
            'heading' => 'Revertir cambios',
            'confirmation' => '¿Está seguro de que desea revertir este cambio? Esto restaurará los valores antiguos.',
            'button' => 'Revertir',
            'success' => 'Cambios revertidos exitosamente',
            'no_old_data' => 'No hay datos antiguos disponibles para revertir',
            'subject_not_found' => 'Modelo de sujeto no encontrado',
            'label' => 'Revertir',
            'nothing_selected' => 'No se seleccionaron atributos para revertir.',
            'helper_text' => 'Cambiar de \':old\' a \':new\'',
        ],
        'export' => [
            'filename' => 'registros_de_actividad',
            'notification' => [
                'completed' => 'La exportación de su registro de actividad se ha completado y se han exportado :successful_rows :rows_label.',
                'failed_rows' => ':count :rows fallaron al exportar.',
            ],
        ],
        'restore' => [
            'label' => 'Restaurar',
            'heading' => 'Restaurar registro',
            'confirmation' => '¿Está seguro de que desea restaurar este registro eliminado?',
            'success' => 'Registro restaurado exitosamente.',
        ],
        'prune' => [
            'label' => 'Limpiar registros',
            'heading' => 'Limpiar registros de actividad',
            'confirmation' => '¿Está seguro de que desea eliminar los registros anteriores a la fecha seleccionada? Esta acción no se puede deshacer.',
            'success' => ':count registros de actividad limpiados exitosamente.',
            'date' => 'Limpiar registros anteriores a',
        ],
        'batch' => [
            'label' => 'Lote',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => '¿Está seguro de que desea eliminar los registros de actividad seleccionados?',
            ],
            'restore' => [
                'label' => 'Restaurar seleccionados',
                'confirmation' => '¿Está seguro de que desea restaurar los registros eliminados seleccionados?',
                'success' => ':count registros restaurados exitosamente.',
            ],
            'revert' => [
                'label' => 'Revertir seleccionados',
                'confirmation' => '¿Está seguro de que desea revertir los cambios para todos los registros seleccionados? Solo se procesarán los registros con datos antiguos.',
                'success' => ':count registros revertidos exitosamente.',
            ],
        ],
    ],
    'filters' => 'Filtros',
    'pages' => [
        'user_activities' => [
            'title' => 'Actividades de Usuario',
            'heading' => 'Actividades de Usuario',
            'description_title' => 'Rastrear Acciones de Usuario',
            'description' => 'Ver todas las actividades realizadas por los usuarios en su aplicación. Filtrar por usuario, tipo de evento o sujeto para ver una línea de tiempo completa de las acciones.',
        ],
        'audit_dashboard' => [
            'title' => 'Panel de control de auditoría',
        ],
    ],
    'event' => [
        'created' => 'Creado',
        'updated' => 'Actualizado',
        'deleted' => 'Eliminado',
        'restored' => 'Restaurado',
    ],
    'filter' => [
        'causer' => 'Usuario',
        'event' => 'Tipo de Evento',
        'subject_type' => 'Tipo de Sujeto',
    ],
    'widgets' => [
        'latest_activity' => 'Actividad reciente',
        'activity_chart' => [
            'heading' => 'Actividad a lo largo del tiempo',
            'label' => 'Actividades',
        ],
        'heatmap' => [
            'heading' => 'Mapa de calor de actividad',
            'less' => 'Menos',
            'more' => 'Más',
            'tooltip' => ':count actividades el :date',
        ],
        'stats' => [
            'total_activities' => 'Actividades totales',
            'total_description' => 'Total de registros en el sistema',
            'top_causer' => 'Causante principal',
            'top_causer_description' => ':count actividades',
            'top_subject' => 'Sujeto principal',
            'top_subject_description' => ':count modificaciones',
            'no_data' => 'Sin datos',
        ],
    ],
    'dashboard' => [
        'title' => 'Panel de control de auditoría',
    ],
    'system' => 'Sistema',
    'row' => 'fila',
    'rows' => 'filas',
];
