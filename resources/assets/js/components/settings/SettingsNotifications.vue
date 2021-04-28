<template>
  <div v-if="!isLoading">

    <b-card no-body>
      <b-tabs card>
        <b-tab title="Email" active>
          <b-card-text>
            <b-form>
              <fieldset>

                <b-row>
                  <b-col>
                    <b-form-group id="group-is_email_enabled">
                      <section class="d-flex justify-content-between align-items-start">
                        <div>
                          <label for="is_email_enabled" class="ml-auto mb-0">Email Notifications</label>
                          <p><small class="text-muted">jafkajfdafda</small></p>
                        </div>
                        <div>
                          <b-form-checkbox id="is_email_enabled" v-model="thisForm.by_email.is_enabled" name="is_email_enabled" switch size="lg"></b-form-checkbox>
                        </div>
                      </section>
                    </b-form-group>

                    <b-form-group v-if="thisForm.by_email.is_enabled" id="group-show_full_text">
                      <section class="d-flex justify-content-between align-items-start">
                        <div>
                          <label for="show_full_text" class="ml-auto mb-0">Show full text</label>
                          <p><small class="text-muted">jafkajfdafda</small></p>
                        </div>
                        <div>
                          <b-form-checkbox id="show_full_text" v-model="thisForm.by_email.show_full_text" name="show_full_text" switch size="lg"></b-form-checkbox>
                        </div>
                      </section>
                    </b-form-group>
                  </b-col>
                </b-row>

              </fieldset>

            </b-form>
          </b-card-text>
        </b-tab>

        <b-tab title="Site">
          <b-card-text>
          </b-card-text>
        </b-tab>

        <b-tab title="Toast">
          <b-card-text>
          </b-card-text>
        </b-tab>
      </b-tabs>
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
    isLoading() {
      return !this.session_user || !this.user_settings
    },
  },

  data: () => ({

    thisForm: {

      by_push: { // browser
      },

      by_email: {

        enabled: false,
        dms_only: false,
        show_full_text: false,
        monthly_newsletter: false,

        campaigns: {
          goal_achieved: false,
          new_contribution: false,
        },

        referrals: {
          new_referral: false,
        },
        income: {
          new_tip: false,
          new_subscriber: false,
          renewal: false, // ?? %TODO
          returning_subsriber: false, // ?? %TODO
        },

        posts: {
          new_posts_summary: false,
        },

        other: {
          new_stream: false,
          upcoming_stream_reminders: false,
        },
      },

    },

    autocompleteItems: [],
    debounce: null,

    options: {
      privacy: [ 
        { value: null, text: 'Please select an option' },
        { value: 'everyone', text: 'Everyone' },
        { value: 'followees', text: 'People I Follow' },
      ],
    },


  }),

  methods: {

    async submitPrivacy(e) {
      const response = await axios.patch(`/users/${this.session_user.id}/settings`, this.formPrivacy)
      this.isEditing.formPrivacy = false
    },

    onReset(e) {
      e.preventDefault()
    },
  },

  watch: {
    //user_settings(newVal) { },
  },

  mounted() {
  },

  created() {
  },

  components: {
  },
}
</script>

<style lang="scss" scoped>
label {
  margin-bottom: 0;
}
</style>

