<template>
  <div v-if="!isLoading" class="list-component tag-following">
    <b-card>

      <b-row>
        <b-col>
          <h4 class="card-title mt-3 mb-1"><span class="tag-title">Following</span> ({{ totalRows }})</h4>
          <small class="text-muted">Creators who I am following or subscribed to</small>
        </b-col>
      </b-row>

      <hr />

      <CtrlBar @apply-filters="applyFilters($event)" />

      <b-row class="mt-3">
        <b-col lg="4" v-for="s in shareables" :key="s.id" > 
          <!-- %NOTE: s is the [shareables] record, s.shareable is the related, 'morhped', 'shareable' object (eg timelines) -->
          <!-- %NOTE: we're using WidgetTimeline here because you're following a timeline, not a user directly -->
          <WidgetTimeline :session_user="session_user" :timeline="s.shareable" :access_level="s.access_level" :created_at="s.created_at">
            <b-card-text @click="handleFavorite(s.shareable_id)" class="mb-2 clickable">
              <fa-icon v-if="s.is_favorited" fixed-width :icon="['fas', 'star']" style="color:#007bff" />
              <fa-icon v-else fixed-width :icon="['far', 'star']" style="color:#007bff" />
              Add to favorites
            </b-card-text>

            <b-button class="mb-1" variant="primary">Message</b-button>
            <b-button class="mb-1" @click="renderTip(s.shareable, 'timelines')" variant="primary">Tip</b-button>
            <b-button class="mb-1" v-if="s.access_level==='default' && s.shareable.userstats.subscriptions.price_per_1_months" @click="renderSubscribe(s.shareable)" variant="primary">Subscribe</b-button>
            <b-button class="mb-1" @click="showUnfollowConfirmation=true;timeline=s.shareable" variant="primary">
              Unfollow
            </b-button>
          </WidgetTimeline>
          <!-- <pre> Access Level: {{ s.access_level }} {{ JSON.stringify(s, null, "\t") }} </pre> -->
        </b-col>
      </b-row>

      <b-pagination
        v-model="currentPage"
        :total-rows="totalRows"
        :per-page="perPage"
        aria-controls="following-list"
        v-on:page-click="pageClickHandler"
        class="mt-3"
      ></b-pagination>

    </b-card >

    <b-modal
      v-model="showUnfollowConfirmation"
      size="md"
      title="Unfollow"
    >
      <template #modal-title>
        {{ $t('unfollow.confirmation.title') }}
      </template>
      <div class="my-2 text-left" v-text="$t('unfollow.confirmation.message')" />
      <template #modal-footer>
        <div class="text-right">
          <b-btn class="px-3 mr-1" variant="secondary" @click="showUnfollowConfirmation=false">
            {{ $t('unfollow.confirmation.cancel') }}
          </b-btn>
          <b-btn class="px-3" variant="danger" :disabled="isInProcess" @click="doUnfollow">
            <b-spinner small v-if="isInProcess" class="mr-2"></b-spinner>
            {{ $t('unfollow.confirmation.ok') }}
          </b-btn>
        </div>
      </template>
    </b-modal>
  </div>
</template>

<script>
//import Vuex from 'vuex';
import { eventBus } from '@/eventBus'
//import { DateTime } from 'luxon'
import moment from 'moment'
import mixinModal from '@mixins/modal'
import CtrlBar from '@components/lists/CtrlBar'
import WidgetTimeline from '@components/lists/WidgetTimeline'

export default {

  mixins: [mixinModal],

  props: {
    session_user: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.shareables || !this.meta
    },
    totalRows() {
      return this.meta ? this.meta.total : 1
    },
  },

  data: () => ({
    moment: moment,
    shareables: null,
    meta: null,
    perPage: 9,
    currentPage: 1,

    sort: {
      by: null,
      dir: 'asc',
    },

    filters: {
      accessLevel: 'all', // %TODO: change default ('all') to null
      onlineStatus: 'all',
    },
    showUnfollowConfirmation: false,
    isInProcess: false,
    timeline: null,
  }),

  methods: {
    getPagedData(type = null) {

      const params = {
        page: this.currentPage, 
        take: this.perPage,
      }

      // Apply filters
      if (this.filters.accessLevel && this.filters.accessLevel !== 'all') {
        params.accessLevel = this.filters.accessLevel
      }
      if (this.filters.onlineStatus && this.filters.onlineStatus !== 'all') {
        params.onlineStatus = this.filters.onlineStatus
      }

      // Apply sort
      if (this.sort.by) {
        params.sortBy = this.sort.by
      }
      if (this.sort.dir) {
        params.sortDir = this.sort.dir
      }
      params.isFollowing = true;

      axios.get( route('shareables.indexFollowing'), { params } ).then( response => {
        this.shareables = response.data.data
        this.meta = response.data.meta
      })
    },

    // may adjust filters, but always reloads from page 1
    reloadFromFirstPage() {
      this.doReset()
      this.getPagedData()
    },

    applyFilters({ filters, sort }) {
      console.log('ListsFollowers', { 
        filters,
        sort,
      })
      this.filters = filters
      this.sort = sort
      this.reloadFromFirstPage()
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },

    doReset() {
      this.currentPage = 1
    },

    handleFavorite(id) {
      const temp = this.shareables.map(s => {
        if (s.shareable_id === id) {
          this.toggleFavorite(s.is_favorited, id);
          s.is_favorited = s.is_favorited ? 0 : 1;
        }
        return s;
      });

      this.shareables = temp;
    },

    async toggleFavorite(isFavorited, id) {
      let response
      if (isFavorited) { // remove
        response = await axios.post(`/favorites/remove`, {
          favoritable_type: 'timelines',
          favoritable_id: id,
        })
      } else { // add
        response = await axios.post(`/favorites`, {
          favoritable_type: 'timelines',
          favoritable_id: id,
        })
      }
    },

    /*
    // shareable in this context is the [shareables] record
    // shareable.shareable in this context is related shareable record (eg timeline)
    doReport(shareable) {
      console.log('doReport() TODO'); // %TODO
    },

    getTimelineUrl(timeline) {
      return route('spa.index', timeline.slug)
    }
     */

    async doUnfollow(e) {
      this.isInProcess = true
      e.preventDefault()
      const response = await this.axios.put( route('timelines.follow', this.timeline.id), {
        sharee_id: this.session_user.id,
        notes: '',
      })
      this.$bvModal.hide('modal-follow')
      const msg = response.data.is_following
        ? `You are now following ${this.timeline.name}!`
        : `You are no longer following ${this.timeline.name}!`
      this.$root.$bvToast.toast(msg, {
        toaster: 'b-toaster-top-center',
        title: 'Success!',
        variant: 'success',
      })
      this.isInProcess = false
      this.showUnfollowConfirmation = false
       eventBus.$emit('update-timelines', this.timeline.id)
    },


  },

  mounted() { },

  created() {
    this.getPagedData()

    eventBus.$on('update-timelines', (timelineId) => {
      this.getPagedData()
    })
  },

  components: {
    CtrlBar,
    WidgetTimeline,
  },
}
</script>

<style lang="scss" scoped>
</style>


<i18n lang="json5" scoped>
{
  "en": {
    "unfollow": {
      "confirmation": {
        "title": "Unfollow",
        "message": "Are you sure you wish to unfollow the user?",
        "ok": "Unfollow",
        "cancel": "Cancel"
      },
    }
  }
}
</i18n>
