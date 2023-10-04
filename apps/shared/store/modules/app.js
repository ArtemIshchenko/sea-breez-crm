const state = {
  googleApiKey: null,
  loadingRequests: []

}

const mutations = {
  setGoogleApiKey (state, key) {
    state.googleApiKey = key
  },
  startLoadingRequest (state, name) {
    state.loadingRequests.push(name)
  },
  stopLoadingRequest (state, name) {
    state.loadingRequests = state.loadingRequests.filter(item => item !== name)
  }
}

const getters = {
  isLoadingRequest (state) {
    return name => state.loadingRequests.includes(name)
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  getters
}
