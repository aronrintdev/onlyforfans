<template>
  <div class="d-flex justify-content-around flex-wrap">
    <router-link v-for="item in items" :key="item.name" :to="{ name: item.linkTo }" v-text="$t(item.name)" class="m-3" />
  </div>
</template>

<script>
/**
 * List of static page links laid out
 */
export default {
  data: () => ({
    items: [
      {
        name: 'faq',
        linkTo: 'static.faq',
      },
      {
        name: 'support',
        linkTo: 'static.support',
      },
      {
        name: 'terms',
        linkTo: 'static.terms',
      },
      {
        name: 'privacy',
        linkTo: 'static.privacy',
      },
      {
        name: 'dmca',
        linkTo: 'static.dmca',
      },
      {
        name: 'usc2257',
        linkTo: 'static.usc2257',
      },
      {
        name: 'legal',
        linkTo: 'static.legal',
      },
      {
        name: 'blog',
        linkTo: 'static.blog',
      },
    ]
  }),
}
</script>

<i18n lang="json5">
{
  "en": {
    "blog": "Blog",
    "dmca": "DMCA",
    "faq": "FAQ",
    "legal": "Legal",
    "privacy": "Privacy",
    "support": "Support",
    "terms": "Terms",
    "usc2257": "USC 2257",
  },
}
</i18n>
