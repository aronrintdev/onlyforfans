<template>
  <section class="avatar-img" :style="{ height: s, width: s }">
    <b-img-lazy
      v-if="noLink"
      :thumbnail="thumbnail"
      rounded="circle"
      class="w-100 h-100"
      :src="filepath"
      :alt="user.name"
      :title="user.name"
    />
    <router-link
      v-else
      :to="{ name: 'timeline.show', params: { slug: user.timeline ? user.timeline.slug : user.slug } }"
    >
      <b-img-lazy
        :thumbnail="thumbnail"
        rounded="circle"
        class="w-100 h-100"
        :src="filepath"
        :alt="user.name"
        :title="user.name"
      />
    </router-link>
    <slot name="append" />
  </section>
</template>

<script>
export default {
  name: 'UserAvatar',

  model: { prop: 'user', event: 'change'},

  props: {
    user: { type: Object, default: () => ({ avatar: {} }) },
    noLink: { type: Boolean, default: false },
    size: { type: String, default: 'sm' },
    thumbnail: { type: Boolean, default: true },
  },

  computed: {
    filepath() {
      if (this.user.avatar) {
        return this.user.avatar.filepath
      }
      return '/images/default_avatar.png'
    },

    s() {
      const baseSize = 3
      switch (this.size) {
        case 'xs': return `${ baseSize * 0.75 }rem`
        case 'sm': return `${ baseSize * 1    }rem`
        case 'md': return `${ baseSize * 1.5  }rem`
        case 'lg': return `${ baseSize * 3    }rem`
      }
    },
  },

}
</script>

<style lang="scss" scoped>

</style>
