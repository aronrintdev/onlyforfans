<template>
  <div>
    <div class="d-flex justify-content-between">
      <div class="h4">{{ mode === 'view' ? 'Viewing' : 'Editing' }} {{ modelName }}</div>
      <div v-if="mode === 'view'">
        <b-btn variant="info" v-b-tooltip.hover title="Edit this resource" @click="edit">
          <fa-icon icon="edit" />
        </b-btn>
      </div>
      <div v-else-if="mode === 'edit'">
        <b-btn-group>
          <b-btn variant="success" v-b-tooltip.hover title="Save Changes" @click="save">
            <fa-icon icon="save" />
          </b-btn>
          <b-btn variant="danger" v-b-tooltip.hover title="Cancel" @click="cancelEdit">
            <fa-icon icon="times" />
          </b-btn>
        </b-btn-group>
      </div>
      <div v-else-if="mode === 'saving'">
        <b-btn disabled variant="secondary">
          <fa-icon icon="spinner" spin size="lg" class="mr-2" /> Saving
        </b-btn>
      </div>
      <error-alert v-else-if="mode === 'error'" />
    </div>

    <div v-for="item in form" :key="item.name">
      <b-form-group
        :id="`input-group-${item.name}`"
        :label="item.name"
        :label-for="`input-${item.name}`"
      >
        <div v-if="item.isTime">
          {{ toDate(item.value) }}
        </div>
        <b-form-input
          v-else
          :id="`input-${item.name}`"
          :value="item.value"
          :disabled="item.isDisabled"
          @input="(v) => fieldInput(v, item.name)"
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
import { findIndex, filter, map } from 'lodash'

const laravelTimeFields = ['created_at', 'updated_at', 'deleted_at']

export default {
  props: {
    value: { type: Object, default: () => ({}) },
    /** Key field or fields of model, these will not be editable. Default is `id` */
    modelKey: { type: [String, Array], default: 'id' },
    /** Name of model for display proposes */
    modelName: { type: String },
    /** These fields will not be editable */
    protectedFields: { type: Array, default: () => [] },
    /** Will ignore these fields */
    ignoreFields: { type: Array, default: () => [] },
    /** Will only show these fields */
    onlyFields: { type: Array, default: () => [] },
    /** Specify any additional time fields on resource */
    timeFields: { type: Array, default: () => [] },
  },
  data: () => ({
    mode: 'view', // view | edit
    valueCopy: null, // Copy of object from when user starts editing
  }),
  computed: {
    form() {
      // Map values to object
      const mapped = map(this.value, (value, name) => {
        return {
          value,
          name,
          isKey: this.isKey(name),
          isDisabled: this.isDisabled(name),
          isTime: this.isTimeField(name),
        }
      })
      // Filter out ignored fields
      return filter(mapped, (o) => {
        if (this.onlyFields.length > 0) {
          return findIndex(this.protectedFields, (v) => v === o.name) !== -1
        }
        if (typeof o.value === 'object') {
          return false
        }
        return findIndex(this.ignoreFields, (v) => v === o.name) === -1
      })
    },
  },
  methods: {
    /**
     * Begins editing
     */
    edit() {
      this.valueCopy = this.value
      this.mode = 'edit'
      this.$emit('editing')
    },
    /**
     * Cancels edit, returns value to state when editing began.
     */
    cancelEdit() {
      this.mode = 'view'
      this.$emit('input', { ...this.valueCopy })
      this.$emit('cancelEdit')
    },
    /**
     * Tells Parent to save new values.
     */
    save() {
      this.mode = 'saving'
      /**
       * @param callback - callback when parent is finished saving
       */
      this.$emit('save', {
        callback: (options = { error: false }) => {
          this.$log.debug('callback', { options })
          options.error ? this.mode = 'error' : this.mode = 'view'
        },
      })
    },

    isDisabled(name) {
      return (
        this.mode !== 'edit' ||
        this.isKey(name) ||
        findIndex(this.protectedFields, (v) => v === name) !== -1 ||
        this.isTimeField(name)
      )
    },
    isKey(name) {
      if (typeof this.modelKey === 'string') {
        return name === this.modelKey
      }
      return findIndex(this.modelKey, (v) => v === name) !== -1
    },
    isTimeField(name) {
      return findIndex([...laravelTimeFields, ...this.timeFields], (v) => v === name) !== -1
    },
    toDate(value) {
      return DateTime.fromISO(value).toLocaleString(DateTime.DATETIME_MED)
    },
    fieldInput(value, name) {
      this.$emit('input', { ...this.value, [name]: value })
    },
  },
}
</script>
