import { eventBus } from '@/app'

export const mixinModal = {
  methods: {

    renderTip(resource, resourceType) {
      eventBus.$emit('open-modal', {
        key: 'render-tip',
        data: { 
          resource: resource, // selectedTimeline
          resource_type: resourceType, // 'timelines',
        },
      })
    },

  }
}

export default mixinModal
