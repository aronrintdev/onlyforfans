<template>
  <ul class="list-unstyled list-inline text-right">
    <li
      v-for="item in statsDisplay"
      :key="item.stat"
      class="list-inline-item pl-3 text-secondary"
      v-b-tooltip.hover
      :title="item.tooltip"
    >
      <span>
        {{ stats[item.stat] }}
        <fa-icon :icon="item.icon" />
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
        icon: 'pen',
      },
      {
        stat: 'follower_count',
        tooltip: 'Followers',
        icon: 'user-friends',
      },
      {
        stat: 'like_count',
        tooltip: 'Likes',
        icon: 'heart',
      },
      {
        stat: 'following_count',
        tooltip: 'Following',
        icon: 'user-check',
      },
    ],
  }),
}
</script>
