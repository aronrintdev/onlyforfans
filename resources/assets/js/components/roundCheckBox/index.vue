<template>
  <span :class="checked ? 'active round__checkbox' : 'round__checkbox'" @click="onChange">
    <svg v-if="checked" class="g-icon" viewBox="0 0 24 24">
      <path d="M9 19.42l-5.71-5.71A1 1 0 0 1 3 13a1 1 0 0 1 1-1 1 1 0 0 1 .71.29L9 16.59l10.29-10.3A1 1 0 0 1 20 6a1 1 0 0 1 1 1 1 1 0 0 1-.29.71z"></path>
    </svg>
    <span class="b-input-ripple"></span>
  </span>
</template>

<script>
  export default {
    name: 'RoundCheckBox',
    props: {
      label: '',
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
  .round__checkbox {
    border-color: #8a96a3;
    display: block;
    width: 20px;
    height: 20px;
    border-radius: 1000px;
    border: 2px solid rgba(138,150,163,.4);
    transition: background-color .3s ease,border .3s ease;
    position: relative;
    pointer-events: none;
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
  .b-input-ripple:before {
    box-shadow: 0 0 0 8px rgb(0 175 240 / 6%);
    transform: scale(0);
    opacity: 0;
    transition: transform .1s,opacity .1s;
    z-index: 0;
  }

  .b-input-ripple:after {
    z-index: 1;
  }
  .active .b-input-ripple:after {
    -webkit-animation: pulse-hover 1s forwards;
    animation: pulse-hover 1s forwards;
    -webkit-animation-delay: .4s;
    animation-delay: .4s;
  }
  .round__checkbox.active {
    background: #00aff0;
    border-color: #00aff0;
  }
  .g-icon {
    display: none;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%,-50%);
    width: 17px;
    height: 17px;
  }
  .g-icon path {
    fill: #fefefe;
  }
  .round__checkbox.active .g-icon {
    display: block;
  }
  .list-item:hover .b-input-ripple:before,
  .user-details:hover .b-input-ripple:before{
    opacity: 1;
    transform: scale(1);
  }
  .list-item:hover .round__checkbox,
  .user-details:hover .round__checkbox {
    border-color: #00aff0;
  }
  .list-item:hover .round__checkbox svg,
  .user-details:hover .round__checkbox svg {
    fill: #00aff0;
  }
</style>
