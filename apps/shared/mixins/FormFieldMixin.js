// Form input mixin handles labels/placeholders, error or success messages generating tasks.

import { StringHelper } from '@/helpers'

const props = {
  name: {
    type: [String, Number, Array],
    required: true
  },
  options: Array,
  value: [String, Number, Array],
  // If ommited no label will be generated, if true label will be generated from name prop
  label: [Boolean, String],
  // If ommited no placeholder will be generated, if true placeholder will be generated from name prop
  placeholder: [Boolean, String],
  hint: String,
  isValid: {
    type: Boolean,
    default: false
  },
  successMessage: String,
  errorMessage: String,
  additionalItem: String,
  // Set to true if input is not a part of Standard form (FormMixin) or Agile form (FormAgieMixin)
  // and may cause conflict with another same name input
  notInForm: Boolean
}

const data = function () {
  return {
    model: this.value
  }
}

const computed = {
  // Field name in normal case
  normalizedName () {
    return StringHelper.nameToLabel(this.name)
  },
  inputLabel () {
    return this.label ? (typeof this.label === 'string' ? this.label : this.normalizedName) : null
  },
  inputPlaceholder () {
    return this.placeholder ? (typeof this.placeholder === 'string' ? this.placeholder : this.normalizedName) : null
  },
  // Normal case name used in input messages
  routineName () {
    return this.inputLabel || this.inputPlaceholder || this.normalizedName
  },
  inputSuccessMessage () {
    return this.successMessage ? this.successMessage : this.routineName + ' сохранено.'
  },
  inputErrorMessage () {
    return this.errorMessage ? this.errorMessage.replace('Значение поля', this.routineName) : null
  },
  isInvalid () {
    return !!this.errorMessage
  },
  hasAdditionalItem () {
    return this.additionalItem ? this.additionalItem : null
  },
  state () {
    return this.isInvalid ? false : (this.isValid ? true : null)
  },
  inputOptions () {
    return this.options ? this.options : []
  }
}

const watch = {
  value (newValue) {
    this.model = newValue
  }
}

export default {
  props,
  data,
  computed,
  watch
}
