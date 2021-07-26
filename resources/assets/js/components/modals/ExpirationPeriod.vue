
<template>
  <b-card no-body>
    <div class="m-5 text-center">
      <select class="custom-select" @change="changeExpirationPeriod">
        <option selected>{{ $t('options.no_limit') }}</option>
        <option value="1">{{ $t('options.1d') }}</option>
        <option value="3">{{ $t('options.3d') }}</option>
        <option value="7">{{ $t('options.7d') }}</option>
        <option value="30">{{ $t('options.30d') }}</option>
      </select>
    </div>
    <b-card-footer>
      <div class="text-right">
        <b-btn class="px-3 mr-1" variant="secondary" @click="exit">
          {{ $t('action_btns.cancel') }}
        </b-btn>
        <b-btn class="px-3" variant="primary" @click="save">
          {{ $t('action_btns.save') }}
        </b-btn>
      </div>
    </b-card-footer>
  </b-card>
</template>

<script>
import { eventBus } from '@/eventBus'

export default {
  name: "ExpirationPeriod",
  data: () => ({
    exiprationPeriod: undefined,
  }),
  methods: {
    exit() {
      this.$bvModal.hide('expiration-period');
    },
    changeExpirationPeriod(e) {
      this.exiprationPeriod = parseInt(e.target.value, 10);
    },
    save() {
      // Save period
      eventBus.$emit('set-expiration-period', this.exiprationPeriod);
      this.exit();
    }
  },
}
</script>

<style lang="scss" scoped>

</style>

<i18n lang="json5" scoped>
{
  "en": {
    "action_btns": {
      "save": "Save",
      "cancel": "Cancel"
    },
    "options": {
        "no_limit": "No limit",
        "1d": "1 day",
        "3d": "3 days",
        "7d": "7 days",
        "30d": "30 days"
    }
  }
}
</i18n>
