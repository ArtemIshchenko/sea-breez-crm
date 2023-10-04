<template>
  <b-form-group
      :state="state"
      :invalid-feedback="inputErrorMessage"
      :valid-feedback="inputSuccessMessage"
      :description="hint">
    <template v-slot:label>
      {{inputLabel}} <span v-if="required" class="text-danger">*</span>
    </template>
    <b-form-radio-group
        :name="name"
        v-model="model"
        :state="state"
        @change="$emit('change', model)"
        @input="$emit('input', model)"
        :options="inputOptions"
    ></b-form-radio-group>
  </b-form-group>

</template>

<script>
import { FormFieldMixin } from '../../mixins'

export default {
  name: 'form-radio',
  mixins: [FormFieldMixin],
  props: {
    value: [String, Number],
    type: {
      type: String,
      default: 'text'
    },
    required: Boolean,
    options: Array,
    prepend: String,
    append: String
  },
  watch: {
    default (newValue) {
      this.model = newValue
    }
  }
}
</script>
