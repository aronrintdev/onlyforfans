<template>
  <b-card v-if="!isLoading" no-body>

    <b-card-header>
      <AvatarWithStatus :timeline="post.timeline" :user="post.user" :textVisible="false" size="md" />
    </b-card-header>

    <b-card-body>
      <PurchaseForm
        :value="post"
        item-type="post"
        :price="post.price"
        :currency="post.currency"
        :display-price="post.price_display || (post.price | niceCurrency)"
        class="mt-3"
      />
    </b-card-body>
  </b-card>
</template>

<script>
import AvatarWithStatus from '@components/user/AvatarWithStatus'
import PurchaseForm from '@components/payments/PurchaseForm'
import OnlineStatus from '@components/common/OnlineStatus'

export default {

  components: {
    AvatarWithStatus,
    OnlineStatus,
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
      // this.$echo.private(this.purchasesChannel)
      //   .listen('ItemPurchased', e => {
      //     if (e.item_id === this.post.id) {
      //       this.$bvModal.hide('modal-purchase-post')
      //     }
      //   })
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

body .user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
body .user-avatar {
  position: relative;
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}

body .user-avatar .onlineStatus {
  position: absolute;
  top: 20px;
  left: 25px;
}
body .user-details ul {
  padding-left: 50px;
  margin-bottom: 0;
}
body .user-details ul > li {
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}
body .user-details ul > li .username {
  text-transform: capitalize;
}

body .user-details ul > li .post-time {
  color: #4a5568;
  font-size: 12px;
  letter-spacing: 0px;
  margin-right: 3px;
}
body .user-details ul > li:last-child {
  font-size: 14px;
}
body .user-details ul > li {
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}

body .user-details .tag-username {
  color: #859AB5;
  text-transform: capitalize;
}
</style>
