<?php

return [
    'api_key' => env('SENDGRID_API_KEY'),

    'debug' => [
        // Overrides any user settings to always send emails 
        // %NOTE: actually this isn't specific to SendGrid
        'force_enable_mail_notify' => env('DEBUG_FORCE_ENABLE_MAIL_NOTIFY', false), 

        // Uses MAIL_DRIVER instead of SendGrid API for notify emails (eg, log or mailtrap.io)
        'bypass_sendgrid_mail_notify' => env('DEBUG_BYPASS_SENDGRID_MAIL_NOTIFY', false), 

        // SendGrid 'sandbox mode'
        'enable_sandbox_mode' => env('DEBUG_ENABLE_SENDGRID_SANDBOX_MODE', false),  

        // Force email to specific recipient for testing/dev !
        //  ~ contains false or the override email to use in place of the real reciepient
        'override_to_email' => env('DEBUG_OVERRIDE_TO_EMAIL_FOR_SENDGRID', false),
    ],

];
