# startpeeps-laravel

## setup social logins

- Google
    - Set your Client id and Client secret
    - While testing the google login into local you have to change `APP_URL=http://localhost`  and need to set the google redirect url `http://localhost/account/google` this is for local, while setting up the live you have to set `APP_URL/account/google`
    - Now wn you do login it will redirect to url which is start from `localhost/...` just change the `localhost` to your virtual host something like `virtual-host/...` and google login works.
    - Please note that for local you have to do this manually, for live it will work fine without this url replacing steps.

    - ```GOOGLE_CLIENT_SECRET=```
    - ```GOOGLE_REDIRECT=http://localhost/account/google```

- Facebook
    - Set your client id and client secret
    - Do the same steps for facebook login, for facebook login into local you don't need to setup the redirect URL.

    - ```FACEBOOK_CLIENT_ID=```
    - ```FACEBOOK_CLIENT_SECRET=TWITTER_CLIENT_ID=```

- Twitter
    - Set your client id and client secret
    - For twitter set redirect URL to `YOUR_VIRTUAL_HOST/account/twitter`

    - ```TWITTER_CLIENT_SECRET=```
    - ```TWITTER_REDIRECT=http://localhost/account/twitterGOOGLE_CLIENT_ID=```

## Application Setup
### Composer Installation
- If you do not already have [composer](https://getcomposer.org/), install it.
- For development:
```
composer install
```
- For production use:
```
composer install --no-dev --optimize-autoloader
```
- For Automated deployment Script:
```
composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
```

### Setting Up .env File (Environment Variables)
#### Application Settings
| Variable      | Type   | Explanation                                                           | Local Development         |
|---------------|--------|-----------------------------------------------------------------------|---------------------------|
| APP_ENV       | string | Environment the application is                                        | `local`                   |
| APP_DEBUG     | bool   | If debugging is turned on in the application, turn off for production | `true`                    |
| APP_KEY       | sting  | Generate with `php artisan key:generate`                              |                           |
| APP_URL       | string | The base url of the application                                       | `"http://localhost:8000"` |
| APP_FULL_NAME | string | Application name                                                      | `Fans Dev`                |

#### Laravel Settings
| Variable         | Type   | Explanation                              | Local Development |
|------------------|--------|------------------------------------------|-------------------|
| CACHE_DRIVER     | string | Which Laravel cache driver to use        | `array`           |
| SESSION_DRIVER   | string | Which Laravel session driver to use      | `database`        |
| SESSION_LIFETIME | number | The timeout length for a laravel session | `86400`           |
| QUEUE_DRIVER     | string | Which Laravel queue driver to use        | `database`        |

#### Database Connection Settings
| Variable      | Type   | Explanation                                     | Local Development |
|---------------|--------|-------------------------------------------------|-------------------|
| DB_CONNECTION | string | The database connection type, typically `mysql` | `mysql`           |
| DB_HOST       | string | The database host location, url or ip address   | `127.0.0.1`       |
| DB_PORT       | number | The database port, typically `3307` for mysql   | `3307`            |
| DB_DATABASE   | string | Name of the database to use                     | `fansplat_dev`    |
| DB_USERNAME   | string | Database user to use                            |                   |
| DB_PASSWORD   | string | Password of database user                       |                   |

#### AWS Keys and Settings
| Variable              | Type   | Explanation | Local Development |
|-----------------------|--------|-------------|-------------------|
| AWS_ACCESS_KEY_ID     | string |             |                   |
| AWS_SECRET_ACCESS_KEY | string |             |                   |
| AWS_DEFAULT_REGION    | string |             | `us-east-1`       |
| AWS_BUCKET            | string |             |                   |

#### Mail Settings
| Variable          | Type   | Explanation                      | Local Development         |
|-------------------|--------|----------------------------------|---------------------------|
| MAIL_DRIVER       | string | Which laravel mail driver to use | `log`                     |
| MAIL_HOST         | string | Mail host location, url or ip    |                           |
| MAIL_PORT         | number | Mail host port                   |                           |
| MAIL_USERNAME     | string |                                  |                           |
| MAIL_PASSWORD     | string |                                  |                           |
| MAIL_FROM_ADDRESS | string |                                  |                           |
| MAIL_FROM_NAME    | string |                                  | `"AllFans Localhost Dev"` |

#### Pusher / Websockets Settings
| Variable           | Type   | Explanation                                                        | Local Development |
|--------------------|--------|--------------------------------------------------------------------|-------------------|
| BROADCAST_DRIVER   | string | The laravel broadcast driver to use, Use `app` with the websockets | `app`             |
| PUSHER_APP_ID      | string |                                                                    |                   |
| PUSHER_APP_KEY     | string |                                                                    |                   |
| PUSHER_APP_SECRET  | string |                                                                    |                   |
| PUSHER_APP_CLUSTER | string |                                                                    |                   |

#### Stripe Settings
| Variable               | Type   | Explanation | Local Development |
|------------------------|--------|-------------|-------------------|
| STRIPR_PUBLISHABLE_KEY | string |             |                   |
| STRIPR_SECRET_KEY      | string |             |                   |
| STRIPR_CLIENT_ID       | string |             |                   |
| PLATFORM_FEE           | number |             |                   |
| STRIPE_WEBHOOK         | string |             |                   |

#### Social Media Site Settings
| Variable               | Type   | Explanation | Local Development |
|------------------------|--------|-------------|-------------------|
| FACEBOOK_CLIENT_ID     | string |             |                   |
| FACEBOOK_CLIENT_SECRET | string |             |                   |
| FACEBOOK_REDIRECT      | string |             |                   |
| GOOGLE_CLIENT_ID       | string |             |                   |
| GOOGLE_CLIENT_SECRET   | string |             |                   |
| GOOGLE_REDIRECT        | string |             |                   |
| TWITTER_CLIENT_ID      | string |             |                   |
| TWITTER_CLIENT_SECRET  | string |             |                   |
| TWITTER_REDIRECT       | string |             |                   |
| LINKEDIN_CLIENT_ID     | string |             |                   |
| LINKEDIN_CLIENT_SECRET | string |             |                   |

#### Misc Settings
| Variable             | Type   | Explanation | Local Development |
|----------------------|--------|-------------|-------------------|
| NOCAPTCHA_SECRET     | string |             |                   |
| NOCAPTCHA_SITEKEY    | string |             |                   |
| GOOGLE_MAPS_API_KEY  | string |             |                   |
| SOUNDCLOUD_CLIENT_ID | string |             |                   |


### Javascript Libraries
- If you do not already have [node.js](https://nodejs.org/en/) and [npm](https://www.npmjs.com/), install them.
- Local Development:
```
npm install
```
- Production Build
```
npm install --only=production && npm run production
```


## Local Development

### Building Javascript / Vue Libraries

#### Installation
```
npm install
```
#### Running watch
In its own console:
```
npm run watch
```

### Laravel Server Starting

#### Installation
```
composer install
```

#### Serving Laravel Server Locally
In its own console:
```
php artisan serve
```

#### Laravel Local Queue worker
In its own console:
```
php artisan queue:work
```

#### Laravel Websocket Server
In its own console:
```
php artisan websockets:serve
```

### Development Hints
- You can watch your log file with:
```
tail ./storage/logs/laravel-{todays date as YYYY-MM-DD}.log -f
```
This will follow the log as it is printed. You may also follow the file manually.

- The `websockets:serve` process will print out debug information to its own console.

### Testing
- Make sure you have populated a `.env.testing`
```
php artisan test
```
- To run a specific test only
```
php artisan test --testsuite=Feature --stop-on-failure
```
