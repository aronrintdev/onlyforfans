<template>
  <div>
    <b-skeleton-wrapper :loading="isLoading" >
      <template #loading>
        <b-card
          no-body
          img-top
          class="background"
        >
          <!-- loading... -->
          <b-skeleton-img card-img="top" no-aspect height="120px" />

          <b-card-body>
            <div class="avatar-img">
              <div class="img-thumbnail rounded-circle h-100">
                <b-skeleton type="avatar" class="w-100 h-100" />
              </div>
            </div>

            <div class="avatar-profile d-flex justify-content-between">
              <div class="avatar-details w-100">
                <h2 class="avatar-name w-100 my-0">
                  <b-skeleton width="50%" />
                </h2>
                <p class="avatar-mail my-0">
                  <b-skeleton width="40%" />
                </p>
              </div>
            </div>
          </b-card-body>
        </b-card>
      </template>

      <b-card
        no-body
        tag="article"
        class="background"
      >
        <b-card-img
          top
          :src="timeline.cover.filepath"
          class="cursor-pointer"
          @click="$router.push({ name: 'timeline.show', params: { slug: timeline.slug } })"
        />
        <b-card-body>
          <div class="avatar-img">
            <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
              <b-img-lazy
                thumbnail
                rounded="circle"
                class="w-100 h-100"
                :src="timeline.avatar.filepath"
                :title="timeline.name"
              />
              <OnlineStatus :user="timeline.user" size="md" :textInvisible="false" />
            </router-link>
          </div>

          <div class="avatar-profile d-flex justify-content-between align-items-center">
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
            <ul class="list-unstyled">
              <li v-if="timeline.is_following">
                <span class="text-secondary">
                  <fa-icon icon="check" /> Following
                </span>
              </li>
              <li v-else>
                <b-button @click="renderFollow" :disabled="timeline.is_owner" variant="primary" class="w-100 OFF-mt-3">
                  <span>Follow</span>
                </b-button>
              </li>
            </ul>
          </div>

          <slot></slot>
        </b-card-body>
      </b-card>
    </b-skeleton-wrapper>
  </div>

</template>

<script>
import { eventBus } from '@/eventBus'
import OnlineStatus from '@components/common/OnlineStatus'

export default {
  components: {
    OnlineStatus,
  },

  props: {
    timeline: null, // { type: Object, default: () => ({ cover: {}, avatar: {} }) },
  },

  computed: {
    isLoading() {
      //return !this.timeline || !this.timeline.cover
      return !this.timeline
    },
  },

  methods: {
    renderFollow() {
      this.$log.debug('FollowCtrl.renderFollow() - emit');
      eventBus.$emit('open-modal', {
        key: 'render-follow',
        data: {
          timeline: this.timeline,
        }
      })
    },
  },
}
</script>

<style lang="scss" scoped>
  .card {
    .card-body {
      padding-top: 0.6rem;
      padding-bottom: 0.6rem;
    }

    &.background {
      position: relative;
      .avatar-details {
        margin-left: 58px;
      }
      .avatar-img {
          position: absolute;
          left: 8px;
          top: 90px; /* bg image height - 1/2*avatar height */
          width: 60px;
          height: 60px;
          .onlineStatus {
            position: absolute;
            top: 35px;
            right: 0;
          }
      }
      .card-img-top {
        overflow: hidden;
        object-fit: cover;
        height: 120px;
      }
    }

    .avatar-details {
      h2.avatar-name {
        font-size: 16px;
        & > a {
          color: #4a5568;
          text-decoration: none;
          text-transform: capitalize;
        }
      }

      .avatar-mail  {
        font-size: 14px;
        & > a {
          color: #7F8FA4;
          text-decoration: none;
        }
      }
    }
  }
</style>
