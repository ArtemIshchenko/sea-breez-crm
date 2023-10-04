<template>

  <b-form-group
        :state="state"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage"
        :description="hint">
    <template v-slot:label>
      {{inputLabel}} <span v-if="required" class="text-danger">*</span>
    </template>
    <b-input-group
          v-if="prepend || append"
          :left="prepend"
          :right="append">
      <b-form-input
            :name="name"
            v-model="model"
            :state="state"
            :placeholder="inputPlaceholder"
            :type="type"
            @change="$emit('change', model)"
            @input="$emit('input', model)"></b-form-input>
    </b-input-group>

    <b-form-input
          v-else
          :name="name"
          v-model="model"
          :state="state"
          :placeholder="inputPlaceholder"
          :type="type"
          @change="$emit('change', model)"
          @input="$emit('input', model)"></b-form-input>
  </b-form-group>

</template>

<script>
import { FormFieldMixin } from '../../mixins'

export default {
  name: 'form-input',
  mixins: [FormFieldMixin],
  props: {
    value: [String, Number],
    type: {
      type: String,
      default: 'text'
    },
    required: Boolean,
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
