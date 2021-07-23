<template>

  <div v-if="!isLoading" class="d-flex flex-column">

    <section class="chatthread-header">
      <div class="d-flex align-items-center">
        <b-button to="/messages" variant="link" class="" @click="doSomething">
          <fa-icon :icon="['fas', 'arrow-left']" class="fa-lg" />
        </b-button>
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
import VaultSelector from './VaultSelector'

export default {

  props: {
    session_user: null,
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
