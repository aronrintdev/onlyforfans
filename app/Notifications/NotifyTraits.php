<?php
namespace App\Notifications;

use Illuminate\Support\Facades\Config;
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
        if ( Config('sendgrid.debug.bypass_sendgrid_mail_notify', false) ) {
            // uses MAIL_DRIVER for notify emails
            return 'mail';
        } else {
            // uses SendGrid API for notify emails
            return \App\Channels\SendgridChannel::class;
        }
    }

    protected function isMailChannelEnabled(string $notifySlug, $settings) : bool
    {
        if ( Config('sendgrid.debug.force_email_notify', false) ) {
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

        // Avoid undefined index Exception Fallback if notifications settings is not set
        if (!isset($settings->cattrs['notifications'])) {
            return $is;
        }
        $notifications = $settings->cattrs['notifications'];
        switch ($notifySlug) {
            // campaigns
            case 'new-campaign-contribution-received':
                if (!isset($notifications['campaigns'])) {
                    break;
                }
                $is = $notifications['campaigns']['new_contribution'] ? true : false;
                break;
            case 'campaign-goal-reached':
                if (!isset($notifications['campaigns'])) {
                    break;
                }
                $is = $notifications['campaigns']['goal_reached'] ? true : false;
                break;
            // income
            case 'tip-received':
                if (!isset($notifications['income'])) {
                    break;
                }
                $is = $notifications['income']['new_tip'] ? true : false;
                break;
            // messages
            case 'new-message-received':
                if (!isset($notifications['messages'])) {
                    break;
                }
                $is = $notifications['messages']['new_message'] ? true : false;
                break;
            // taggables
            case 'tag_received':
                if (!isset($notifications['usertags'])) {
                    break;
                }
                $is = $notifications['usertags']['new_tag'] ? true : false;
                break;
            // likeables
            case 'like-received':
                if (!isset($notifications['posts'])) {
                    break;
                }
                $is = $notifications['posts']['new_like'] ? true : false;
                break;
            // commentables
            case 'comment-received':
                if (!isset($notifications['posts'])) {
                    break;
                }
                $is = $notifications['posts']['new_comment'] ? true : false;
                break;
            // referrals
            case 'new-referral-received':
                if (!isset($notifications['referrals'])) {
                    break;
                }
                $is = $notifications['referrals']['new_referral'] ? true : false;
                break;
            // subscriptions
            case 'new-sub-payment-received':
                if (!isset($notifications['subscriptions'])) {
                    break;
                }
                $is = $notifications['subscriptions']['new_payment'] ? true : false;
                break;
            default:
        }
        return $is;
    }

}
