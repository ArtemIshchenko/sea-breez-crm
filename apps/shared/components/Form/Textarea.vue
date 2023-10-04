<template>

  <b-form-group
        :label="inputLabel"
        :state="state"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage">

        <b-form-textarea
              :name="name"
              v-model="model"
              :placeholder="inputPlaceholder"
              :state="state"
              @change.native="$emit('change', model)"
              @input="$emit('input', model)"
              :rows="rows"
              :max-rows="maxRows"></b-form-textarea>

  </b-form-group>

</template>

<script>
import { FormFieldMixin } from '../../mixins'

export default {
  name: 'form-textarea',
  mixins: [FormFieldMixin],
  props: {
    value: [String],
    rows: {
      type: Number,
      default: 4
    },
    required: Boolean,
    maxRows: {
      type: Number,
      default: 6
    }
  }
}
</script>
