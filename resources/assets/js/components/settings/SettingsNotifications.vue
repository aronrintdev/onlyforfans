<template>
  <div v-if="!isLoading" class="component-settings_notification">
    <b-card no-body>
      <b-tabs card>
        <b-tab title="Email" active>
          <b-card-text>
            <b-form>
              <fieldset>

                <b-form-group id="group-email-global">
                  <section class="d-flex justify-content-between align-items-start">
                    <div>
                      <label for="is_email_enabled" class="ml-auto mb-0">Email Notifications</label>
                      <p><small class="text-muted">Receive email notifications</small></p>
                    </div>
                    <div>
                      <b-form-checkbox id="is_email_enabled" v-model="thisForm.by_email.enabled" name="is_email_enabled" switch size="lg"></b-form-checkbox>
                    </div>
                  </section>
                </b-form-group>

                <template v-if="thisForm.by_email.enabled">
                  <b-form-group id="group-email-show_full_text">
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

                  <b-form-group id="group-email-income">
                    <h5>Income</h5>
                    <div>
                      <b-form-checkbox id="email_new_tip" v-model="thisForm.by_email.income.new_tip" name="email_new_tip" size="lg">New Tip</b-form-checkbox>
                      <p><small class="text-muted">Email whenever a new tip is received</small></p>
                    </div>
                    <div>
                      <b-form-checkbox id="email_new_subscription" v-model="thisForm.by_email.income.new_subscription" name="email_new_subscription" size="lg">New Subscription</b-form-checkbox>
                      <p><small class="text-muted">Email on new subscriptions</small></p>
                    </div>
                  </b-form-group>

                  <hr />

                  <b-form-group id="group-email-post">
                    <h5>Posts</h5>
                    <!--
                    <div>
                      <b-form-checkbox id="new_post_summary" v-model="thisForm.by_email.posts.new_post_summary" name="new_post_summary" size="lg">New Post Summary</b-form-checkbox>
                      <p><small class="text-muted">Email a summary of new posts.</small></p>
                    </div>
                    -->
                    <div>
                      <b-form-checkbox id="new_like" v-model="thisForm.by_email.posts.new_like" name="new_like" size="lg">New Like</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox id="new_comment" v-model="thisForm.by_email.posts.new_comment" name="new_comment" size="lg">New Comment</b-form-checkbox>
                    </div>
                  </b-form-group>

                  <hr />

                  <b-form-group id="group-email-other">
                    <h5>Other</h5>
                    <div>
                      <b-form-checkbox id="new_stream" v-model="thisForm.by_email.other.new_stream" name="new_stream" size="lg">New Stream</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox id="upcoming_stream_reminders" v-model="thisForm.by_email.other.upcoming_stream_reminders" name="upcoming_stream_reminders" size="lg">Upcoming Stream Reminders</b-form-checkbox>
                    </div>
                  </b-form-group>
                </template>

              </fieldset>

            </b-form>
          </b-card-text>
        </b-tab>

        <b-tab title="Site">
          <b-card-text>
            <b-form>
              <fieldset>

                <b-form-group id="group-site-global">
                  <section class="d-flex justify-content-between align-items-start">
                    <div>
                      <label for="is_site_enabled" class="ml-auto mb-0">Site Notifications</label>
                      <p><small class="text-muted">Receive site notifications</small></p>
                    </div>
                    <div>
                      <b-form-checkbox id="is_site_enabled" v-model="thisForm.by_site.enabled" name="is_site_enabled" switch size="lg"></b-form-checkbox>
                    </div>
                  </section>
                </b-form-group>

                <template v-if="thisForm.by_site.enabled">

                <b-form-group id="group-site-income">
                  <h5>Income</h5>
                  <div>
                    <b-form-checkbox id="new_tip" v-model="thisForm.by_site.income.new_tip" name="new_tip" size="lg">New Tip</b-form-checkbox>
                    <p><small class="text-muted">Whenever a new tip is received</small></p>
                  </div>
                </b-form-group>

                <hr />

                <b-form-group id="group-site-post">
                  <h5>Posts</h5>
                  <div>
                    <b-form-checkbox id="new_comment" v-model="thisForm.by_site.posts.new_comment" name="new_comment" size="lg">New Comment</b-form-checkbox>
                  </div>
                  <div>
                    <b-form-checkbox id="new_like" v-model="thisForm.by_site.posts.new_like" name="new_like" size="lg">New Like</b-form-checkbox>
                  </div>
                </b-form-group>

                </template>

              </fieldset>

            </b-form>
          </b-card-text>
        </b-tab>

        <!-- <b-tab title="Push">
          <b-card-text>
          </b-card-text>
        </b-tab> -->
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

        enabled: null,
        show_full_text: null,
        //dms_only: null,
        //monthly_newsletter: null,

        campaigns: {
          goal_achieved: null,
          new_contribution: null,
        },
        income: {
          new_tip: null,
          new_subscription: null,
          //renewal: null, // ?? %TODO
          //returning_subscriber: null, // ?? %TODO
        },
        other: {
          new_stream: null,
          upcoming_stream_reminders: null,
        },
        posts: {
          new_post_summary: null,
          new_comment: null,
          new_like: null,
        },

        referrals: {
          new_referral: null,
        },

      },

      by_site: {
        enabled: null,
        show_full_text: null,

        campaigns: {
          goal_achieved: null,
          new_contribution: null,
        },
        income: {
          new_tip: null,
          new_subscription: null,
        },
        posts: {
          new_post_summary: null,
          new_comment: null,
          new_like: null,
        },

        referrals: {
          new_referral: null,
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


  mounted() {

    // by email
    this.thisForm.by_email.enabled = this.user_settings.cattrs.notifications.global.enabled?.includes('email') || false
    this.thisForm.by_email.show_full_text = this.user_settings.cattrs.notifications.global.show_full_text?.includes('email') || false

    this.thisForm.by_email.income.new_tip = this.user_settings.cattrs.notifications.income.new_tip?.includes('email') || false
    this.thisForm.by_email.income.new_subscription = this.user_settings.cattrs.notifications.income.new_subscription?.includes('email') || false
    //this.thisForm.by_email.posts.new_post_summary = this.user_settings.cattrs.notifications.posts.new_post_summary?.includes('email') || false
    this.thisForm.by_email.posts.new_comment = this.user_settings.cattrs.notifications.posts.new_comment?.includes('email') || false
    this.thisForm.by_email.posts.new_like = this.user_settings.cattrs.notifications.posts.new_like?.includes('email') || false
    this.thisForm.by_email.campaigns.goal_achieved = this.user_settings.cattrs.notifications.campaigns?.goal_achieved?.includes('email') || false
    this.thisForm.by_email.campaigns.new_contribution = this.user_settings.cattrs.notifications.campaigns?.new_contribution?.includes('email') || false

    // by site
    this.thisForm.by_site.enabled = this.user_settings.cattrs.notifications.global.enabled?.includes('site') || false
    this.thisForm.by_site.show_full_text = this.user_settings.cattrs.notifications.global.show_full_text?.includes('site') || false

    this.thisForm.by_site.income.new_tip = this.user_settings.cattrs.notifications.income.new_tip?.includes('site') || false
    this.thisForm.by_site.income.new_subscription = this.user_settings.cattrs.notifications.income.new_subscription?.includes('site') || false
    //this.thisForm.by_site.posts.new_post_summary = this.user_settings.cattrs.notifications.posts.new_post_summary?.includes('site') || false
    this.thisForm.by_site.posts.new_comment = this.user_settings.cattrs.notifications.posts.new_comment?.includes('site') || false
    this.thisForm.by_site.posts.new_like = this.user_settings.cattrs.notifications.posts.new_like?.includes('site') || false
    this.thisForm.by_site.campaigns.goal_achieved = this.user_settings.cattrs.notifications.campaigns?.goal_achieved?.includes('site') || false
    this.thisForm.by_site.campaigns.new_contribution = this.user_settings.cattrs.notifications.campaigns?.new_contribution?.includes('site') || false

    this.$watch('thisForm.by_email.enabled', function (newVal) {
      this.updateSetting('global', {enabled: ['email']}, newVal)
    })
    this.$watch('thisForm.by_email.income.new_tip', function (newVal) { 
      this.updateSetting('income', {new_tip: ['email']}, newVal)
    })
    this.$watch('thisForm.by_email.income.new_subscription', function (newVal) { 
      this.updateSetting('income', {new_subscription: ['email']}, newVal)
    })
    /*
    this.$watch('thisForm.by_email.posts.new_post_summary', function (newVal) { 
      this.updateSetting('posts', {new_post_summary: ['email']}, newVal)
    })
    */
    this.$watch('thisForm.by_email.posts.new_comment', function (newVal) { 
      this.updateSetting('posts', {new_comment: ['email']}, newVal)
    })
    this.$watch('thisForm.by_email.posts.new_like', function (newVal) { 
      this.updateSetting('posts', {new_like: ['email']}, newVal)
    })
    this.$watch('thisForm.by_email.campaigns.goal_achieved', function (newVal) { 
      this.updateSetting('campaigns', {goal_achieved: ['email']}, newVal)
    })
    this.$watch('thisForm.by_email.campaigns.new_contribution', function (newVal) { 
      this.updateSetting('campaigns', {new_contribution: ['email']}, newVal)
    })
    
    // by site
    this.$watch('thisForm.by_site.enabled', function (newVal) {
      this.updateSetting('global', {enabled: ['site']}, newVal)
    })
    this.$watch('thisForm.by_site.income.new_tip', function (newVal) { 
      this.updateSetting('income', {new_tip: ['site']}, newVal)
    })
    this.$watch('thisForm.by_site.income.new_subscription', function (newVal) { 
      this.updateSetting('income', {new_subscription: ['site']}, newVal)
    })
    /*
    this.$watch('thisForm.by_site.posts.new_post_summary', function (newVal) { 
      this.updateSetting('posts', {new_post_summary: ['site']}, newVal)
    })
    */
    this.$watch('thisForm.by_site.posts.new_comment', function (newVal) { 
      this.updateSetting('posts', {new_comment: ['site']}, newVal)
    })
    this.$watch('thisForm.by_site.posts.new_like', function (newVal) { 
      this.updateSetting('posts', {new_like: ['site']}, newVal)
    })
    this.$watch('thisForm.by_site.campaigns.goal_achieved', function (newVal) { 
      this.updateSetting('campaigns', {goal_achieved: ['site']}, newVal)
    })
    this.$watch('thisForm.by_site.campaigns.new_contribution', function (newVal) { 
      this.updateSetting('campaigns', {new_contribution: ['site']}, newVal)
    })
  },

  created() { },
  components: { },

  watch: {
    // by email
  },

}
</script>

<style lang="scss">
body .custom-checkbox.b-custom-control-lg > label {
    padding-top: 0.2rem;
    font-size: 1.0rem;
    line-height: 1.5;
}
// Adjust bootstrap checkbox: keep large checkbox, but decrease font size and adjust accordingly to center
body .component-settings_notification {

}
</style

<style lang="scss" scoped>
</style>

