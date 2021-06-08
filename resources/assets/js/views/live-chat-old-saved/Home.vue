<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 visible-lg" />
      <div class="col-md-12 col-lg-12">
        <div class="messages-page" id="messages-page">
          <div class="card">
            <div class="card-body nopadding">
              <div class="row message-box">
                <chat-sidebar :selectedUser="null" :proplists="[]" />
                <div class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                  <div class="coversation-tree">
                    <div class="conversations-start">
                      <div class="conversations-start__title">Select any Conversation or send a New Message</div>
                      <router-link to="/messages/new"><button>New Message</button></router-link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from './Sidebar';
/**
 * Messages Dashboard View
 */
export default {
  components: {
    'chat-sidebar': Sidebar
  },
}
</script>

<style lang="scss" scoped>
@import "../../../sass/views/live-chat/home.scss";
</style>
