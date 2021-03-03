<template>
  <div v-if>

    <b-card title="Privacy">
      <b-card-text>
        <b-form @submit.prevent="submitPrivacy($event)" @reset="onReset">
          <fieldset :disabled="!isEditing.formPrivacy">

            <table class="w-100 table">
              <tr>
                <td class="align-middle">
                  <label id="group-who_can_post_on_timeline" label-for="who_can_post_on_timeline" class="">Who can post on my timeline</label>
                </td>
                <td class="">
                  <b-form-select v-model="formPrivacy.privacy.who_can_post_on_timeline" :options="options.privacy" class=""></b-form-select>
                </td>
              </tr>
              <tr>
                <td class="align-middle">
                  <label id="group-who_can_comment_on_post" label-for="who_can_comment_on_post" class="">Who can comment on my post</label>
                </td>
                <td class="">
                  <b-form-select v-model="formPrivacy.privacy.who_can_comment_on_post" :options="options.privacy" class=""></b-form-select>
                </td>
              </tr>
              <tr>
                <td class="align-middle">
                  <label id="group-who_can_see_post" label-for="who_can_see_post" class="">Who can see my post</label>
                </td>
                <td class="">
                  <b-form-select v-model="formPrivacy.privacy.who_can_see_post" :options="options.privacy" class=""></b-form-select>
                </td>
              </tr>
              <tr>
                <td class="align-middle">
                  <label id="group-who_can_message" label-for="who_can_message" class="">Who can message me</label>
                </td>
                <td class="">
                  <b-form-select v-model="formPrivacy.privacy.who_can_message" :options="options.privacy" class=""></b-form-select>
                </td>
              </tr>
            </table>

          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div v-if="isEditing.formPrivacy" class="w-100 d-flex justify-content-end">
                <b-button class="w-25" @click.prevent="isEditing.formPrivacy=false" variant="outline-secondary">Cancel</b-button>
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
              <div v-else class="w-100 d-flex justify-content-end">
                <b-button @click.prevent="isEditing.formPrivacy=true" class="w-25" variant="warning">Edit</b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Blocked">
      <b-card-text>
        <b-form @submit.prevent="submitBlocked($event)" @reset="onReset">
          <fieldset v-if="state !== 'loading'">

            <b-row>
              <b-col>

                <vue-tags-input
                  v-if="isEditing.formBlocked"
                  v-model="blockedItem"
                  :tags="formBlocked.blocked"
                  :autocomplete-items="autocompleteItems"
                  @tags-changed="handleUpdatedTags"
                  />

                  <div v-else class="accordion" role="tablist">
                    <section class="mb-3">
                      <b-button block v-b-toggle.accordion-ips variant="light">IPs</b-button>
                      <b-collapse id="accordion-ips" accordion="my-accordion" role="tabpanel">
                        <ul class="list-unstyled">
                          <li v-for="(b,idx) in user_settings.cattrs.blocked.ips || []"> {{ b }}</li>
                        </ul>
                      </b-collapse>
                    </section>
                    <section class="mb-3">
                      <b-button block v-b-toggle.accordion-countries variant="light">Countries</b-button>
                      <b-collapse id="accordion-countries" accordion="my-accordion" role="tabpanel">
                        <ul class="list-unstyled">
                          <li v-for="(b,idx) in user_settings.cattrs.blocked.countries || []"> {{ b }}</li>
                        </ul>
                      </b-collapse>
                    </section>
                    <section class="mb-3">
                      <b-button block v-b-toggle.accordion-users variant="light">Users</b-button>
                      <b-collapse id="accordion-users" accordion="my-accordion" role="tabpanel">
                        <ul class="list-unstyled">
                          <li v-for="(b,idx) in user_settings.cattrs.blocked.usernames || []"> {{ b }}</li>
                        </ul>
                      </b-collapse>
                    </section>
                  </div>
              </b-col>
            </b-row>

          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div v-if="isEditing.formBlocked" class="w-100 d-flex justify-content-end">
                <b-button class="w-25" @click.prevent="isEditing.formBlocked=false" variant="outline-secondary">Cancel</b-button>
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
              <div v-else class="w-100 d-flex justify-content-end">
                <b-button @click.prevent="isEditing.formBlocked=true" class="w-25" variant="warning">Edit</b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

    <b-card title="Watermark">
      <b-card-text>
        <b-form @submit.prevent="submitWatermark($event)" @reset="onReset">
          <fieldset :disabled="!isEditing.formWatermark">

            <b-row>
              <b-col>
                <b-form-group id="group-is_watermark_enabled" label="Follow for Free?" label-for="is_watermark_enabled">
                  <b-form-checkbox
                    id="is_watermark_enabled"
                    v-model="formWatermark.is_watermark_enabled"
                    name="is_watermark_enabled"
                    value=1
                    unchecked-value=0
                    switch size="lg"></b-form-checkbox>
                </b-form-group>
              </b-col>
            </b-row>

          </fieldset>

          <b-row class="mt-3">
            <b-col>
              <div v-if="isEditing.formWatermark" class="w-100 d-flex justify-content-end">
                <b-button class="w-25" @click.prevent="isEditing.formWatermark=false" variant="outline-secondary">Cancel</b-button>
                <b-button class="w-25 ml-3" type="submit" variant="primary">Save</b-button>
              </div>
              <div v-else class="w-100 d-flex justify-content-end">
                <b-button @click.prevent="isEditing.formWatermark=true" class="w-25" variant="warning">Edit</b-button>
              </div>
            </b-col>
          </b-row>

        </b-form>
      </b-card-text>
    </b-card>

  </div>
