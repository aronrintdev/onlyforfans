<?php
namespace App\Notifications;

use App\Channels\SendgridChannel;

trait NotifyTraits {

    protected function getUrl($slug, $attrs=null) 
    {
        switch ($slug) {
        case 'login':
            return url('/login');
        case 'privacy':
            return url('/privacy');
        case 'manage_preferences':
            return url( route('users.showSettings', $attrs['username']) );
        case 'unsubscribe':
            return url( route('users.showSettings', $attrs['username']) );
        case 'referrals':
            return url('/referrals'); 
        case 'verify':
            return url('/settings/verify'); 
        case 'password_reset':
            return url( route('password.reset', $attrs['token']) );
        case 'reply_to_message':
            return url( '/messages/'.$attrs['chatthread_id'] );
        case 'home':
        default:
            return url('/');
        }
    }

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

        $isGlobalEmailEnabled = ($settings->cattrs['notifications']['global']['enabled'] ?? false)
            ? in_array('email', $settings->cattrs['notifications']['global']['enabled'])
            : false;
        if ( $isGlobalEmailEnabled ) {
            return true; // global enable
        }

        $is = false; // default to false, possibly set to true below
        switch ($notifySlug) {
        case 'tip-received':
            $is = $settings->cattrs['notifications']['income']['new_tip'] ?? false;
            break;
        case 'comment-received':
            $is = $settings->cattrs['notifications']['posts']['new_comment'] ?? false;
            break;
        case 'new-campaign-contribution-received':
            $is = $settings->cattrs['notifications']['campaigns']['new_contribution'] ?? false;
            break;
        case 'new-message-received':
            $is = $settings->cattrs['notifications']['messages']['new_message'] ?? false;
            break;
        case 'new-sub-payment-received':
            $is = $settings->cattrs['notifications']['subscriptions']['new_payment'] ?? false;
            break;
        default:
        }
        return $is;
    }

}
