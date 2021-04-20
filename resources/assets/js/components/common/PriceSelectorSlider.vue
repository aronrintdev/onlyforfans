<template>
  <div>
    {{ formatCurrency(min) }}
      <VueSlider
        class="flex-grow-1 mx-3"
        :min="min"
        :max="max"
        :value="value"
        :interval="interval"
        :tooltipFormatter="formatCurrency"
        :marks="marks"
        @change="onChange"
      />
      {{ formatCurrency(max) }}
  </div>
</template>

<script>
export default {
  props: {
    value: { type: Number, default: 0 },
    min: { type: Number, default: 300 },
    max: { type: Number, default: 5000 },
    interval: { type: Number, default: 100 },
    formatCurrency: { type: Function, default: (v) => (v)},
  },

  computed: {
    marks(value) {
      if (value % this.majorMark === 0) {
        const height = 0.75
        return {
          label: this.formatCurrency(value),
          style: {
            height: `${height}rem`,
            transform: `translateY(-${height * 0.35}rem)`,
            borderRadius: '0.1rem'
          },
        }
      }
      if (value % this.minorMark === 0) {
        return { label: '', }
      }
      return false
    },
    onChange(value) {
      this.$emit('input', value)
    }
  },

}
</script>

<style lang="scss" scoped>

</style>
