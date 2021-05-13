<template>
  <b-form-group :id="groupID" label="Full Name" :label-for="ikey">
    <b-form-input v-model="dvalue" :state="isValid" @update="doUpdate()" placeholder="Enter first name" :disabled="disabled"></b-form-input>
    <b-form-invalid-feedback id="input-live-feedback">{{ vmsg }}</b-form-invalid-feedback>
  </b-form-group>
</template>

<script>

export default {

  props: {
    ikey: null, // 'input key'
    value:  null,
    verrors: null,
    disabled: false,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user_settings
    },
    groupID() {
      return `group-${this.ikey}`
    },
    isValid() {
      return (this.dverrors && this.dverrors[this.ikey]) ? false : null
    },
    vmsg() {
      return (this.dverrors && this.dverrors[this.ikey]) ?  this.dverrors[this.ikey][0]: ''
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

