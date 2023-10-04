<template>

  <b-form-group
        :label="inputLabel"
        :state="state"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage"
        :description="hint">

    <b-form-select
          :name="name"
          v-model="model"
          :options="options"
          :state="state"
          @change="$emit('change', $event)"
          @input="$emit('input', $event)">

      <template v-if="inputPlaceholder || allowEmpty" slot="first">
        <option :value="null" :disabled="!allowEmpty">{{ inputPlaceholder }}</option>
      </template>

    </b-form-select>
  </b-form-group>

</template>

<script>
import { FormFieldMixin } from '../../mixins'

export default {
  name: 'form-input',
  mixins: [FormFieldMixin],
  props: {
    value: [String, Number],
    options: [Array, Object],
    allowEmpty: {
      type: Boolean,
      default: false
    }
  },
  watch: {
    default (newValue) {
      this.model = newValue
    }
  }
}
</script>
