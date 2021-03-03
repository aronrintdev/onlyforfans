<template>
  <div>

    <b-card title="Edit Profile">
      <b-card-text>
        <b-table striped hover :items="items"></b-table>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
//import Vuex from 'vuex';

export default {

  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    //...Vuex.mapState(['vault']),
  },

  data: () => ({

    items: [
      { age: 40, first_name: 'Dickerson', last_name: 'Macdonald' },
      { age: 21, first_name: 'Larsen', last_name: 'Shaw' },
      { age: 89, first_name: 'Geneva', last_name: 'Wilson' },
      { age: 38, first_name: 'Jami', last_name: 'Carney' }
    ]
  }),

  watch: {

    session_user(newVal) {
    },

    user_settings(newVal) {
      console.log('watch',  {
        about: newVal.about,
      });
      this.formProfile.gender = newVal.gender;
      this.formProfile.about = newVal.about;
      if ( newVal.cattrs.weblinks ) {
        this.formProfile.weblinks = newVal.cattrs.weblinks;
      }
    },
  },

  methods: {
    async submitProfile(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formProfile);
      this.isEditing.formProfile = false;
    },

    onReset(e) {
      e.preventDefault()
    },
  },

}
</script>

<style scoped>
</style>
