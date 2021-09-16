<template>
  <div v-if="!isLoading" class="follow_ctrl-crate tag-crate my-3">
    <b-card tag="article" class="OFF-mb-2">
      <b-card-text>
        <ul class="list-unstyled">
          <li v-if="timeline.is_owner" class="mb-3">
            <template v-if="activeCampaign">
              <b-button :to="{ name: 'settings.subscription' }" variant="primary" class="w-100">Manage Promotion</b-button>
            </template>
            <template v-else>
              <b-button :to="{ name: 'settings.subscription' }" variant="primary" class="w-100">Add a Promotion</b-button>
            </template>
          </li>
          <li v-if="timeline.userstats.subscriptions && timeline.userstats.subscriptions.price_per_1_months" class="mb-3">
            <b-button v-if="timeline.is_subscribed" :disabled="timeline.is_subscribed" variant="primary" class="w-100" >
              Subscribed
            </b-button>
            <template v-else>
              <b-button @click="renderSubscribe" :disabled="timeline.is_owner" variant="primary" class="w-100" >
                Subscribe - {{ timeline.userstats.subscriptions.price_per_1_months * 100 | niceCurrency }} per month
              </b-button>
              <section v-if="activeCampaign" class="box-campaign-blurb mt-1">
                <h6 v-if="activeCampaign.type==='trial'" class="m-0 text-center">Limited offer - Free trial for {{ activeCampaign.trial_days }} days!</h6>
                <h6 v-if="activeCampaign.type==='discount'" class="m-0 text-center">Limited offer - {{ activeCampaign.discount_percent }}% off for {{ activeCampaign.offer_days }} days!</h6>
                <p class="m-0 text-center"><small class="text-muted">{{ activeCampaign | renderCampaignBlurb }}</small></p>
                <article v-if="activeCampaign.message" class="tag-message d-flex align-items-center">
                  <div class="user-avatar">
                    <b-img rounded="circle" :src="timeline.avatar.filepath" :title="timeline.name" />
                  </div>
                  <div class="text-wrap py-2 w-100">
                    <p class="mb-0">{{ activeCampaign.message }}</p>
                  </div>
                </article>
              </section>
            </template>
          </li>
          <li v-if="!timeline.is_following && timeline.is_follow_for_free" class="mb-3">
            <b-button @click="renderFollow" :disabled="timeline.is_owner" variant="primary" class="w-100">Follow for Free</b-button>
          </li>
          <li v-if="timeline.is_following" class="mb-3">
            <b-button :disabled="timeline.is_owner" @click="redirectToMessages" variant="primary" class="w-100">Message</b-button>
          </li>
          <li class="mb-3">
            <b-button @click="renderTip" :disabled="timeline.is_owner" variant="primary" class="w-100">Send Tip</b-button>
          </li>
        </ul>

        <div class="mt-3" v-if="timeline.about" :class="{ 'normal-view': !isFullVisiable }">
          <VueMarkdown :html="true" :source="timeline.about || ''" />
        </div>
        <div v-if="!isFullVisiable && isOverLength" class="toggle-read-more text-primary text-right mr-3 mt-1" @click="isFullVisiable = !isFullVisiable">Read more</div>
        <ul class="social-links list-unstyled mt-3" v-if="timeline.userstats.website || timeline.userstats.instagram">
          <li v-if="timeline.userstats.website">Website: <a :href="websiteLink" class="tag-website" target="_blank">{{ timeline.userstats.website }}</a></li>
          <li v-if="timeline.userstats.instagram">Instagram: <a :href="socialLink('instagram')" class="tag-website tag-instagram" target="_blank">{{ `@${timeline.userstats.instagram}` }}</a></li>
          <li v-if="timeline.userstats.twitter">Twitter: <a :href="socialLink('twitter')" class="tag-website tag-twitter" target="_blank">{{ `@${timeline.userstats.twitter}` }}</a></li>
          <li v-if="timeline.userstats.amazon">Amazon: <a :href="socialLink('amazon')" class="tag-website tag-amazon" target="_blank">My Wish List</a></li>
        </ul>
        <ul class="list-unstyled list-details mt-3" v-if="timeline.userstats.city || timeline.userstats.country">
          <li v-if="timeline.userstats.city"><span><fa-icon icon="map-pin" class="map-pin-icon" /> {{ timeline.userstats.city }}</span></li>
          <li v-if="timeline.userstats.country"><span><fa-icon icon="globe" /> {{ timeline.userstats.country }}</span></li>
        </ul>
      </b-card-text>
      <router-link v-if="timeline.is_owner" :to="{ name: 'settings.profile', params: { } }" class="float-right mr-3 cursor-pointer">Edit</router-link>
    </b-card>
  </div>
