<template>
  <div v-if="!isLoading" class="tag-media-lightbox" v-bind:data-mediafile_guid="mediafile.id">
    <b-card
      no-body
      header-tag="header"
      footer-tag="footer"
      tag="article"
      class="superbox-mediafile"
      header-class="d-flex justify-content-between"
    >

      <template v-if="true || mediafile.access">
        <b-row>
          <b-col cols="12" class="d-flex align-items-center justify-content-center">
            <div v-if="mediafile.is_image">
              <b-img fluid class="d-block" :src="(use_mid && mediafile.has_mid) ? mediafile.midFilepath : mediafile.filepath" :alt="mediafile.mfname">
              </b-img>
            </div>
            <div v-else class="w-100">
              <MediaSlider :mediafiles="[mediafile]" :session_user="session_user" :use_mid="use_mid" />
            </div>
          </b-col>
        </b-row>
      </template>

      <template footer>
        <section class="panel-footer">

          <div v-if="context==='vault-dashboard'" class="crate-tag_mgmt p-3">
            <b-form-tags v-model="contenttags" separator=" ," no-outer-focus class="mb-2">

              <template v-slot="{ tags, inputAttrs, inputHandlers, tagVariant, addTag, removeTag }">
                <b-input-group class="mb-2 d-flex align-items-center">
                  <b-form-input
                    v-bind="inputAttrs"
                    v-on="inputHandlers"
                    placeholder="New tag - Press enter to add"
                    class="OFF-form-control"
                  ></b-form-input>
                  <div class="ml-2" v-b-tooltip.hover.html="{title: 'Add tags - use hash at start for <em>#publictag</em> or hash and exclamation at end for <em>#privatetag!</em>' }">
                    <fa-icon :icon="['far', 'info-circle']" class="text-secondary" />
                  </div>
                </b-input-group>
                <div class="d-inline-block">
                  <b-form-tag v-for="tag in tags"
                    @remove="removeTag(tag)"
                    :key="tag"
                    :title="tag"
                    :variant="isHashtagPrivate(tag) ? 'danger' : 'secondary'" 
                    class="mr-1"
                  >{{ tag }}</b-form-tag>
                </div>
              </template>

            </b-form-tags>

            <div class="d-flex justify-content-end">
              <b-button @click="updateTags" style="width: 10rem;" variant="primary">Save</b-button>
            </div>

          </div>

        </section>
      </template>

    </b-card>
  </div>
</template>

<script>
import Vuex from 'vuex'
//import { eventBus } from '@/eventBus'
import MediaSlider from '@components/posts/MediaSlider'

export default {
  props: {
    context: null, // to determine which UI elements to show conditionally
    mediafile: null,
    session_user: null,
    use_mid: { type: Boolean, default: false }, // use mid-sized images instead of full
  },

  computed: {
    isLoading() {
      return !this.mediafile || !this.session_user
    },
  },

  data: () => ({
    stats: null,
    contenttags: [],
  }),

  methods: {
    handleTagInput(tags) {
      console.log('handleTagInput', { value })
    },

    isHashtagPrivate(s) {
      return s.endsWith('!')
    },

    async updateTags() {
      const payload = {
        contenttags: this.contenttags,
      }
      let response = null
      try { 
        console.log('updateTags', { payload })
        response = await axios.patch( this.$apiRoute('mediafiles.updateTags', this.mediafile.id), payload )
        this.$emit('reload')
        this.$emit('close')
      } catch (e) {
        console.log('err', { e, })
        return
      }
      this.contenttags = []
    },

  },

  created() {
    this.axios.get(this.$apiRoute('mediafiles.diskStats', this.mediafile.id)).then(response => {
      this.stats = response.data.stats
    })
  },

  mounted() {
    console.log(this.mediafile, this.mediafile.contenttags)
    this.contenttags = this.mediafile.contenttags.map( ct => {
      switch ( ct.pivot.access_level ) {
        case 'management-group':
          return `#${ct.ctag}!`
        case 'open':
          return `#${ct.ctag}`
      }
    })
  },

  components: {
    MediaSlider,
  },

}
</script>

<style lang="scss" scoped>
ul {
  margin: 0;
}

.feed-crate .superbox-post .card-text {
  color: #383838;
  white-space: no-wrap;
  overflow: hidden;
  max-height: 18rem;
  text-overflow: ellipsis;

  display: -webkit-box;
  -webkit-line-clamp: 5;
  -webkit-box-orient: vertical;
}

.user-avatar {
  width: 40px;
  height: 40px;
  float: left;
  margin-right: 10px;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

.user-details ul {
  padding-left: 50px;
  margin-bottom: 0;
}

.user-details ul > li {
  color: #859ab5;
  font-size: 16px;
  font-weight: 400;
}

.user-details ul > li .username {
  text-transform: capitalize;
}

.user-details ul > li .post-time {
  color: #4a5568;
  font-size: 12px;
  letter-spacing: 0px;
  margin-right: 3px;
}
.user-details ul > li:last-child {
  font-size: 14px;
}

.tag-media-lightbox .superbox-mediafile img {
  max-height: calc(100vh - 17rem);
}
</style>
