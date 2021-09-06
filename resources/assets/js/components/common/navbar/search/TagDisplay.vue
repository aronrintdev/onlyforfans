<template>
<router-link
  custom
  :to="{ name: 'tags.show', params: { id: value.id } }"
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
    <b-media class="align-items-center">
      <template #aside>
        <b-avatar>
          <fa-icon icon="tag" />
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
      return `#${this.value.ctag}`
    }
  },
}
</script>
