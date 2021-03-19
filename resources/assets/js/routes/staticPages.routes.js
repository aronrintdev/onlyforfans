/**
 * Static pages route defs
 */
import StaticPages from '@components/staticPages'

export const routes = [
  {
    name: 'static.faq',
    path: '/faq',
    component: StaticPages.FAQ
  },
  {
    name: 'static.support',
    path: '/support',
    component: StaticPages.Support
  },
  {
    name: 'static.terms',
    path: '/terms',
    component: StaticPages.Terms
  },
  {
    name: 'static.privacy',
    path: '/privacy',
    component: StaticPages.Privacy
  },
  {
    name: 'static.dmca',
    path: '/dmca',
    component: StaticPages.DMCA
  },
  {
    name: 'static.usc2257',
    path: '/usc2257',
    component: StaticPages.USC2257
  },
  {
    name: 'static.legal',
    path: '/legal',
    component: StaticPages.Legal
  },
  {
    name: 'static.blog',
    path: '/blog',
    component: StaticPages.Blog
  },
]

export default routes