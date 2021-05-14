<template>
  <b-form-group :id="groupID" :label="label" :label-for="ikey">
    <b-input-group prepend="$" class="mb-2 mr-sm-2 mb-sm-0">
      <b-form-input 
        v-model="dvalue" 
        :state="isValid" 
        @update="doUpdate()" 
        :placeholder="placeholder" 
        :disabled="disabled"
      ></b-form-input>
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
  },

  computed: {
    groupID() {
      return `group-${this.ikey}`
    },
    isValid() {
      return (this.dverrors && this.dverrors[this.ikey]) ? false : null
    },
    vmsg() {
      return (this.dverrors && this.dverrors[this.ikey]) ?  this.dverrors[this.ikey][0] : ''
    },
  },

  data: () => ({
    dvalue: null,
    dverrors: null,
  }),

  watch: {
    dvalue(v) {
      this.$emit('input', this.dvalue)
    },
    verrors(v) {
      this.dverrors = v
    },
  },

  methods: {
    doUpdate() {
      console.log('do update')
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

