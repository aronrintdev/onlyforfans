<template>
  <b-card no-body class="background mb-5">
    <b-card-img
      :src="value.cover.filepath"
      :alt="value.username"
      top
      class="cursor-pointer"
      @click="$router.push(link)"
    />

    <b-card-body class="py-1">

      <div class="last-seen">Last seen TBD</div>

      <div class="banner-ctrl">
        <slot name="banner-ctrl" />
      </div>

      <div class="avatar-img">
        <router-link :to="link">
          <b-img-lazy thumbnail rounded="circle" class="w-100 h-100" :src="value.avatar.filepath" :alt=" value.slug || value.username" :title="value.name" />
        </router-link>
      </div>


      <div class="sharee-id">
        <b-card-title class="mb-1">
          <router-link :to="link">{{ value.name }}</router-link>
          <fa-icon v-if="accessLevel === 'premium'" fixed-width :icon="['fas', 'rss-square']" style="color:#138496; font-size: 16px;" />
        </b-card-title>
        <b-card-sub-title class="mb-1">
          <router-link :to="link">@{{  value.slug
          || value.username }}</router-link>
        </b-card-sub-title>
      </div>

      <slot />
    </b-card-body>
  </b-card>
</template>

<script>
export default {
  name: 'ListsCard',

  props: {
    value: { type: Object, default: () => ({ avatar: {}, cover: {} }) },
    accessLevel: { type: String, default: '' },
  },

  computed: {
    link() {
      return { name: 'timeline.show', params: { slug: this.value.slug || this.value.username } }
    }
  },
}
</script>

<style lang="scss" scoped>
.card {
  .card-body {
    padding-top: 0.6rem;
    padding-bottom: 0.6rem;
  }

  .card-title a {
    color: #4a5568;
    text-decoration: none;
  }
  .card-subtitle a {
    color: #6e747d;
    text-decoration: none;
  }

  &.background {
    position: relative;
    .avatar-details {
      margin-left: 58px;
    }
    .shareable-id, .sharee-id {
      margin-left: 5.5rem;
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
    .last-seen {
      color: #fff;
      margin-left: 5.5rem;
      font-size: 0.9rem;
      position: absolute;
      top: 91px;
    }
    .banner-ctrl {
      position: absolute;
      top: 5px;
      right: 0;
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

<i18n lang="json5" scoped>
{
  "en": {
    "lastSeen": "Last seen at {time}"
  }
}
</i18n>
