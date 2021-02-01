<template>
  <div>
    <div class="h4">
      {{ mode === 'view' ? 'Viewing' : 'Editing' }} {{ modelName }}
    </div>
    <div v-for="(item, name) in value" :key="name">
      <b-form-group :id="`input-group-${name}`" :label="name" :label-for="`input-${name}`">
        <div v-if="isTimeField(name)">
          {{ toDate(item) }}
        </div>
        <b-form-input v-else
          :id="`input-${name}`"
          :value="item"
          :disabled="isDisabled(name)"
          @input="(v) => fieldInput(v, name)"
        />
      </b-form-group>
    </div>
  </div>
</template>

<script>
/**
 * Quick Resource viewer / editor Form
 */
import { DateTime } from 'luxon'
import { findIndex } from 'lodash'

const laravelTimeFields = [
   'created_at', 'updated_at', 'deleted_at'
]

export default {
  props: {
    value: { type: Object, default: () =>({}) },
    modelKey: { type: [String, Array], default: 'id' },
    modelName: { type: String, },
    protectedFields: { type: Array, default: () => ([]) },
  },
  data: () => ({
    mode: 'view', // view | edit
  }),
  methods: {
    isDisabled(name) {
      return this.mode !== 'edit' ||
        this.isKey(name) ||
        findIndex(this.protectedFields, (v) => ( v === name ) ) !== -1 ||
        findIndex(laravelTimeFields, (v) => ( v === name ) ) !== -1
    },
    isKey(name) {
      if (typeof this.modelKey === 'string') {
        return name === this.modelKey
      }
      return findIndex(this.modelKey, (v) => ( v === name ) ) !== -1
    },
    isTimeField(name) {
      return findIndex(laravelTimeFields, (v) => ( v === name ) ) !== -1
    },
    toDate(value) {
      return DateTime.fromISO(value).toLocaleString(DateTime.DATETIME_MED)
    },
    fieldInput(value, name) {
      this.$emit('input', { ...this.value, name: value })
    }
  },
}
</script>

<style lang="scss" scoped>

</style>
