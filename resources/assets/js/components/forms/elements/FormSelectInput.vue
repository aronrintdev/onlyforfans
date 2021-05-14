<template>
  <b-form-group :id="groupID" :label="label" :label-for="ikey">
    <b-form-select v-model="dvalue" :options="options"></b-form-select>
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
    options: null,
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

