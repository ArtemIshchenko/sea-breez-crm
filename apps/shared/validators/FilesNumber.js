import { req, withParams } from 'vuelidate/lib/validators/common'

export default (allowedNumber) =>
  withParams({ type: 'filesNumber', allowedNumber }, value => {
    if (!Number.isInteger(allowedNumber)) {
      throw new Error('Parameter allowedSize should be an integer.')
    }

    if (!req(value)) {
      return true
    }
    if (value instanceof File) {
      return true
    }

    if (value instanceof FileList || Array.isArray(value)) {
      return value.length <= allowedNumber
    }

    throw new Error('Files number validated value should be a File instance or FileList/array of File instances.')
  })
