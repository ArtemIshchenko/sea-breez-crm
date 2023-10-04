<template>

  <b-form-group
        :label="inputLabel"
        :state="state"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage"
        :description="hint">

    <div v-if="defaultImage">
      <b-img v-if="!multiple" fluid center :src="defaultImage" class="mb-3" :class="previewClass" />
      <!-- add carousel if multiple = true -->
    </div>

    <b-form-file
          :name="name"
          v-model="model"
          :state="state"
          multiple
          :placeholder="inputPlaceholder"
          @input="$emit('input', model)"></b-form-file>
  </b-form-group>

</template>

<script>
import { FormFieldMixin } from '../../mixins'

export default {
  name: 'form-image-input',
  mixins: [FormFieldMixin],
  props: {
    value: [String, Object],
    multiple: {
      type: Boolean,
      default: false
    },
    previewClass: String
  },
  computed: {
    defaultImage () {
      return (this.value !== null && typeof this.value === 'object') ? this.value.xl : this.value
    }
  }
}
</script>
