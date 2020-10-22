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
