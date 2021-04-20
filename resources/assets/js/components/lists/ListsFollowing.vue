<template>
  <div v-if="!isLoading">
    <b-card>

      <b-row>
        <b-col>
          <h2 class="card-title mb-1"><span class="tag-title">Following</span> ({{ totalRows }})</h2>
          <small class="text-muted">Creators who I am following or subscribed to</small>
       </b-col>
     </b-row>

     <hr />

      <b-row class="mt-3">
        <b-col lg="4" v-for="(s,idx) in shareables" :key="s.id" >
          <b-card no-body class="background mb-5">
            <b-card-img :src="s.shareable.cover.filepath" alt="s.shareable.slug" top></b-card-img>

            <b-card-body>

              <div class="avatar-img">
                <router-link :to="{ name: 'timeline.show', params: { slug: s.shareable.slug } }">
                  <b-img thumbnail rounded="circle" class="w-100 h-100" :src="s.shareable.avatar.filepath" :alt="s.shareable.slug" :title="s.shareable.name" />
                </router-link>
              </div>

              <div class="shareable-id">
                <b-card-title class="mb-2">
                  <router-link :to="{ name: 'timeline.show', params: { slug: s.shareable.slug } }">{{ s.shareable.name }}</router-link>
                </b-card-title>
                <b-card-sub-title class="mb-2">
                  <router-link :to="{ name: 'timeline.show', params: { slug: s.shareable.slug } }">@{{ s.shareable.slug }}</router-link>
                </b-card-sub-title>
              </div>

              <b-card-text class="mb-2"><fa-icon fixed-width :icon="['far', 'star']" style="color:#007bff" /> Add to favorites</b-card-text>

              <b-button variant="outline-primary">Message</b-button>
              <b-button variant="outline-success">Send A Tip</b-button>
              <b-button v-if="s.access_level==='default'" @click="renderSubscribe(s.shareable)" variant="outline-info">Premium Access</b-button>
              <b-button variant="outline-warning">Cancel</b-button>
              <div>
                <small v-if="s.access_level==='premium'" class="text-muted">subscribed since {{ moment(s.updated_at).format('MMM DD, YYYY') }}</small>
                <small v-else class="text-muted">following for free since {{ moment(s.updated_at).format('MMM DD, YYYY') }}</small>
              </div>
              <!--
                <pre>
                Access Level: {{ s.access_level }}
                  {{ JSON.stringify(s, null, "\t") }}
                </pre>
              -->

            </b-card-body>

          </b-card>
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

  </div>
</template>

<script>
//import Vuex from 'vuex';
import { eventBus } from '@/app'
//import { DateTime } from 'luxon'
import moment from 'moment'

export default {

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
    perPage: 10,
    currentPage: 1,

  }),

  methods: {
    getPagedData(type=null) {
      const params = {
        page: this.currentPage, 
        take: this.perPage,
      }
      if (this.filter && this.filter!=='none') {
        params.type = this.filterToType // PostTipped, etc
      }
      axios.get( route('shareables.indexFollowing'), { params } ).then( response => {
        this.shareables = response.data.data
        this.meta = response.data.meta
      })
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },

    renderSubscribe(selectedTimeline) {
      this.$log.debug('ListsFollowing.renderSubscribe() - emit', {
        selectedTimeline,
      });
      eventBus.$emit('open-modal', {
        key: 'render-subscribe',
        data: {
          timeline: selectedTimeline,
        }
      })
    },
  },

  mounted() { },

  created() {
    this.getPagedData()
  },

  components: {
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
    .shareable-id {
      margin-left: 5.5rem;
    }
    .avatar-img {
      position: absolute;
      left: 8px;
      top: 90px; /* bg image height - 1/2*avatar height */
      width: 90px;
      height: 90px;

      .rounded-circle.img-thumbnail {
        padding: 0.11rem;
      }
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

