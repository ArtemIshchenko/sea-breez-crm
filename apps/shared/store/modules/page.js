const state = {
  title: null,
  translations: {}
}

const mutations = {
  setTitle (state, title) {
    state.title = title
  },
  resetTitle (state) {
    state.title = null
  },
  setTranslations (state, translations) {
    state.translations = translations
  }
}

const actions = {
  attachTranslations: async ({ state, commit, rootState }) => {
    try {
      const { data } = await rootState.http.client.get('languages/' + rootState.user.lang + '/translations.json', {
        baseURL: process.env.BASE_URL
      })
      if (data && typeof data === 'object') {
        commit('setTranslations', data)
      }
    } catch (e) {
      throw e
    }
  }
}

const getters = {
  translate: state => (cat, str) => (state.translations && state.translations[cat] && state.translations[cat][str])
    ? state.translations[cat][str]
    : str
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}
