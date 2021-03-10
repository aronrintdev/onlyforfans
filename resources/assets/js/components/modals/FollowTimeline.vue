<template>
  <b-card no-body>

    <b-card-header>
      <section class="user-avatar">
        <a :href="timelineUrl"><b-img :src="timeline.avatar.filepath" :alt="timeline.name" :title="timeline.name"></b-img></a>
      </section>
      <section class="user-details">
        <div>
          <a href="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ timeline.name }}</a>
          <span v-if="timeline.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
        </div>
        <div>
          <a :href="timelineUrl" class="tag-username">@{{ timeline.slug }}</a>
        </div>
      </section>
    </b-card-header>

      <b-card-body>
        <div v-if="timeline.is_following"> <!-- un-follow or un-suscribe -->
          <p>Sorry to see you go! If you're sure you want to cancel, confirm below...</p>
          <b-button v-if="timeline.is_subscribed" @click="doSubscribe" variant="danger" class="w-100">Click to Cancel Subscription</b-button>
          <b-button v-else @click="doFollow" variant="danger" class="w-100">Click to Unfollow</b-button>
        </div>
        <div v-else> <!-- follow or subscribe -->
          <p>Follow this creator for free to see limited content...or, for {{ timeline.price | niceCurrency }} montly buy a premium subscripton for full access!</p>
          <b-button @click="doFollow" variant="primary" class="w-100 mb-3">Follow for Free</b-button>
          <b-button @click="doSubscribe" variant="success" class="w-100">Subscribe for Full Access</b-button>
        </div>

      </b-card-body>

  </b-card>
</template>

<script>
import { eventBus } from '@/app'

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    timelineUrl() {
      return `/${this.timeline.slug}`
    },
  },

  data: () => ({ }),

  methods: {

    async doFollow(e) {
      e.preventDefault()
      const response = await this.axios.put( route('timelines.follow', this.timeline.id), {
        sharee_id: this.session_user.id,
        notes: '',
      })
      this.$bvModal.hide('modal-follow')
      const msg = response.is_following 
        ? `You are now following ${this.timeline.name}!`
        : `You are no longer following ${this.timeline.name}!`
      this.$root.$bvToast.toast(msg, {
        toaster: 'b-toaster-top-center',
        title: 'Success!',
      })
      eventBus.$emit('update-timeline', this.timeline.id)
      //this.load()
    },

    async doSubscribe(e) {
      e.preventDefault()
      const response = await this.axios.put( route('timelines.subscribe', this.timeline.id), {
        sharee_id: this.session_user.id,
        notes: '',
      })
      this.$bvModal.hide('modal-follow')
      const msg = response.is_subscribed 
        ? `You are now subscribed to ${this.timeline.name}!`
        : `You are no longer subscribed to ${this.timeline.name}!`
      this.$root.$bvToast.toast(msg, {
        toaster: 'b-toaster-top-center',
        title: 'Success!',
      })
      eventBus.$emit('update-timeline', this.timeline.id)
      //this.load()
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

/* %TODO DRY */
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
