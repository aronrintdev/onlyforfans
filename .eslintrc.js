/**
 * EsLint Rules
 */

module.exports = {
  extends: [
    'eslint:recommended',
    'plugin:vue/vue2-recommended',
    'plugin:prettier-vue/recommended',
    'prettier/vue',
  ],
  settings: {
    'prettier-vue': {
      customBlocks: {
        docs: { lang: 'markdown' },
        config: { lang: 'json' },
        module: { lang: 'js' },
        i18n: { lang: 'json' },
      },
    },
  },
  rules: {
    'prettier-vue/prettier': [
      'error',
      {
        // Override all options of `prettier` here
        // @see https://prettier.io/docs/en/options.html
        printWidth: 100,
        singleQuote: true,
        semi: false,
        trailingComma: 'es5',
        arrowParens: 'avoid',
      },
    ],
    /**
     * Additional/Overwrite Rules go here.
     * See:
     * - https://eslint.org/docs/rules/
     * - https://eslint.vuejs.org/rules/
     */
    'quotes': 'single'
  },
}
