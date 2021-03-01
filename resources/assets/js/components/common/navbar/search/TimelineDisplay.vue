<template>
  <router-link
    custom
    :to="{ name: 'timeline.show', params: { slug: timeline.slug } }"
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
          <b-avatar :src="timeline.avatar.filepath" />
        </template>
        <p class="h5" v-text="timeline.name || 'No Name'" />
        <p class="mb-0" v-text="`@${timeline.slug}`" />
      </b-media>
    </li>
  </router-link>
</template>

<script>
export default {
  props: {
    timeline: { type: Object, default: () => ({}) },
    highlighted: { type: Boolean, default: false },
    index: { type: Number, default: 0 },
  }
}
</script>
