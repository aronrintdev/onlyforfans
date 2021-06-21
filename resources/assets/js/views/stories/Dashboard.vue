<template>
  <div id="view-stories_create" class="row w-100">
      <div class="col-sm-12" v-if="!loading">
        <Wizard :dto-user="dtoUser" :stories="stories" />
      </div>
  </div>
</template>

<script>
/**
 * Stories Dashboard View
 */
import Wizard from '@components/stories/Wizard'

export default {
  components: {
    Wizard,
  },
  data: () => ({
    dtoUser: null,
    stories: null,
    loading: true,
  }),
  methods: {
    load() {
      this.loading = true,
      this.axios.get(this.$apiRoute('stories.dashboard'))
        .then(response => {
          this.dtoUser = response.data.dtoUser
          this.stories = response.data.stories
          this.loading = false
        })
    }
  },
  created() {
    this.load()
  }
}
</script>
