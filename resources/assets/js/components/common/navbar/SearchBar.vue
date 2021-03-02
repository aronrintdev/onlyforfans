<template>
  <b-nav-form form-class="w-100 position-relative" v-click-outside="close">
    <b-input-group class="w-100">
      <template #prepend>
        <b-input-group-text>
          <fa-icon icon="search" />
        </b-input-group-text>
      </template>
      <b-form-input
        ref="input"
        :value="query"
        @input="updateQuery"
        placeholder="Search for creators"
        @focus="focus = true"
        @blur="focus = false"
        @keydown="onKeydown"
      />
      <div v-if="loading" class="loading">
        <fa-icon icon="spinner" spin />
      </div>
      <div v-if="!loading && query" class="clear" @click="reset">
        <fa-icon icon="times" />
      </div>
      <div class="results" :class="{ 'open': open }">
        <ul class="nav">
          <li
            class="group-header"
            v-for="(group, index) in groups"
            :key="index"
            :class="{'selected': selectedGroup === index}"
            @click="selectGroup(index)"
          >
            {{ group.label }}
          </li>
        </ul>
        <ul v-if="selectedGroupName !== ''" class="result-list">
          <component
            :is="groups[selectedGroup].component"
            v-for="(item, index) in results[selectedGroupName]"
            :key="item.id"
            :value="item"
            :highlighted="highlighted === index"
            :index="index"
            @click="reset"
          />
        </ul>
      </div>
    </b-input-group>
  </b-nav-form>
</template>

<script>
/**
 * Search Bar for navbar
 */
import _ from 'lodash'
import ClickOutside from 'vue-click-outside'
import Vuex from 'vuex'

import PostDisplay from './search/PostDisplay'
import StoryDisplay from './search/StoryDisplay'
import TimelineDisplay from './search/TimelineDisplay'

