import { req, withParams } from 'vuelidate/lib/validators/common'
import { FILES_UPLOAD_MAX_SIZE } from '@/config/constants'

const validate = (files, allowedSize = FILES_UPLOAD_MAX_SIZE) => {
  allowedSize = allowedSize * 1024 * 1024

  if (files instanceof File) {
    return files.size <= allowedSize
  }

  if (files instanceof FileList || Array.isArray(files)) {
    let fullSize = 0
    for (let file of files) {
      fullSize += file.size
    }
    return fullSize <= allowedSize
  }

  throw new Error('File size validated value should be a File instance or FileList/array of File instances.')
}

// allowedSize is a maximum file(s) size in megabytes.
export default (allowedSize = FILES_UPLOAD_MAX_SIZE) => {
  return withParams({ type: 'fileSize', allowedSize }, value => {
    if (!req(value)) return true
    if (allowedSize !== null && !Number.isInteger(allowedSize)) throw new Error('allowedSize parameter should be an integer.')

    return validate(value, allowedSize)
  })
}
export const validateFilesSize = validate
