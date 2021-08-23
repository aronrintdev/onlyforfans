<template>
  <b-list-group-item class="bank-account-list-item">
    <Display :value="value">
      <template #append>
        <b-dropdown no-caret right variant="outline-secondary">
          <template #button-content>
            <fa-icon icon="ellipsis-h" />
          </template>
          <b-dropdown-item link-class="pl-3" @click="setDefault(value.id)">
            <fa-icon icon="dot-circle" fixed-width class="mr-2" />
            {{ $t('options.setDefault') }}
          </b-dropdown-item>
          <b-dropdown-item link-class="pl-3" variant="danger" @click="deleteConfirmationOpen = true">
            <fa-icon icon="trash-alt" fixed-width class="mr-2" />
            {{ $t('options.delete') }}
          </b-dropdown-item>
        </b-dropdown>
      </template>
    </Display>
    <b-modal
      v-model="deleteConfirmationOpen"
      :title="$t('confirmation.title')"
      header-bg-variant="danger"
      header-text-variant="light"
      ok-variant="danger"
      :ok-title="$t('confirmation.ok')"
      :cancel-title="$t('confirmation.cancel')"
      @ok="removeAccount(value.id)"
    >
      {{ $t('confirmation.body') }}
    </b-modal>
  </b-list-group-item>
</template>

<script>
import Vuex from 'vuex'
import Display from './Display'

export default {
  name: 'BankingAccountListItem',

  components: {
    Display,
  },

  props: {
    value: { type: Object, default: () => ({}) },
  },

  data: () => ({
    deleteConfirmationOpen: false,
  }),

  methods: {
    ...Vuex.mapActions('banking', [ 'removeAccount', 'setDefault' ]),
  },

}
</script>

<style lang="scss" scoped>
.primary-dot {
  position: absolute;
  top: -0.5rem;
  right: 0;
  border-radius: 50%;
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "account_type": {
      "CHK": "Checking Account Ending in {last_4}",
      "SAV": "Savings Account Ending in {last_4}"
    },
    "options": {
      "setDefault": "Set as Default Payout Account",
      "delete": "Remove"
    },
    "confirmation": {
      "title": "Are you sure?",
      "body": "Are you sure you want to remove this account?",
      "ok": "Yes, Remove Account",
      "cancel": "Back"
    },
    "primary": "This is your primary account",
  }
}
</i18n>
