<template>
  <b-card v-if="!isLoading" no-body>

    <b-card-header>
      <section class="user-avatar">
        <router-link :to="timelineUrl"><b-img :src="post.user.avatar.filepath" :alt="post.user.name" :title="post.user.name"></b-img></router-link>
      </section>
      <section class="user-details">
        <div>
          <router-link :to="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ post.user.name }}</router-link>
          <span v-if="post.user.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
        </div>
        <div>
          <router-link :to="timelineUrl" class="tag-username">@{{ post.timeline.slug }}</router-link>
        </div>
      </section>
    </b-card-header>

    <b-card-body>
      <p class="w-100 text-center m-0 tag-purchase_amount">
        Purchase post for {{ post.price_display || (post.price | niceCurrency) }}
      </p>
      <PurchaseForm
        :value="post"
        :price="post.price"
        :currency="post.currency"
        :display-price="post.price_display || (post.price | niceCurrency)"
        class="mt-3"
      />
    </b-card-body>
  </b-card>
</template>

<script>
import PurchaseForm from '@components/payments/PurchaseForm'

export default {

  components: {
    PurchaseForm,
  },

  props: {
    session_user: null,
    post_id: null,
  },

  computed: {
    isLoading() {
      //return !this.post || !this.session_user || !this.timeline
      return !this.post_id || !this.session_user || !this.post
    },
    timelineUrl() {
      return `/${this.post.timeline.slug}`
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
            this.$bvModal.hide('modal-purchase_post')
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
