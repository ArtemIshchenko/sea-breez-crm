const CONSTANTS = {
  BASE_URL: 'https://' + window.location.hostname,
  LOGIN_URL: '/auth/login',
  CONDITIONS: {
    NOT: 'not',
    LESS_THAN: 'lt',
    GREATER_THAN: 'gt',
    LESS_THAN_EQUAL: 'lte',
    GREATER_THAN_EQUAL: 'gte',
    EQUAL: 'eq',
    NOT_EQUAL: 'neq',
    IN: 'in',
    NOT_IN: 'nin',
    LIKE: 'like'
  },
  FILES_UPLOAD_MAX_SIZE: 100
}

export const CONDITIONS = CONSTANTS.CONDITIONS
export const FILES_UPLOAD_MAX_SIZE = CONSTANTS.FILES_UPLOAD_MAX_SIZE
export const LOGIN_URL = CONSTANTS.LOGIN_URL
export default CONSTANTS
