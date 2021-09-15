<template>
  <div v-if="!isLoading" class="component-settings_notification">

    <b-card no-body>

      <b-tabs card>

        <!-- ++++++++++++ Email Tab ++++++++++++ -->

        <b-tab title="Email" active>
          <b-card-text>
            <b-form>
              <fieldset>

                <b-form-group id="group-email-global">
                  <section class="d-flex justify-content-between align-items-start">
                    <div>
                      <label for="is_email_enabled" class="ml-auto mb-0">Email Notifications</label>
                    </div>
                    <div>
                      <b-form-checkbox id="is_email_enabled" v-model="thisForm.by_email.enabled" name="is_email_enabled" switch></b-form-checkbox>
                    </div>
                  </section>
                </b-form-group>

                <template v-if="thisForm.by_email.enabled">

                  <hr />

                  <b-form-group id="group-email-income">
                    <h5>Transactions</h5> <!-- Income -->
                    <div>
                      <b-form-checkbox v-model="thisForm.by_email.income.new_tip">When I receive a new tip</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_email.income.new_subscription">When I receive a new subscriber</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_email.income.new_paid_post_purchase">When I receive a new paid post purchase</b-form-checkbox>
                    </div>
                  </b-form-group>

                  <!-- +++ -->

                  <hr />

                  <b-form-group id="group-email-post">
                    <h5>Posts</h5>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_email.posts.new_like">When I receive a new like on a post</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_email.posts.new_comment">When I receive a new comment on a post</b-form-checkbox>
                    </div>
                  </b-form-group>

                  <!-- +++ -->

                  <hr />

                  <b-form-group id="group-email-interaction">
                    <h5>User Interactions</h5>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_email.timelines.new_follower">When I receive a new follower</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_email.usertags.new_tag">When I am tagged by another user on a post</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_email.messages.new_message">When I receive a new direct message</b-form-checkbox>
                    </div>
                  </b-form-group>

                </template>

              </fieldset>

            </b-form>
          </b-card-text>

        </b-tab>

        <!-- ++++++++++++ Site Tab ++++++++++++ -->

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
                      <b-form-checkbox id="is_site_enabled" v-model="thisForm.by_site.enabled" switch></b-form-checkbox>
                    </div>
                  </section>
                </b-form-group>

                <template v-if="thisForm.by_site.enabled">

                  <hr />

                  <b-form-group id="group-site-income">
                    <h5>Transactions</h5> <!-- Income -->
                    <div>
                      <b-form-checkbox v-model="thisForm.by_site.income.new_tip">When I receive a new tip</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_site.income.new_subscription">When I receive a new subscriber</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_site.income.new_paid_post_purchase">When I receive a new paid post purchase</b-form-checkbox>
                    </div>
                  </b-form-group>

                  <!-- +++ -->

                  <hr />

                  <b-form-group id="group-site-post">
                    <h5>Posts</h5>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_site.posts.new_like">When I receive a new like on a post</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_site.posts.new_comment">When I receive a new comment on a post</b-form-checkbox>
                    </div>
                  </b-form-group>

                  <!-- +++ -->

                  <hr />

                  <b-form-group id="group-site-interaction">
                    <h5>User Interactions</h5>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_site.timelines.new_follower">When I receive a new follower</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_site.usertags.new_tag">When I am tagged by another user on a post</b-form-checkbox>
                    </div>
                    <div>
                      <b-form-checkbox v-model="thisForm.by_site.messages.new_message">When I receive a new direct message</b-form-checkbox>
                    </div>
                  </b-form-group>

                </template>

              </fieldset>
            </b-form>
          </b-card-text>

        </b-tab>

      </b-tabs>

      <!--
      <div class="mx-3 my-1 px-3 py-1" style="border: solid pink 2px;">
        <h2>DEBUG</h2>
        <pre v-if="true">{{ JSON.stringify(this.user_settings.cattrs.notifications, null, 2) }}</pre>
        <pre v-else>{{ JSON.stringify(this.user_settings.cattrs, null, 2) }}</pre>
      </div>
      -->

    </b-card>

  </div>
</template>

<script>
import Vuex from 'vuex'

