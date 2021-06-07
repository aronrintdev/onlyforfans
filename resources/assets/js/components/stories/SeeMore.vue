<template>
  <div class="see-more">
    <b-link :href="'//' + link" target="_blank" ref="link">
      <b-icon icon="chevron-up" />
      <span>See More</span>
    </b-link>
  </div>
</template>

<script>
export default {
  mounted() {
    this.$parent.$on('open-see-more-link', () => {
      this.$refs.link.$el.click()
    })
  },

  props: [
    'link',
  ],

  data: () => ({}),

  methods: {},

  components: {},
}
</script>

<style scoped>
.see-more {
  position: absolute;
  bottom: 20px;
  left: 50%;
  text-align: center;
  transform: translateX(-50%);
}
.see-more > a {
  color: #fff;
}
.see-more span {
  display: block;
}
</style>
