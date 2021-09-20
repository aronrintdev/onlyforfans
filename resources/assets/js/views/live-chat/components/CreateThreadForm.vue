<template>

  <div v-if="!isLoading" class="d-flex flex-column">

    <section class="chatthread-header pt-3">
      <div class="d-flex align-items-center">
        <b-btn v-if="mobile" variant="link" size="lg" class="back-btn" @click="$emit('back')">
          <fa-icon icon="arrow-left" size="lg" />
        </b-btn>
        <p class="m-0"><strong>Select one or more recipients from contacts</strong></p>
      </div>
    </section>

    <hr />

    <transition name="quick-fade" mode="out-in">
      <section v-if="vaultSelectionOpen" class="vault-selection flex-fill">
        <VaultSelector @close="vaultSelectionOpen = false" />
      </section>
    </transition>

    <!-- %FIXME DRY -->
    <MessageForm
      :session_user="session_user"
      chatthread_id="new"
      v-on="$listeners"
      class="mt-auto"
      @toggleVaultSelect="vaultSelectionOpen = !vaultSelectionOpen"
    />

  </div>
</template>

<script>
import moment from 'moment'
import MessageForm from '@views/live-chat/components/NewMessageForm'
import VaultSelector from './ShowThread/VaultSelector'

export default {

  props: {
    session_user: null,
    mobile: false,
  },

  computed: {

    isLoading() {
      return !this.session_user
    },

  },

  data: () => ({
    moment: moment,

    vaultSelectionOpen: false,

  }), // data

  created() { },

  mounted() { 
    //const channel = `chatthreads.${this.id}`
    //this.$echo.private(channel).listen('.chatmessage.sent', e => {
    //this.chatmessages.push(e.chatmessage)
    //})
  },

  methods: {


    doSomething() {
      // stub placeholder for impl
    },

  }, // methods

  watch: { },

  components: {
    MessageForm,
    VaultSelector,
  },

}
</script>

<style lang="scss" scoped>
body {
  .btn-link:hover {
    text-decoration: none;
  }
  .btn:focus, .btn.focus {
    box-shadow: none;
  }

}

</style>

<style lang="scss">
body {
}
</style>