export default {

  props: {
    session_user: null,
    //user_settings: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user_settings
    },
    ...Vuex.mapGetters(['user_settings']),
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
          new_paid_post_purchase: null,
          //renewal: null, // ?? %TODO
          //returning_subscriber: null, // ?? %TODO
        },
        posts: {
          new_post_summary: null,
          new_comment: null,
          new_like: null,
        },
        timelines: {
          new_follower: null,
        },
        messages: {
          new_message: null,
        },
        usertags: {
          new_tag: null,
        },
        referrals: {
          new_referral: null,
        },
        other: {
          new_stream: null,
          upcoming_stream_reminders: null,
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
          new_paid_post_purchase: null,
        },
        posts: {
          new_post_summary: null,
          new_comment: null,
          new_like: null,
        },
        timelines: {
          new_follower: null,
        },
        messages: {
          new_message: null,
        },
        usertags: {
          new_tag: null,
        },
        referrals: {
          new_referral: null,
        },

      },

    },

    debounce: null,

  }),

  methods: {

    ...Vuex.mapActions([
      'getMe',
      'getUserSettings',
      'subscriptions/updateCount'
    ]),

    async updateSetting(group, val, isEnabled) {
      console.log('updatedSetting', {
        group,
        val,
        isEnabled,
      })
      const payload = { }
      const url = isEnabled 
        ? route('users.enableSetting', [this.session_user.id, 'notifications'])
        : route('users.disableSetting', [this.session_user.id, 'notifications'])
      payload[group] = val
      console.log('updateSetting', { payload })
      const response = await axios.patch( url, payload )
      this.getUserSettings( { userId: this.session_user.id })
      //this.isEditing.formPrivacy = false
    },

    onReset(e) {
      e.preventDefault()
    },
  },


  mounted() {

    // --- by email ---

    this.thisForm.by_email.enabled = this.user_settings.cattrs.notifications.global?.enabled?.includes('email') || false
    this.thisForm.by_email.show_full_text = this.user_settings.cattrs.notifications.global?.show_full_text?.includes('email') || false

    this.thisForm.by_email.campaigns.goal_achieved = this.user_settings.cattrs.notifications.campaigns?.goal_achieved?.includes('email') || false
    this.thisForm.by_email.campaigns.new_contribution = this.user_settings.cattrs.notifications.campaigns?.new_contribution?.includes('email') || false

    this.thisForm.by_email.income.new_paid_post_purchase = this.user_settings.cattrs.notifications?.income.new_paid_post_purchase?.includes('email') || false
    this.thisForm.by_email.income.new_subscription = this.user_settings.cattrs.notifications.income?.new_subscription?.includes('email') || false
    this.thisForm.by_email.income.new_tip = this.user_settings.cattrs.notifications.income?.new_tip?.includes('email') || false

    this.thisForm.by_email.messages.new_message = this.user_settings.cattrs.notifications.messages?.new_message?.includes('email') || false

    this.thisForm.by_email.posts.new_comment = this.user_settings.cattrs.notifications.posts?.new_comment?.includes('email') || false
    this.thisForm.by_email.posts.new_like = this.user_settings.cattrs.notifications.posts?.new_like?.includes('email') || false

    this.thisForm.by_email.timelines.new_follower = this.user_settings.cattrs.notifications.timelines?.new_follower?.includes('email') || false

    this.thisForm.by_email.usertags.new_tag = this.user_settings.cattrs.notifications.usertags?.new_tag?.includes('email') || false

    // --- by site ---

    this.thisForm.by_site.enabled = this.user_settings.cattrs.notifications.global?.enabled?.includes('site') || false
    this.thisForm.by_site.show_full_text = this.user_settings.cattrs.notifications.global?.show_full_text?.includes('site') || false

    this.thisForm.by_site.campaigns.goal_achieved = this.user_settings.cattrs.notifications.campaigns?.goal_achieved?.includes('site') || false
    this.thisForm.by_site.campaigns.new_contribution = this.user_settings.cattrs.notifications.campaigns?.new_contribution?.includes('site') || false

    this.thisForm.by_site.income.new_paid_post_purchase = this.user_settings.cattrs.notifications.income?.new_paid_post_purchase?.includes('site') || false
    this.thisForm.by_site.income.new_subscription = this.user_settings.cattrs.notifications.income?.new_subscription?.includes('site') || false
    this.thisForm.by_site.income.new_tip = this.user_settings.cattrs.notifications.income?.new_tip?.includes('site') || false

    this.thisForm.by_site.messages.new_message = this.user_settings.cattrs.notifications.messages?.new_message?.includes('site') || false

    this.thisForm.by_site.posts.new_comment = this.user_settings.cattrs.notifications.posts?.new_comment?.includes('site') || false
    this.thisForm.by_site.posts.new_like = this.user_settings.cattrs.notifications.posts?.new_like?.includes('site') || false

    this.thisForm.by_site.timelines.new_follower = this.user_settings.cattrs.notifications.timelines?.new_follower?.includes('site') || false

    this.thisForm.by_site.usertags.new_tag = this.user_settings.cattrs.notifications.usertags?.new_tag?.includes('site') || false

    // ===============================================

    // --- by email ---

    this.$watch('thisForm.by_email.enabled', function (newVal) {
      this.updateSetting('global', {enabled: ['email']}, newVal)
    })

    this.$watch('thisForm.by_email.campaigns.goal_achieved', function (newVal) { 
      this.updateSetting('campaigns', {goal_achieved: ['email']}, newVal)
    })
    this.$watch('thisForm.by_email.campaigns.new_contribution', function (newVal) { 
      this.updateSetting('campaigns', {new_contribution: ['email']}, newVal)
    })

    this.$watch('thisForm.by_email.income.new_paid_post_purchase', function (newVal) { 
      this.updateSetting('income', {new_paid_post_purchase: ['email']}, newVal)
    })
    this.$watch('thisForm.by_email.income.new_subscription', function (newVal) { 
      this.updateSetting('income', {new_subscription: ['email']}, newVal)
    })
    this.$watch('thisForm.by_email.income.new_tip', function (newVal) { 
      this.updateSetting('income', {new_tip: ['email']}, newVal)
    })

    this.$watch('thisForm.by_email.messages.new_message', function (newVal) { 
      this.updateSetting('messages', {new_message: ['email']}, newVal)
    })

    this.$watch('thisForm.by_email.posts.new_comment', function (newVal) { 
      this.updateSetting('posts', {new_comment: ['email']}, newVal)
    })
    this.$watch('thisForm.by_email.posts.new_like', function (newVal) { 
      this.updateSetting('posts', {new_like: ['email']}, newVal)
    })

    this.$watch('thisForm.by_email.timelines.new_follower', function (newVal) { 
      this.updateSetting('timelines', {new_follower: ['email']}, newVal)
    })

    this.$watch('thisForm.by_email.usertags.new_tag', function (newVal) { 
      this.updateSetting('usertags', {new_tag: ['email']}, newVal)
    })

    // --- by site ---

    this.$watch('thisForm.by_site.enabled', function (newVal) {
      this.updateSetting('global', {enabled: ['site']}, newVal)
    })

    this.$watch('thisForm.by_site.campaigns.goal_achieved', function (newVal) { 
      this.updateSetting('campaigns', {goal_achieved: ['site']}, newVal)
    })
    this.$watch('thisForm.by_site.campaigns.new_contribution', function (newVal) { 
      this.updateSetting('campaigns', {new_contribution: ['site']}, newVal)
    })

    this.$watch('thisForm.by_site.income.new_paid_post_purchase', function (newVal) { 
      this.updateSetting('income', {new_paid_post_purchase: ['site']}, newVal)
    })
    this.$watch('thisForm.by_site.income.new_subscription', function (newVal) { 
      this.updateSetting('income', {new_subscription: ['site']}, newVal)
    })
    this.$watch('thisForm.by_site.income.new_tip', function (newVal) { 
      this.updateSetting('income', {new_tip: ['site']}, newVal)
    })

    this.$watch('thisForm.by_site.messages.new_message', function (newVal) { 
      this.updateSetting('messages', {new_message: ['site']}, newVal)
    })

    this.$watch('thisForm.by_site.posts.new_comment', function (newVal) { 
      this.updateSetting('posts', {new_comment: ['site']}, newVal)
    })
    this.$watch('thisForm.by_site.posts.new_like', function (newVal) { 
      this.updateSetting('posts', {new_like: ['site']}, newVal)
    })

    this.$watch('thisForm.by_site.timelines.new_follower', function (newVal) { 
      this.updateSetting('timelines', {new_follower: ['site']}, newVal)
    })

    this.$watch('thisForm.by_site.usertags.new_tag', function (newVal) { 
      this.updateSetting('usertags', {new_tag: ['site']}, newVal)
    })

  },

  watch: {
    session_user(value) {
      if (value) {
        if (!this.user_settings) {
          this.getUserSettings( { userId: this.session_user.id })
        }
      }
    },

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
</style>

<style lang="scss" scoped>
</style>

