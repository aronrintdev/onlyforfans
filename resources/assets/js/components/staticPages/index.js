/**
 * components/staticPages
 */
// import Blog from './Blog.vue'
// import DMCA from './DMCA.vue'
// import FAQ from './FAQ.vue'
// import Legal from './Legal.vue'
// import LinkBar from './LinkBar.vue'
// import Privacy from './Privacy.vue'
// import Support from './Support.vue'
// import Terms from './Terms.vue'
// import USC2257 from './USC2257.vue'

export default {
  Blog: () => ({
    component: import(/* webpackChunkName: "group-static-page" */'./Blog.vue'),
  }),
  DMCA: () => ({
    component: import(/* webpackChunkName: "group-static-page" */'./DMCA.vue'),
  }),
  FAQ: () => ({
    component: import(/* webpackChunkName: "group-static-page" */'./FAQ.vue'),
  }),
  Legal: () => ({
    component: import(/* webpackChunkName: "group-static-page" */'./Legal.vue'),
  }),
  LinkBar: () => ({
    component: import('./LinkBar.vue'),
  }),
  Privacy: () => ({
    component: import(/* webpackChunkName: "group-static-page" */'./Privacy.vue'),
  }),
  Support: () => ({
    component: import(/* webpackChunkName: "group-static-page" */'./Support.vue'),
  }),
  Terms: () => ({
    component: import(/* webpackChunkName: "group-static-page" */'./Terms.vue'),
  }),
  USC2257: () => ({
    component: import(/* webpackChunkName: "group-static-page" */'./USC2257.vue'),
  }),
}
