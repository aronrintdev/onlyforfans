<template>
  <div class="g-input__wrapper b-input-radio__wrapper">
    <span class="b-input-radio__container">
      <input
        :id="`${group_name}_input_block_${value}`"
        :name="group_name"
        type="radio"
        :value="value"
        :checked="checked"
        class="b-input-radio"
        @change="onChange"
      />
      <label :for="`${group_name}_input_block_${value}`" class="b-input-radio__text"> {{ label }}</label>
      <span class="b-input-radio__label">
        <span class="b-input-ripple"></span>
      </span>
    </span>
  </div>
</template>

<script>
  export default {
    name: 'RadioGroupBox',
    props: {
      label: '',
      value: undefined,
      checked: false,
      group_name: '',
    },
    methods: {
      onChange: function() {
        this.$emit('onChange', this.value);
      }
    }
  }
</script>

<style>
.g-input__wrapper.b-input-radio__wrapper, .g-input__wrapper .b-input-radio__wrapper {
  padding: 0 8px 22px;
}
.g-input__wrapper {
  padding: 0 0 20px;
  position: relative;
}

.b-input-radio__wrapper {
  display: flex;
  align-items: flex-start;
}
.b-input-radio__container {
    position: relative;
    display: inline-block;
    margin: 0;
}
.b-input-radio__container .b-input-radio {
    display: block;
    width: 1px;
    height: 1px;
    left: 50%;
    bottom: 0;
    position: absolute;
    opacity: 0;
}
.b-input-radio__text {
    padding-left: 32px;
    margin-bottom: 0;
    text-align: left;
    position: relative;
    z-index: 1;
    color: inherit;
    flex: 1;
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
    -webkit-hyphens: auto;
    hyphens: auto;
        cursor: pointer;
    display: inline-block;
    max-width: 100%;
}
.b-input-radio__container:hover .b-input-radio__label {
    border-color: #0091ea;
}
.b-input-radio__container .b-input-radio__label, .g-input__wrapper.b-input-radio__wrapper .b-input-radio__container .b-input-radio__label, .g-input__wrapper .b-input-radio__wrapper .b-input-radio__container .b-input-radio__label {
    margin: 2px 0;
}
.b-input-radio__label {
    width: 20px;
    height: 20px;
    border-radius: 1000px;
    border: 2px solid rgba(138,150,163,.4);
    transition: background-color .3s ease,border .3s ease;
    margin: 0 10px 0 0;
    min-width: 20px;
    position: absolute;
    left: 0;
    top: 0;
    pointer-events: none;
}
.b-input-radio__container .b-input-radio[type=radio]~.b-input-radio__label:after {
    content: "";
    position: absolute;
    width: 14px;
    height: 14px;
    left: 50%;
    top: 50%;
    transform: translate(-50%,-50%) scale(0);
    opacity: 0;
    border: 2px solid #fefefe;
    border-radius: 1000px;
    transition: opacity .2s,transform .3s;
}
.b-input-ripple {
    position: absolute;
    left: -2px;
    top: -2px;
    right: -2px;
    bottom: -2px;
}
.b-input-ripple:after, .b-input-ripple:before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    border-radius: 1000px;
}
.b-input-ripple:after {
    z-index: 1;
}
.b-input-ripple:before {
    box-shadow: 0 0 0 8px rgb(0 175 240 / 6%);
    transform: scale(0);
    opacity: 0;
    transition: transform .1s,opacity .1s;
    z-index: 0;
}
.b-input-radio__container .b-input-radio[type=radio]:checked~.b-input-radio__label {
    background-color: #00aff0;
    border-color: #00aff0;
}
.b-input-radio__container .b-input-radio[type=radio]:checked~.b-input-radio__label:after {
    opacity: 1;
    transform: translate(-50%,-50%) scale(1);
}
.b-input-radio__container:hover .b-input-ripple:before {
    opacity: 1;
    transform: scale(1);
}
.dropdown-item:hover .b-input-radio__label {
    border-color: #00aff0;
}
.dropdown-item:hover .b-input-ripple:before {
    opacity: 1;
    transform: scale(1);
}
</style>