</template>

<script>
import moment from 'moment'
import Vuex from 'vuex';
import { eventBus } from '@/eventBus'

// https://github.com/adapttive/vue-markdown
import VueMarkdown from '@adapttive/vue-markdown'

export default {

  props: {
    session_user: null,
    timeline: null,
  },

  computed: {
    isLoading() {
      return !this.session_user || !this.timeline
    },

    isOverLength() {
      if (this.timeline && this.timeline.about) {
        const ele = $('<div></div>').html(this.timeline.about);
        const text = $(ele)[0].innerText
        if (text.length > 95) {
          return true
        }
      }
      return false
    },
    
    activeCampaign() {
      return this.timeline?.userstats?.campaign || null
    },

    websiteLink() {
      const url = this.timeline.userstats.website
      if (/(http(s?)):\/\//i.test(url)) {
        return url
      }
      return `//${url}`
    },

  },

  data: () => ({
    isFullVisiable: false,
  }),

  created() { },

  methods: { 

    renderFollow() {
      eventBus.$emit('open-modal', {
        key: 'render-follow',
        data: {
          timeline: this.timeline,
        }
      })
    },

    renderSubscribe() {
      eventBus.$emit('open-modal', {
        key: 'render-subscribe',
        data: {
          timeline: this.timeline,
        }
      })
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

    async redirectToMessages() {
      const payload = {
        originator_id: this.session_user.id,
        participant_id: this.timeline.user.id,
      }
      const response = await axios.post( this.$apiRoute('chatthreads.findOrCreateDirect'), payload)
      if (response.data.chatthread) {
        this.$router.push({ name: 'chatthreads.show', params: { id: response.data.chatthread.id }})
      }
    },

    renderSubscribe() {
      eventBus.$emit('open-modal', {
        key: 'render-subscribe',
        data: {
          timeline: this.timeline,
        }
      })
    },

    socialLink(type) {
      let prefix = '';
      switch (type) {
        case 'instagram':
          prefix = 'https://instagram.com/';
          break;
        case 'twitter':
          prefix = 'https://twitter.com/';
          break;
        case 'amazon':
          prefix = 'https://amazon.com/gp/profile/';
          break;
      }
      return prefix + this.timeline.userstats[type];
    }
  },

  components: {
    VueMarkdown,
  },
}
</script>

<style lang="scss" scoped>

body #modal-send_tip .modal-body {
  padding: 0;
}

.box-campaign-blurb {

  .tag-message {
    position: relative;
    margin-top: 0.3rem;
    padding: 0.2rem 0.3rem;

    .text-wrap {
      border-radius: 0.5rem;
      background: #f1f1f1;
      margin-left: 5px;
      p { 
        margin-left: 40px;
      }
    }

    .user-avatar {
      position: absolute;
      top: -5px;
      left: 0;
    }

    .user-avatar img {
      object-fit: cover;
      width: 40px;
      height: 40px;
    }
  }
}

.normal-view {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;

  p:last-child {
    margin-bottom: 0;
  }
}

.social-links li {
  display: flex;
  align-items: center;
}

li a.tag-website {
  margin-left: 20px;
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  flex: 1;
}

li a.tag-instagram {
  margin-left: 7px;
}

li a.tag-twitter {
  margin-left: 30px;
}

.toggle-read-more {
  cursor: pointer;
}

.map-pin-icon {
  margin-left: 3px;
  margin-right: 4px;
}

.list-unstyled {
  margin: 0;

  li:first-child button {
    margin-top: 0 !important;
  }
}
</style>

