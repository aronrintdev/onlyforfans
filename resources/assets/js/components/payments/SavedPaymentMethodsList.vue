<template>
  <div>
    <b-skeleton-wrapper :loading="loading">
      <template #loading>
        <b-skeleton />
      </template>
      <span class="header mb-2">Use Payment Method:</span>
      <b-list-group class="my-2">
        <SavedPaymentMethod
          v-for="(item, index) in paymentMethods"
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
import SavedPaymentMethod from './SavedPaymentMethod'
export default {
  components: {
    SavedPaymentMethod,
  },

  data: () => ({
    loading: true,
    paymentMethods: [
      { id: 'a', nickname: 'Card 1', type: 'card', brand: 'visa', last4: '1234' },
      { id: 'b', nickname: 'Card 2', type: 'card', brand: 'mastercard', last4: '5555' },
    ],
    selected: null,
  }),

  methods: {
    loadPaymentMethods() {

      this.loading = false
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
        var paymentMethod =  _.find(this.paymentMethods, ['id', value])
        this.$emit('loadPayWithForm', paymentMethod || this.paymentMethods[value] );
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