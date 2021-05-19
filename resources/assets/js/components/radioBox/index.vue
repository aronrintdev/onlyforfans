<template>
    <a role="menuitem" href="javascript:void(0)" target="_self" class="dropdown-item">
        <input :id="groupName" type="radio" :value="value" :checked="checked" :name="groupName" class="b-input-radio">
        <label :for="groupName"> {{label}}</label>
        <span class="b-dropdown__icon"><span class="b-input-ripple"></span></span>
    </a>
</template>

<script>
  export default {
    name: 'RadioBox',
    props: {
      label: '',
      groupName: '',
      value: false,
    },
    data: () => ({
      checked: false,
    }),
    created() {
      this.checked = this.value;
    },
    methods: {
      onChange: function() {
        this.checked = !this.checked;
        this.$emit('onChange', this.checked);
      }
    }
  }
</script>

<style>
.b-input-radio {
    display: block;
    width: 1px;
    height: 1px;
    left: 50%;
    bottom: 0;
    position: absolute;
    opacity: 0;
}
.b-input-radio__container .dropdown-item label {
    width: 100%;
    padding: 14px 16px 15px 48px;
    margin-bottom: 0;
}
.b-input-radio__container .b-dropdown__icon {
    width: 20px;
    height: 20px;
    min-width: 20px;
    flex: 0 0 20px;
    border-radius: 1000px;
    border: 2px solid rgba(138,150,163,.4);
    margin: 0 13px 0 0;
    display: inline-block;
    vertical-align: top;
    position: absolute;
    left: 16px;
    top: 14px;
    margin: 0;
    transition: border-color .1s,box-shadow .1s;
    pointer-events: none;
}
.b-input-ripple {
    position: absolute;
    left: -2px;
    top: -2px;
    right: -2px;
    bottom: -2px;
}
.dropdown-menu .b-dropdown__icon:after {
    content: "";
    position: absolute;
    width: 14px;
    height: 14px;
    left: 50%;
    top: 50%;
    transform: translate(-50%,-50%);
    opacity: 0;
    border: 2px solid #fefefe;
    border-radius: 1000px;
    transition: opacity .2s;
}

</style>
