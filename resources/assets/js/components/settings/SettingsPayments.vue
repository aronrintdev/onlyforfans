<template>
  <b-card :title="$t('PaymentMethods')">
    Save Payment Methods:
    <b-row class="align-items-stretch">
      <b-col v-if="savedPaymentMethods.length === 0">
        You have no save payment methods.
      </b-col>
      <b-col lg="6" v-else>
        <b-list-group class="my-2">
          <SavedPaymentMethod
            v-for="(item, index) in savedPaymentMethods"
            :key="item.id || index"
            :value="item"
            :selected="selected === item.id || selected === index"
            :selected-icon="'caret-right'"

            @selected="onSelected((item.id || index))"
          />
        </b-list-group>
      </b-col>
      <b-col lg="6" v-if="selectedItem" class="d-flex align-items-stretch">
        <transition name="quick-fade" mode="out-in">
          <b-card class="w-100" :body-class="`d-flex justify-content-between ${mobile ? 'flex-column': ''}`">
            <div class="d-flex flex-column">
              <SavedPaymentMethod
                :as="'div'"
                :value="selectedItem"
                :selectable="false"
                class="align-self-start"
              />
              <div v-if="defaultMethod === selected" class="mt-3" v-text="$t('IsYouDefault')" />
            </div>

            <b-row v-if="mobile">
              <b-col xl class="mt-3">
                <b-btn
                  block
                  :variant="defaultMethod === selected ? 'info' :'outline-info'"
                  class="py-3"
                  @click="onSetDefault"
                >
                  <fa-icon fixed-width icon="star" size="lg" />
                  <span v-if="mobile" v-text="$t('SetAsDefaultButton')" @click="onSetDefault" />
                </b-btn>
              </b-col>
              <b-col xl class="mt-3">
                <b-btn block variant="outline-danger" class="py-3">
                  <fa-icon fixed-width icon="trash" size="lg" />
                  <span v-if="mobile" v-text="$t('RemoveButton')" />
                </b-btn>
              </b-col>
            </b-row>
            <div v-else class="d-flex flex-column justify-content-around">
              <b-btn
                :variant="defaultMethod === selected ? 'info' :'outline-info'"
                class="py-3 my-2"
                v-b-tooltip="$t('SetAsDefaultButton')"
                @click="onSetDefault"
              >
                <fa-icon fixed-width icon="star" size="lg" />
              </b-btn>
              <b-btn variant="outline-danger" class="py-3 my-2" v-b-tooltip="$t('RemoveButton')">
                <fa-icon fixed-width icon="trash" size="lg" />
              </b-btn>
            </div>
          </b-card>
        </transition>


      </b-col>
    </b-row>
  </b-card>
</template>

<script>
/**
 * Payment methods settings
 */
import _ from 'lodash'
import Vuex from 'vuex'
import SavedPaymentMethod from '@components/payments/SavedPaymentMethod'

export default {
  name: 'SettingPayments',

  components: {
    SavedPaymentMethod,
  },

  computed: {
    ...Vuex.mapState([ 'mobile' ]),
    ...Vuex.mapState('payments', [ 'savedPaymentMethods', 'defaultMethod' ]),
    selectedItem() {
      if (this.selected) {
        return _.find(this.savedPaymentMethods, [ 'id', this.selected ]);
      }
      return null
    },
  },

  data: () => ({
    loading: true,
    selected: null,
  }),

  methods: {
    ...Vuex.mapActions('payments', [ 'updateSavedPaymentMethods', 'setDefaultPaymentMethod' ]),

    load() {
      this.loading = true
      this.updateSavedPaymentMethods()
        .then(() => {
          this.loading = false
          this.selected = this.defaultMethod
        })
    },

    onSelected(index) {
      this.selected = index
    },

    onSetDefault() {
      if (this.selected !== this.defaultMethod) {
        this.setDefaultPaymentMethod(this.selected)
      }
    }
  },

  mounted() {
    this.load()
  },

}
</script>

<i18n lang="json5">
{
  "en": {
    "PaymentMethods": "Payment Methods",
    "SetAsDefaultButton": "Set as default payment method",
    "IsYouDefault": "This is your default payment method.",
    "RemoveButton": "Remove payment method"
  }
}
</i18n>