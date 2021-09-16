<template>
  <div>
    <b-skeleton-wrapper :loading="loading">
      <template #loading>
        <b-skeleton />
      </template>
      <div v-if="showChangeButton && collapse && !open" class="w-100 d-flex justify-content-around">
        <b-btn
          variant="link"
          class="ml-auto"
          v-text="$t('change.button')" @click="open = true"
        />
      </div>
      <b-collapse :visible="!this.collapse || open">
        <span class="header mb-2" v-text="$t('list.title')" />
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
      </b-collapse>

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
    collapse: { type: Boolean, default: false },
    showChangeButton: { type: Boolean, default: false },
  },

  computed: {
    ...Vuex.mapState('payments', [ 'savedPaymentMethods' ])
  },

  data: () => ({
    loading: true,
    open: false,
  }),

  methods: {
    ...Vuex.mapActions('payments', [ 'getSavedPaymentMethods' ]),

    change() {
      this.open = true
    },

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
      this.open = false
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
    "change": {
      "button": "Change"
    },
    "list": {
      "title": "Use Payment Method:"
    }
  }
}
</i18n>