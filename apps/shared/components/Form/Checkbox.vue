<template>

  <b-form-group
        :state="state"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage"
        :description="hint">

    <b-form-checkbox
          :name="name"
          v-model="model"
          :state="state"
          plain
          value="1"
          unchecked-value="0"
          @input="$emit('change', model)">{{ label }}</b-form-checkbox>

  </b-form-group>

</template>

<script>
import { FormFieldMixin } from '../../mixins'

export default {
  name: 'form-input',
  mixins: [FormFieldMixin],
  props: {
    value: [Number]
  },
  watch: {
    default (newValue) {
      this.model = newValue
    }
  }
}
</script>
