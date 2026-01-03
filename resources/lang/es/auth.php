<?php

return [

    'failed' => 'Estas credenciales no coinciden con nuestros registros.',
    'password' => 'La contraseña es incorrecta.',
    'throttle' => 'Demasiados intentos de inicio de sesión. Por favor intente nuevamente en :seconds segundos.',

    'register' => [
        'title' => 'Crea tu cuenta',
        'description' => 'Ingresa tus datos para crear tu cuenta y workspace',
        'name' => 'Nombre completo',
        'name_placeholder' => 'Tu nombre',
        'last_name' => 'Apellidos',
        'last_name_placeholder' => 'Tus apellidos',
        'email' => 'Correo electrónico',
        'email_placeholder' => 'email@ejemplo.com',
        'tenant_name' => 'Nombre del negocio',
        'tenant_name_placeholder' => 'Mi Cafetería',
        'tenant_domain' => 'Dominio único',
        'tenant_domain_placeholder' => 'mi-empresa',
        'tenant_domain_help' => 'Este será tu subdominio único',
        'password' => 'Contraseña',
        'password_placeholder' => 'Contraseña',
        'password_confirmation' => 'Confirmar contraseña',
        'password_confirmation_placeholder' => 'Confirmar contraseña',
        'submit' => 'Crear cuenta',
        'already_registered' => '¿Ya tienes una cuenta?',
        'login_link' => 'Iniciar sesión',

        'validation' => [
            'name_required' => 'El nombre es obligatorio.',
            'name_string' => 'El nombre debe ser texto.',
            'name_max' => 'El nombre no puede superar los 255 caracteres.',
            'last_name_required' => 'Los apellidos son obligatorios.',
            'last_name_string' => 'Los apellidos deben ser texto.',
            'last_name_max' => 'Los apellidos no pueden superar los 255 caracteres.',
            'email_required' => 'El correo electrónico es obligatorio.',
            'email_email' => 'Debe proporcionar un correo electrónico válido.',
            'email_unique' => 'Este correo electrónico ya está registrado.',
            'password_required' => 'La contraseña es obligatoria.',
            'password_confirmed' => 'Las contraseñas no coinciden.',
            'tenant_name_required' => 'El nombre del workspace es obligatorio.',
            'tenant_name_max' => 'El nombre del workspace no puede superar los 255 caracteres.',
            'tenant_domain_required' => 'El dominio es obligatorio.',
            'tenant_domain_lowercase' => 'El dominio debe estar en minúsculas.',
            'tenant_domain_regex' => 'El dominio solo puede contener letras minúsculas, números y guiones.',
            'tenant_domain_unique' => 'Este dominio ya está en uso.',
        ],

        'activation' => [
            'subject' => 'Activa tu cuenta',
            'greeting' => '¡Hola :name!',
            'line1' => 'Gracias por registrarte. Por favor, activa tu cuenta haciendo clic en el botón a continuación.',
            'action' => 'Activar cuenta',
            'line2' => 'Este enlace expirará en 24 horas.',
            'line3' => 'Si no creaste esta cuenta, puedes ignorar este correo.',
            'sent' => 'Te hemos enviado un correo con el enlace de activación. Por favor revisa tu bandeja de entrada.',
            'success' => '¡Tu cuenta ha sido activada exitosamente!',
            'invalid_link' => 'El enlace de activación es inválido o ha expirado.',
            'already_activated' => 'Tu cuenta ya está activada.',
            'back_login' => 'Volver al inicio de sesión'
        ],
    ],

];
