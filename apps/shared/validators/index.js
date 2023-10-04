// Core vuelidate vaidators
import {
  alpha,
  alphaNum,
  numeric,
  between,
  email,
  ipAddress,
  macAddress,
  maxLength,
  minLength,
  required,
  requiredIf,
  requiredUnless,
  sameAs,
  url,
  or,
  and,
  minValue,
  maxValue
} from 'vuelidate/lib/validators'
// Custom validators
import fileType from './FileType'
import fileSize from './FileSize'
import fileExtension from './FileExtension'
import filesNumber from './FilesNumber'
import coordinates from './Coordinates'
import phone from './Phone'

export {
  alpha,
  alphaNum,
  numeric,
  between,
  email,
  ipAddress,
  macAddress,
  maxLength,
  minLength,
  required,
  requiredIf,
  requiredUnless,
  sameAs,
  url,
  or,
  and,
  minValue,
  maxValue,

  fileType,
  fileSize,
  fileExtension,
  filesNumber,
  coordinates,
  phone
}
