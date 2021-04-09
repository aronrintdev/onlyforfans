<template>
  <div>
    <b-skeleton-wrapper :loading="loading">
      <template #loading>
        <b-skeleton />
      </template>
      <span class="header mb-2" v-text="$t('UsePaymentMethod')" />
      <b-list-group class="my-2">
        <SavedPaymentMethod
          v-for="(item, index) in savedPaymentMethods"
          :key="item.id || index"
          :value="item"
          :selected="(item.id || index) === selected.id"
          @selected="onSelected((item.id || index))"
        />
        <SavedPaymentMethod
          :value="{ id: 'new', nickname: 'New Payment Method', icon: 'plus-circle' }"
          :selected="selected.id === null || selected.id === 'new'"
          @selected="onSelected('new')"
        />
      </b-list-group>
    </b-skeleton-wrapper>
  </div>
</template>

<script>
/**
 * User's Saved Payment methods that can be used
 */
import _ from 'lodash'
import Vuex from 'vuex'
import SavedPaymentMethod from './SavedPaymentMethod'
export default {
  components: {
    SavedPaymentMethod,
  },

  props: {
    selected: { type: Object, default: () => ({}) },
  },

  computed: {
    ...Vuex.mapState('payments', [ 'savedPaymentMethods' ])
  },

  data: () => ({
    loading: true,
  }),

  methods: {
    ...Vuex.mapActions('payments', [ 'getSavedPaymentMethods' ]),

    loadPaymentMethods() {
      this.loading = true
      if (this.savedPaymentMethods.length > 0) {
        this.loading = false
      }
      this.getSavedPaymentMethods()
      .then(() => {
        this.loading = false
      })
    },

    onSelected(index) {
      this.$log.debug(`Payment method selected`, index);
      if (index === 'new') {
        this.$emit('loadNewForm')
      } else {
        var paymentMethod =  _.find(this.savedPaymentMethods, ['id', index])
        this.$emit('loadPayWithForm', paymentMethod || this.savedPaymentMethods[index] );
      }
    }
  },

  mounted() {
    this.loadPaymentMethods()
  },
}
</script>

<i18n lang="json5" scoped>
{
  "en": {
    "UsePaymentMethod": "Use Payment Method:"
  }
}
</i18n>