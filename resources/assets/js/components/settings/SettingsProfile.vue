<template>
  <div v-if="!isLoading">

    <b-card :title="mobile ? null : $t('title')">
      <b-card-text>
        <b-form @submit.prevent="submitProfile($event)" @reset="onReset">
          <fieldset :disabled="isSubmitting.formProfile">
            <b-row>
              <b-col sm="12" md="6">
                <b-form-group id="group-name" label="Name" label-for="name">
                  <b-form-input id="name" v-model="formProfile.name"></b-form-input>
                </b-form-group>
              </b-col>
              <b-col sm="12" md="6">
                <b-form-group id="group-slug" label="Profile URL" label-for="slug">
                  <div class="d-flex">
                    <h6 class="mt-2 mr-2 text-muted">https://allfans.com/</h6>
                    <b-form-input id="slug" v-model="formProfile.slug" disabled></b-form-input>
                  </div>
                </b-form-group>
                <small class="text-secondary">* Changing the profile URL is currently disabled.</small>
              </b-col>
            </b-row>

            <b-row>
              <b-col>
                <b-form-group id="group-about" label="Bio" label-for="about">
                  <div v-custom-click-outside="closeEmojiBox" class="emoji-opener" v-b-tooltip.hover="'Add Emoji Icon'">
                    <div @click="isEmojiBoxVisible=!isEmojiBoxVisible" >
                      <fa-icon :icon="isEmojiBoxVisible ? ['fas', 'smile'] : ['far', 'smile']" :class="isEmojiBoxVisible ? 'text-primary' : 'text-secondary'" size="lg" />
                    </div>
                    <VEmojiPicker v-if="isEmojiBoxVisible" @select="selectEmoji" />
                  </div>
                  <!-- <b-form-textarea id="about" v-model="formProfile.about" rows="3"></b-form-textarea> -->
                  <div
                    class="text-left text-editor settings-profile-editor"
                    contenteditable
                    v-html="descriptionForEditor"
                    @keydown="editorChanged"
                    @input="onInput"
                    @click="editorClicked"
                  ></div>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col sm="12" md="6">
                <b-form-group id="group-country" label="I'm From" label-for="country">
                  <b-form-input id="country" v-model="formProfile.country"></b-form-input>
                </b-form-group>
              </b-col>
              <b-col sm="12" md="6">
                <b-form-group id="group-city" label="Currently In" label-for="city">
                  <b-form-input id="city" v-model="formProfile.city"></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col sm="12" md="6">
                <b-form-group id="group-gender" label="Gender" label-for="gender">
                  <b-form-select id="gender" v-model="formProfile.gender" :options="options.genders"></b-form-select>
                </b-form-group>
              </b-col>
              <b-col sm="12" md="6" class="d-block">
                <b-form-group id="group-birthdate" label="Birthdate" label-for="birthdate">
                  <input type="date" class="form-control" id="birthdate" v-model="formProfile.birthdate">
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col sm="12" md="6">
                <b-form-group id="group-weblinks_amazon" label="Amazon URL" label-for="weblinks_amazon">
                  <b-input-group>
                    <template #prepend>
                      <b-input-group-text >https://amazon.com/gp/profile/</b-input-group-text>
                    </template>
                    <b-form-input id="weblinks_amazon" v-model="formProfile.weblinks.amazon" @change="v => socialLinkChanged('amazon', v)" placeholder="username" ></b-form-input>
                  </b-input-group>
                </b-form-group>
              </b-col>
              <b-col sm="12" md="6">
                <b-form-group id="group-weblinks_website" label="Website URL" label-for="weblinks_website">
                  <b-form-input id="weblinks_website" v-model="formProfile.weblinks.website" placeholder="example.com" ></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>
              <b-col sm="12" md="6">
                <b-form-group id="group-weblinks_instagram" label="Instagram URL" label-for="weblinks_instagram">
                  <b-input-group>
                    <template #prepend>
                      <b-input-group-text >https://instagram.com/</b-input-group-text>
                    </template>
                    <b-form-input id="weblinks_instagram" v-model="formProfile.weblinks.instagram" @change="v => socialLinkChanged('instagram', v)" placeholder="username" ></b-form-input>
                  </b-input-group>
                </b-form-group>
              </b-col>
              <b-col sm="12" md="6">
                <b-form-group id="group-weblinks_twitter" label="Twitter URL" label-for="weblinks_twitter">
                  <b-input-group>
                    <template #prepend>
                      <b-input-group-text >https://twitter.com/</b-input-group-text>
                    </template>
                    <b-form-input id="weblinks_twitter" v-model="formProfile.weblinks.twitter" @change="v => socialLinkChanged('twitter', v)" placeholder="username" ></b-form-input>
                  </b-input-group>
                </b-form-group>
              </b-col>
            </b-row>
            <b-row>
              <b-col cols="12" class="text-right">
                <div class="d-inline-block demographics_visible text-primary" @click="isDemographicsVisible=!isDemographicsVisible">
                  <span v-if="!isDemographicsVisible">View Demographics</span>
                  <span v-else>Hide Demographics</span>
                </div>
              </b-col>
            </b-row>

            <template v-if="isDemographicsVisible">
              <b-card-title class="mt-4 mb-3">Demographics</b-card-title>
              <b-row>
                <b-col sm="12" md="6">
                  <b-form-group id="group-bodytype" label="Body Type" label-for="bodytype">
                    <b-form-select id="bodytype" v-model="formProfile.body_type" :options="options.bodyTypes"></b-form-select>
                  </b-form-group>
                </b-col>
                <b-col></b-col>
              </b-row>

              <b-row>
                <b-col sm="12" md="6" lg="3">
                  <b-form-group id="group-chest" label="Chest" label-for="chest">
                    <b-form-input id="chest" v-model="formProfile.chest"></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="6" lg="3">
                  <b-form-group id="group-waist" label="Waist" label-for="waist">
                    <b-form-input id="waist" v-model="formProfile.waist"></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="6" lg="3">
                  <b-form-group id="group-hips" label="Hips" label-for="hips">
                    <b-form-input id="hips" v-model="formProfile.hips"></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="6" lg="3">
                  <b-form-group id="group-arms" label="Arms" label-for="arms">
                    <b-form-input id="arms" v-model="formProfile.arms"></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>

              <b-row>
                <b-col sm="12" md="6">
                  <b-form-group id="group-haircolor" label="Hair Color" label-for="haircolor">
                    <b-form-select id="haircolor" v-model="formProfile.hair_color" :options="options.hairColors"></b-form-select>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="6">
                  <b-form-group id="group-eyecolor" label="Eye Color" label-for="eyecolor">
                    <b-form-select id="eyecolor" v-model="formProfile.eye_color" :options="options.eyeColors"></b-form-select>
                  </b-form-group>
                </b-col>
              </b-row>

              <b-row>
                <b-col sm="12" md="4">
                  <b-form-group id="group-age" label="Age" label-for="age">
                    <b-form-input id="age" v-model="formProfile.age"></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="4">
                  <b-form-group id="group-height" label="Height" label-for="height">
                    <b-form-input id="height" v-model="formProfile.height"></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="4">
                  <b-form-group id="group-weight" label="Weight" label-for="weight">
                    <b-form-input id="weight" v-model="formProfile.weight"></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>

              <b-row>
                <b-col sm="12" md="4">
                  <b-form-group id="group-education" label="Education" label-for="education">
                    <b-form-select id="education" v-model="formProfile.education" :options="options.educations"></b-form-select>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="4">
                  <b-form-group id="group-ethnicity" label="Ethnicity" label-for="ethnicity">
                    <b-form-select id="ethnicity" v-model="formProfile.ethnicity" :options="options.ethnicitys"></b-form-select>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="4">
                  <b-form-group id="group-profession" label="Profession" label-for="profession">
                    <b-form-input id="profession" v-model="formProfile.profession"></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>
            </template>
          </fieldset>

          <b-row class="mt-4 mb-3">
            <b-col>
              <div class="w-100 d-flex justify-content-end">
                <b-button :disabled="isSubmitting.formProfile" class="w-25 ml-3" type="submit" variant="primary">
                  <b-spinner v-if="isSubmitting.formProfile" small class="mr-1" />
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
import { VEmojiPicker } from 'v-emoji-picker'

