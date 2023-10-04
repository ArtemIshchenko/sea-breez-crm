import Axios from 'axios'
import qs from 'qs'
import constants from '@/config/constants'

// create default http client
const httpClient = Axios.create({
  baseURL: '/api',
  withCredentials: true
})

// set default http errors handler
httpClient.interceptors.response.use(response => {
  return response
}, error => {
  if (error.response) {
    if (error.response.status === 401) {
      window.location.href = constants.LOGIN_URL
    }
    if (error.response.data.message) {
      error.message = error.response.data.message
    }
  }
  return Promise.reject(error)
})

httpClient.interceptors.request.use(config => {
  config.paramsSerializer = params => {
    return qs.stringify(params, {
      arrayFormat: 'brackets',
      encode: false
    })
  }
  return config
})

export default httpClient
