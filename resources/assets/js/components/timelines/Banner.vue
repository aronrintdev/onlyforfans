<template>
  <div v-if="!isLoading" class="session_banner-crate tag-crate">
    <header
      @mouseenter="toggleUploadCover(true)"
      @mouseleave="toggleUploadCover(false)"
      class="masthead text-white text-center"
      v-bind:style="{ backgroundImage: 'url(' + coverImage + ')' }"
    >
      <input type="file" id="cover-upload-btn" class="file-input" @change="onCoverChange" />
      <label v-if="uploadCoverVisible" for="cover-upload-btn" class="btn photo-btn">
        <fa-icon :icon="['far', 'camera']" size="2x" class="text-white" />
      </label>
      <div class="overlay" />
      <b-dropdown no-caret variant="transparent" id="profile-ctrl-dropdown" class="tag-ctrl">
        <template #button-content>
          <fa-icon :icon="['far', 'bars']" size="2x" class="text-white" />
        </template>
        <b-dropdown-item @click="renderTip">Send a Tip</b-dropdown-item>
        <b-dropdown-item @click="toggleFavorite">{{ isFavoritedByMe ? 'Unfavorite' : 'Favorite'}}</b-dropdown-item>
        <b-dropdown-item
          v-clipboard:copy="profileLink"
          v-clipboard:success="onCopySuccess"
          v-clipboard:error="onCopyError"
        >Copy link to profile</b-dropdown-item>
        <b-dropdown-item v-if="timeline.is_owner">
          <router-link :to="{ name: 'settings.profile', params: {} }">
            Edit Profile
          </router-link>
        </b-dropdown-item>
        <b-dropdown-item disabled>Restrict</b-dropdown-item>
        <b-dropdown-item disabled>Block</b-dropdown-item>
        <b-dropdown-item disabled>Report</b-dropdown-item>
      </b-dropdown>
    </header>

    <section
      @mouseenter="toggleUploadAvatar(true)"
      @mouseleave="toggleUploadAvatar(false)"
      class="avatar-img"
    >
      <b-img-lazy
        thumbnail
        rounded="circle"
        class="w-100 h-100"
        :src="avatarImage"
        :alt="timeline.name"
        :title="timeline.name"
      />
      <OnlineStatus :user="timeline.user" size="lg" :textInvisible="false" />
      <input type="file" id="avatar-upload-btn" class="file-input" @change="onAvatarChange" />
      <label v-if="uploadAvatarVisible" for="avatar-upload-btn" class="btn photo-btn">
        <fa-icon :icon="['far', 'camera']" size="2x" class="text-white" />
      </label>
    </section>

    <b-container fluid>
      <b-row class="avatar-profile py-3">
        <b-col cols="12" md="4" offset-md="1" class="pl-5 avatar-details text-right text-md-left">
          <h2 class="avatar-name my-0 text-secondary">
            {{ timeline.name }}
            <span v-if="timeline.verified" class="verified-badge">
              <fa-icon icon="check-circle" class="text-primary" />
            </span>
          </h2>
          <p class="avatar-mail my-0 text-secondary">
            @{{ timeline.slug || 'TODO' }}
          </p>
          <div class="banner-online-status">
            <OnlineStatus :user="timeline.user" :indicatorVisible="false" />
          </div>
        </b-col>

        <b-col cols="12" md="4" offset-md="3" class="tag-stats">
          <Stats :stats="timeline.userstats" />
        </b-col>
      </b-row>
    </b-container>
  </div>
</template>

<script>
import Vuex from 'vuex'
import { eventBus } from '@/eventBus'
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

    coverImage() {
      const { cover } = this.timeline
      return cover ? cover.filepath : '/images/locked_post.png'
    },

    avatarImage() {
      const { avatar } = this.timeline
      return avatar ? avatar.filepath : '/images/default_avatar.png'
    },

    profileLink() {
      return window.location.href
    }
  },

  data: () => ({
    uploadCoverVisible: false,
    uploadAvatarVisible: false,

    isFavoritedByMe: false, // is timeline/feed a favorite
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

    async toggleFavorite() {
      let response
      if (this.isFavoritedByMe) { // remove
        response = await axios.post(`/favorites/remove`, {
          favoritable_type: 'timelines',
          favoritable_id: this.timeline.id,
        })
        this.isFavoritedByMe = false
      } else { // add
        response = await axios.post(`/favorites`, {
          favoritable_type: 'timelines',
          favoritable_id: this.timeline.id,
        })
        this.isFavoritedByMe = true
      }
    },

    renderTip() {
      eventBus.$emit('open-modal', {
        key: 'render-tip',
        data: { 
          resource: this.timeline,
          resource_type: 'timelines', 
        },
      })
    },

    onCopySuccess() {
      alert("Copy to Clipboard has been succeed");
      this.showCopyToClipboardModal = false;
    },

    onCopyError() {
      this.showCopyToClipboardModal = false;
      alert("Copy to Clipboard has been failed. Please try again later.");
    },
  },

  created() {
    this.isFavoritedByMe = this.timeline.is_favorited;
  },
}
</script>

<style lang="scss" scoped>
.session_banner-crate {
  border: solid #dfdfdf 1px;
  border-radius: 3px;
}
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

.banner-online-status {
  @media (max-width: 576px) {
    float: right;
  }
}

.avatar-img {
  position: absolute;
  left: 32px;
  top: 185px; /* %TODO: bg image height - 1/2*avatar height */
  width: 130px;
  height: 130px;
  .onlineStatus {
    position: absolute;
    bottom: 15px;
    right: 5px;
    z-index: 1;
  }
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

.tag-ctrl {
  a {
    color: #212529;
    text-decoration: none;
  }
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
  }

  h2 {
    font-size: 20px;
  }

  p {
    font-size: 16px;
  }
}

.tag-stats {
  margin-top: -0.5em;
  cursor: default;
}
</style>
