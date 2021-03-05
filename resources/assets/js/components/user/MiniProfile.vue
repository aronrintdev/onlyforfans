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

      <b-card v-if="!!timeline.cover"
        :img-src="timeline.cover.filepath"
        :img-alt="timeline.slug"
        img-top
        tag="article"
        class="background"
      >
        <div class="avatar-img">
          <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
            <b-img
              thumbnail
              rounded="circle"
              class="w-100 h-100"
              :src="timeline.avatar.filepath"
              :alt="timeline.slug"
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
                <b-icon icon="check-circle-fill" variant="success" font-scale="1" />
              </span>
            </h2>
            <p class="avatar-mail my-0">
              <router-link :to="{ name: 'timeline.show', params: { slug: timeline.slug } }">
                @{{ timeline.slug }}
              </router-link>
            </p>
          </div>
        </div>
      </b-card>
    </b-skeleton-wrapper>
  </div>

</template>

<script>
export default {
  props: {
    timeline: null, // { type: Object, default: () => ({ cover: {}, avatar: {} }) },
  },

  computed: {
    isLoading() {
      return !this.timeline
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
      }
      .card-img-top {
        overflow: hidden;
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