export default {
  props: {
    session_user: null,
    user_settings: null,
    timeline: null,
  },

  components: {
    VEmojiPicker,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    isLoading() {
      return !this.session_user || !this.user_settings || !this.timeline
    },
  },

  data: () => ({
    isSubmitting: {
      formProfile: false,
    },

    formProfile: {
      name: '',
      slug: '',
      about: '',
      country: '',
      city: '',
      gender: '',
      birthdate: '',
      weblinks: { // cattrs
        amazon: null,
        website: null,
        instagram: null,
        twitter: null,
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
        { value: 'transgender', text: 'Transgender' },
        { value: 'gender_neutral', text: 'Gender Neutral' },
        { value: 'non_binary', text: 'Non-binary' },
        { value: 'agender', text: 'Agender' },
        { value: 'pangender', text: 'Pangender' },
        { value: 'genderqueer', text: 'Genderqueer' },
        { value: 'two_spirit', text: 'Two-Spirit' },
        { value: 'third_gender', text: 'Third Gender' },
        { value: 'other', text: 'Other' },
      ],
      bodyTypes: [
        { value: null, text: 'Please select an option' },
        { value: 'slim', text: 'Slim' },
        { value: 'fit', text: 'Fit' },
        { value: 'average', text: 'Average' },
        { value: 'curvy', text: 'Curvy' },
        { value: 'overweight', text: 'Overweight' },
        { value: 'rectangle', text: 'Rectangle' },
        { value: 'triangle', text: 'Triangle' },
        { value: 'spoon', text: 'Spoon' },
        { value: 'hourglass', text: 'Hourglass' },
        { value: 'top_hourglass', text: 'Top Hourglass' },
        { value: 'bottom_hourglass', text: 'Bottom Hourglass' },
        { value: 'inverted_triangle', text: 'Inverted Triangle' },
        { value: 'round', text: 'Round' },
        { value: 'diamond', text: 'Diamond' },
        { value: 'other', text: 'Other' },
      ],
      hairColors: [
        { value: null, text: 'Please select an option' },
        { value: 'sunflower_blonde', text: 'Sunflower Blonde' },
        { value: 'caramel', text: 'Caramel' },
        { value: 'french_roast', text: 'French Roast' },
        { value: 'copper_shimmer', text: 'Copper Shimmer' },
        { value: 'ruby_fusion', text: 'Ruby Fusion' },
        { value: 'beeline_honey', text: 'Beeline Honey' },
        { value: 'light_brown', text: '	Light Brown' },
        { value: 'light_auburn', text: 'Light Auburn' },
        { value: 'reddish_blonde', text: 'Reddish Blonde' },
        { value: 'jet_black', text: 'Jet Black' },
        { value: 'blonde', text: 'Blonde' },
        { value: 'nrunette', text: 'Brunette' },
        { value: 'red', text: 'Red' },
        { value: 'hombre', text: 'Hombre' },
        { value: 'balayage', text: 'Balayage' },
        { value: 'auburn', text: 'Auburn' },
      ],
      eyeColors: [
        { value: null, text: 'Please select an option' },
        { value: 'brown', text: 'Brown' },
        { value: 'blue', text: 'Blue' },
        { value: 'hazel', text: 'Hazel' },
        { value: 'amber', text: 'Amber' },
        { value: 'gray', text: 'Gray' },
        { value: 'green', text: 'Green' },
        { value: 'red', text: 'Red' },
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
      // languages: [
      //   { value: null, text: 'Please select an option' },
      //   { value: 'en', text: 'English' },
      // ],
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
    isDemographicsVisible: false,
    descriptionForEditor: '',
    description: '',
    isEmojiBoxVisible: false,
  }),

  mounted() {
  },

  created() {
    this.formProfile.name = this.timeline.name || ''
    this.formProfile.slug = this.timeline.slug || ''
    this.formProfile.about = this.timeline.about
    this.description = this.timeline.about
    this.descriptionForEditor = this.timeline.about
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
    this.formProfile.weblinks.twitter = this.user_settings.weblinks && JSON.parse(this.user_settings.weblinks)['twitter']
  },

  methods: {
    ...Vuex.mapActions(['getUserSettings', 'getMe']),

    async submitProfile(e) {
      this.isSubmitting.formProfile = true
      const formProfile = {
        ...this.formProfile,
        birthdate: this.formProfile.birthdate == '0000-00-00' ? null : this.formProfile.birthdate,
        about: this.description,
      }
      axios.patch(`/users/${this.session_user.id}/settings`, formProfile)
        .then(() => {
          // re-load user settings
          this.getUserSettings({ userId: this.session_user.id })
          this.getMe()
          this.isSubmitting.formProfile = false
          this.$root.$bvToast.toast('Profile settings have been updated successfully!', {
            toaster: 'b-toaster-top-center',
            title: 'Success',
            variant: 'success',
          })
        })
        .catch(error => {
          this.isSubmitting.formProfile = false
          this.onErrorHandler(error)
        })

    },

    onReset(e) {
      e.preventDefault()
    },

    onErrorHandler(error) {
      let message;
      if (error.response) {
        const types = Object.keys(error.response.data.errors);
        for(const type of types) {
          switch(type) {
            case 'weblinks.website':
              message = 'Website URL is invalid'
              break;
            case 'birthdate':
              message = 'Birthdate is invalid'
              break;
            default:
              message = error.response.data.message;
          }
          this.$root.$bvToast.toast(message, {
            toaster: 'b-toaster-top-center',
            title: 'Failed',
            variant: 'danger',
          })
        }
      } else {
        message = error.message;
        this.$root.$bvToast.toast(message, {
          toaster: 'b-toaster-top-center',
          title: 'Failed',
          variant: 'danger',
        })
      }
    },

    socialLinkChanged(type, val) {
      const parts = val.split('/')
      let lastPart = parts.pop()
      if (!lastPart) {
        lastPart = parts.pop()
      }
      switch (type) {
        case 'instagram':
          this.formProfile.weblinks.instagram = lastPart;
          break;
        case 'twitter':
          this.formProfile.weblinks.twitter = lastPart;
          break;
        case 'amazon':
          this.formProfile.weblinks.amazon = lastPart;
          break;
        default:
      }
    },

    editorChanged(e) {
      if (e.keyCode == 50 && e.shiftKey) {
        e.preventDefault();
        let content = e.target.innerHTML;
        content += `<a>@`;
        this.descriptionForEditor = content;
        this.$nextTick(() => {
          const p = e.target,
              s = window.getSelection(),
              r = document.createRange();
          let ele = p.childElementCount > 0 ? p.lastChild : p;
          if (p.lastChild.textContent == '') {
            r.setStart(ele, 0);
            r.setEnd(ele, 0);
          } else {
            r.setStart(ele, 1);
            r.setEnd(ele, 1);
          }
    
          s.removeAllRanges();
          s.addRange(r);
        })
      } else if (e.keyCode == 32) {
        let content = e.target.innerHTML;
        if (content.slice(-4) == '</a>') {
          e.preventDefault();
          this.descriptionForEditor = content + '<span>&nbsp;';
          this.$nextTick(() => {
            const p = e.target,
                s = window.getSelection(),
                r = document.createRange();
            let ele = p.childElementCount > 0 ? p.lastChild : p;
            if (p.lastChild.textContent == '') {
              r.setStart(ele, 0);
              r.setEnd(ele, 0);
            } else {
              r.setStart(ele, 1);
              r.setEnd(ele, 1);
            }
      
            s.removeAllRanges();
            s.addRange(r);
          })
        }
      }
    },

    onInput(e) {
      this.description = e.target.innerHTML
    },

    editorClicked(e) {
      if (e.target.tagName == 'A') {
        const url = e.target.textContent.slice(1)
        window.location.href = url;
      }
    },
    
    selectEmoji(emoji) {
      this.description += `<span class="emoji">${emoji.data}</span><span>&nbsp;</span>`
      this.descriptionForEditor = this.description
      this.$nextTick(() => {
        const p = document.querySelector('#group-about .text-editor'),
            s = window.getSelection(),
            r = document.createRange();
        let ele = p.childElementCount > 0 ? p.lastChild : p;
        if (!p.lastChild.textContent) {
          r.setStart(ele, 0);
          r.setEnd(ele, 0);
        } else {
          r.setStart(ele, 1);
          r.setEnd(ele, 1);
        }
  
        s.removeAllRanges();
        s.addRange(r);
      })
    },

    closeEmojiBox() {
      this.isEmojiBoxVisible = false;
    }
  },
}
</script>

<style lang="scss" scoped>
textarea#about {
  border: 1px solid #ced4da;
}

#group-birthdate input[type="date"] {
  -webkit-appearance: none;
  width: 100%;
}

.demographics_visible {
  cursor: pointer;
}

.settings-profile-editor {
  color: #495057;
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;

  &:focus {
    background-color: #fff;
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgb(0 123 255 / 25%);
  }
}

#group-about {
  position: relative;

  .emoji-opener {
    position: absolute;
    top: 0;
    right: 0;
  }
}

@media (max-width: 576px) {
  .input-group {
    &-prepend {
      width: 100%;
      margin-right: 0;
      margin-bottom: -1px;

      .input-group-text {
        width: 100%;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
        border-bottom-left-radius: 0;
      }
    }
    .form-control {
      border-top-left-radius: 0;
      border-top-right-radius: 0;
      border-bottom-left-radius: 0.25rem;
    }
  }
}
</style>

<style lang="scss">

#group-about {
  #EmojiPicker {
    left: auto;
    right: -10px;
    top: auto;
    bottom: 120%;

    .container-emoji {
      height: 160px;
    }
  }

  @media (max-width: 576px) {
    #EmojiPicker {
      left: auto;
      right: -10px;
      top: auto;
      bottom: 120%;
    }
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Edit Profile",
  }
}
</i18n>
