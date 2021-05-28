<template>
  <div v-if="!isLoading">

    <section>
      <div>Chat Title</div>
      <div>Last seen | Gallery | Search</div>
    </section>

    <hr />

    <section>

      <b-list-group>
        <b-list-group-item
          v-for="(cm, idx) in chatmessages"
          :key="cm.id"
          class=""
        >
          <div>{{ cm.mcontent }}</div>
        </b-list-group-item>
      </b-list-group>

      <b-form @submit="storeChatmessage">
        <b-form-group id="newMessage-group-1" >
          <b-form-textarea
            v-model="newMessageForm.mcontent"
            placeholder="Type a message..."
            rows="3"
            max-rows="6"
          ></b-form-textarea>
        </b-form-group>
      </b-form>

      </section>

    </div>
  </template>

<script>
//import Vuex from 'vuex'
import moment from 'moment'

export default {
  //name: 'LivechatDashboard',

  props: {
    session_user: null,
    id: null,
  },

  computed: {
    //...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.session_user || !this.chatmessages
    },

  },

  data: () => ({

    moment: moment,

    newMessageForm: {
      mcontent: null,
    },

    chatmessages: null,
    meta: null,
    perPage: 10,
    currentPage: 1,

  }), // data

  created() { 
  },

  mounted() { },

  methods: {

    //...Vuex.mapActions([
    //'getMe',
    //]),

    async storeChatmessage() {
      const params = {
        mcontent: this.mcontent,
      }
      const response = await axios.post( this.$apiRoute('chatthreads.sendMessage', this.id), { params } )
    },

    async getChatmessages(chatthreadID) {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
        chatthread_id: chatthreadID,
      }
      const response = await axios.get( this.$apiRoute('chatmessages.index'), { params } )
      this.chatmessages = response.data.data
      this.meta = response.meta
    },

  }, // methods

  watch: {

    id (newValue, oldValue) {
      if ( newValue && (newValue!==oldValue) ) {
        this.getChatmessages(newValue)
      }
    },

  }, // watch

  components: { },

}
</script>

<style lang="scss" scoped>
</style>

<style lang="scss">
</style>
