<?php

return [
    'panel' => [
        'group' => 'Configurações',
        'label' => 'Gerenciar recursos',
        'title' => 'Gerenciar recursos e segmentos',
    ],

    'form' => [
        'feature' => 'Recurso',
        'scope' => 'Escopo',
        'status' => 'Status',
        'activate' => 'Ativar',
        'deactivate' => 'Desativar',
        'unique_validation' => 'A segmentação de recurso já existe. Cada escopo de recurso pode ter apenas um segmento ativado e um segmento desativado. Para continuar, modifique o segmento existente ou remova-o e crie um novo.',
    ],

    'table' => [
        'title' => 'Título',
        'segment' => 'Segmento',
        'status' => 'Status',
        'activated' => 'ATIVADO',
        'deactivated' => 'DESATIVADO',
    ],

    'actions' => [
        'create_segment' => 'Criar segmento de recurso',
        'segment_feature' => 'Segmentar recurso',
        'activate' => 'Ativar',
        'activate_description' => 'Esta ação ativará o recurso selecionado para os usuários.',
        'deactivate' => 'Desativar',
        'deactivate_for_all' => 'Desativar para todos',
        'deactivate_description' => 'Esta ação desativará este recurso para os usuários.',
        'purge' => 'Limpar',
        'purge_description' => 'Esta ação limpará os recursos resolvidos do armazenamento.',
        'all_features' => 'Todos os recursos',
        'modify' => 'Modificar',
        'modify_heading' => 'Modificar segmento de recurso',
        'remove' => 'Remover',
        'remove_heading' => 'A remoção deste segmento de recurso não pode ser desfeita!',
    ],

    'notifications' => [
        'done' => 'Pronto!',
        'activated_for_all' => ':feature ativado para os usuários.',
        'deactivated_for_all' => ':feature desativado para os usuários.',
        'all_features_purged' => 'Todos os recursos foram limpos do armazenamento com sucesso.',
        'feature_purged' => 'Recurso :feature limpo do armazenamento com sucesso.',
    ],
];
