<template>
  <div>

    <b-card title="Edit Profile">
      <b-card-text>
        <b-form @submit.prevent="submitProfile($event)" @reset="onReset">

          <b-row>
            <b-col>
              <b-form-group id="group-about" label="About" label-for="about">
                <b-form-textarea id="about" placeholder="Talk about yourself.." rows="8"></b-form-textarea>
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
                <b-form-input id="weblinks_amazon" v-model="formProfile.weblinks.amazon" placeholder="weblinks_amazon" ></b-form-input>
              </b-form-group>
            </b-col>
            <b-col>
              <b-form-group id="group-weblinks_website" label="weblinks_website" label-for="weblinks_website">
                <b-form-input id="weblinks_website" v-model="formProfile.weblinks.website" placeholder="weblinks_website" ></b-form-input>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <b-form-group id="group-weblinks_instagram" label="weblinks_instagram" label-for="weblinks_instagram">
                <b-form-input id="weblinks_instagram" v-model="formProfile.weblinks.instagram" placeholder="weblinks_instagram" ></b-form-input>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row align-h="end" class="mt-3">
            <b-col sm="2">
              <b-button type="submit" class="w-100" variant="primary">Save</b-button>
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
  },

  computed: {
    //...Vuex.mapState(['vault']),
  },

  data: () => ({

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
  },

  methods: {
    async submitProfile(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formProfile);
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

