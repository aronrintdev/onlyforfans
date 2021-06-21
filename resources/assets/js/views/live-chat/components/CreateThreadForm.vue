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

    <!-- %FIXME DRY -->
    <MessageForm
      :session_user="session_user"
      chatthread_id="new"
      v-on="$listeners"
      class="flex-grow-1"
    />

  </div>
</template>

<script>
import moment from 'moment'
import MessageForm from '@views/live-chat/components/MessageForm'

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
