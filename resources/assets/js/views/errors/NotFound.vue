<template>
  <div id="notfound">
    <div class="notfound">
      <div class="notfound-404">
      <h1>404</h1>
      </div>
      <h2>Oops!  Page not found!</h2>
      <p>The page you are looking for might have been removed had its name changed or is temporarily unavailable.</p>
      <router-link :to="{ name: 'index' }">Back To Homepage</router-link>
    </div>
  </div>
</template>

<script>
/**
 * 404 Not Found Page
 */
import ErrorDisplay from '../../components/common/errors/Display'
export default {
  components: {
    ErrorDisplay,
  },
}
</script>

<style scoped lang="scss">
#notfound {
  position: relative;
  height: 100vh;
  width: 100%;
  text-align: center;

  .notfound {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 100%;
    transform: translate(-50%,-50%);

    .notfound-404 {
      position: absolute;
      height: 100px;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      z-index: -1;

      h1 {
        color: rgba(0, 123, 255, 0.15);
        font-weight: 900;
        font-size: 276px;
        margin: 0;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
      }
    }

    h2 {
      font-size: 46px;
      color: #000;
      font-weight: 900;
      text-transform: uppercase;
      margin: 0;
    }

    p {
      font-size: 16px;
      color: #000;
      font-weight: 400;
      margin-top: 15px;
    }

    a {
      font-size: 14px;
      text-decoration: none;
      text-transform: uppercase;
      background: #007bff;
      display: inline-block;
      padding: 12px 30px;
      border: 2px solid transparent;
      border-radius: 40px;
      color: #fff;
      font-weight: 400;
      transition: 0.2s all;
      margin-top: 2em;
    }
  }
}
</style>
