<template>
  <div v-if="!isLoading" class="container-fluid" id="view-lists">

    <section class="row">
      <article class="col-sm-12">
        <h1>Lists</h1>
      </article>
    </section>

    <section class="row">

      <aside class="col-md-3 col-lg-2">
        <b-list-group>
          <b-list-group-item
            v-for="(link, i) in routes"
            :key="i"
            :to="link.to"
            :active="$router.currentRoute.name === link.to.name"
            class="d-flex align-items-center"
          >
            <span v-text="$t(link.name)" />
            <fa-icon icon="caret-right" class="ml-auto" />
          </b-list-group-item>
        </b-list-group>
      </aside>

      <main class="col-md-9 col-lg-10">
        <transition mode="out-in" name="quick-fade">
          <router-view :session_user="session_user" />
        </transition>
      </main>

    </section>

    <Modals />

  </div>
</template>

<script>
import Vuex from 'vuex';
import Modals from '@components/Modals'

export default {

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.session_user
    },
  },

  data: () => ({
    routes: [
      {
        name: 'Favorites',
        to: { name: 'lists.favorites', params: {} },
      }, 
      {
        name: 'Following',
        to: { name: 'lists.following', params: {} },
      }, 
      {
        name: 'Followers',
        to: { name: 'lists.followers', params: {} },
      }, 
    ]
  }),

  created() { },

  mounted() {
    this.getMe()
  },

  methods: {
    ...Vuex.mapActions([
      'getMe',
      'getUserSettings',
    ]),
  },

  watch: {
    session_user(value) {
      if (value) {
      }
    }
  },

  components: {
    Modals,
  },

}
</script>

<style lang="scss" >
/* %NOTE: this is not scoped...meant to apply to child components */
body .list-component {
  .card {
    .card-body {
      padding-top: 0.6rem;
      padding-bottom: 0.6rem;
    }

    .card-title a {
      color: #4a5568;
      text-decoration: none;
    }
    .card-subtitle a {
      color: #6e747d;
      text-decoration: none;
    }

    &.background {
      position: relative;
      .avatar-details {
        margin-left: 58px;
      }
      .shareable-id, .sharee-id {
        margin-left: 5.5rem;
      }
      .avatar-img {
        position: absolute;
        left: 8px;
        top: 75px; /* bg image height - 1/2*avatar height */
        width: 90px;
        height: 90px;

        .rounded-circle.img-thumbnail {
          padding: 0.11rem;
        }
      }
      .last-seen {
        color: #fff;
        margin-left: 5.5rem;
        font-size: 0.9rem;
        position: absolute;
        top: 91px;
        /*
        left: 0; 
        right: 0; 
        margin-left: auto; 
        margin-right: auto; 
        width: 200px;
         */
      }
      .card-img-top {
        overflow: hidden;
        height: 120px;
      }
    }

    .avatar-details {
      h2.avatar-name {
        font-size: 16px;
        & > a {
          color: #4a5568;
          text-decoration: none;
          text-transform: capitalize;
        }
      }

      .avatar-mail  {
        font-size: 14px;
        & > a {
          color: #7F8FA4;
          text-decoration: none;
        }
      }
    }
  }
}

</style>
