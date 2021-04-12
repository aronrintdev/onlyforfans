<template>
  <div class="g-input__increment">
    <button
      type="button"
      class="g-input__increment__btn m-with-round-hover"
      :disabled="value <= min"
      @click="decrease"
    >
      <svg id="icon-minus" viewBox="0 0 24 24"><path d="M17 13H7a1 1 0 010-2h10a1 1 0 010 2z"></path></svg>
    </button>
    <div class="g-input__increment__value">{{` ${prefix ? prefix : ''} `}}{{ value }}{{` ${suffix ? suffixD : ''} `}}</div>
    <button
      type="button"
      class="g-input__increment__btn m-with-round-hover"
      :disabled="value >= max"
      @click="increase"
    >
      <svg id="icon-plus" viewBox="0 0 24 24"><path d="M17 13h-4v4a1 1 0 01-2 0v-4H7a1 1 0 010-2h4V7a1 1 0 012 0v4h4a1 1 0 010 2z"></path></svg>
    </button>
  </div>
</template>

<script>

export default {
  props: {
    prefix: '',
    suffix: '',
    step: undefined,
    min: 0,
    max: 0,
  },
  data: () => ({
    value: 0,
  }),
  computed: {
    suffixD: function() {
      return this.value > 1 ? this.suffix + 's' : this.suffix;
    }
  },
  mounted: function() {
    this.value = this.min;
  },
  methods: {
    decrease: function() {
      this.value = parseInt(this.value) - parseInt(this.step);
      this.$emit('onchange', this.value);
    },
    increase: function() {
      this.value = parseInt(this.value) + parseInt(this.step);
      this.$emit('onchange', this.value);
    }
  }
};
</script>
<style lang="scss" scoped>
  .g-input__increment {
    display: flex;
    align-content: center;
    align-items: center;

    .g-input__increment__btn[disabled] {
      pointer-events: none;
      color: #8a96a3;
    }
    .g-input__increment__value {
      flex: 1;
      text-align: center;
      white-space: nowrap;
      padding: 6px 8px;
      min-height: 48px;
      font-size: 16px;
      color: rgb(36, 37, 41);
      display: flex;
      min-width: 90px;
      box-shadow: border-box;
      align-items: center;
      justify-content: center;
    }
    .g-input__increment__btn {
      border-radius: 1000px;
      border: none;
      width: 36px;
      height: 36px;
      padding: 0;
      background: none;
      position: relative;
      color: #00aff0;
      outline: none;
      svg {
        font-size: 24px;
        width: 1em;
        height: 1em;
        min-width: 1em;
        display: inline-block;
        fill: currentColor;
        line-height: 1;
      }
      &:hover {
        background-color: rgb(0 145 234 / 12%);
        color: #0091ea;
      }
    }
  }
</style>