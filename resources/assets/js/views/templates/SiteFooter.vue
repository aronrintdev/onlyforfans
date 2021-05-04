<template>
  <div class="container footer">

    <b-row>
      <b-col>
        <div class="footer-description">
          <div class="row" style="margin-bottom: 60px; text-align:center">
            <span class="col-sm-2 col-2 col-lg-2"></span>
            <a href="/faq" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">FAQ</a>
            <a href="/support" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">Support</a>
            <a href="/terms-of-use" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">Terms</a>
            <a href="/privacy-policy" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">Privacy</a>
            <a href="/dmca" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">DMCA</a>
            <a href="/usc2257" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">USC 2257</a>
            <a href="/legal" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">Legal</a>
            <a href="/blog" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">Blog</a>
          </div>
          <div class="fans-terms text-center">
            Copyright © 2021 <a href="http://fansplatform.mjmdesign.co">AllFans™</a>. All rights reserved.
            <br />
            AllFans • 1027 S. Rainbow Blvd. #119, Las Vegas, NV 89145
            <br />
            <span class="dropup" style="margin-left: 20px">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">
                <span> <span class="flag-icon flag-icon-us"></span> </span> <i class="fa fa-angle-down" aria-hidden="true"></i></a>
              <ul class="dropdown-menu">
                <li class=""><a href="#" class="switch-language" data-language="en"> <span class="flag-icon flag-icon-us"></span> English</a></li>
                <li class=""><a href="#" class="switch-language" data-language="fr"> <span class="flag-icon flag-icon-fr"></span> French</a></li>
                <li class=""><a href="#" class="switch-language" data-language="es"> <span class="flag-icon flag-icon-es"></span> Spanish</a></li>
                <li class=""><a href="#" class="switch-language" data-language="ar"> <span class="flag-icon flag-icon-ar"></span> Arabic</a></li>
                <li class=""><a href="#" class="switch-language" data-language="jp"> <span class="flag-icon flag-icon-jp"></span> Japanese</a></li>
                <li class=""><a href="#" class="switch-language" data-language="zh"> <span class="flag-icon flag-icon-cn"></span> Chinese</a></li>
                <li class=""><a href="#" class="switch-language" data-language="de"> <span class="flag-icon flag-icon-de"></span> German</a></li>
              </ul>
            </span>
          </div>
        </div>
      </b-col>
    </b-row>

  </div>
</template>

<style lang="scss">
.footer-description {
    padding-top: 100px;
}
.footer-description .fans-terms {
    padding-bottom: 10px;
    font-size: 14px;
    font-family: 'Open Sans', sans-serif;
    font-weight: 400;
}
.dropup, .dropdown {
    position: relative;
}
.footer-description .fans-terms a {
    color: #4a5568;
    text-decoration: none;
}
.footer-description .fans-terms a {
    font-family: 'Open Sans', sans-serif;
    font-size: 14px;
    color: #249AF2;
    text-transform: capitalize;
    font-weight: 400;
}

.dropup .dropdown-menu, .navbar-fixed-bottom .dropdown .dropdown-menu {
    top: auto;
    bottom: 100%;
    margin-bottom: 2px;
}
.dropdown-menu {
    position: absolute;
    top: 100%;
    left: -150px;
    z-index: 1000;
    display: none;
    float: left;
    min-width: 180px;
    padding: 5px 0;
    margin: 2px 0 0;
        margin-bottom: 0px;
    list-style: none;
    font-size: 14px;
    text-align: left;
    background-color: #fff;
    border: 1px solid #ccc;
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 4px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
    background-clip: padding-box;
}
.flag-icon {
    background-size: contain;
    background-position: 50%;
    background-repeat: no-repeat;
    position: relative;
    display: inline-block;
    width: 1.33333333em;
    line-height: 1em;
}
.flag-icon-us {
  background-image: url(/images/flags/4x3/us.svg);
}
</style>
