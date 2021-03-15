import Vue from 'vue'
import VueI18n from 'vue-i18n'
import VueDMedia from 'vue-d-media'
import en from 'vue-d-media/lib/lang/en'
import ru from 'vue-d-media/lib/lang/ru'

Vue.config.productionTip = false

Vue.use(VueDMedia)

Vue.use(VueI18n)

const i18n = new VueI18n({
  locale: 'en',
  fallbackLocale: 'en',
  messages: {
    en,
    ru,
  },
  pluralizationRules: {
    /**
     * @param choice {number} a choice index given by the input to $tc: `$tc('path.to.rule', choiceIndex)`
     * @param choicesLength {number} an overall amount of available choices
     * @returns a final choice index to select plural word by
     */
    ru(choice, choicesLength) {
      // this === VueI18n instance, so the locale property also exists here

      if (choice === 0) {
        return 0
      }

      const teen = choice > 10 && choice < 20
      const endsWithOne = choice % 10 === 1

      if (choicesLength < 4) {
        return (!teen && endsWithOne) ? 1 : 2
      }
      if (!teen && endsWithOne) {
        return 1
      }
      if (!teen && choice % 10 >= 2 && choice % 10 <= 4) {
        return 2
      }

      return (choicesLength < 4) ? 2 : 3
    }
  }
})

new Vue({
  i18n,
  data() {
    return {
      singleMedias: window.__data.singleMedias,
      multiMedias: window.__data.multiMedias,
    }
  },
}).$mount('#app')
