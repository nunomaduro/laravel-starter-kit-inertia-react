<?php

return [
    'label' => 'Registro de Atividade',
    'plural_label' => 'Registros de Atividade',
    'table' => [
        'column' => [
            'log_name' => 'Nome do Registro',
            'event' => 'Evento',
            'subject_id' => 'ID do Assunto',
            'subject_type' => 'Tipo de Assunto',
            'causer_id' => 'ID do Causador',
            'causer_type' => 'Tipo de Causador',
            'properties' => 'Propriedades',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
            'description' => 'Descrição',
            'subject' => 'Assunto',
            'causer' => 'Causador',
            'ip_address' => 'Endereço IP',
            'browser' => 'Navegador',
            'id' => 'ID',
        ],
        'filter' => [
            'event' => 'Evento',
            'created_at' => 'Criado em',
            'created_from' => 'Criado a partir de',
            'created_until' => 'Criado até',
            'causer' => 'Causador',
            'subject_type' => 'Tipo de Assunto',
            'batch' => 'UUID do Lote',
        ],
    ],
    'infolist' => [
        'section' => [
            'activity_details' => 'Detalhes da Atividade',
        ],
        'tab' => [
            'overview' => 'Visão Geral',
            'changes' => 'Alterações',
            'raw_data' => 'Dados Brutos',
            'old' => 'Antigo',
            'new' => 'Novo',
        ],
        'entry' => [
            'log_name' => 'Nome do Registro',
            'event' => 'Evento',
            'created_at' => 'Criado em',
            'description' => 'Descrição',
            'subject' => 'Assunto',
            'causer' => 'Causador',
            'ip_address' => 'Endereço IP',
            'browser' => 'Navegador',
            'attributes' => 'Atributos',
            'old' => 'Antigo',
            'key' => 'Chave',
            'value' => 'Valor',
            'properties' => 'Propriedades',
        ],
    ],
    'action' => [
        'timeline' => [
            'label' => 'Linha do Tempo',
            'empty_state_title' => 'Nenhum registro de atividade encontrado',
            'empty_state_description' => 'Ainda não há atividades registradas para este registro.',
        ],
        'delete' => [
            'confirmation' => 'Tem certeza de que deseja excluir este registro de atividade? Esta ação não pode ser desfeita.',
            'heading' => 'Excluir Registro de Atividade',
            'button' => 'Excluir',
        ],
        'revert' => [
            'heading' => 'Reverter Alterações',
            'confirmation' => 'Tem certeza de que deseja reverter esta alteração? Isso restaurará os valores antigos.',
            'button' => 'Reverter',
            'success' => 'Alterações revertidas com sucesso',
            'no_old_data' => 'Nenhum dado antigo disponível para reverter',
            'subject_not_found' => 'Modelo de assunto não encontrado',
            'label' => 'Reverter',
            'nothing_selected' => 'Nenhum atributo selecionado para reverter.',
            'helper_text' => 'Alterar de \':old\' de volta para \':new\'',
        ],
        'export' => [
            'filename' => 'registros_de_atividade',
            'notification' => [
                'completed' => 'A exportação do seu registro de atividade foi concluída e :successful_rows :rows_label foram exportadas.',
                'failed_rows' => 'Falha ao exportar :count :rows.',
            ],
        ],
        'restore' => [
            'label' => 'Restaurar',
            'heading' => 'Restaurar Registro',
            'confirmation' => 'Tem certeza de que deseja restaurar este registro excluído?',
            'success' => 'Registro restaurado com sucesso.',
        ],
        'prune' => [
            'label' => 'Limpar Registros',
            'heading' => 'Limpar Registros de Atividade',
            'confirmation' => 'Tem certeza de que deseja excluir registros anteriores à data selecionada? Esta ação não pode ser desfeita.',
            'success' => ':count registros de atividade limpos com sucesso.',
            'date' => 'Limpar registros anteriores a',
        ],
        'batch' => [
            'label' => 'Lote',
        ],
        'bulk' => [
            'delete' => [
                'confirmation' => 'Tem certeza de que deseja excluir os registros de atividade selecionados?',
            ],
            'restore' => [
                'label' => 'Restaurar Selecionados',
                'confirmation' => 'Tem certeza de que deseja restaurar os registros excluídos selecionados?',
                'success' => ':count registros restaurados com sucesso.',
            ],
            'revert' => [
                'label' => 'Reverter Selecionados',
                'confirmation' => 'Tem certeza de que deseja reverter as alterações para todos os registros selecionados? Apenas registros com dados antigos serão processados.',
                'success' => ':count registros revertidos com sucesso.',
            ],
        ],
    ],
    'filters' => 'Filtros',
    'pages' => [
        'user_activities' => [
            'title' => 'Atividades do Usuário',
            'heading' => 'Atividades do Usuário',
            'description_title' => 'Rastrear Ações do Usuário',
            'description' => 'Visualize todas as atividades realizadas pelos usuários na sua aplicação. Filtre por usuário, tipo de evento ou assunto para ver uma linha do tempo completa das ações.',
        ],
        'audit_dashboard' => [
            'title' => 'Painel de Auditoria',
        ],
    ],
    'event' => [
        'created' => 'Criado',
        'updated' => 'Atualizado',
        'deleted' => 'Excluído',
        'restored' => 'Restaurado',
    ],
    'filter' => [
        'causer' => 'Usuário',
        'event' => 'Tipo de Evento',
        'subject_type' => 'Tipo de Assunto',
    ],
    'widgets' => [
        'latest_activity' => 'Atividade Recente',
        'activity_chart' => [
            'heading' => 'Atividade ao Longo do Tempo',
            'label' => 'Atividades',
        ],
        'heatmap' => [
            'heading' => 'Mapa de Calor da Atividade',
            'less' => 'Menos',
            'more' => 'Mais',
            'tooltip' => ':count atividades em :date',
        ],
        'stats' => [
            'total_activities' => 'Total de Atividades',
            'total_description' => 'Total de registros no sistema',
            'top_causer' => 'Principal Autor',
            'top_causer_description' => ':count atividades',
            'top_subject' => 'Principal Assunto',
            'top_subject_description' => ':count modificações',
            'no_data' => 'Sem dados',
        ],
    ],
    'dashboard' => [
        'title' => 'Audit Dashboard',
    ],
    'system' => 'Sistema',
    'row' => 'linha',
    'rows' => 'linhas',
];
