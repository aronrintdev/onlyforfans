/**
 * Import translations from the `locales` folder for i18n.js
 */
import Vue from 'vue';
import VueI18n from 'vue-i18n';

Vue.use(VueI18n);

function loadLocaleMessages() {
  const locales = require.context('./locales', true, /[A-Za-z0-9_,\s]+\.json$/i);
  const messages = {};
  locales.keys().forEach(key => {
    const matched = key.match(/([A-Za-z0-9-_]+)\./i);
    if (matched && matched.length > 1) {
      const locale = matched[1];
      messages[locale] = locales(key);
    }
  });
  return messages;
}

const numberFormats = {
  'en-US': {
    currency: {
      style: 'currency',
      currency: 'USD',
    },
  },
}

/**
 * Number parser
 */
window.parseNumber = function parseNumber(value, locales = navigator.languages) {
  if (typeof value === 'number') {
    return value
  }
  const example = Intl.NumberFormat(locales).format('1.1');
  const cleanPattern = new RegExp(`[^-+0-9${ example.charAt( 1 ) }]`, 'g');
  const cleaned = value.replace(cleanPattern, '');
  const normalized = cleaned.replace(example.charAt(1), '.');

  return parseFloat(normalized);
}

Vue.mixin({
  methods: {
    $parseNumber: window.parseNumber,
  }
});

export default new VueI18n({
  locale: process.env.VUE_APP_I18N_LOCALE || 'en',
  fallbackLocale: process.env.VUE_APP_I18N_FALLBACK_LOCALE || 'en',
  numberFormats,
  messages: loadLocaleMessages(),
});
