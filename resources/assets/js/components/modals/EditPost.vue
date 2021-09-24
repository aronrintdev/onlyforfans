<template>
  <div class="edit-post container p-0">

    <b-card header-tag="header" footer-tag="footer" class="position-relative">

      <template #header>
        <section class="d-flex edit-post-header">
          <div class="my-auto mr-3">
            <div class="h5 mb-0" v-text="$t('title')" />
          </div>
          <div class="post_create-ctrl d-flex align-items-center flex-grow-1">
            <!-- <b-form-select id="post-type" class="w-auto ml-auto" v-model="type" :options="ptypes" required /> -->
            <button type="button" @click="discard" aria-label="Close" class="close ml-auto">×</button>
          </div>
        </section>
      </template>

      <LoadingOverlay :loading="loading" />

      <div>
        <div class="alert alert-secondary py-1 px-2" role="alert" v-if="schedule_datetime" @click="showSchedulePicker()">
          <fa-icon size="lg" :icon="['far', 'calendar-check']" class="text-primary mr-1" />
          <span>Scheduled for</span>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="closeSchedulePicker">
            <span aria-hidden="true">&times;</span>
          </button>
          <strong class="float-right mr-3">{{ moment.utc(schedule_datetime).local().format('MMM DD, h:mm a') }}</strong>
        </div>

        <div v-if="type === 'price'" class="d-flex w-100">
          <PriceSelector
            class="mb-3 mr-5"
            :label="$t('priceForFollowers')"
            v-model="price"
          />
          <PriceSelector
            class="mb-3"
            :label="$t('priceForSubscribers')"
            v-model="priceForPaidSubscribers"
          />
          <hr />
        </div>
        <div
          class="text-left text-editor"
          contenteditable
          v-html="descriptionForEditor"
          @input="onInput"
          @click="editorClicked"
        ></div>

      </div>

      <template #footer>
        <b-row v-if="suggestions.length" class="mb-1">
          <b-col cols="12" class="d-flex flex-wrap">
            <div class="bg-secondary mr-2 mb-1 p-1 suggestion" @click="selectSuggestion(suggestion)" v-for="suggestion in suggestions" :key="suggestion.id">
              {{ suggestion.label }}
            </div>
          </b-col>
        </b-row>
        <b-row v-if="isTagFormVisible" class="mb-1">
          <b-col cols="12" class="d-flex align-items-center">
            <b-form-tags v-model="hashtags" no-outer-focus class="">
              <template v-slot="{ tags, inputAttrs, inputHandlers, tagVariant, addTag, removeTag }">
                <div class="d-inline-block">
                  <b-form-tag v-for="tag in tags" 
                    @remove="removeTag(tag)" 
                    :key="tag" 
                    :title="tag" 
                    :variant="isHashtagPrivate(tag) ? 'danger' : 'secondary'" 
                    size="sm" class="mr-1" 
                  > 
                    {{ tag.endsWith('!') ? tag.slice(0, -1) : tag }}
                  </b-form-tag>
                </div>
              </template>
            </b-form-tags>
            <div class="ml-2" v-b-tooltip.hover.html="{title: 'Enter tags in post body, use hash at start for <em>#publictag</em> or hash and exclamation at end for <em>#privatetag!</em>' }">
              <fa-icon :icon="['far', 'info-circle']" class="text-secondary" />
            </div>
          </b-col>
        </b-row>

        <b-row>
          <b-col cols="4" md="4" class="d-flex">
            <ul class="list-inline d-flex mb-0">
              <li class="selectable select-calendar" v-if="post && post.schedule_datetime" @click="showSchedulePicker()">
                <fa-icon size="lg" :icon="['far', 'calendar-check']" class="text-secondary" />
              </li>
              <li v-custom-click-outside="closeEmojiBox" class="selectable select-emoji" v-b-tooltip.hover="'Add Emoji Icon'">
                <div @click="isEmojiBoxVisible=!isEmojiBoxVisible" >
                  <fa-icon :icon="isEmojiBoxVisible ? ['fas', 'smile'] : ['far', 'smile']" :class="isEmojiBoxVisible ? 'text-primary' : 'text-secondary'" size="lg" />
                </div>
                <VEmojiPicker v-if="isEmojiBoxVisible" @select="selectEmoji" />
              </li>
            </ul>
          </b-col>
          <b-col cols="8" md="8" class="d-flex justify-content-end">
            <div class="d-flex">
              <b-btn class="px-3" variant="primary" :disabled="!changed" @click="save">
                {{ $t('save.button') }}
              </b-btn>
            </div>
          </b-col>
        </b-row>

      </template>

    </b-card>

  </div>
</template>

<script>
/**
 * js/views/posts/Edit
 *
 * Edit Post View
 */
import moment from 'moment'
import { eventBus } from '@/eventBus'
import PriceSelector from '@components/common/PriceSelector'
import LoadingOverlay from '@components/common/LoadingOverlay'
import CalendarIcon from '@components/common/icons/CalendarIcon.vue'
import { VEmojiPicker } from 'v-emoji-picker'