export default {
  components: {
    PostDisplay,
    StoryDisplay,
    TimelineDisplay,
  },

  directives: {
    ClickOutside,
  },

  props: {
    /** Number of results to request */
    takeAmount: { type: Number, default: 10 },
    /** Search debounce wait time in ms */
    debounceWait: { type: Number, default: 500 },
  },

  computed: {
    ...Vuex.mapState('search', {
      results: 'results',
      query: 'query',
      autoComplete: 'autoCompleteSuggestions'
    }),
    ...Vuex.mapGetters('search', {
      groups: 'availableGroups',
      timelines: 'timelines',
      stories: 'stories',
      posts: 'posts',
    }),

    selectedGroupName() {
      if (this.groups.length === 0) {
        return ''
      }
      return this.groups[this.selectedGroup].name
    },
  },

  data: () => ({
    selectedGroup: 0,
    highlighted: -1,
    open: false,
    focus: false,
    loading: false,
    search: null,
  }),

  methods: {
    ...Vuex.mapActions('search', { doSearch: 'search' }),
    ...Vuex.mapMutations('search', { updateQuery: 'UPDATE_QUERY' }),

    close() {
      this.open = false
    },

    reset() {
      this.open = false
      this.updateQuery('')
      this.selectedGroup = 0
      this.highlighted = -1
      this.$refs['input'].blur()
    },

    onKeydown(e) {
      if (e.key === 'ArrowDown') {
        e.preventDefault()
        this.highlighted++
      }
      if (e.key === 'ArrowUp') {
        e.preventDefault()
        this.highlighted--
      }
      if (e.key === 'Tab') {
        if (this.query) {
          e.preventDefault()
          e.shiftKey ? this.selectedGroup-- : this.selectedGroup++
          this.highlighted = -1
        }
      }
      if (e.key === 'Enter') {
        e.preventDefault()
        if ( this.highlighted > -1 ) {
          if (this.groups[this.selectedGroup].name === 'timelines') {
            this.$router.push({
              name: 'timeline.show',
              params: { slug: this.results.timelines[this.highlighted].slug }
            })
          } else if (this.groups[this.selectedGroup].name === 'stories') {
            this.$router.push({
              name: 'stories.player',
              params: { slug: this.results.stories[this.highlighted].slug }
            })
          } else if (this.groups[this.selectedGroup].name === 'posts') {
            this.$router.push({
              name: 'post.show',
              params: { slug: this.results.posts[this.highlighted].slug }
            })
          }
        } else {
          this.$router.push({ name: 'search.home', params: { query: this.query } })
        }
        this.reset()
      }
      if (e.key === 'Escape') {
        console.log('Escape')
        e.preventDefault()
        this.reset()
      }
    },

    selectGroup(index) {
      this.selectedGroup = index
      this.highlighted = -1
      this.$refs['input'].focus()
    },

    _search() {
      this.loading = true
      this.doSearch()
        .then(() => {
          this.loading = false
          this.$forceUpdate()
        })
        .catch(error => {
          this.$log.error(error)
          this.loading = false
        })
    },

    parseData(data) {
      this.results.autoComplete = data.hasOwnProperty('autoComplete')
        ? data.autoComplete.hasOwnProperty('data')
          ? data.autoComplete.data : data.autoComplete : []
      this.groups.forEach(group => {
        this.results[group.name] = !data.hasOwnProperty(group.name)
          ? []
          : data[group.name].hasOwnProperty('data')
            ? data[group.name].data
              : data[group.name]
      })
    }
  },

  watch: {
    open(value) {
      if (value) {
        this.$emit('opening')
      } else {
        this.$emit('closing')
      }
    },
    query(value) {
      this.search()
    },
    debounceWait(value) {
      this.search = _.debounce(this._search, value)
    },
    highlighted(value) {
      if (this.selectedGroupName === '') {
        this.highlighted = -1
        return
      }
      const groupLength = this.results[this.selectedGroupName].length + 1
      value = value + 1 < 0 ? value + 1 + groupLength : value + 1
      this.highlighted = ( value % groupLength ) - 1
    },
    focus(value) {
      if (value) {
        this.open = true
      } else if (this.query === '') {
        this.open = false
      }
    },
    selectedGroup(value) {
      this.selectedGroup = (value < 0) ? value + this.groups.length : value % this.groups.length
    }
  },

  created() {
    this.search = _.debounce(this._search, this.debounceWait)
  }
}
</script>

<style lang="scss" scoped>
$spacer: 1rem;
$border-radius: 0.25 * $spacer;
$border: 1px var(--gray) solid;

.loading, .clear {
  position: absolute;
  top: 0.5rem;
  right: 1rem;
  z-index: 9999;
}
.clear {
  cursor: pointer;
}

.results {
  display: none;
  position: absolute;
  top: 100%;
  right: 0;
  left: 0;
  z-index: 9999;
  background-color: var(--white, #ffffff);
  padding-top: 0;
  border-radius: 0 0 $border-radius $border-radius;
  border: $border;
  border-top: 0;

  user-select: none;
  -moz-user-select: none;
  -webkit-user-select: none;

  &.open {
    display: block;
  }

  .nav {
    display: flex;
    justify-content: space-around;
    padding: 0.25 * $spacer 0 0.25 * $spacer 0;
    border-bottom: $border;
    .group-header {
      cursor: pointer;
      margin-left: $spacer;
      margin-right: $spacer;
      &.selected {
        font-weight: bold;
      }
    }
  }

  .result-list {
    overflow-y: auto;
    max-height: 80vh;
    padding: 0;
    .result-list-item {
      list-style: none;
      padding-top: 0.5 * $spacer;
      padding-bottom: 0.5 * $spacer;
      padding-left: $spacer;
      padding-right: $spacer;

      cursor: pointer;

      border-bottom: $border;
      &:last-child {
        border-bottom: 0;
        border-radius: 0 0 $border-radius $border-radius;
      }

      &:hover {
        background-color: var(--light)
      }
      &.highlighted {
        color: var(--white);
        background-color: var(--primary)
      }
    }
  }


}
</style>
