<template>
  <b-card v-if="!isLoading" no-body>

    <b-card-header v-if="timeline">
      <AvatarWithStatus :timeline="timeline" :textVisible="true" size="md" />
    </b-card-header>

    <b-card-body>
      <PurchaseForm
        :value="message"
        item-type="chatmessages"
        :price="message.price"
        :currency="message.currency"
        :display-price="message.price | niceCurrency"
        class="mt-3"
        @completed="onCompleted"
      />
    </b-card-body>
  </b-card>
</template>

<script>
import { eventBus } from '@/eventBus'
import Vuex from 'vuex'
import PurchaseForm from '@components/payments/PurchaseForm'
import AvatarWithStatus from '@components/user/AvatarWithStatus'

export default {

  components: {
    AvatarWithStatus,
    PurchaseForm,
  },

  props: {
    timeline: null,
    message: null,
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),

    isLoading() {
      //return !this.post || !this.session_user || !this.timeline
      return !this.message || !this.session_user
    },
  },

  methods: {
    onCompleted() {
      eventBus.$emit('unlock-message', this.message)
    }
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
