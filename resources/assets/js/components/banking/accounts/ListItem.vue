<template>
  <b-list-group-item class="bank-account-list-item">
    <b-media vertical-align="center">
      <template #aside>
        <div class="position-relative" v-b-tooltip.hover="value.default ? ($t('primary')) : false">
          <span v-if="value.default" class="text-primary primary-dot">
            <fa-icon icon="circle" />
          </span>
          <fa-icon icon="university" fixed-width size="3x" />
        </div>
      </template>
      <div class="d-flex">
        <div>
          <div class="h4" v-text="value.bank_name" />
          <div v-text="$t(`account_type.${value.account_type}`, { last_4: value.account_number_last_4 })" />
        </div>
        <div class="ml-auto align-self-center">
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
        </div>
      </div>
    </b-media>
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

export default {
  name: 'BankingAccountListItem',

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
