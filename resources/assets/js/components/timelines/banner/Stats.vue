<template>
  <ul class="list-unstyled list-inline text-right">
    <li
      v-for="item in statsDisplay"
      :key="item.stat"
      class="list-inline-item pl-3"
      v-b-tooltip.hover
      :title="item.tooltip"
    >
      <span>
        {{ stats[item.stat] }}
        <b-icon :icon="item.icon" variant="default" />
      </span>
    </li>
  </ul>
</template>

<script>
/**
 * components/timelines/banner/Stats.vue
 */
export default {
  props: {
    stats: {
      type: Object,
      default: () => ({
        post_count: 0,
        follower_count: 0,
        like_count: 0,
        following_count: 0,
      }),
    },
  },
  data: () => ({
    statsDisplay: [
      {
        stat: 'post_count',
        tooltip: 'Posts',
        icon: 'favorites-fill',
      },
      {
        stat: 'follower_count',
        tooltip: 'Followers',
        icon: 'people-fill',
      },
      {
        stat: 'like_count',
        tooltip: 'Likes',
        icon: 'heart-fill',
      },
      {
        stat: 'following_count',
        tooltip: 'Following',
        icon: 'person-check-fill',
      },
    ],
  }),
}
</script>
