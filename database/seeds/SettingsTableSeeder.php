<?php

namespace Database\Seeders;

use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    /** Run in all environments */
    protected $environments = [ 'all' ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = ['comment_privacy'                    => 'everyone',
                            'confirm_follow'              => 'no',
                            'follow_privacy'              => 'everyone',
                            'user_timeline_post_privacy'  => 'everyone',
                            'post_privacy'                => 'everyone',
                            'page_message_privacy'        => 'everyone',
                            'page_timeline_post_privacy'  => 'everyone',
                            'page_member_privacy'         => 'only_admins',
                            'member_privacy'              => 'only_admins',
                            'group_timeline_post_privacy' => 'members',
                            'group_member_privacy'        => 'only_admins',
                            'site_name'                   => 'AllFans',
                            'site_title'                  => 'AllFans',
                            'site_url'                    => 'allfans.com',
                            'site_image'                  => 'https://www.allfans.com/images/logos/allfans-logo-986x205.png',
                            'site_type'                   => 'website',
                            'meta_description'            => 'AllFans is a social networking service on which users post and interact with their fans.',
                            'meta_keywords'               => 'AllFans',
                            'twitter_link'                => 'http://twitter.com/',
                            'facebook_link'               => 'http://facebook.com/',
                            'youtube_link'                => 'http://youtube.com/',
                            'support_email'               => 'admin@fans.com',
                            'mail_verification'           => 'off',
                            'captcha'                     => 'off',
                            'censored_words'              => '',
                            'birthday'                    => 'off',
                            'city'                        => 'off',
                            'about'                       => 'fans is the FIRST Social networking script developed on Laravel with all enhanced features, Pixel perfect design and extremely user friendly. User interface and user experience are extra added features to fans. Months of research, passion and hard work had made the fans more flexible, feature-available and very user friendly!',
                            'contact_text'                => 'Contact page description can be here',
                            'address_on_mail'             => '<strong>fans</strong>,<br> fans street,<br> India',
                            'items_page'                  => '10',
                            'min_items_page'              => '5',
                            'user_message_privacy'        => 'everyone',
                            'footer_languages'            => 'on',
                            'linkedin_link'               => 'http://linkedin.com/',
                            'instagram_link'              => 'http://instagram.com/',
                            'dribbble_link'               => 'http://dribbble.com/',
                            'home_welcome_message'        => 'Welcome To fans Laravel Application',
                            'home_widget_one'             => 'Developed on Twitter Bootstrap which makes the application fully responsive on Desktop, Tablet and Mobile',
                            'home_widget_two'             => 'Powerful Admin panel to manage entire application and all kinds of timelines',
                            'home_widget_three'           => 'Emoticons, hashtags, music, youtube video, photos, hangouts and many others can be posted',
                            'home_list_heading'           => 'Enhancing Features of fans',
                            'home_feature_one_icon'       => 'users',
                            'home_feature_one'            => 'Find and connect with real people living through out the world',
                            'home_feature_two_icon'       => 'share-alt',
                            'home_feature_two'            => 'Share your posts in other social networks',
                            'home_feature_three_icon'       => 'link',
                            'home_feature_three'            => 'Add links in your posts with new innovative look',
                            'home_feature_four_icon'       => 'bullhorn',
                            'home_feature_four'            => 'Place your google Adsense through out the application',
                            'home_feature_five_icon'       => 'connectdevelop',
                            'home_feature_five'            => 'Connect to fans through Facebook, Twitter, Google Plus and Instagram',
                            'home_feature_six_icon'       => 'save',
                            'home_feature_six'            => 'Now you can save your favourite posts, pages, groups and events',
                            'home_feature_seven_icon'       => 'file-photo-o',
                            'home_feature_seven'            => 'Create your albums and upload the pictures right now',
                            'home_feature_eight_icon'       => 'flag-o',
                            'home_feature_eight'            => 'Any page or a post or a group or an event can be reported',
                            'home_feature_nine_icon'       => 'language',
                            'home_feature_nine'            => 'fans is multi-lingual and available in 16 languages',
                            'home_feature_ten_icon'       => 'user-plus',
                            'home_feature_ten'            => 'Affiliate System adds an extra flavour to fans',
                            ];

        foreach ($settings as $key => $value) {
            $settings = Setting::firstOrNew(['key' => $key, 'value' => $value]);
            $settings->save();
        }
    }
}
