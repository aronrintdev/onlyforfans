<?php return array (
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'api' => 
      array (
        'driver' => 'token',
        'provider' => 'users',
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_resets',
        'expire' => 60,
      ),
    ),
  ),
  'app' => 
  array (
    'name' => 'FansPlatform',
    'env' => 'server',
    'debug' => true,
    'url' => 'http://fansplatform.mjmdesign.co',
    'timezone' => 'UTC',
    'locales' => 
    array (
      'en' => 'English',
      'fr' => 'French',
      'es' => 'Spanish',
      'ar' => 'Arabic',
      'jp' => 'Japanese',
      'zh' => 'Chinese',
      'de' => 'German',
    ),
    'fallback_locale' => 'en',
    'key' => 'base64:nMubiL6dhJfzyBd2g+oo3i2xWVXE5IatII0+n9zCK8I=',
    'cipher' => 'AES-256-CBC',
    'log' => 'single',
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Cookie\\CookieServiceProvider',
      6 => 'Illuminate\\Database\\DatabaseServiceProvider',
      7 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      8 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      9 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      10 => 'Illuminate\\Hashing\\HashServiceProvider',
      11 => 'Illuminate\\Mail\\MailServiceProvider',
      12 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      14 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      15 => 'Illuminate\\Queue\\QueueServiceProvider',
      16 => 'Illuminate\\Redis\\RedisServiceProvider',
      17 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      18 => 'Illuminate\\Session\\SessionServiceProvider',
      19 => 'Illuminate\\Translation\\TranslationServiceProvider',
      20 => 'Illuminate\\Validation\\ValidationServiceProvider',
      21 => 'Illuminate\\View\\ViewServiceProvider',
      22 => 'App\\Providers\\AppServiceProvider',
      23 => 'App\\Providers\\AuthServiceProvider',
      24 => 'App\\Providers\\EventServiceProvider',
      25 => 'App\\Providers\\RouteServiceProvider',
      26 => 'Collective\\Html\\HtmlServiceProvider',
      27 => 'Laracasts\\Flash\\FlashServiceProvider',
      28 => 'Prettus\\Repository\\Providers\\RepositoryServiceProvider',
      29 => 'InfyOm\\Generator\\InfyOmGeneratorServiceProvider',
      30 => 'InfyOm\\CoreTemplates\\CoreTemplatesServiceProvider',
      31 => 'Teepluss\\Theme\\ThemeServiceProvider',
      32 => 'Alaouy\\Youtube\\YoutubeServiceProvider',
      33 => 'Intervention\\Image\\ImageServiceProvider',
      34 => 'Anhskohbo\\NoCaptcha\\NoCaptchaServiceProvider',
      35 => 'Laravel\\Socialite\\SocialiteServiceProvider',
      36 => 'Vinkla\\Pusher\\PusherServiceProvider',
      37 => 'Cmgmyr\\Messenger\\MessengerServiceProvider',
      38 => 'Zizaco\\Entrust\\EntrustServiceProvider',
      39 => 'Cviebrock\\EloquentSluggable\\ServiceProvider',
      40 => 'Vijaytupakula\\Transvel\\TransvelServiceProvider',
      41 => 'RachidLaasri\\LaravelInstaller\\Providers\\LaravelInstallerServiceProvider',
      42 => 'Mews\\Purifier\\PurifierServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Redis' => 'Illuminate\\Support\\Facades\\Redis',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Form' => 'Collective\\Html\\FormFacade',
      'Html' => 'Collective\\Html\\HtmlFacade',
      'Flash' => 'Laracasts\\Flash\\Flash',
      'Theme' => 'Teepluss\\Theme\\Facades\\Theme',
      'Image' => 'Intervention\\Image\\Facades\\Image',
      'Setting' => 'App\\Setting',
      'Socialite' => 'Laravel\\Socialite\\Facades\\Socialite',
      'LaravelPusher' => 'Vinkla\\Pusher\\Facades\\Pusher',
      'Entrust' => 'Zizaco\\Entrust\\EntrustFacade',
      'Purifier' => 'Mews\\Purifier\\Facades\\Purifier',
      'LinkPreview' => 'LinkPreview\\LinkPreview',
    ),
  ),
  'mail' => 
  array (
    'driver' => 'smtp',
    'host' => 'mail.fansplatform.mjmdesign.co',
    'port' => '465',
    'from' => 
    array (
      'address' => 'noreply@fansplatform.mjmdesign.co',
      'name' => 'FansPlatform',
    ),
    'encryption' => 'ssl',
    'username' => 'noreply@fansplatform.mjmdesign.co',
    'password' => '+PwG5GAG3nHm',
    'sendmail' => '/usr/sbin/sendmail -bs',
  ),
  'compile' => 
  array (
    'files' => 
    array (
    ),
    'providers' => 
    array (
    ),
  ),
  'services' => 
  array (
    'mailgun' => 
    array (
      'domain' => NULL,
      'secret' => NULL,
    ),
    'ses' => 
    array (
      'key' => NULL,
      'secret' => NULL,
      'region' => 'us-east-1',
    ),
    'sparkpost' => 
    array (
      'secret' => NULL,
    ),
    'stripe' => 
    array (
      'model' => 'App\\User',
      'key' => NULL,
      'secret' => NULL,
    ),
    'facebook' => 
    array (
      'client_id' => '1123492638036441',
      'client_secret' => '5c46107ea15e47eb4acd376f7aaf3aa9',
      'redirect' => 'http://fansplatform.mjmdesign.co/account/facebook',
    ),
    'google' => 
    array (
      'client_id' => '899405893553-7b4ddc2tkv58os56ucihdqt0ee63ob4t.apps.googleusercontent.com',
      'client_secret' => '0XqwVDTcbmbTo3cN2v8tPINa',
      'redirect' => 'http://fansplatform.mjmdesign.co/account/google',
    ),
    'twitter' => 
    array (
      'client_id' => '899405893553-7b4ddc2tkv58os56ucihdqt0ee63ob4t.apps.googleusercontent.com',
      'client_secret' => '0XqwVDTcbmbTo3cN2v8tPINa',
      'redirect' => 'http://fansplatform.mjmdesign.co/account/twitter',
    ),
    'linkedin' => 
    array (
      'client_id' => '899405893553-7b4ddc2tkv58os56ucihdqt0ee63ob4t.apps.googleusercontent.com',
      'client_secret' => '0XqwVDTcbmbTo3cN2v8tPINa',
      'redirect' => 'http://fansplatform.mjmdesign.co/account/linkedin',
    ),
  ),
  'database' => 
  array (
    'modes' => 
    array (
      0 => 'ONLY_FULL_GROUP_BY',
      1 => 'STRICT_TRANS_TABLES',
      2 => 'NO_ZERO_IN_DATE',
      3 => 'NO_ZERO_DATE',
      4 => 'ERROR_FOR_DIVISION_BY_ZERO',
      5 => 'NO_AUTO_CREATE_USER',
      6 => 'NO_ENGINE_SUBSTITUTION',
    ),
    'fetch' => 5,
    'default' => 'mysql',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'database' => 'fansplat_mysocialite',
        'prefix' => '',
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'fansplat_mysocialite',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'strict' => false,
        'engine' => NULL,
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'fansplat_mysocialite',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
      ),
    ),
    'migrations' => 'migrations',
    'redis' => 
    array (
      'cluster' => false,
      'default' => 
      array (
        'host' => 'localhost',
        'password' => NULL,
        'port' => 6379,
        'database' => 0,
      ),
    ),
  ),
  'cache' => 
  array (
    'default' => 'array',
    'stores' => 
    array (
      'apc' => 
      array (
        'driver' => 'apc',
      ),
      'array' => 
      array (
        'driver' => 'array',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'cache',
        'connection' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/storage/framework/cache',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
    ),
    'prefix' => 'laravel',
  ),
  'session' => 
  array (
    'driver' => 'database',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/storage/framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'laravel_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => false,
    'http_only' => true,
  ),
  'theme' => 
  array (
    'assetUrl' => '',
    'themeDefault' => 'default',
    'layoutDefault' => 'default',
    'themeDir' => 'themes',
    'containerDir' => 
    array (
      'layout' => 'layouts',
      'asset' => 'assets',
      'partial' => 'partials',
      'widget' => 'widgets',
      'view' => 'views',
    ),
    'namespaces' => 
    array (
      'widget' => 'App\\Widgets',
    ),
    'events' => 
    array (
      'before' => 'C:32:"SuperClosure\\SerializableClosure":219:{a:5:{s:4:"code";s:70:"function ($theme) {
    //$theme->setTitle(\'Something in global.\');
};";s:7:"context";a:0:{}s:7:"binding";N;s:5:"scope";s:49:"Illuminate\\Foundation\\Bootstrap\\LoadConfiguration";s:8:"isStatic";b:0;}}',
      'asset' => 'C:32:"SuperClosure\\SerializableClosure":597:{a:5:{s:4:"code";s:447:"function ($asset) {
    // Preparing asset you need to serve after.
    $asset->cook(\'backbone\', function ($asset) {
        $asset->add(\'backbone\', \'//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js\');
        $asset->add(\'underscorejs\', \'//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js\');
    });
    // To use cook \'backbone\' you can fire with \'serve\' method.
    // Theme::asset()->serve(\'backbone\');
};";s:7:"context";a:0:{}s:7:"binding";N;s:5:"scope";s:49:"Illuminate\\Foundation\\Bootstrap\\LoadConfiguration";s:8:"isStatic";b:0;}}',
    ),
    'engines' => 
    array (
      'twig' => 
      array (
        'allows' => 
        array (
          0 => 'Auth',
          1 => 'Cache',
          2 => 'Config',
          3 => 'Cookie',
          4 => 'Form',
          5 => 'HTML',
          6 => 'Input',
          7 => 'Lang',
          8 => 'Paginator',
          9 => 'Str',
          10 => 'Theme',
          11 => 'URL',
          12 => 'Validator',
        ),
        'hooks' => 'C:32:"SuperClosure\\SerializableClosure":524:{a:5:{s:4:"code";s:374:"function ($twig) {
    // Example add funciton name "demo".
    /*$function = new Twig_SimpleFunction(\'example\', function()
                    {
                        $args = func_get_args();
    
                        return "Example" . print_r($args, true);
                    });
    
                    $twig->addFunction($function);*/
    return $twig;
};";s:7:"context";a:0:{}s:7:"binding";N;s:5:"scope";s:49:"Illuminate\\Foundation\\Bootstrap\\LoadConfiguration";s:8:"isStatic";b:0;}}',
      ),
    ),
  ),
  'purifier' => 
  array (
    'encoding' => 'UTF-8',
    'finalize' => true,
    'cachePath' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/storage/app/purifier',
    'cacheFileMode' => 493,
    'settings' => 
    array (
      'default' => 
      array (
        'HTML.Doctype' => 'HTML 4.01 Transitional',
        'Attr.AllowedFrameTargets' => '_blank',
        'HTML.Allowed' => 'h1[class],h2[class],h3[class],h4[class],h5[class],div[class],b,strong,i,em,a[href|title|class|target],ul,ol,li,p[style|class],br,span[style|class],img[class|width|height|alt|src|style]',
        'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align,width,height',
        'AutoFormat.AutoParagraph' => true,
        'AutoFormat.RemoveEmpty' => true,
      ),
      'test' => 
      array (
        'Attr.EnableID' => 'true',
      ),
      'youtube' => 
      array (
        'HTML.SafeIframe' => 'true',
        'URI.SafeIframeRegexp' => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%',
      ),
      'custom_definition' => 
      array (
        'id' => 'html5-definitions',
        'rev' => 1,
        'debug' => false,
        'elements' => 
        array (
          0 => 
          array (
            0 => 'section',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          1 => 
          array (
            0 => 'nav',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          2 => 
          array (
            0 => 'article',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          3 => 
          array (
            0 => 'aside',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          4 => 
          array (
            0 => 'header',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          5 => 
          array (
            0 => 'footer',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          6 => 
          array (
            0 => 'address',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
          ),
          7 => 
          array (
            0 => 'hgroup',
            1 => 'Block',
            2 => 'Required: h1 | h2 | h3 | h4 | h5 | h6',
            3 => 'Common',
          ),
          8 => 
          array (
            0 => 'figure',
            1 => 'Block',
            2 => 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow',
            3 => 'Common',
          ),
          9 => 
          array (
            0 => 'figcaption',
            1 => 'Inline',
            2 => 'Flow',
            3 => 'Common',
          ),
          10 => 
          array (
            0 => 'video',
            1 => 'Block',
            2 => 'Optional: (source, Flow) | (Flow, source) | Flow',
            3 => 'Common',
            4 => 
            array (
              'src' => 'URI',
              'type' => 'Text',
              'width' => 'Length',
              'height' => 'Length',
              'poster' => 'URI',
              'preload' => 'Enum#auto,metadata,none',
              'controls' => 'Bool',
            ),
          ),
          11 => 
          array (
            0 => 'source',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
            4 => 
            array (
              'src' => 'URI',
              'type' => 'Text',
            ),
          ),
          12 => 
          array (
            0 => 's',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          13 => 
          array (
            0 => 'var',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          14 => 
          array (
            0 => 'sub',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          15 => 
          array (
            0 => 'sup',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          16 => 
          array (
            0 => 'mark',
            1 => 'Inline',
            2 => 'Inline',
            3 => 'Common',
          ),
          17 => 
          array (
            0 => 'wbr',
            1 => 'Inline',
            2 => 'Empty',
            3 => 'Core',
          ),
          18 => 
          array (
            0 => 'ins',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
            4 => 
            array (
              'cite' => 'URI',
              'datetime' => 'CDATA',
            ),
          ),
          19 => 
          array (
            0 => 'del',
            1 => 'Block',
            2 => 'Flow',
            3 => 'Common',
            4 => 
            array (
              'cite' => 'URI',
              'datetime' => 'CDATA',
            ),
          ),
        ),
        'attributes' => 
        array (
          0 => 
          array (
            0 => 'iframe',
            1 => 'allowfullscreen',
            2 => 'Bool',
          ),
          1 => 
          array (
            0 => 'table',
            1 => 'height',
            2 => 'Text',
          ),
          2 => 
          array (
            0 => 'td',
            1 => 'border',
            2 => 'Text',
          ),
          3 => 
          array (
            0 => 'th',
            1 => 'border',
            2 => 'Text',
          ),
          4 => 
          array (
            0 => 'tr',
            1 => 'width',
            2 => 'Text',
          ),
          5 => 
          array (
            0 => 'tr',
            1 => 'height',
            2 => 'Text',
          ),
          6 => 
          array (
            0 => 'tr',
            1 => 'border',
            2 => 'Text',
          ),
        ),
      ),
      'custom_elements' => 
      array (
        0 => 
        array (
          0 => 'u',
          1 => 'Inline',
          2 => 'Inline',
          3 => 'Common',
        ),
      ),
    ),
  ),
  'checkout' => 
  array (
    'publishable_key' => 'pk_test_kd9HWNDTQUWGyZOyNCZxnEGc00aJqDeuHG',
    'secret_key' => 'sk_test_DP3udsRoL7eUS1SAKxWiMuGf00ykXU5JjG',
    'client_id' => 'ca_HJLaNwakmU9S2RbgHJymewd826j6Wbtb',
    'platform_fee' => 10,
  ),
  'queue' => 
  array (
    'default' => 'sync',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => 'your-public-key',
        'secret' => 'your-secret-key',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'your-queue-name',
        'region' => 'us-east-1',
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
      ),
    ),
    'failed' => 
    array (
      'database' => 'mysql',
      'table' => 'failed_jobs',
    ),
  ),
  'infyom' => 
  array (
    'laravel_generator' => 
    array (
      'path' => 
      array (
        'migration' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/database/migrations/',
        'model' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app//',
        'datatables' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app/DataTables/',
        'repository' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app/Repositories/',
        'routes' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app/Http/routes.php',
        'api_routes' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app/Http/api_routes.php',
        'request' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app/Http/Requests/',
        'api_request' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app/Http/Requests/API/',
        'controller' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app/Http/Controllers/',
        'api_controller' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app/Http/Controllers/API/',
        'test_trait' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/tests/traits/',
        'repository_test' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/tests/',
        'api_test' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/tests/',
        'views' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/resources/views/',
        'schema_files' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/resources/model_schemas/',
        'templates_dir' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/resources/infyom/infyom-generator-templates/',
      ),
      'namespace' => 
      array (
        'model' => 'App',
        'datatables' => 'App\\DataTables',
        'repository' => 'App\\Repositories',
        'controller' => 'App\\Http\\Controllers',
        'api_controller' => 'App\\Http\\Controllers\\API',
        'request' => 'App\\Http\\Requests',
        'api_request' => 'App\\Http\\Requests\\API',
      ),
      'templates' => 'core-templates',
      'model_extend_class' => 'Eloquent',
      'api_prefix' => 'api',
      'api_version' => 'v1',
      'options' => 
      array (
        'softDelete' => true,
        'tables_searchable_default' => false,
      ),
      'add_on' => 
      array (
        'swagger' => true,
        'tests' => true,
        'datatables' => false,
        'menu' => 
        array (
          'enabled' => false,
          'menu_file' => 'layouts/menu.blade.php',
        ),
      ),
      'timestamps' => 
      array (
        'enabled' => true,
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'deleted_at' => 'deleted_at',
      ),
    ),
  ),
  'messenger' => 
  array (
    'user_model' => 'App\\User',
    'message_model' => 'Cmgmyr\\Messenger\\Models\\Message',
    'participant_model' => 'Cmgmyr\\Messenger\\Models\\Participant',
    'thread_model' => 'Cmgmyr\\Messenger\\Models\\Thread',
    'messages_table' => NULL,
    'participants_table' => NULL,
    'threads_table' => NULL,
  ),
  'google-translate' => 
  array (
    'api_key' => NULL,
    'translate_url' => 'https://www.googleapis.com/language/translate/v2',
    'detect_url' => 'https://www.googleapis.com/language/translate/v2/detect',
  ),
  'pusher' => 
  array (
    'default' => 'main',
    'connections' => 
    array (
      'main' => 
      array (
        'auth_key' => 'b000c16250c7a5b98f67',
        'secret' => '26a1f40964bbaefcf0f6',
        'app_id' => '934442',
        'options' => 
        array (
          'cluster' => 'mt1',
          'encrypted' => false,
        ),
        'host' => NULL,
        'port' => NULL,
        'timeout' => NULL,
      ),
      'alternative' => 
      array (
        'auth_key' => 'your-auth-key',
        'secret' => 'your-secret',
        'app_id' => 'your-app-id',
        'options' => 
        array (
        ),
        'host' => NULL,
        'port' => NULL,
        'timeout' => NULL,
      ),
    ),
  ),
  'broadcasting' => 
  array (
    'default' => 'pusher',
    'connections' => 
    array (
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => 'b000c16250c7a5b98f67',
        'secret' => '26a1f40964bbaefcf0f6',
        'app_id' => '934442',
        'options' => 
        array (
          'cluster' => 'mt1',
          'encrypted' => false,
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
    ),
  ),
  'flavy' => 
  array (
    'ffmpeg_path' => '/usr/bin/ffmpeg',
    'ffprobe_path' => '/usr/bin/ffprobe',
  ),
  'debugbar' => 
  array (
    'enabled' => NULL,
    'storage' => 
    array (
      'enabled' => true,
      'driver' => 'file',
      'path' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/storage/debugbar',
      'connection' => NULL,
      'provider' => '',
    ),
    'include_vendors' => true,
    'capture_ajax' => true,
    'clockwork' => false,
    'collectors' => 
    array (
      'phpinfo' => true,
      'messages' => true,
      'time' => true,
      'memory' => true,
      'exceptions' => true,
      'log' => true,
      'db' => true,
      'views' => true,
      'route' => true,
      'laravel' => false,
      'events' => false,
      'default_request' => false,
      'symfony_request' => true,
      'mail' => true,
      'logs' => false,
      'files' => false,
      'config' => false,
      'auth' => false,
      'gate' => false,
      'session' => true,
    ),
    'options' => 
    array (
      'auth' => 
      array (
        'show_name' => false,
      ),
      'db' => 
      array (
        'with_params' => true,
        'timeline' => false,
        'backtrace' => false,
        'explain' => 
        array (
          'enabled' => false,
          'types' => 
          array (
            0 => 'SELECT',
          ),
        ),
        'hints' => true,
      ),
      'mail' => 
      array (
        'full_log' => false,
      ),
      'views' => 
      array (
        'data' => false,
      ),
      'route' => 
      array (
        'label' => true,
      ),
      'logs' => 
      array (
        'file' => NULL,
      ),
    ),
    'inject' => true,
    'route_prefix' => '_debugbar',
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/resources/views',
    ),
    'compiled' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/storage/framework/views',
  ),
  'entrust' => 
  array (
    'role' => 'App\\Role',
    'roles_table' => 'roles',
    'permission' => 'App\\Permission',
    'permissions_table' => 'permissions',
    'permission_role_table' => 'permission_role',
    'role_user_table' => 'role_user',
    'user_foreign_key' => 'user_id',
    'role_foreign_key' => 'role_id',
  ),
  'captcha' => 
  array (
    'secret' => '',
    'sitekey' => '',
  ),
  'sluggable' => 
  array (
    'source' => NULL,
    'maxLength' => NULL,
    'method' => NULL,
    'separator' => '-',
    'unique' => true,
    'uniqueSuffix' => NULL,
    'includeTrashed' => false,
    'reserved' => NULL,
    'onUpdate' => false,
  ),
  'installer' => 
  array (
    'requirements' => 
    array (
      'php' => 
      array (
        0 => 'openssl',
        1 => 'pdo',
        2 => 'mbstring',
        3 => 'tokenizer',
      ),
      'apache' => 
      array (
        0 => 'mod_rewrite',
      ),
    ),
    'permissions' => 
    array (
      'storage/framework/' => '775',
      'storage/logs/' => '775',
      'bootstrap/cache/' => '775',
    ),
  ),
  'youtube' => 
  array (
    'KEY' => 'AIzaSyB4xSPAHW4XU6QgcKhuRq2y522B15FHdpc',
  ),
  'image' => 
  array (
    'driver' => 'gd',
  ),
  'repository' => 
  array (
    'pagination' => 
    array (
      'limit' => 15,
    ),
    'fractal' => 
    array (
      'params' => 
      array (
        'include' => 'include',
      ),
      'serializer' => 'League\\Fractal\\Serializer\\DataArraySerializer',
    ),
    'cache' => 
    array (
      'enabled' => true,
      'minutes' => 30,
      'repository' => 'cache',
      'clean' => 
      array (
        'enabled' => true,
        'on' => 
        array (
          'create' => true,
          'update' => true,
          'delete' => true,
        ),
      ),
      'params' => 
      array (
        'skipCache' => 'skipCache',
      ),
      'allowed' => 
      array (
        'only' => NULL,
        'except' => NULL,
      ),
    ),
    'criteria' => 
    array (
      'acceptedConditions' => 
      array (
        0 => '=',
        1 => 'like',
      ),
      'params' => 
      array (
        'search' => 'search',
        'searchFields' => 'searchFields',
        'filter' => 'filter',
        'orderBy' => 'orderBy',
        'sortedBy' => 'sortedBy',
        'with' => 'with',
      ),
    ),
    'generator' => 
    array (
      'basePath' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/app',
      'rootNamespace' => 'App\\',
      'paths' => 
      array (
        'models' => 'Entities',
        'repositories' => 'Repositories',
        'interfaces' => 'Repositories',
        'transformers' => 'Transformers',
        'presenters' => 'Presenters',
        'validators' => 'Validators',
        'controllers' => 'Http/Controllers',
        'provider' => 'RepositoryServiceProvider',
      ),
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'cloud' => 's3',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/storage/app',
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/storage/app/public',
        'visibility' => 'public',
      ),
      'uploads' => 
      array (
        'driver' => 'local',
        'root' => '/Users/admin/Documents/Work/Rentmerchant/FansPlatform/fans-platform/storage/uploads',
        'visibility' => 'public',
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => 'your-key',
        'secret' => 'your-secret',
        'region' => 'your-region',
        'bucket' => 'your-bucket',
      ),
    ),
  ),
);
