<template>
  <b-list-group-item class="cursor-pointer" @click="onClicked">
    <section class="d-flex align-items-center">
      <b-avatar :src="avatarSrc" size="3rem" :alt="contact.contact.name" />
      <OnlineStatus size="md" :user="user" :textVisible="false" />
      <div class="contact-info pl-2">
        <p class="my-0">
          <span class="msg-username">{{ contact.alias || contact.contact.username || contact.contact.name}}</span>
        </p>
      </div>
      <div class="pl-2 tag-ctrl ml-auto" :style="{ pointerEvents: 'none' }">
        <b-form-checkbox ref="checkbox" size="lg" :checked="contact.selected" :value="true" @change="onSelect" />
      </div>
    </section>
  </b-list-group-item>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/PreviewContact.vue
 */
import Vuex from 'vuex'
import moment from 'moment'
import OnlineStatus from '@components/common/OnlineStatus'

export default {
  name: 'PreviewContract',

  model: {
    prop: 'contact',
  },

  props: {
    contact: { type: Object, default: () => ({ contact: {} })},
    selected: { type: Boolean, default: false },
  },

  computed: {
    ...Vuex.mapState([ 'session_user' ]),

    isLoading() {
      return !this.session_user || !this.contact
    },

    avatarSrc() {
      return this.contact.contact.avatar?.filepath || ''
    },

    user() {
      return this.contact.contact.user || this.contact.contact
    }

  },

  data: () => ({
    moment: moment,
  }), // data

  created() { },

  mounted() { },

  methods: {
    /**
     * TODO: Deprecate, this functionality is better suited for a modal
     */
    link(id) {
      return { name: 'mycontacts.show', params: { id: id } }
    },

    onClicked() {
      this.onSelect(!this.contact.selected)
    },

    onSelect(value) {
      this.$emit('input', { ...this.contact, selected: value })
    },
  }, // methods


  watch: { }, // watch

  components: {
    OnlineStatus,
  },

}
</script>

<style lang="scss" scoped>
body {
  a, .btn-link:hover {
    text-decoration: none;
  }
  .btn:focus, .btn.focus {
    box-shadow: none;
  }
  .msg-username.tag-unread {
    font-weight: bold;
  }
}
.contact-info {
  width: calc(100% - 70px);
  overflow: hidden;

  .msg-snippet {
    display: block;
    font-size: 14px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
    margin-right: 10px;
    word-wrap: break-word;
  }
}
.onlineStatus {
  position: absolute;
  left: 40px;
  bottom: 10px;
}
</style>
