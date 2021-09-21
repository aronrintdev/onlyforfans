<template>
  <div v-if="!isLoading">

    <h4 class="card-title"><span class="tag-title">{{ title }}</span> ({{ totalRows | niceNumber }})</h4>

    <ul class="list-unstyled">
      <b-media v-for="n in notifications" :key="n.id" tag="li" class="mb-0">
        <template #aside>
          <router-link v-if="n.user.slug" :to="{ name: 'timeline.show', params: { slug: n.user.slug } }">
            <b-img-lazy width="48" height="48" rounded="circle" :src="n.user.avatar.filepath" :alt="n.user.slug" :title="n.user.name" />
          </router-link>
          <b-img-lazy v-else width="48" height="48" rounded="circle" :src="n.user.avatar.filepath" :alt="n.user.slug" :title="n.user.name" />
        </template>
        <h6 class="mt-0 mb-1">
          <template v-if="n.user.slug">
            <router-link :to="{ name: 'timeline.show', params: { slug: n.user.slug } }">
              <span>{{n.user.name}}</span>
            </router-link>&nbsp;
            <router-link :to="{ name: 'timeline.show', params: { slug: n.user.slug } }">
              <small class="text-muted">@{{ n.user.slug }}</small>
            </router-link>
          </template>
          <template v-else>
            <span>{{n.user.name}}</span>&nbsp;<small class="text-muted">@{{ n.user.username }}</small>
          </template>
        </h6>
        <p class="mb-0 notify-message">
          <template v-if="n.type==='App\\Notifications\\TimelineFollowed'">followed you</template>
          <template v-if="n.type==='App\\Notifications\\TimelineSubscribed'">subcribed for {{ n.data.amount }}</template>
          <template v-if="n.type==='App\\Notifications\\ResourceLiked'">liked your
            <template v-if="n.data.resource_type==='posts'">
              <router-link :to="{ name: 'posts.show', params: { slug: n.data.resource_slug } }">post</router-link>
            </template>
          </template>
          <template v-if="n.type==='App\\Notifications\\TipReceived'">sent you a tip
            <template v-if="n.data.amount"> for {{ n.data.amount | niceCurrency }}</template>
            <template v-if="n.data.resource_type==='posts'">
              on <router-link :to="{ name: 'posts.show', params: { slug: n.data.resource_slug } }">post</router-link>
            </template>
          </template>
          <template v-if="n.type==='App\\Notifications\\ResourcePurchased'">purchased
            <template v-if="n.data.resource_type==='posts'">
              your <router-link :to="{ name: 'posts.show', params: { slug: n.data.resource_slug } }">post</router-link>
            </template>
            <template v-if="n.data.amount"> for {{ n.data.amount | niceCurrency }}</template>
          </template>
          <template v-if="n.type==='App\\Notifications\\CommentReceived'">sent you a 
            <template v-if="n.data.resource_type==='posts'"> <!-- link the post containing the comment -->
              <router-link :to="{ name: 'posts.show', params: { slug: n.data.resource_slug } }">comment</router-link>
            </template>
            <template v-else>comment</template>
          </template>
          <template v-if="n.type==='App\\Notifications\\MessageReceived' && n.data.actor">sent a 
            <template v-if="n.data.resource_type==='chatmessages'">
              <router-link :to="{ name: 'chatthreads.show', params: { id: n.data.resource_slug } }">message</router-link> to {{ n.data.reciever_name }}
            </template>
            <template v-else>message to {{ n.data.reciever_name }}</template>
          </template>
          <template v-if="n.type==='App\\Notifications\\MessageReceived' && n.data.sender">sent you a 
            <template v-if="n.data.resource_type==='chatmessages'">
              <router-link :to="{ name: 'chatthreads.show', params: { id: n.data.resource_slug } }">message</router-link>
            </template>
            <template v-else>message</template>
          </template>
          <template v-if="n.type==='App\\Notifications\\StaffSettingsChanged'">
            <template v-if="n.data.settings.earnings">
              updated your management percentage to {{ n.data.settings.earnings.value }}%
            </template>
            <template v-else>changed your staff settings</template>
          </template>
          <template v-if="n.type==='App\\Notifications\\InviteStaffManager'">
            You've been invited to become a manager of {{ n.data.actor.name }}'s profile.
            Click <a :href="n.data.invite_url">HERE</a> to accept the invitation.
          </template>
          <template v-if="n.type==='App\\Notifications\\UserTagged'">
            tagged you in a
            <template v-if="n.data.resource_slug">
              <router-link :to="{ name: 'posts.show', params: { slug: n.data.resource_slug } }">post</router-link>
            </template>
            <template v-else>post</template>
          </template>
        </p>
        <small>{{ moment(n.created_at).format('MMM DD, YYYY') }}</small>
        <hr class="mt-2 mb-3" />
      </b-media>
    </ul>

    <b-pagination
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      aria-controls="notifications-list"
      v-on:page-click="pageClickHandler"
      class="mt-3"
    ></b-pagination>

  </div>
</template>

<script>
import Vuex from 'vuex'
//import { DateTime } from 'luxon'
import moment from 'moment'

export default {

  props: {
    session_user: null,
    filter: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.notifications || !this.meta || !this.filter
    },

    ...Vuex.mapActions(['getUnreadNotificationsCount']),

    totalRows() {
      return this.meta ? this.meta.total : 1
    },

    filterToType() {
      switch (this.filter) {
        case 'purchases':
          return 'ResourcePurchased'
        case 'liked':
          return 'ResourceLiked'
        case 'followers':
          return 'TimelineFollowed'
        case 'subscribers':
          return 'TimelineSubscribed'
        case 'tips':
          return 'TipReceived'
        case 'comments':
          return 'CommentReceived'
        case 'messages':
          return 'MessageReceived'
        case 'staff':
          //return 'StaffSettingsChanged'
          return 'InviteStaffManager'
        case 'tagged':
          return 'UserTagged'
        case 'none':
        default:
          return null
      }
    },
    title() {
      switch (this.filter) {
        case 'purchases':
          return 'Purchases'
        case 'liked':
          return 'Likes'
        case 'followers':
          return 'Followers'
        case 'subscribers':
          return 'Subscribed'
        case 'tips':
          return 'Tips'
        case 'comments':
          return 'Comments'
        case 'messages':
          return 'Messages'
        case 'staff':
          return 'Staff'
        case 'tagged':
          return 'Tagged'
        case 'none':
        default:
          return 'All'
      }
    },

  },

  data: () => ({
    moment: moment,
    notifications: null,
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
      axios.get( route('notifications.index'), { params } ).then( response => {
        this.notifications = response.data.data
        this.meta = response.data.meta
      })
    },

    pageClickHandler(e, page) {
      this.currentPage = page
      this.getPagedData()
    },

  },

  created() {
    this.getPagedData()
  },

  mounted() { 
    this.$store.dispatch('getUnreadNotificationsCount')
  },

  components: { },
}
</script>

<style lang="scss" scoped>
/*
.card-title .tag-title:first-letter {
    text-transform: capitalize;
}
 */
/*
.notify-message a {
  pointer-events: none;
  color: #212529;

  &:hover {
    color: #212529;
  }
}
*/
</style>
