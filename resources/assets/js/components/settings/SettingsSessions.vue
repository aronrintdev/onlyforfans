<template>
  <div>

    <b-card title="Edit Profile">
      <b-card-text>
        <b-form @submit.prevent="submitProfile($event)" @reset="onReset">
          <fieldset :disabled="!isEditing.formProfile">

            <b-row>
              <b-col>
                <b-form-group id="group-about" label="About" label-for="about">
                  <b-form-textarea id="about" v-model="formProfile.about" placeholder="Talk about yourself.." rows="8"></b-form-textarea>
                </b-form-group>
              </b-col>
            </b-row>

          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div v-if="isEditing.formProfile" class="w-100 d-flex justify-content-end">
                <b-button class="w-25" @click.prevent="isEditing.formProfile=false" variant="outline-secondary">Cancel</b-button>
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
              <div v-else class="w-100 d-flex justify-content-end">
                <b-button @click.prevent="isEditing.formProfile=true" class="w-25" variant="warning">Edit</b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
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

    isEditing: {
      formProfile: false,
    },

    formProfile: {
      about: '',
      country: '',
      city: '',
      gender: '',
      birthdate: '',
      weblinks: { // cattrs
        instagram: null,
      },
    },

    options: {
      genders: [ 
        { value: null, text: 'Please select an option' },
        { value: 'other', text: 'Other' },
      ],
    },

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
