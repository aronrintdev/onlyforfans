<template>
  <div>

    <section class="d-flex align-items-center">
      <b-avatar :src="avatarSrc" size="3rem" :alt="contact.contact.name" />
      <b-link :to="link(contact.id)" class="contact-info pl-2">
        <p class="my-0">
          <span class="msg-username">{{ contact.alias || contact.contact.username || contact.contact.name}}</span>
        </p>
      </b-link>
      <div class="pl-2 tag-ctrl ml-auto">
        <b-form-checkbox size="lg" :checked="contact.selected" :value="true" @change="onSelect" />
      </div>
    </section>

  </div>
</template>

<script>
/**
 * resources/assets/js/views/live-chat/components/PreviewContact.vue
 */
import Vuex from 'vuex'
import moment from 'moment'

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
    }

  },

  data: () => ({
    moment: moment,
  }), // data

  created() { },

  mounted() { },

  methods: {
    link(id) {
      return { name: 'mycontacts.show', params: { id: id } }
    },

    onSelect(value) {
      if (value) {
        this.$emit('input', { ...this.contact, selected: true })
      } else {
        this.$emit('input', { ...this.contact, selected: false })
      }
    },
  }, // methods


  watch: { }, // watch

  components: { },

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
</style>
