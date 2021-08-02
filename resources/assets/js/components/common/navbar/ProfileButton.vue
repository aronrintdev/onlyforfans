<template>
  <div class="button-content d-flex align-items-center">
    <b-skeleton-wrapper :loading="!session_user">
      <template #loading>
        <b-skeleton type="avatar" class="mr-2" size="2rem" />
        <b-skeleton width="30px" class="mr-2" />
        <fa-icon icon="caret-down" />
      </template>
      <b-avatar v-if="session_user && session_user.avatar.filepath" :src="session_user.avatar.filepath" class="mr-2" size="2rem" />
      <b-avatar v-else class="mr-2" size="2rem" />
      <span v-if="session_user && !mobile" class="mr-2" v-text="session_user.name || session_user.username" />
      <fa-icon icon="caret-down" />
    </b-skeleton-wrapper>
  </div>
</template>

<script>
import Vuex from 'vuex'

export default {
  name: 'ProfileButton',
  computed: {
    ...Vuex.mapState(['mobile']),
    ...Vuex.mapGetters(['session_user', 'uiFlags']),
  }
}
</script>

<style lang="scss" scoped>

</style>
