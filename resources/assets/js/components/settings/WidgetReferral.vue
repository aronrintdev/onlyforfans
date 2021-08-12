<template>
  <b-card no-body class="background mb-5">
    <b-card-img :src="referral.cover.filepath" alt="referral.username" top></b-card-img>

    <b-card-body class="py-1">
      <div class="avatar-img">
        <router-link :to="{ name: 'timeline.show', params: { slug } }">
          <b-img-lazy thumbnail rounded="circle" class="w-100 h-100" :src="referral.avatar.filepath" :alt="referral.username" :title="referral.name" />
        </router-link>
      </div>

      <div class="referral-info">
        <b-card-title class="mb-1 subscriber-card text-dark">
          <router-link :to="{ name: 'timeline.show', params: { slug } }">{{ referral.name }}</router-link>
          <span v-if="referral.access_level==='premium'" class="subscriber">
            <b-badge variant='info'>
              Subscriber
            </b-badge>
          </span>
        </b-card-title>
        <b-card-sub-title class="mb-1">
          <router-link :to="{ name: 'timeline.show', params: { slug } }">@{{ referral.username }}</router-link>
        </b-card-sub-title>
      </div>

      <b-row class="referral-detail">
        <b-col class="pl-0">
          <b-card-sub-title>Joined</b-card-sub-title>
        </b-col>
        <b-col class="pr-0">
          <b-card-sub-title class="text-right">{{ moment(referral.created_at).format('YYYY MMM D')}}</b-card-sub-title>
        </b-col>
      </b-row>
    </b-card-body>
  </b-card>
</template>
<script>
import moment from 'moment'

export default {
  props: {
    referral: null,
    slug: null,
  },

  data: () => ({
    moment: moment,
  }),

  computed: {
    isLoading() {
      return !this.referral
    }
  }
}
</script>
<style lang="scss" scoped>
  .background {
    position: relative;
    .referral-info {
      margin-left: 5.5rem;
    }
    .referral-detail {
      margin: 35px 0 20px;
    }
    .avatar-img {
      position: absolute;
      left: 8px;
      top: 75px; /* bg image height - 1/2*avatar height */
      width: 90px;
      height: 90px;
      .rounded-circle.img-thumbnail {
        padding: 0.11rem;
      }
    }
    .card-img-top {
      overflow: hidden;
      object-fit: cover;
      height: 120px;
    }
    .card-title a {
      color: #4a5568;
      text-decoration: none;
    }
    .card-subtitle a {
      color: #6e747d;
      text-decoration: none;
    }
  }
</style>
