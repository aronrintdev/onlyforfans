<template>
  <div v-if="!isLoading && timelines.length > 0" class="suggested_feed-crate tag-crate px-3 mt-3">

    <section class="ctrl-top d-flex justify-content-between align-items-center">
      <h5 class="my-0 flex-grow-1">My Managed Profiles</h5>
    </section>

    <ul class="list-suggested list-group">
      <template v-for="(timeline, i) in timelines">
        <li :key="timeline.id || i" v-if="timeline.slug">
          <div class="d-flex align-items-center mb-3 ml-3">
            <div class="avatar-img" :class="hasStaffNotification(timeline) ? 'has-staff-notification' : ''">
              <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
                <b-img-lazy
                  thumbnail
                  rounded="circle"
                  class="w-100 h-100"
                  :src="timeline.avatar ? timeline.avatar.filepath : '/images/default_avatar.png'"
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
      </template>
    </ul>
  </div>
</template>

<script>
export default {
  props: {
    session_user: null,
    unread_notifications: Array,
  },

  computed: {
    isLoading() {
      return this.session_user === null
    },
    timelines() {
      return this.session_user && this.session_user.companies || []
    },
  },

  methods: {
    hasStaffNotification(timeline) {
      return this.unread_notifications.filter(n => n.data.actor && n.data.actor.id == timeline.user_id && n.type == 'App\\Notifications\\StaffSettingsChanged').length > 0;
    }
  }
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

    &.has-staff-notification img {
      border: 2px solid var(--danger);
    }

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
