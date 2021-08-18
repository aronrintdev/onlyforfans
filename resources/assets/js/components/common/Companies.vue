<template>
  <div v-if="!isLoading && timelines.length > 0" class="suggested_feed-crate tag-crate px-3 mt-3">

    <section class="ctrl-top d-flex justify-content-between align-items-center">
      <h5 class="my-0 flex-grow-1">COMPANIES</h5>
    </section>

    <ul class="list-suggested list-group">
      <li v-for="(timeline, i) in timelines" :key="timeline.id || i">
        <div class="d-flex align-items-center mb-3 ml-3">
          <div class="avatar-img">
            <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
              <b-img-lazy
                thumbnail
                rounded="circle"
                class="w-100 h-100"
                :src="timeline.avatar.filepath"
                :title="timeline.name"
              />
            </router-link>
          </div>

          <div class="avatar-profile d-flex justify-content-between">
            <div class="avatar-details">
              <h2 class="avatar-name my-0">
                <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
                  {{ timeline.name }}
                </router-link>
                <span v-if="timeline.verified" class="verified-badge">
                  <fa-icon icon="check-circle" class="text-primary" />
                </span>
              </h2>
              <p class="avatar-mail my-0">
                <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
                  @{{ timeline.slug }}
                </router-link>
              </p>
            </div>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import MiniProfile from '@components/user/MiniProfile'

export default {
  components: {
    MiniProfile,
  },

  props: {
    session_user: null,
  },

  computed: {
    isLoading() {
      return this.session_user === null
    },
    timelines() {
      return this.session_user.companies || []
    }
  },
}
</script>

<style scoped lang="scss">
  .list-group {
    li {
      list-style: none;
    }
  }
  .avatar-img {
    width: 50px;
    height: 50px;

    img {
      border: 0;
    }
  }
  .avatar-details {
    margin-left: 0.5em;
    .avatar-name {
      font-size: 16px;
    }
    .avatar-mail {
      font-size: 14px;
    }
  }
</style>
