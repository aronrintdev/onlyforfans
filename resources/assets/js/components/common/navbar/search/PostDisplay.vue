<template>
  <router-link
    custom
    :to="{ name: 'posts.show', params: { slug: value.slug } }"
    v-slot="{ href, navigate, isActive, isExactActive }"
  >
    <li
      class="result-list-item"
      :class="{
        'highlighted': highlighted,
        'router-link-active': isActive,
        'router-link-exact-active': isExactActive
      }"
      :href="href"
      @click="(e) => {
        navigate(e)
        $emit('click')
      }"
    >
      <b-media>
        <template #aside>
          <b-avatar v-if="value.mediafiles && value.mediafiles[0]" :src="value.mediafiles[0].filepath" />
          <b-avatar v-else>
            <fa-icon icon="pen" />
          </b-avatar>
        </template>
        <p class="mb-0" v-text="text" />
      </b-media>
    </li>
  </router-link>
</template>

<script>
export default {
  props: {
    value: { type: Object, default: () => ({}) },
    highlighted: { type: Boolean, default: false },
    index: { type: Number, default: 0 },
    maxCharacters: { type: Number, default: 100 },
  },

  computed: {
    text() {
      if (this.value.description) {
        return `${this.value.description.slice(0, this.maxCharacters)}...`
      }
      return ''
    }
  },
}
</script>
