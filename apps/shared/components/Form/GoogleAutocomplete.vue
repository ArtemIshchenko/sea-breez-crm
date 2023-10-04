<template>

  <b-form-group
        :label="inputLabel"
        :state="state"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage">
    <gmap-autocomplete
          :name="name"
          :value="value"
          class="form-control"
          :placeholder="inputPlaceholder || ''"
          :state="state"
          @input="$emit('input', model)"
          @place_changed="$emit('place-changed', $event)">
          </gmap-autocomplete>
  </b-form-group>

</template>

<script>
import { FormFieldMixin } from '../../mixins'

export default {
  name: 'form-google-autocomplete',
  mixins: [FormFieldMixin],
  props: {
    value: [String]
  }
}
</script>
