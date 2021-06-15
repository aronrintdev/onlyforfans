<template>
  <div v-if="!isLoading">

    <b-card title="Edit Profile">
      <b-card-text>
        <b-form @submit.prevent="submitProfile($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formProfile">

            <b-row>
              <b-col>
                <b-form-group id="group-about" label="About" label-for="about">
                  <b-form-textarea id="about" v-model="formProfile.about" placeholder="Talk about yourself.." rows="8"></b-form-textarea>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-country" label="I'm from" label-for="country">
                  <b-form-input id="country" v-model="formProfile.country" placeholder="Enter country..." ></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-city" label="Currently In" label-for="city">
                  <b-form-input id="city" v-model="formProfile.city" placeholder="Enter city..." ></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-gender" label="gender" label-for="gender">
                  <b-form-select id="gender" v-model="formProfile.gender" :options="options.genders"></b-form-select>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-birthdate" label="birthdate" label-for="birthdate">
                  <b-form-datepicker id="birthdate" v-model="formProfile.birthdate"></b-form-datepicker>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-weblinks_amazon" label="weblinks_amazon" label-for="weblinks_amazon">
                  <b-form-input id="weblinks_amazon" v-model="formProfile.weblinks.amazon" placeholder="Amazon URL" ></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-weblinks_website" label="weblinks_website" label-for="weblinks_website">
                  <b-form-input id="weblinks_website" v-model="formProfile.weblinks.website" placeholder="Website URL" ></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-weblinks_instagram" label="weblinks_instagram" label-for="weblinks_instagram">
                  <b-form-input id="weblinks_instagram" v-model="formProfile.weblinks.instagram" placeholder="Instagram URL" ></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>
          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div class="w-100 d-flex justify-content-end">
                <b-button :disabled="isSubmitting.formProfile" class="w-25 ml-3" type="submit" variant="primary">
                  <b-spinner v-if="isSubmitting.formProfile" small />&nbsp;
                  Save
                </b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
import Vuex from 'vuex';

export default {
  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user_settings
    },
  },

  data: () => ({
    isSubmitting: {
      formProfile: false,
    },

    formProfile: {
      about: '',
      country: '',
      city: '',
      gender: '',
      birthdate: '',
      weblinks: { // cattrs
        amazon: null,
        website: null,
        instagram: null,
      },
    },

    options: {
      genders: [ 
        { value: null, text: 'Please select an option' },
        { value: 'male', text: 'Male' },
        { value: 'female', text: 'Female' },
        { value: 'other', text: 'Other' },
      ],
    },

  }),

  mounted() {
  },

  created() {
    this.formProfile.about = this.user_settings.about
    this.formProfile.country = this.user_settings.country
    this.formProfile.city = this.user_settings.city
    this.formProfile.gender = this.user_settings.gender
    this.formProfile.birthdate = this.user_settings.birthdate
  },

  methods: {
    ...Vuex.mapActions(['getUserSettings']),

    async submitProfile(e) {
      this.isSubmitting.formProfile = true
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formProfile)

      // re-load user settings
      this.getUserSettings({ userId: this.session_user.id })

      this.$root.$bvToast.toast('Profile settings have been updated successfully!', {
        toaster: 'b-toaster-top-center',
        title: 'Success',
        variant: 'success',
      })
      this.isSubmitting.formProfile = false
    },

    onReset(e) {
      e.preventDefault()
    },
  },

  components: {
  },
}
</script>

<style scoped>
</style>