export default {

  name: "EditPost",

  components: {
    LoadingOverlay,
    PriceSelector,
    CalendarIcon,
    VEmojiPicker,
  },

  props: {
    post: { type: Object, default: () => ({}) },
  },

  computed: {

    hashtags: {
      // tag representation in the create post footer (can be deleted here but not added)
      get: function () {
        return this.parseHashtags(this.description) || []
      },
      set: function (newValue) {
        const oldValue = this.parseHashtags(this.description) || []
        const diffs = oldValue.filter( s => !newValue.includes(s) )
        diffs.forEach( s => {
          console.log(`replacing ${s}`)
          this.description = this.description.replace('#'+s, '')
        })
      }
    },

    changed() {
      if (!this.loading) {
        return this.post.description !== this.description
          || this.post.type !== this.type
          || this.post.price !== this.price
          || this.post.price_for_subscribers !== this.priceForPaidSubscribers
          || this.post.schedule_datetime !== this.schedule_datetime
      }
      return false
    }
  },

  data: () => ({
    loading: false,
    description: '',
    type: 'free',
    price: 0,
    priceForPaidSubscribers: 0,
    currency: 'USD',
    // ptypes: [
    //   { text: 'Free', value: 'free' },
    //   { text: 'By Purchase', value: 'price' },
    //   { text: 'Subscriber-Only', value: 'paid' },
    // ],
    postSchedule: {},
    moment,
    schedule_datetime: null,
    isTagFormVisible: true,
    descriptionForEditor: '',
    isEmojiBoxVisible: false,
    lastRange: null,
    suggestions: [],
    lastMatches: [],
    newMatch: null,
  }),

  methods: {

    discard(e) {
      this.$bvModal.hide('edit-post');
    },

    save(e) {
      const payload = {
        description: this.description,
        type: this.type,
        price: this.price,
        price_for_subscribers: this.priceForPaidSubscribers,
        currency: this.currency,
        schedule_datetime: this.schedule_datetime,
      }
      console.log('save', { 
        payload,
        desc: this.description,
      })
      this.loading = true
      this.axios.patch(this.$apiRoute('posts.update', { post: this.post.slug }), payload ).then(response => {
        this.loading = false
        eventBus.$emit('update-posts', this.post.id)
        this.$bvModal.hide('edit-post');
      }).catch(error => {
        eventBus.$emit('error', { error, message: this.$t('save.error') })
        this.loading = false
      })
    },

    showSchedulePicker() {
      eventBus.$emit('open-modal', {
        key: 'show-schedule-datetime',
        data: {
          scheduled_at: this.schedule_datetime,
          is_for_edit: true,
        }
      })
    },

    closeSchedulePicker(e) {
      this.schedule_datetime = null;
      e.stopPropagation();
    },

    parseHashtags(searchText) {
      const regexp = /\B#[@\w][\w-.]+(!)?/g
      const htList = searchText.match(regexp) || [];
      return htList.map(s => s.slice(1))
    },

    isHashtagPrivate(s) {
      return s.endsWith('!')
    },

    async getMatches(text) {
      const params = {
        term: text,
        field: 'slug',
      }
      const response = await axios.get( this.$apiRoute('users.match'), { params } )
      this.suggestions = response.data;
    },

    compareMatches(a, b) {
      if (a.length >= b.length) {
        let i = 0;
        while(a[i] == b[i] && i < a.length) {
          i++;
        }
        if (i < a.length) {
          return a[i];
        }
      } else {
        let i = 0;
        while(a[i] == b[i] && i < b.length) {
          i++;
        }
        if (i < b.length) {
          return b[i];
        }
      }
    },

    onInput(e) {
      this.lastRange = this.saveSelection()
      const cursorPos = this.lastRange.startOffset
      const fontEle = e.target.querySelector('font')
      if (fontEle) {
        fontEle.outerHTML = `<span>${fontEle.innerText}</span>`;
        this.restoreSelection(this.lastRange)
      }
      const anchors = e.target.querySelectorAll('a');
      if (anchors.length > 0) {
        anchors.forEach(anchor => {
          if (anchor.innerText[0] != '@') {
            anchor.outerHTML = `<span>${anchor.innerText}</span>`;
            this.restoreSelection(this.lastRange)
          }
        })
      }
      let html = e.target.innerHTML
      const matches = html.match(/\B(@[\w\-.]+)/g) || []
      if (matches.length > 0) {
        this.newMatch = this.compareMatches(matches, this.lastMatches);
        if (html.search(`<a>${this.newMatch}</a>`) > -1) {
          if (anchors.length > 0) {
            anchors.forEach(anchor => {
              if (anchor.innerText == this.newMatch) {
                const randClass = `s${new Date().getTime()}`;
                anchor.outerHTML = `<span class='${randClass}'>${this.newMatch.substring(0, cursorPos)}</span><span>${this.newMatch.substring(cursorPos)}</span>`;
                this.setCursorPosition('.' + randClass)
              }
            })
          }
          this.description = e.target.innerHTML
        }
        if (this.newMatch) {
          this.lastMatches = matches;
          this.getMatches(this.newMatch.slice(1));
        } else {
          this.suggestions = [];
        }
      } else {
        this.newMatch = null;
        this.suggestions = [];
      }
    },

    selectSuggestion(suggestion) {
      this.restoreSelection(this.lastRange)
      this.pasteHtmlAtCaret(`<a>@${suggestion.label}</a>&nbsp;`)
      const ele = document.querySelector('.edit-post .text-editor')
      this.description = ele.innerHTML
      this.suggestions = []
      this.lastMatches = this.description.match(/\B(@[\w\-.]+)/g) || []
    },

    editorClicked(e) {
      this.lastRange = this.saveSelection()
      if (e.target.tagName == 'A') {
        const url = e.target.textContent.slice(1)
        window.location.href = url;
      }
    },

    saveSelection() {
      if (window.getSelection) {
        let sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
          return sel.getRangeAt(0);
        }
      } else if (document.selection && document.selection.createRange) {
        return document.selection.createRange();
      }
      return null;
    },

    restoreSelection(range) {
      if (range) {
        if (window.getSelection) {
          let sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
        } else if (document.selection && range.select) {
          range.select();
        }
      }
    },

    setCursorPosition(ele) {
      const p = document.querySelector(ele),
          s = window.getSelection(),
          r = document.createRange();
      r.setStart(p, 1);
      r.setEnd(p, 1);
      s.removeAllRanges();
      s.addRange(r);
    },

    pasteHtmlAtCaret(html) {
      let sel, range;
      if (window.getSelection) {
        // IE9 and non-IE
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
          range = sel.getRangeAt(0);
          range.deleteContents();

          // Range.createContextualFragment() would be useful here but is
          // non-standard and not supported in all browsers (IE9, for one)
          const el = document.createElement("span");
          el.innerHTML = html;
          let frag = document.createDocumentFragment(), node, lastNode;
          while ( (node = el.firstChild) ) {
            lastNode = frag.appendChild(node);
          }
          range.insertNode(frag);

          const text = range.startContainer.textContent
          if (text && this.newMatch) {
            range.startContainer.textContent = text.substring(0, text.length - this.newMatch.length)
          }
          
          // Preserve the selection
          if (lastNode) {
            range = range.cloneRange();
            range.setStartAfter(lastNode);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
          }
        }
      } else if (document.selection && document.selection.type != "Control") {
        // IE < 9
        document.selection.createRange().pasteHTML(html);
      }
      this.lastRange = this.saveSelection()
    },


    selectEmoji(emoji) {
      const ele = document.querySelector('.edit-post .text-editor')
      ele.focus()
      this.restoreSelection(this.lastRange)
      this.pasteHtmlAtCaret(emoji.data)
      this.description = ele.innerHTML
    },

    closeEmojiBox() {
      this.isEmojiBoxVisible = false;
    }
  },

  created() {
    this.type = this.post.type
    this.price = this.post.price
    this.priceForPaidSubscribers = this.post.price_for_subscribers
    // this.currency = this.post.currency

    if ( this.post.contenttags_mgmt.length > 0 ) {
      const str = this.post.contenttags_mgmt.map( ct => `#${ct}!`).join(' ')
      // embed private tags at end of post for editing (public tags are already in post body as saved in DB)
      this.description = this.post.description + ' ' + this.post.contenttags_mgmt.map( ct => `#${ct}!`).join(' ')
    } else {
      this.description = this.post.description
    }
    this.descriptionForEditor = this.description;
    this.lastMatches = this.description.match(/\B(@[\w\-.]+)/g) || []
  },

  mounted() {
    if (this.post.schedule_datetime) {
      this.schedule_datetime = moment.utc(this.post.schedule_datetime)
    }

    const self = this
    eventBus.$on('edit-apply-schedule', function(data) {
      self.schedule_datetime = data
    })
  },

}
</script>

