<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'register' => [
        'title' => 'Create your account',
        'description' => 'Enter your details to create your account and workspace',
        'name' => 'First name',
        'name_placeholder' => 'Your name',
        'last_name' => 'Last name',
        'last_name_placeholder' => 'Your last name',
        'email' => 'Email address',
        'email_placeholder' => 'email@example.com',
        'tenant_name' => 'Business name',
        'tenant_name_placeholder' => 'My Coffee Shop',
        'tenant_domain' => 'Unique domain',
        'tenant_domain_placeholder' => 'my-business',
        'tenant_domain_help' => 'This will be your unique subdomain',
        'password' => 'Password',
        'password_placeholder' => 'Password',
        'password_confirmation' => 'Confirm password',
        'password_confirmation_placeholder' => 'Confirm password',
        'submit' => 'Create account',
        'already_registered' => 'Already have an account?',
        'login_link' => 'Log in',

        'terms_accept_prefix' => 'I have read and accept the',
        'terms_accept_terms' => 'Terms of Service',
        'terms_accept_and' => 'and the',
        'terms_accept_privacy' => 'Privacy Policy',

        'validation' => [
            'terms_required' => 'You must accept the terms and privacy policy to continue.',
            'name_required' => 'Name is required.',
            'name_string' => 'Name must be text.',
            'name_max' => 'Name cannot exceed 255 characters.',
            'last_name_required' => 'Last name is required.',
            'last_name_string' => 'Last name must be text.',
            'last_name_max' => 'Last name cannot exceed 255 characters.',
            'email_required' => 'Email is required.',
            'email_email' => 'Please provide a valid email address.',
            'email_unique' => 'This email is already registered.',
            'password_required' => 'Password is required.',
            'password_confirmed' => 'Passwords do not match.',
            'tenant_name_required' => 'Workspace name is required.',
            'tenant_name_max' => 'Workspace name cannot exceed 255 characters.',
            'tenant_domain_required' => 'Domain is required.',
            'tenant_domain_lowercase' => 'Domain must be lowercase.',
            'tenant_domain_regex' => 'Domain can only contain lowercase letters, numbers, and hyphens.',
            'tenant_domain_unique' => 'This domain is already in use.',
        ],

        'attributes' => [
            'terms_accepted' => 'terms and conditions',
        ],

        'activation' => [
            'subject' => 'Activate your account',
            'greeting' => 'Hello :name!',
            'line1' => 'Thank you for signing up. Please activate your account by clicking the button below.',
            'action' => 'Activate account',
            'line2' => 'This link will expire in 24 hours.',
            'line3' => 'If you did not create this account, you can ignore this email.',
            'sent' => 'We have sent you an email with the activation link. Please check your inbox.',
            'success' => 'Your account has been activated successfully!',
            'invalid_link' => 'The activation link is invalid or has expired.',
            'already_activated' => 'Your account is already activated.',
            'back_login' => 'Back to login',
        ],
    ],

];
