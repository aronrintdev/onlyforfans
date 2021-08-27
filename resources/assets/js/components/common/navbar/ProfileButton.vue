<template>
  <div class="button-content d-flex align-items-center">
    <b-skeleton-wrapper :loading="!session_user">
      <template #loading>
        <b-skeleton type="avatar" class="mr-2" size="2rem" />
        <b-skeleton width="30px" class="mr-2" />
        <fa-icon icon="caret-down" />
      </template>
      <b-avatar
        v-if="session_user && session_user.avatar.filepath"
        :src="session_user.avatar.filepath"
        class="mr-2"
        size="2rem"
      />
      <b-avatar v-else class="mr-2" size="2rem" />
      <OnlineStatus :user="session_user" size="md" :textInvisible="false" />
      <span
        v-if="showName && session_user && !mobile"
        v-text="session_user.name || session_user.username"
      />
    </b-skeleton-wrapper>
  </div>
</template>

<script>
/**
 * resources/assets/js/components/common/navbar/ProfileButton.vue
 */
import Vuex from 'vuex'
import OnlineStatus from '@components/common/OnlineStatus'

export default {
  name: 'ProfileButton',
  computed: {
    ...Vuex.mapState(['mobile']),
    ...Vuex.mapGetters(['session_user', 'uiFlags']),
  },
  data: () => ({
    showName: false,
  }),
  components: {
    OnlineStatus,
  },
}
</script>

<style scoped>
.button-content .onlineStatus {
  position: absolute;
  top: 22px;
  right: 7px;
}
</style>
