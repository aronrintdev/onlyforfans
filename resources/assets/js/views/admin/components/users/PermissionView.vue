<template>
  <div>
    <ResourceEditor
      ref="editor"
      v-model="permission"
      model-name="Permission"
      :protected-fields="['guard_name']"
      @save="savePermission"
    />
    <!-- TODO: Add more functionally -->
  </div>
</template>

<script>
/**
 * View / Edit a specific permission
 */
import ResourceEditor from '../../common/ResourceEditor'

export default {
  components: {
    ResourceEditor,
  },
  data: () => ({
    permission: {},
  }),
  methods: {
    load() {
      // TODO: Add load
    },
    savePermission() {
      // TODO: Add save permission
    },
    deletePermission() {
      // TODO: Add delete permission
    }
  },
}
</script>
