<template>
  <b-form-group :id="groupID" label="Full Name" :label-for="ikey">
    <!--
    <input
      v-bind:value="value"
      v-on:input="$emit('input', $event.target.value)"
      :id="ikey" 
      :state="isValid" 
      placeholder="Enter first name" 
      type="text"
      class="form-control"
    >
    -->
    <b-form-input v-model="value" :state="isValid" placeholder="Enter first name"></b-form-input>
    <b-form-invalid-feedback id="input-live-feedback">{{ vmsg }}</b-form-invalid-feedback>
  </b-form-group>
</template>

<script>
//import Vuex from 'vuex';

export default {

  props: {
    ikey: null,
    value:  null,
    verrors: null,
    //isValid: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.user_settings
    },
    groupID() {
      return `group-${this.ikey}`
    },
    isValid() {
      return (this.verrors && this.verrors[this.ikey]) ? false : null
    },
    vmsg() {
      return (this.verrors && this.verrors[this.ikey]) ?  this.verrors[this.ikey][0]: ''
    },
  },

  data: () => ({
    //isValid: null,
  }),

  watch: {
    value(v) {
      this.$emit('input', this.value)
    },
    //this.$emit('post', this.post.id)
  },

  mounted() {
  },

  created() {
    //this.formGeneral.email = this.session_user.email || '';
  },

  methods: {
  },

  components: {
  },
}
</script>

<style scoped>
</style>

