<?php

return [

    'failed' => 'Estas credenciais non coinciden cos nosos rexistros.',
    'password' => 'O contrasinal é incorrecto.',
    'throttle' => 'Demasiados intentos de inicio de sesión. Téntao de novo en :seconds segundos.',

    'register' => [
        'title' => 'Crea a túa conta',
        'description' => 'Introduce os teus datos para crear a conta e o workspace',
        'name' => 'Nome completo',
        'name_placeholder' => 'O teu nome',
        'last_name' => 'Apelidos',
        'last_name_placeholder' => 'Os teus apelidos',
        'email' => 'Correo electrónico',
        'email_placeholder' => 'correo@exemplo.com',
        'tenant_name' => 'Nome do negocio',
        'tenant_name_placeholder' => 'A miña cafetería',
        'tenant_domain' => 'Dominio único',
        'tenant_domain_placeholder' => 'a-mina-empresa',
        'tenant_domain_help' => 'Este será o teu subdominio único',
        'password' => 'Contrasinal',
        'password_placeholder' => 'Contrasinal',
        'password_confirmation' => 'Confirmar contrasinal',
        'password_confirmation_placeholder' => 'Confirmar contrasinal',
        'submit' => 'Crear conta',
        'already_registered' => 'Xa tes unha conta?',
        'login_link' => 'Iniciar sesión',

        'terms_accept_prefix' => 'Lin e acepto os',
        'terms_accept_terms' => 'Termos de servizo',
        'terms_accept_and' => 'e a',
        'terms_accept_privacy' => 'Política de privacidade',

        'validation' => [
            'terms_required' => 'Debes aceptar os termos e a política de privacidade para continuar.',
            'name_required' => 'O nome é obrigatorio.',
            'name_string' => 'O nome debe ser texto.',
            'name_max' => 'O nome non pode superar os 255 caracteres.',
            'last_name_required' => 'Os apelidos son obrigatorios.',
            'last_name_string' => 'Os apelidos deben ser texto.',
            'last_name_max' => 'Os apelidos non poden superar os 255 caracteres.',
            'email_required' => 'O correo electrónico é obrigatorio.',
            'email_email' => 'Debes proporcionar un correo electrónico válido.',
            'email_unique' => 'Este correo electrónico xa está rexistrado.',
            'password_required' => 'O contrasinal é obrigatorio.',
            'password_confirmed' => 'Os contrasinais non coinciden.',
            'tenant_name_required' => 'O nome do workspace é obrigatorio.',
            'tenant_name_max' => 'O nome do workspace non pode superar os 255 caracteres.',
            'tenant_domain_required' => 'O dominio é obrigatorio.',
            'tenant_domain_lowercase' => 'O dominio debe estar en minúsculas.',
            'tenant_domain_regex' => 'O dominio só pode conter letras minúsculas, números e guións.',
            'tenant_domain_unique' => 'Este dominio xa está en uso.',
        ],

        'attributes' => [
            'terms_accepted' => 'termos e condicións',
        ],

        'activation' => [
            'subject' => 'Activa a túa conta',
            'greeting' => 'Ola :name!',
            'line1' => 'Grazas por rexistrarte. Activa a túa conta facendo clic no botón.',
            'action' => 'Activar conta',
            'line2' => 'Esta ligazón expirará en 24 horas.',
            'line3' => 'Se non creaches esta conta, podes ignorar este correo.',
            'sent' => 'Enviámosche un correo coa ligazón de activación. Revisa a túa caixa de entrada.',
            'success' => 'A túa conta foi activada correctamente!',
            'invalid_link' => 'A ligazón de activación non é válida ou expirou.',
            'already_activated' => 'A túa conta xa está activada.',
            'back_login' => 'Volver ao inicio de sesión',
        ],
    ],

];
