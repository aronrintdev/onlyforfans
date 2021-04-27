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

    renderSubscribe(selectedTimeline) {
      eventBus.$emit('open-modal', {
        key: 'render-subscribe',
        data: {
          timeline: selectedTimeline,
        }
      })
    },

    renderCancel(selectedTimeline, accessLevel) {
      // normally these attributes would be passed from server, but in this context we can determine them on client-side...
      selectedTimeline.is_following = true
      selectedTimeline.is_subscribed = (accessLevel==='premium')
      eventBus.$emit('open-modal', {
        key: 'render-follow',
        data: {
          timeline: selectedTimeline,
        }
      })
    },

  }
}

export default mixinModal
