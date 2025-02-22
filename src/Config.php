<?php

return [
    'database' => [
        'host' => $_ENV['DATABASE_HOST'],
        'dbname' => $_ENV['DATABASE_NAME'],
        'user' => $_ENV['DATABASE_USER'],
        'password' => $_ENV['DATABASE_PASSWORD']
    ],
    'asaas' => [
        'environment' => $_ENV['ASAAS_ENVIRONMENT'],
        'api_keys' => [
            'sandbox' => $_ENV['ASAAS_SANDBOX_KEY'],
            'production' => $_ENV['ASAAS_PRODUCTION_KEY']
        ],
        'base_urls' => [
            'sandbox' => 'https://sandbox.asaas.com/api/v3',
            'production' => 'https://api.asaas.com/v3'
        ],
        'endpoints' => [
            'customers' => '/customers',
            'payments' => '/payments'
        ],
        'split' => [
            'enabled' => true,
            'walletId' => $_ENV['ASAAS_WALLET_ID'],
            'percentualValue' => 20
        ]
    ],
    'smtp' => [
        'host' => $_ENV['SMTP_HOST'],
        'port' => $_ENV['SMTP_PORT'],
        'username' => $_ENV['SMTP_USERNAME'],
        'password' => $_ENV['SMTP_PASSWORD'],
        'from_email' => $_ENV['SMTP_FROM_EMAIL'],
        'from_name' => $_ENV['SMTP_FROM_NAME']
    ],
    'cobranca' => [
        'valor' => 169.44,
        'assunto_email' => 'Contribuição Sindical Anual',
        'descricao' => 'Contribuição Sindical Anual',
        'vencimento_dias' => 5,
        'id_tipo_cobranca' => 1,
        'template' => 'Templates/enviar-email-cobranca.html',
        'boletos' => 'boletos/'
    ],
    'upload' => [
        'diretorio' => 'uploads/anexos/',
        'tipos_permitidos' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png'
        ],
        'tamanho_maximo' => 5242880 // 5MB em bytes
    ]
];
