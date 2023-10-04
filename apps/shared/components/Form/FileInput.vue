<template>

  <b-form-group
        :label="inputLabel"
        :state="state"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage"
        :description="hint">

    <div v-if="defaultFile">
      <span v-if="!multiple" class="text-muted mb-2 d-inline-block">{{ defaultFile }}</span>
      <ul v-else class="mb-2">
        <li v-for="(file, key) in defaultFile" :key="key">
          <span class="text-muted">{{ file }}</span>
        </li>
      </ul>
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
  name: 'form-file-input',
  mixins: [FormFieldMixin],
  props: {
    value: String,
    multiple: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    defaultFile () {
      return this.value
    }
  }
}
</script>
