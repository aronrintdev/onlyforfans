<template>
  <b-card no-body>

    <b-card-header>
      <section class="user-avatar">
        <a :href="timelineUrl"><b-img :src="post.user.avatar.filepath" :alt="post.user.name" :title="post.user.name"></b-img></a>
      </section>
      <section class="user-details">
        <div>
          <a href="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ post.user.name }}</a>
          <span v-if="post.user.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
        </div>
        <div>
          <a :href="timelineUrl" class="tag-username">@{{ post.timeline_slug }}</a>
        </div>
      </section>
    </b-card-header>

    <b-form @submit="purchasePost">
      <b-card-body>
        <p class="w-100 text-center m-0 tag-purchase_amount">{{ post.price | niceCurrency }}</p>
      </b-card-body>
      <b-card-footer>
        <b-button type="submit" variant="warning" class="w-100">Purchase Post</b-button>
      </b-card-footer>
    </b-form>

  </b-card>
</template>

<script>
import { eventBus } from '@/app'

export default {

  props: {
    session_user: null,
    post: null,
  },

  computed: {
    timelineUrl() {
      return `/${this.post.timeline.slug}`
    },
  },

  data: () => ({ }),

  methods: {

    async purchasePost(e) {
      e.preventDefault()
      const response = await axios.put( route('posts.purchase', this.post.id) )
      this.$bvModal.hide('modal-purchase_post')
      this.$root.$bvToast.toast(`Post successfully purchased!`, {
        toaster: 'b-toaster-top-center',
        title: 'Success!',
      })
      eventBus.$emit('update-post', this.post.id)
    },

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
