<template>
  <section class="auth-page">
    <div id="container-inside">
      <div id="circle-small"></div>
      <div id="circle-medium"></div>
      <div id="circle-large"></div>
      <div id="circle-xlarge"></div>
      <div id="circle-xxlarge"></div>
    </div>
    <div class="auth-container position-absolute row no-gutters">
      <div class="col-md-6 text-center auth-container-left">
        <div class="auth-description">
          <Branding type="text" size="3x" class="mb-3 sidebar-logo" />
          <swiper class="auth-slider-swiper" :options="swiperOptions">
            <swiper-slide>
              <h3 class="card-title">Subscribe and Tip</h3>
              <p>Fans subscribe to profiles for access to premium content and can send tips.</p>
            </swiper-slide>
            <swiper-slide>
              <h3 class="card-title">Message with Fans</h3>
              <p>Instant messaging to share photos, videos, and to send or receive tips.</p>
            </swiper-slide>
            <swiper-slide>
              <h3 class="card-title">Find Fans and Creators</h3>
              <p>Explore to find fans and creators, then follow or subscribe.</p>
            </swiper-slide>
            <div class="swiper-pagination" slot="pagination"></div>
          </swiper>
        </div>
      </div>
      <div class="col-md-6 px-0 mr-0">
        <slot></slot>
      </div>
    </div>
  </section>
</template>

<script>
import Branding from '@components/common/Branding'

export default {
  name: 'AuthTemplate',

  components: {
    Branding,
  },

  data:() => ({
    swiperOptions: {
      spaceBetween: 30,
      centeredSlides: true,
      autoplay: {
        delay: 2500,
        disableOnInteraction: false
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true
      }
    },
  })
}
</script>

<style lang="scss" scoped>
.auth-page {
  background: #00adef;
  background: -moz-linear-gradient(-45deg, #00adef 0%, #0076e5 100%);
  background: -webkit-linear-gradient(-45deg, #00adef 0%, #0076e5 100%);
  background: linear-gradient(135deg, #00adef 0%, #0076e5 100%);
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#00adef', endColorstr='#0076e5', GradientType=1);
  position: relative;
  overflow: hidden;
  height: 100vh;
  width: 100%;
  margin: 0px auto;
  padding: 0px auto;
}
#container-inside {
  position: relative;
  min-width: 960px;
  max-width: 1280px;
  height: auto;
  min-height: 100%;
  margin: 0px auto;
  padding: 0px auto;
  overflow: visible;
}
@keyframes circle-small-scale {
  0% {
    transform: scale(1, 1);
  }
  50% {
    transform: scale(1.1, 1.1);
  }
  100% {
    transform: scale(1, 1);
  }
}
#circle-small {
  -webkit-animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation-timing-function: cubic-bezier(.6, 0, .4, 1);
  animation-delay: 0s;
  position: absolute;
  top: 200px;
  left: -150px;
  background: #fff;
  width: 300px;
  height: 300px;
  border-radius: 50%;
  opacity: 0.4;
}
#circle-medium {
  -webkit-animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation-timing-function: cubic-bezier(.6, 0, .4, 1);
  animation-delay: 0.3s;
  position: absolute;
  top: 50px;
  left: -300px;
  background: #fff;
  width: 600px;
  height: 600px;
  border-radius: 50%;
  opacity: 0.3;
}
#circle-large {
  -webkit-animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation-timing-function: cubic-bezier(.6, 0, .4, 1);
  animation-delay: 0.6s;
  position: absolute;
  top: -100px;
  left: -450px;
  background: #fff;
  width: 900px;
  height: 900px;
  border-radius: 50%;
  opacity: 0.2;
}
#circle-xlarge {
  -webkit-animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation-timing-function: cubic-bezier(.6, 0, .4, 1);
  animation-delay: 0.9s;
  position: absolute;
  top: -250px;
  left: -600px;
  background: #fff;
  width: 1200px;
  height: 1200px;
  border-radius: 50%;
  opacity: 0.1;
}
#circle-xxlarge {
  -webkit-animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation: circle-small-scale 7s ease-in-out infinite alternate;
  animation-timing-function: cubic-bezier(.6, 0, .4, 1);
  animation-delay: 1.2s;
  position: absolute;
  top: -400px;
  left: -750px;
  background: #fff;
  width: 1500px;
  height: 1500px;
  border-radius: 50%;
  opacity: 0.05;
}
.auth-container {
  top: 0;
  bottom: 0;
  width: 100%;
  overflow: auto;
}
.auth-description {
    display: none;
  @media (min-width: 576px) {
    display: block;
    float: none;  
    margin-top: 120px;
    margin-bottom: 120px;
    margin-right: auto;
    width: 350px;
    margin-left: auto;
  }
  @media (min-width: 992px) {
    display: block;
    float: right;
    margin-top: 120px;
    margin-right: 150px;
    width: 350px;
  }
}
.auth-container-left {
  display: none;
  @media (min-width: 576px) {
    display: block;
  }
}
.sidebar-logo {
  display: block;
  margin: auto;
}
</style>
