import { req, withParams } from 'vuelidate/lib/validators/common'

export default (allowedTypes) =>
  withParams({ type: 'fileType', allowedTypes }, value => {
    if (typeof allowedTypes === 'string') {
      allowedTypes = allowedTypes.split(/[ ,]+/)
    }
    if (!Array.isArray(allowedTypes)) {
      throw new Error('Parameter allowTypes should be a string or array of strings.')
    }

    if (!req(value)) {
      return true
    }
    if (value instanceof File) {
      return allowedTypes.indexOf(value.type.toLowerCase()) > -1
    }

    if (value instanceof FileList || Array.isArray(value)) {
      for (let file of value) {
        if (allowedTypes.indexOf(file.type.toLowerCase()) === -1) {
          return false
        }
      }
      return true
    }

    throw new Error('File type validated value should be a File instance or FileList/array of File instances.')
  })