</template>

<script>
import Vuex from 'vuex'

export default {

  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    //...Vuex.mapState(['vault']),
  },

  data: () => ({
    state: 'loading', // loading | loaded

    isEditing: {
      formPrivacy: false,
      formBlocked: false,
      formWatermark: false,
    },

    formPrivacy: {
      privacy: {
        who_can_post_on_timeline: null,
        who_can_comment_on_post: null,
        who_can_see_post: null,
        who_can_message: null,
      },
    },

    formBlocked: {
      blocked: [], // ip, country, or username (?) , tags
    },
    blockedItem: '', // tag
    autocompleteItems: [],
    debounce: null,

    formWatermark: {
      watermark: {
        is_watermark_enabled: null,
      },
    },

    options: {
      privacy: [ 
        { value: null, text: 'Please select an option' },
        { value: 'everyone', text: 'Everyone' },
        { value: 'followees', text: 'People I Follow' },
      ],
    },


  }),

  methods: {

    handleUpdatedTags(newTags) {
      this.autocompleteItems = []
      this.formBlocked.blocked = newTags
    },

    // see: http://www.vue-tags-input.com/#/examples/autocomplete
    queryBlockables() {
      if (this.blockedItem.length < 2) {
        return
      }
      const url = `/blockables/match?term=${this.blockedItem}&take=6`
      clearTimeout(this.debounce)
      this.debounce = setTimeout(() => {
        axios.get(url).then(response => {
          this.autocompleteItems = response.data.results.map(a => {
            return { slug: a.slug, text: a.display }
          })
        }).catch(() => console.warn('Oh. Something went wrong'))
      }, 600)
    },

    async submitPrivacy(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formPrivacy)
      this.isEditing.formPrivacy = false
    },
    async submitBlocked(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formBlocked)
      this.$store.dispatch('getUserSettings', { userId: this.session_user.id })
      this.isEditing.formBlocked = false
      this.formBlocked.blocked = []
    },
    async submitWatermark(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formWatermark)
      this.isEditing.formWatermark = false
    },

    onReset(e) {
      e.preventDefault()
    },
  },

  watch: {
    'blockedItem': 'queryBlockables',

    session_user(newVal) {
    },

    user_settings(newVal) {
      this.state = 'loaded'
      if ( newVal.cattrs.privacy ) {
        this.formPrivacy.privacy = newVal.cattrs.privacy
      }
      if ( newVal.cattrs.blocked ) {
        this.formBlocked.blocked
      }
      if ( newVal.cattrs.watermark ) {
        this.formWatermark.watermark = newVal.cattrs.watermark
      }
    },
  },

  mounted() {
  },

  created() {
  },


  components: {
  },
}
</script>

<style scoped>
label {
  margin-bottom: 0;
}
</style>

