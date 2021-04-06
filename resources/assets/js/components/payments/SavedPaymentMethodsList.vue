<template>
  <div>
    <b-skeleton-wrapper :loading="loading">
      <template #loading>
        <b-skeleton />
      </template>
      <span class="header mb-2">Use Payment Method:</span>
      <b-list-group class="my-2">
        <SavedPaymentMethod
          v-for="(item, index) in savedPaymentMethods"
          :key="item.id || index"
          :value="item"
          :selected="(item.id || index) === selected"
          @selected="onSelected((item.id || index))"
        />
        <SavedPaymentMethod
          :value="{ id: 'new', nickname: 'New Payment Method', icon: 'plus-circle' }"
          :selected="selected === null || selected === 'new'"
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

  computed: {
    ...Vuex.mapState('payments', [ 'savedPaymentMethods' ])
  },

  data: () => ({
    loading: true,
    selected: null,
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
      this.selected = index
    }
  },

  watch: {
    selected(value) {
      if (value === 'new') {
        this.$emit('loadNewForm')
      } else {
        var paymentMethod =  _.find(this.savedPaymentMethods, ['id', value])
        this.$emit('loadPayWithForm', paymentMethod || this.savedPaymentMethods[value] );
      }
    }
  },

  mounted() {
    this.loadPaymentMethods()
  },
}
</script>

<style lang="scss" scoped>

</style>