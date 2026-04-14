<?php

return [

    'failed' => 'Aquestes credencials no coincideixen amb els nostres registres.',
    'password' => 'La contrasenya és incorrecta.',
    'throttle' => 'Massa intents d\'inici de sessió. Si us plau, torna-ho a provar en :seconds segons.',

    'register' => [
        'title' => 'Crea el teu compte',
        'description' => 'Introdueix les teves dades per crear el compte i el workspace',
        'name' => 'Nom complet',
        'name_placeholder' => 'El teu nom',
        'last_name' => 'Cognoms',
        'last_name_placeholder' => 'Els teus cognoms',
        'email' => 'Correu electrònic',
        'email_placeholder' => 'correu@exemple.com',
        'tenant_name' => 'Nom del negoci',
        'tenant_name_placeholder' => 'La meva cafeteria',
        'tenant_domain' => 'Domini únic',
        'tenant_domain_placeholder' => 'la-meva-empresa',
        'tenant_domain_help' => 'Aquest serà el teu subdomini únic',
        'password' => 'Contrasenya',
        'password_placeholder' => 'Contrasenya',
        'password_confirmation' => 'Confirmar contrasenya',
        'password_confirmation_placeholder' => 'Confirmar contrasenya',
        'submit' => 'Crear compte',
        'already_registered' => 'Ja tens un compte?',
        'login_link' => 'Iniciar sessió',

        'terms_accept_prefix' => 'He llegit i accepto els',
        'terms_accept_terms' => 'Termes de servei',
        'terms_accept_and' => 'i la',
        'terms_accept_privacy' => 'Política de privacitat',

        'validation' => [
            'terms_required' => 'Has d\'acceptar els termes i la política de privacitat per continuar.',
            'name_required' => 'El nom és obligatori.',
            'name_string' => 'El nom ha de ser text.',
            'name_max' => 'El nom no pot superar els 255 caràcters.',
            'last_name_required' => 'Els cognoms són obligatoris.',
            'last_name_string' => 'Els cognoms han de ser text.',
            'last_name_max' => 'Els cognoms no poden superar els 255 caràcters.',
            'email_required' => 'El correu electrònic és obligatori.',
            'email_email' => 'Has de proporcionar un correu electrònic vàlid.',
            'email_unique' => 'Aquest correu electrònic ja està registrat.',
            'password_required' => 'La contrasenya és obligatòria.',
            'password_confirmed' => 'Les contrasenyes no coincideixen.',
            'tenant_name_required' => 'El nom del workspace és obligatori.',
            'tenant_name_max' => 'El nom del workspace no pot superar els 255 caràcters.',
            'tenant_domain_required' => 'El domini és obligatori.',
            'tenant_domain_lowercase' => 'El domini ha d\'estar en minúscules.',
            'tenant_domain_regex' => 'El domini només pot contenir lletres minúscules, números i guions.',
            'tenant_domain_unique' => 'Aquest domini ja s\'està utilitzant.',
        ],

        'attributes' => [
            'terms_accepted' => 'termes i condicions',
        ],

        'activation' => [
            'subject' => 'Activa el teu compte',
            'greeting' => 'Hola :name!',
            'line1' => 'Gràcies per registrar-te. Activa el teu compte fent clic al botó.',
            'action' => 'Activar compte',
            'line2' => 'Aquest enllaç expirarà en 24 hores.',
            'line3' => 'Si no has creat aquest compte, pots ignorar aquest correu.',
            'sent' => 'T\'hem enviat un correu amb l\'enllaç d\'activació. Revisa la safata d\'entrada.',
            'success' => 'El teu compte s\'ha activat correctament!',
            'invalid_link' => 'L\'enllaç d\'activació no és vàlid o ha expirat.',
            'already_activated' => 'El teu compte ja està activat.',
            'back_login' => 'Tornar a l\'inici de sessió',
        ],
    ],

];
