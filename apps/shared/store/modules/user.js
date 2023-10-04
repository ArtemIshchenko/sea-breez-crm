const state = {
  isAuthenticated: false,
  id: null,
  email: '',
  first_name: '',
  middle_name: '',
  business_type: '',
  business_id: '',
  address: '',
  website: '',
  provider: '',
  last_name: '',
  company: '',
  phone: '',
  mobile_phone: '',
  role: '',
  lang: ''
}

const mutations = {
  updateDetails: (state, [name, value]) => {
    if (['isAuthenticated', 'id'].indexOf(name) >= 0) {
      throw new Error('Cannot update value of ' + name)
    }
    if (!(name in state)) {
      throw new Error('No such user detail exists as ' + name)
    }

    state[name] = value
  }
}

const actions = {
  init: async ({ state, commit, rootState }) => {
    try {
      const response = await rootState.http.client.get('auth/init')
      if (!response.data.identity) {
        throw new Error('No data returned by init user request.')
      }
      Object.keys(response.data.identity).filter(key => key in state).forEach(function (key) { state[key] = response.data.identity[key] })
      if (response.data.csrfToken) {
        commit('http/setCSRFToken', response.data.csrfToken, { root: true })
      }
      if (response.data.googleApiKey) {
        commit('app/setGoogleApiKey', response.data.googleApiKey, { root: true })
      }
      state.isAuthenticated = true
    } catch (e) {
      throw e
    }
  },
  logout: async ({ state, rootState }) => {
    try {
      const response = await rootState.http.client.post('auth/logout')
      if (response.status === 200) {
        state.isAuthenticated = false
      } else {
        throw new Error('User was not logout. Response status ' + response.status)
      }
    } catch (e) {
      throw e
    }
  }
}

const getters = {
  name: state => state.first_name + ' ' + state.last_name
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
