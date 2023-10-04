const state = {
  client: null
}

const mutations = {
  setClient (state, client) {
    state.client = client
  },
  setCSRFToken (state, token) {
    state.client.defaults.headers.common['X-CSRF-Token'] = token
    state.client.defaults.headers.common['Access-Control-Allow-Origin'] = '*'
  }
}

export default {
  namespaced: true,
  state,
  mutations
}
