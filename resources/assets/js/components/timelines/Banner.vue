<template>
  <div v-if="!isLoading" class="session_banner-crate tag-crate">
    <header
      @mouseenter="toggleUploadCover(true)"
      @mouseleave="toggleUploadCover(false)"
      class="masthead text-white text-center"
      v-bind:style="{ backgroundImage: 'url(' + timeline.cover.filepath + ')' }"
    >
      <input type="file" id="cover-upload-btn" class="file-input" @change="onCoverChange" />
      <label v-if="uploadCoverVisible" for="cover-upload-btn" class="btn photo-btn">
        <fa-icon :icon="['far', 'camera']" size="2x" class="text-white" />
      </label>
      <div class="overlay" />
      <b-dropdown no-caret variant="transparent" id="profile-ctrl-dropdown" class="tag-ctrl">
        <template #button-content>
          <b-icon icon="filter-left" font-scale="2" variant="light"></b-icon>
        </template>
        <b-dropdown-item>First Action</b-dropdown-item>
        <b-dropdown-item>Second Action</b-dropdown-item>
        <b-dropdown-item>Third Action</b-dropdown-item>
      </b-dropdown>
    </header>

    <section
      @mouseenter="toggleUploadAvatar(true)"
      @mouseleave="toggleUploadAvatar(false)"
      class="avatar-img"
    >
      <b-img
        thumbnail
        rounded="circle"
        class="w-100 h-100"
        :src="timeline.avatar.filepath"
        :alt="timeline.name"
        :title="timeline.name"
      />
      <input type="file" id="avatar-upload-btn" class="file-input" @change="onAvatarChange" />
      <label v-if="uploadAvatarVisible" for="avatar-upload-btn" class="btn photo-btn">
        <fa-icon :icon="['far', 'camera']" size="2x" class="text-white" />
      </label>
    </section>

    <b-container fluid>
      <b-row class="avatar-profile py-3">
        <b-col cols="12" md="4" offset-md="2" class="avatar-details text-right text-md-left">
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
              @{{ timeline.slug || 'TODO' }}
            </router-link>
          </p>
          <div>
            <OnlineStatus :user="timeline.user" />
          </div>
        </b-col>

        <b-col cols="12" md="4" offset-md="2" class="tag-stats my-0">
          <Stats :stats="timeline.userstats" />
        </b-col>
      </b-row>
    </b-container>
  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/app'
import OnlineStatus from '@components/user/OnlineStatus'
import Stats from './banner/Stats'

export default {
  components: {
    OnlineStatus,
    Stats,
  },
  props: {
    timeline: null,
  },

  computed: {
    ...Vuex.mapGetters(['session_user']),

    isLoading() {
      return !this.session_user || !this.timeline
    },
  },

  data: () => ({
    uploadCoverVisible: false,
    uploadAvatarVisible: false,
  }),

  methods: {
    toggleUploadCover(visible) {
      if (!this.timeline.is_owner) return

      this.uploadCoverVisible = visible
    },

    toggleUploadAvatar(visible) {
      if (!this.timeline.is_owner) return

      this.uploadAvatarVisible = visible
    },

    onCoverChange(e) {
      const files = e.target.files || e.dataTransfer.files

      if (!files.length) return

      const formData = new FormData()
      formData.append('cover', files[0])

      this.axios
        .post('/users/cover', formData)
        .then((response) => {
          eventBus.$emit('update-timelines', this.timeline.id)
        })
        .catch((error) => {
          this.$log.error(error)
        })
    },

    onAvatarChange(e) {
      const files = e.target.files || e.dataTransfer.files

      if (!files.length) return

      eventBus.$emit('open-modal', {
        key: 'render-crop',
        data: {
          url: URL.createObjectURL(files[0]),
          timelineId: this.timeline.id,
        },
      })
    },
  },

  created() {},
}
</script>

<style lang="scss" scoped>
.tag-crate {
  background-color: #fff;
}

header.masthead {
  position: relative;
  padding-top: 12rem;
  padding-bottom: 12rem;
  position: relative;
  background-color: #343a40;
  background-position: center;
  background-size: cover;
  padding-top: 8rem;
  padding-bottom: 8rem;

  #profile-ctrl-dropdown {
    position: absolute;
    top: 0;
    right: 0;
  }
}

/* Why doesn't this CSS have any effect ? */
header.masthead .profile-ctrl.dropdown button {
  color: red !important;
  border: none;
  background: transparent;
}

.avatar-img {
  position: absolute;
  left: 32px;
  top: 185px; /* %TODO: bg image height - 1/2*avatar height */
  width: 130px;
  height: 130px;
}

.file-input {
  display: none;
}

.photo-btn {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.avatar-details {
  /*
  margin-left: 172px;
   */
  font-weight: 400;

  a {
    /*
    color: #4a5568;
    color: #7F8FA4;
    */
    color: #555;
    text-decoration: none;
    text-transform: capitalize;
  }

  h2 {
    font-size: 20px;
  }

  p {
    font-size: 16px;
  }
}
</style>
