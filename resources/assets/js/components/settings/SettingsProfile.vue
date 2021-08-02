<template>
  <div v-if="!isLoading">

    <b-card title="Edit Profile">
      <b-card-text>
        <b-form @submit.prevent="submitProfile($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formProfile">
            <b-row>
              <b-col>
                <b-form-group id="group-firstname" label="First Name" label-for="firstname">
                  <b-form-input id="firstname" v-model="formProfile.firstname" placeholder="Enter first name..." ></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-lastname" label="Last Name" label-for="lastname">
                  <b-form-input id="lastname" v-model="formProfile.lastname" placeholder="Enter last name..." ></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-about" label="Bio" label-for="about">
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
                <b-form-group id="group-gender" label="Gender" label-for="gender">
                  <b-form-select id="gender" v-model="formProfile.gender" :options="options.genders"></b-form-select>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-birthdate" label="Birthdate" label-for="birthdate">
                  <b-form-datepicker id="birthdate" v-model="formProfile.birthdate"></b-form-datepicker>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-weblinks_amazon" label="Amzaon URL" label-for="weblinks_amazon">
                  <b-form-input id="weblinks_amazon" v-model="formProfile.weblinks.amazon" placeholder="https://www.amazon.com" ></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-weblinks_website" label="Website URL" label-for="weblinks_website">
                  <b-form-input id="weblinks_website" v-model="formProfile.weblinks.website" placeholder="https://" ></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-weblinks_instagram" label="Instagram URL" label-for="weblinks_instagram">
                  <b-form-input id="weblinks_instagram" v-model="formProfile.weblinks.instagram" placeholder="https://www.instagram.com" ></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>

            <b-card-title class="mt-4 mb-3">Demographics</b-card-title>
            <b-row>
              <b-col>
                <b-form-group id="group-bodytype" label="Body Type" label-for="bodytype">
                  <b-form-select id="bodytype" v-model="formProfile.body_type" :options="options.bodyTypes"></b-form-select>
                </b-form-group>
              </b-col>
              <b-col></b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-chest" label="Chest" label-for="chest">
                  <b-form-input id="chest" v-model="formProfile.chest" placeholder="Enter chest size..."></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-waist" label="Waist" label-for="waist">
                  <b-form-input id="waist" v-model="formProfile.waist" placeholder="Enter waist size..."></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-hips" label="Hips" label-for="hips">
                  <b-form-input id="hips" v-model="formProfile.hips" placeholder="Enter hips size..."></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-arms" label="Arms" label-for="arms">
                  <b-form-input id="arms" v-model="formProfile.arms" placeholder="Enter arms size..."></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-haircolor" label="Hair Color" label-for="haircolor">
                  <b-form-select id="haircolor" v-model="formProfile.hair_color" :options="options.hairColors"></b-form-select>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-eyecolor" label="Eye Color" label-for="eyecolor">
                  <b-form-select id="eyecolor" v-model="formProfile.eye_color" :options="options.eyeColors"></b-form-select>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-age" label="Age" label-for="age">
                  <b-form-input id="age" v-model="formProfile.age" placeholder="Enter age..."></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-height" label="Height" label-for="height">
                  <b-form-input id="height" v-model="formProfile.height" placeholder="Enter height..."></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-weight" label="Weight" label-for="weight">
                  <b-form-input id="weight" v-model="formProfile.weight" placeholder="Enter weight..."></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-education" label="Education" label-for="education">
                  <b-form-select id="education" v-model="formProfile.education" :options="options.educations"></b-form-select>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-lang" label="Language" label-for="lang">
                  <b-form-select id="lang" v-model="formProfile.language" :options="options.languages"></b-form-select>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-ethnicity" label="Ethnicity" label-for="ethnicity">
                  <b-form-select id="ethnicity" v-model="formProfile.ethnicity" :options="options.ethnicitys"></b-form-select>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group id="group-profession" label="Profession" label-for="profession">
                  <b-form-input id="profession" v-model="formProfile.weight" placeholder="Enter profession..."></b-form-input>
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
      firstname: '',
      lastname: '',
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
      body_type: '',
      chest: '',
      waist: '',
      hips: '',
      arms: '',
      hair_color: '',
      eye_color: '',
      age: '',
      height: '',
      weight: '',
      education: '',
      language: '',
      ethnicity: '',
      profession: '',
    },

    options: {
      genders: [ 
        { value: null, text: 'Please select an option' },
        { value: 'male', text: 'Male' },
        { value: 'female', text: 'Female' },
        { value: 'other', text: 'Other' },
      ],
      bodyTypes: [
        { value: null, text: 'Please select an option' },
        { value: 'slim', text: 'Slim' },
        { value: 'fit', text: 'Fit' },
        { value: 'average', text: 'Average' },
        { value: 'curvy', text: 'Curvy' },
        { value: 'overweight', text: 'Overweight' },
      ],
      hairColors: [
        { value: null, text: 'Please select an option' },
        { value: 'black', text: 'Black' },
        { value: 'brown', text: 'Brown' },
      ],
      eyeColors: [
        { value: null, text: 'Please select an option' },
        { value: 'black', text: 'Black' },
        { value: 'brown', text: 'Brown' },
        { value: 'blue', text: 'Blue' },
      ],
      educations: [
        { value: null, text: 'Please select an option' },
        { value: 'highschool', text: 'High School' },
        { value: 'college', text: 'Some College' },
        { value: 'associates', text: 'Associates Degree' },
        { value: 'bachelors', text: 'Bachelors Degree' },
        { value: 'graduate', text: 'Graduate Degree' },
        { value: 'doctoral', text: 'PhD / Post Doctoral' },
      ],
      languages: [
        { value: null, text: 'Please select an option' },
        { value: 'en', text: 'English' },
      ],
      ethnicitys: [
        { value: null, text: 'Please select an option' },
        { value: 'asia', text: 'Asian' },
        { value: 'african', text: 'Black / African Decent' },
        { value: 'latin', text: 'Latin / Hispanic' },
        { value: 'indian', text: 'East Indian' },
        { value: 'eastern', text: 'Middle Eastern' },
        { value: 'mixed', text: 'Mixed' },
        { value: 'american', text: 'Native American' },
        { value: 'islander', text: 'Pacific Islander' },
        { value: 'caucasian', text: 'White / Caucasian' },
      ]
    },

  }),

  mounted() {
  },

  created() {
    this.formProfile.firstname = this.session_user.firstname || ''
    this.formProfile.lastname = this.session_user.lastname || ''
    this.formProfile.about = this.user_settings.about
    this.formProfile.country = this.user_settings.country
    this.formProfile.city = this.user_settings.city
    this.formProfile.gender = this.user_settings.gender
    this.formProfile.birthdate = this.user_settings.birthdate
    this.formProfile.body_type = this.user_settings.body_type || null
    this.formProfile.chest = this.user_settings.chest
    this.formProfile.waist = this.user_settings.waist
    this.formProfile.hips = this.user_settings.hips
    this.formProfile.arms = this.user_settings.arms
    this.formProfile.hair_color = this.user_settings.hair_color || null
    this.formProfile.eye_color = this.user_settings.eye_color || null
    this.formProfile.age = this.user_settings.age
    this.formProfile.height = this.user_settings.height
    this.formProfile.weight = this.user_settings.weight
    this.formProfile.education = this.user_settings.education || null
    this.formProfile.language = this.user_settings.language || null
    this.formProfile.ethnicity = this.user_settings.ethnicity || null
    this.formProfile.profession = this.user_settings.profession
    this.formProfile.weblinks.amazon = this.user_settings.weblinks && JSON.parse(this.user_settings.weblinks)['amazon']
    this.formProfile.weblinks.website = this.user_settings.weblinks && JSON.parse(this.user_settings.weblinks)['website']
    this.formProfile.weblinks.instagram = this.user_settings.weblinks && JSON.parse(this.user_settings.weblinks)['instagram']
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
textarea#about {
  border: 1px solid #ced4da;
}
</style>

