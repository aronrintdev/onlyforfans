<template>
  <b-form-group :id="groupID" :label="label" :label-for="ikey">
    <b-input-group class="mb-2 mr-sm-2 mb-sm-0">
      <template v-if="itype==='currency'" #prepend>
        <b-input-group-text>$</b-input-group-text>
      </template>
      <b-form-input 
        v-model="dvalue"
        :state="isValid"
        @update="doUpdate()" 
        :placeholder="placeholder" 
        :disabled="disabled"
        :type="inputType"
      ></b-form-input>
      <template v-if="itype==='percent'" #append>
        <b-input-group-text>%</b-input-group-text>
      </template>
      <b-form-invalid-feedback id="input-live-feedback">{{ vmsg }}</b-form-invalid-feedback>
    </b-input-group>
  </b-form-group>
</template>

<script>

export default {

  props: {
    ikey: null, // 'input key'
    value:  null,
    verrors: null,
    disabled: false,
    label: '',
    placeholder: '',
    itype: { // 'input type' (text, currency, etc)
      default: 'text',
      type: String,
    },
  },

  computed: {
    groupID() {
      return `group-${this.ikey}`
    },
    isValid() {
      return (this.dverrors && this.dverrors[this.ikey]) ? false : null
    },
    vmsg() {
      return (this.dverrors && this.dverrors[this.ikey]) ?  this.dverrors[this.ikey][0]: ''
    },
    inputType() {
      if (this.itype === 'percent' || this.itype === 'currency') {
        return 'text'
      }
      return this.itype
    }
  },

  data: () => ({
    dvalue: null,
    dverrors: null,
  }),

  watch: {
    dvalue(v) {
      this.$emit('input', v)
    },
    verrors(v) {
      this.dverrors = v
    },
  },

  methods: {
    doUpdate() {
      this.dverrors = null
    }
  },

  created() {
    this.dvalue = this.value
    this.dverrors = this.verrors
  },

}
</script>

<style scoped>
</style>

