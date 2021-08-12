<template>
  <b-card v-if="!isLoading" class="settings-messages" :title="mobile ? null : $t('title')">
    <b-card-text>
      <div class="pt-3 pb-2">
        <b-form-checkbox disabled switch v-model="user_settings.cattrs.message_with_tip_only" @change="onTipOnlyMessagesSettingChange">
          Require sender to include a tip to receive messages.
        </b-form-checkbox>
      </div>
      <div class="pt-2 pb-3">
        <b-form-checkbox disabled v-model="user_settings.cattrs.enable_message_with_tip_only_pay" @change="onTipOnlyMessagesPaySettingChange" >
          Require sender to include a tip of at least $5 amount to receive messages.
        </b-form-checkbox>
      </div>
    </b-card-text>
  </b-card>
</template>

<script>
import Vuex from 'vuex'
export default {

  props: {
    session_user: null,
    user_settings: null,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    isLoading() {
      return !this.session_user || !this.user_settings
    },
  },
  methods: {
    onTipOnlyMessagesSettingChange: function(status) {
      this.axios.patch(`/users/${this.session_user.id}/settings`, { message_with_tip_only: status });
    },
    onTipOnlyMessagesPaySettingChange: function(status) {
      this.axios.patch(`/users/${this.session_user.id}/settings`, { enable_message_with_tip_only_pay: status });
    },
  },

  watch: {
    'blockedItem': 'queryBlockables',

    user_settings(newVal) {
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

.list-blocked .unblock {
  cursor: pointer; 
}

</style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Messages",
  }
}
</i18n>
