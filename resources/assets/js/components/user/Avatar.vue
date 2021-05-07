<template>
  <section class="avatar-img" :style="{ height: s, width: s }">
    <router-link :to="{ name: 'timeline.show', params: { slug: user.timeline ? user.timeline.slug : user.slug } }">
      <b-img
        thumbnail
        rounded="circle"
        class="w-100 h-100"
        :src="user.avatar.filepath"
        :alt="user.name"
        :title="user.name"
      />
    </router-link>
  </section>
</template>

<script>
export default {
  name: 'UserAvatar',

  model: { prop: 'user', event: 'change'},

  props: {
    user: { type: Object, default: () => ({ avatar: {} }) },
    size: { type: String, default: 'sm' },
  },

  computed: {
    s() {
      switch (this.size) {
        case 'xs': return '2rem'
        case 'sm': return '3rem'
        case 'md': return '6rem'
        case 'lg': return '9rem'
      }
    },
  },

}
</script>

<style lang="scss" scoped>

</style>
