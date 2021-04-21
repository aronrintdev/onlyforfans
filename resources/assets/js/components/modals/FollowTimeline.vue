<template>
  <b-card no-body>

    <b-card-header>
      <section class="user-avatar">
        <router-link :to="timelineUrl"><b-img :src="timeline.avatar.filepath" :alt="timeline.name" :title="timeline.name"></b-img></router-link>
      </section>
      <section class="user-details">
        <div>
          <router-link :to="timelineUrl" title="" data-toggle="tooltip" data-placement="top" class="username">{{ timeline.name }}</router-link>
          <span v-if="timeline.verified" class="verified-badge"><b-icon icon="check-circle-fill" variant="success" font-scale="1"></b-icon></span>
        </div>
        <div>
          <router-link :to="timelineUrl" class="tag-username">@{{ timeline.slug }}</router-link>
        </div>
      </section>
    </b-card-header>
    <transition name="quick-fade" mode="out-in">
      <b-card-body v-if="step === 'initial'" key="initial">
        <div v-if="timeline.is_following"> <!-- un-follow or un-subscribe -->
          <p>Sorry to see you go! If you're sure you want to cancel, confirm below...</p>
          <b-button v-if="timeline.is_subscribed" @click="doSubscribe" variant="danger" class="w-100">Click to Cancel Subscription</b-button>
          <b-button v-else @click="doFollow" variant="danger" class="w-100">Click to Unfollow</b-button>
        </div>
        <div v-else> <!-- follow or subscribe -->
          <p>Get Full Access for {{ timeline.price_display || (timeline.price | niceCurrency) }} monthly premium subscription!</p>
          <b-button @click="doSubscribe" variant="success" class="w-100 mb-3">Subscribe for Full Access</b-button>
          <template v-if="!subscribe_only">
            <p>...or follow this creator for free to see limited content</p>
            <b-button @click="doFollow" variant="primary" class="w-100 mb-3">Follow for Free</b-button>
          </template>
        </div>
      </b-card-body>
      <b-card-body v-if="step === 'payment'" key="payment">
        <PurchaseForm
          :value="timeline"
          :price="timeline.price"
          :currency="'USD'"
          type="subscription"
          :display-price="timeline.price_display || (timeline.price | niceCurrency)"
          class="mt-3"
        />
      </b-card-body>
    </transition>
  </b-card>
</template>

<script>
import { eventBus } from '@/app'
import PurchaseForm from '@components/payments/PurchaseForm'

export default {

  components: {
    PurchaseForm,
  },

  props: {
    session_user: null,
    timeline: null,
    subscribe_only: { type: Boolean, default: true },
  },

  computed: {
    timelineUrl() {
      return `/${this.timeline.slug}`
    },
  },

  mounted() {
    console.log({timeline: this.timeline})
  },

  data: () => ({
    /** 'initial' | 'payment' */
    step: 'initial',
  }),

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
      // %FIXME: this emit should be more general, as this modal may be used elsewhere
       eventBus.$emit('update-originator')
    },

    async doSubscribe(e) {
      e.preventDefault()
      this.step = 'payment'

      // const response = await this.axios.put( route('timelines.subscribe', this.timeline.id), {
      //   sharee_id: this.session_user.id,
      //   notes: '',
      // })
      // this.$bvModal.hide('modal-follow')


      // Needs to be moved

      // const msg = response.is_subscribed 
      //   ? `You are now subscribed to ${this.timeline.name}!`
      //   : `You are no longer subscribed to ${this.timeline.name}!`
      // this.$root.$bvToast.toast(msg, {
      //   toaster: 'b-toaster-top-center',
      //   title: 'Success!',
      // })
      // eventBus.$emit('update-originator') // %ERIK use this
      // eventBus.$emit('update-timeline', this.timeline.id)
      // eventBus.$emit('update-feed') // updates feed being viewed
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
