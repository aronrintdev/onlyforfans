<template>
  <div v-if="!isLoading">

    <b-row>
      <b-col xl="9">

        <b-card no-body>

          <b-tabs card>
            <b-tab title="Email" active>
              <b-card-text>
                <b-form>
                  <fieldset>

                    <b-form-group id="group-email-general">
                      <section class="d-flex justify-content-between align-items-start">
                        <div>
                          <label for="is_email_enabled" class="ml-auto mb-0">Email Notifications</label>
                          <p><small class="text-muted">Receive email notifications...</small></p>
                        </div>
                        <div>
                          <b-form-checkbox id="is_email_enabled" v-model="thisForm.by_email.enabled" name="is_email_enabled" switch size="lg"></b-form-checkbox>
                        </div>
                      </section>
                    </b-form-group>

                    <b-form-group v-if="thisForm.by_email.enabled" id="group-email-show_full_text">
                      <section class="d-flex justify-content-between align-items-start">
                        <div>
                          <label for="show_full_text" class="ml-auto mb-0">Show full text</label>
                          <p><small class="text-muted">Show the full text of the email</small></p>
                        </div>
                        <div>
                          <b-form-checkbox id="show_full_text" v-model="thisForm.by_email.show_full_text" name="show_full_text" switch size="lg"></b-form-checkbox>
                        </div>
                      </section>
                    </b-form-group>

                    <hr />

                    <b-form-group v-if="thisForm.by_email.enabled" id="group-email-income">
                      <h5>Income</h5>
                      <section class="d-flex align-items-start">
                        <div>
                          <b-form-checkbox id="email_new_tip" v-model="thisForm.by_email.income.new_tip" name="email_new_tip" size="lg"></b-form-checkbox>
                        </div>
                        <div>
                          <label for="email_new_tip" class="ml-auto mb-0">New Tip</label>
                          <p><small class="text-muted">Email whenever a new tip is received.</small></p>
                        </div>
                      </section>
                      <section class="d-flex align-items-start">
                        <div>
                          <b-form-checkbox id="email_new_subscription" v-model="thisForm.by_email.income.new_subscription" name="email_new_subscription" size="lg"></b-form-checkbox>
                        </div>
                        <div>
                          <label for="email_new_subscription" class="ml-auto mb-0">New Subscription</label>
                          <p><small class="text-muted">Email on new subscriptions</small></p>
                        </div>
                      </section>
                    </b-form-group>

                    <hr />

                    <b-form-group v-if="thisForm.by_email.enabled" id="group-email-posts">
                      <h5>Posts</h5>
                      <section class="d-flex align-items-start">
                        <div>
                          <b-form-checkbox id="new_post_summary" v-model="thisForm.by_email.posts.new_post_summary" name="new_post_summary" size="lg"></b-form-checkbox>
                        </div>
                        <div>
                          <label for="posts.new_post_summary" class="ml-auto mb-0">New Post Summary</label>
                          <p><small class="text-muted">Email a summary of new posts.</small></p>
                        </div>
                      </section>
                    </b-form-group>

                    <hr />

                    <b-form-group v-if="thisForm.by_email.enabled" id="group-email-other">
                      <h5>Other</h5>
                      <section class="d-flex align-items-start">
                        <div>
                          <b-form-checkbox id="new_stream" v-model="thisForm.by_email.other.new_stream" name="new_stream" size="lg"></b-form-checkbox>
                        </div>
                        <div>
                          <label for="other.new_stream" class="ml-auto mb-0">New Stream</label>
                          <p><small class="text-muted">Email ....</small></p>
                        </div>
                      </section>
                      <section class="d-flex align-items-start">
                        <div>
                          <b-form-checkbox id="upcoming_stream_reminders" v-model="thisForm.by_email.other.upcoming_stream_reminders" name="upcoming_stream_reminders" size="lg"></b-form-checkbox>
                        </div>
                        <div>
                          <label for="other.upcoming_stream_reminders" class="ml-auto mb-0">Upcoming Stream Reminders</label>
                          <p><small class="text-muted">Email ....</small></p>
                        </div>
                      </section>
                    </b-form-group>

                  </fieldset>

                </b-form>
              </b-card-text>
            </b-tab>

            <b-tab title="Site">
              <b-card-text>
                <b-form>
                  <fieldset>

                    <b-form-group v-if="thisForm.by_email.enabled" id="group-site-income">
                      <h5>Income</h5>
                      <section class="d-flex align-items-start">
                        <div>
                          <b-form-checkbox id="new_tip" v-model="thisForm.by_email.income.new_tip" name="new_tip" size="lg"></b-form-checkbox>
                        </div>
                        <div>
                          <label for="income.new_tip" class="ml-auto mb-0">New Tip</label>
                          <p><small class="text-muted">Email whenever a new tip is received.</small></p>
                        </div>
                      </section>
                    </b-form-group>

                    <hr />

                    <b-form-group v-if="thisForm.by_email.enabled" id="group-site-posts">
                      <h5>Posts</h5>
                      <section class="d-flex align-items-start">
                        <div>
                          <b-form-checkbox id="new_post_summary" v-model="thisForm.by_email.posts.new_post_summary" name="new_post_summary" size="lg"></b-form-checkbox>
                        </div>
                        <div>
                          <label for="posts.new_post_summary" class="ml-auto mb-0">New Post Summary</label>
                          <p><small class="text-muted">Email a summary of new posts.</small></p>
                        </div>
                      </section>
                    </b-form-group>

                  </fieldset>

                </b-form>
              </b-card-text>
            </b-tab>

            <b-tab title="Toast">
              <b-card-text>
              </b-card-text>
            </b-tab>
          </b-tabs>

        </b-card>

      </b-col>
    </b-row>

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

        enabled: null,
        show_full_text: null,
        //dms_only: null,
        //monthly_newsletter: null,

        campaigns: {
          goal_achieved: null,
          new_contribution: null,
        },

        referrals: {
          new_referral: null,
        },

        income: {
          new_tip: null,
          new_subscription: null,
          //renewal: null, // ?? %TODO
          //returning_subscriber: null, // ?? %TODO
        },

        posts: {
          new_post_summary: null,
        },

        other: {
          new_stream: null,
          upcoming_stream_reminders: null,
        },
      },

      by_site: {
        posts: {
          new_comment: null,
          new_like: null,
        },
        campaigns: {
          new_contribution: null,
        },
        income: {
          new_tip: null,
          new_subscription: null,
        },

      },

    },

    debounce: null,

  }),

  methods: {

    async updateSetting(group, val, isEnable) {
      const payload = { }
      const url = isEnable 
        ? route('users.enableSetting', [this.session_user.id, 'notifications'])
        : route('users.disableSetting', [this.session_user.id, 'notifications'])
      payload[group] = val
      console.log('updateSetting', { payload })
      const response = await axios.patch( url, payload )
      //this.isEditing.formPrivacy = false
    },

    onReset(e) {
      e.preventDefault()
    },
  },

  watch: {
    'thisForm.by_email.enabled': function (newVal, oldVal) {
      this.updateSetting('global', {enabled: ['email']}, newVal).then( () => {
        //this.thisForm.by_email.enabled = newVal
      })
    },
    'thisForm.by_email.income.new_tip': function (newVal, oldVal) { 
      this.updateSetting('income', {new_tip: ['email']}, newVal).then( () => {
        //this.thisForm.by_email.new_tip = newVal
      })
    },
    'thisForm.by_email.income.new_subscription': function (newVal, oldVal) { 
      this.updateSetting('income', {new_subscription: ['email']}, newVal).then( () => {
        //this.thisForm.by_email.new_subscription = newVal
      })
    },
    'thisForm.by_email.posts.new_post_summary': function (newVal, oldVal) { 
      this.updateSetting('posts', {new_post_summary: ['email']}, newVal).then( () => {
        //this.thisForm.by_email.new_post_summary = newVal
      })
    },
  },

  mounted() {
    console.log('mounted', { here: this.user_settings.cattrs.notifications.global })
    this.thisForm.by_email.enabled = this.user_settings.cattrs.notifications.global.enabled?.includes('email') || false
    this.thisForm.by_email.show_full_text = this.user_settings.cattrs.notifications.global.show_full_text?.includes('email') || false

    this.thisForm.by_email.income.new_tip = this.user_settings.cattrs.notifications.income.new_tip?.includes('email') || false
    this.thisForm.by_email.income.new_subscription = this.user_settings.cattrs.notifications.income.new_subscription?.includes('email') || false
    this.thisForm.by_email.posts.new_post_summary = this.user_settings.cattrs.notifications.posts.new_post_summary?.includes('email') || false

    this.thisForm.by_site.income.new_tip = this.user_settings.cattrs.notifications.income.new_tip?.includes('site') || false
    this.thisForm.by_site.income.new_subscription = this.user_settings.cattrs.notifications.income.new_subscription?.includes('site') || false
    this.thisForm.by_site.posts.new_post_summary = this.user_settings.cattrs.notifications.posts.new_post_summary?.includes('site') || false
    //this.thisForm.by_email.enabled = this.user_settings.cattrs?.notifications?.global?.includes('email') || false
  },

  created() { },
  components: { },
}
</script>

<style lang="scss" scoped>
label {
  margin-bottom: 0;
}
</style>

