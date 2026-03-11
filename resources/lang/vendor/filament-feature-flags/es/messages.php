<?php

return [
    'panel' => [
        'group' => 'Configuración',
        'label' => 'Gestionar funciones',
        'title' => 'Gestionar funciones y segmentos',
    ],

    'form' => [
        'feature' => 'Función',
        'scope' => 'Alcance',
        'status' => 'Estado',
        'activate' => 'Activar',
        'deactivate' => 'Desactivar',
        'unique_validation' => 'La segmentación de funciones ya existe. Cada alcance de función solo puede tener un segmento activado y un segmento desactivado. Para continuar, modifique el segmento existente o elimínelo y cree uno nuevo.',
    ],

    'table' => [
        'title' => 'Título',
        'segment' => 'Segmento',
        'status' => 'Estado',
        'activated' => 'ACTIVADO',
        'deactivated' => 'DESACTIVADO',
    ],

    'actions' => [
        'create_segment' => 'Crear segmento de función',
        'segment_feature' => 'Segmentar función',
        'activate' => 'Activar',
        'activate_description' => 'Esta acción activará la función seleccionada para los usuarios.',
        'deactivate' => 'Desactivar',
        'deactivate_for_all' => 'Desactivar para todos',
        'deactivate_description' => 'Esta acción desactivará esta función para los usuarios.',
        'purge' => 'Purgar',
        'purge_description' => 'Esta acción purgará las funciones resueltas del almacenamiento.',
        'all_features' => 'Todas las funciones',
        'modify' => 'Modificar',
        'modify_heading' => 'Modificar segmento de función',
        'remove' => 'Eliminar',
        'remove_heading' => '¡Eliminar este segmento de función no se puede deshacer!',
    ],

    'notifications' => [
        'done' => '¡Hecho!',
        'activated_for_all' => ':feature activado para los usuarios.',
        'deactivated_for_all' => ':feature desactivado para los usuarios.',
        'all_features_purged' => 'Todas las funciones se purgaron exitosamente del almacenamiento.',
        'feature_purged' => 'Función :feature purgada exitosamente del almacenamiento.',
    ],
];
