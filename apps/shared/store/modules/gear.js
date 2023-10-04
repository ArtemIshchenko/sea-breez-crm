const state = {
  list: []
}

const mutations = {
  setList (state, value) {
    state.list = value
  }
}

export default {
  namespaced: true,
  state,
  mutations
}
