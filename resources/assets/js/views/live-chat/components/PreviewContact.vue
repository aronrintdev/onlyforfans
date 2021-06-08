<template>
  <div>

    <section class="d-flex align-items-center">
      <b-avatar :src="avatarSrc" size="3rem" :alt="contact.name" />
      <b-link :to="link(contact.id)" class="contact-info pl-2">
        <p class="my-0">
          <span class="msg-username">{{ contact.username || contact.name}}</span>
        </p>
      </b-link>
      <div class="pl-2 tag-ctrl ml-auto">
        <b-form-checkbox v-model="isSelected" @change="selectContact(contact.id)"></b-form-checkbox>
      </div>
    </section>

  </div>
</template>

<script>
import moment from 'moment'

export default {

  props: {
    session_user: null,
    contact: null,
  },

  computed: {

    isLoading() {
      return !this.session_user || !this.contact
    },

    avatarSrc() {
      return this.contact.avatar?.filepath || ''
    }

  },

  data: () => ({

    moment: moment,

    isSelected: false,

  }), // data

  created() { },

  mounted() { },

  methods: { 
    link(id) {
      return { name: 'mycontacts.show', params: { id: id } }
    },

    selectContact(contact) {
      this.$emit('select-contact', {
        contact: contact, 
        isSelected: this.isSelected,
      })
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

<style lang="scss">
</style>
