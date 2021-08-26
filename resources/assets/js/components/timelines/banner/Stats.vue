<template>
  <ul class="list-unstyled list-inline text-right">
    <li
      v-for="item in statsDisplay"
      :key="item.stat"
      class="list-inline-item pl-3 text-secondary"
      v-b-tooltip.hover
      :title="item.tooltip"
    >
      <router-link :to="item.link">
        <span>
          {{ stats[item.stat] }}
          <fa-icon :icon="item.icon" />
        </span>
      </router-link>
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
        link: '',
        stat: 'post_count',
        tooltip: 'Posts',
        icon: 'photo-video',
      },
      {
        link: '/fans/followers',
        stat: 'follower_count',
        tooltip: 'Fans',
        icon: 'user-friends',
      },
      {
        link: '/notifications/dashboard',
        stat: 'like_count',
        tooltip: 'Likes',
        icon: 'heart',
      },
      {
        link: '/fans/following',
        stat: 'following_count',
        tooltip: 'Following',
        icon: 'user-check',
      },
    ],
  }),
}
</script>

<style lang="scss" scoped>
  .list-inline-item a {
    color: inherit;
    text-decoration: none;
  }
</style>
