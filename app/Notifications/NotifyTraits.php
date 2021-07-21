<?php
namespace App\Notifications;

use App\Channels\SendgridChannel;

trait NotifyTraits {

    protected function getMailChannel() 
    {
        if ( env('DEBUG_BYPASS_SENDGRID_MAIL_NOTIFY', false) ) { 
            // uses MAIL_DRIVER instead of SendGrid API for notify emails
            return 'mail';
        } else {
            return \App\Channels\SendgridChannel::class; // DEBUG only
        }
    }

    protected function isMailChannelEnabled(string $notifySlug, $settings) : bool
    {
        if ( env('DEBUG_FORCE_ENABLE_MAIL_NOTIFY', false) ) { 
            // overrides any user settings to always send email
            return true; // DEBUG ONLY!
        }

        $is = false; // default to false, possibly set to true below
        switch ($notifySlug) {
        case 'tip-received':
            $exists = $settings->cattrs['notifications']['income']['new_tip'] ?? false;
            if ( $exists && is_array($exists) && in_array('email', $exists) ) {
                $is = true;
            }
            break;
        case 'comment-received':
            $exists = $settings->cattrs['notifications']['posts']['new_comment'] ?? false;
            if ( $exists && is_array($exists) && in_array('email', $exists) ) {
                $isGlobalEmailEnabled = ($settings->cattrs['notifications']['global']['enabled'] ?? false)
                    ? in_array('email', $settings->cattrs['notifications']['global']['enabled'])
                    : false;
                if ( $isGlobalEmailEnabled ) {
                    $channels[] =  'mail';
                }
            }
            break;
        }
        return $is;
    }

}
