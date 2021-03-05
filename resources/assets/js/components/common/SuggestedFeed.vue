<template>
  <div v-if="!!timelines" class="suggested_feed-crate tag-crate">
    <ul class="list-suggested list-group">
      <li class="tag-heading list-group-item my-3"><h3 class="my-0">Suggested People</h3></li>
      <li v-for="(timeline, i) in timelines" :key="timeline.id || i" class="list-group-item my-3">
        <MiniProfile :timeline="timeline" :loading="isLoading" />
      </li>
    </ul>
  </div>
</template>

<script>
import Vuex from 'vuex';
import MiniProfile from '@components/user/MiniProfile'

export default {
  components: {
    MiniProfile,
  },
  props: {
    skeletons: { type: Number, default: 5 },
  },

  computed: {
    //...Vuex.mapState(['is_loading']),
  },

  data: () => ({
    isLoading: true,
    timelines: null,
  }),

  mounted() {
    this.load()
  },

  methods: {
    load() {
      this.isLoading = true
      this.timelines = Array(this.skeletons).fill({})
      axios.get(`/timelines-suggested`)
        .then( response => {
          const json = response.data;
          this.timelines = json.timelines || [];
          this.isLoading = false
        });
    }
  },
}
</script>

<style lang="scss" scoped>
  ul.list-suggested {
    background-color: #fff;
    padding: 0.5rem 0.5rem;
    li.tag-heading {
      h3 {
        font-size: 18px;
        text-transform: capitalize;
        color: #4a5568;
        font-weight: 500;
      }
    }
  }


  .list-group-item {
    border: none;
    padding: 0 0.5rem;
    a {
      text-decoration: none;
    }
  }
</style>
