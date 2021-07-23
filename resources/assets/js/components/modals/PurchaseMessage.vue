<template>
  <b-card v-if="!isLoading" no-body>

    <b-card-header>
      <!-- <section class="user-avatar">
        <router-link :to="timelineUrl"><b-img :src="message.user.avatar.filepath" :alt="message.user.name" :title="message.user.name"></b-img></router-link>
      </section> -->
      <!-- <section class="user-details">
        <div>
          <router-link :to="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ message.user.name }}</router-link>
          <span v-if="message.user.verified" class="verified-badge">
            <fa-icon icon="check-circle" class="text-primary" />
          </span>
        </div>
        <div>
          <router-link :to="timelineUrl" class="tag-username">@{{ message.timeline.slug }}</router-link>
        </div>
      </section> -->
    </b-card-header>

    <b-card-body>
      <p class="w-100 text-center m-0 tag-purchase_amount">
        Purchase Message for {{ message.price | niceCurrency }}
      </p>
      <PurchaseForm
        :value="message"
        item-type="message"
        :price="message.price"
        :currency="message.currency"
        :display-price="message.price | niceCurrency"
        class="mt-3"
      />
    </b-card-body>
  </b-card>
</template>

<script>
import Vuex from 'vuex'
import PurchaseForm from '@components/payments/PurchaseForm'

export default {

  components: {
    PurchaseForm,
  },

  props: {
    message: null,
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),
    
    isLoading() {
      //return !this.post || !this.session_user || !this.timeline
      return !this.message || !this.session_user
    },
    purchasesChannel() {
      return `user.${this.session_user.id}.purchases`
    },
  },

  data: () => ({
    post: null,
    //timeline: null, // dynamically load from server based on post
  }),

  methods: {
    init() {
      this.$echo.private(this.purchasesChannel)
        .listen('ItemPurchased', e => {
          if (e.item_id === this.post.id) {
            this.$bvModal.hide('modal-purchase-post')
          }
        })
    },
  },


  created() {
    // Dynamically load the full post so we ensure we have the related timeline data
    // (not guaranteed to be attached as a relation as this is ref'd from multiple components)
    axios.get( route('posts.show', this.post_id) ).then( response => {
      this.post = response.data.data
    })
  },

  mounted() {
    this.init()
  },

}
</script>

<style scoped>
ul {
  margin: 0;
}

header.card-header,
footer.card-footer {
  background-color: #fff;
}

body .user-avatar {
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}
body .user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

body .user-details .tag-username {
  color: #859AB5;
  text-transform: capitalize;
}
</style>
