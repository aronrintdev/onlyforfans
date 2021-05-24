<template>
  <b-form-group :label="label" >
    <b-form-select :value="value" :options="countryList" @input="val => $emit('input', val)" />
  </b-form-group>
</template>

<script>
/**
 * Select box with country list
 */
import countryList from 'country-list'

export default {
  name: 'CountrySelectInput',

  props: {
    groupAttr: {},
    label: { type: String, default: 'Select Country' },
    value: { type: String, default: 'US' },
  },

  computed: {
    countryList() {
      var list = _.sortBy(countryList.getData().map(o => ({ value: o.code, text: o.name })), 'text')
      return [
        { value: 'US', text: "United States of America" },
        { value: 'CA', text: 'Canada' },
        { value: 'UK', text: 'United Kingdom of Great Britain and Northern Ireland' },
        { html: '<hr></hr>', value: '', disabled: true },
        ...list,
      ]
    },
  },
}
</script>
