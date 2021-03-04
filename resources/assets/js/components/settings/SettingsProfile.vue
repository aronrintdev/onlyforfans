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

  watch: {
    session_user(newVal) {
    },
    user_settings(newVal) {
      console.log('watch',  {
        about: newVal.about,
      });
      this.formProfile.gender = newVal.gender;
      this.formProfile.country = newVal.country;
      this.formProfile.city = newVal.city;
      this.formProfile.birthdate = newVal.birthdate;
      this.formProfile.about = newVal.about;
      if ( newVal.cattrs.weblinks ) {
        this.formProfile.weblinks = newVal.cattrs.weblinks;
      }
    },
  },

  mounted() {
  },

  created() {
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

  components: {
  },
}
</script>

<style scoped>
</style>

