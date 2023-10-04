import { req, withParams } from 'vuelidate/lib/validators/common'

export default (allowedExtensions) =>
  withParams({ type: 'fileExtension', allowedExtensions }, value => {
    if (typeof allowedExtensions === 'string') {
      allowedExtensions = allowedExtensions.split(/[ ,]+/)
    }
    if (!Array.isArray(allowedExtensions)) {
      throw new Error('Parameter allowedExtensions should be a string or array of strings.')
    }

    if (!req(value)) {
      return true
    }

    if (value instanceof File) {
      return allowedExtensions.indexOf(value.name.split('.').pop().toLowerCase()) > -1
    }

    if (value instanceof FileList || Array.isArray(value)) {
      for (let file of value) {
        if (allowedExtensions.indexOf(file.name.split('.').pop().toLowerCase()) === -1) {
          return false
        }
      }
      return true
    }

    throw new Error('File extension validated value should be a File instance or FileList/array of File instances.')
  })