<style lang="scss" scoped>
textarea,
.dropzone,
.vue-dropzone {
  border: none;
}
.select-calendar {
  cursor: pointer;
  align-self: center;
}
.edit-post-header button.close {
  padding: 1rem;
  margin: -1rem -1rem -1rem auto;
  line-height: 1em;
}

.edit-post .text-editor {
  padding: 1em; 
  background: #fff;
  color: #383838;
  min-height: 70px;
  margin: -1em;
  max-height: calc(100vh - 250px);
  overflow: auto;

  a {
    cursor: pointer;
  }
}
.list-inline {
  li {
    display: flex;
    align-items: center;
  }
}
</style>

<style lang="scss">
@media (max-width: 576px) {
  .edit-post {
    #EmojiPicker {
      left: 10%;
      right: auto;
      top: 90%;
    }
  }
}
</style>

<i18n lang="json5" scoped>
{
  "en": {
    "title": "Edit Post",
    "loading": {
      "error": "An error has occurred while attempting to load this post. Please return to the previous page and try again later."
    },
    "save": {
      "button": "Save",
      "error": "An error has occurred while attempting to save this post. Please try again later."
    },
    "priceForFollowers": "Price for free followers",
    "priceForSubscribers": "Price for paid subscribers",
  }
}
</i18n>
