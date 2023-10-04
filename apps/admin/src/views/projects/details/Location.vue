<template>
  <div class="animated fadeIn">
    <div class="row">
      <div class="col-lg-6">
        <form @submit="onSubmit">
          <form-google-autocomplete
                name="address"
                :label="$t('project', 'Адрес')"
                :value="form.model.address"
                :required="!!$v.form.model.address && !!$v.form.model.address.$params.required"
                :error-message="form.errors.address"
                :isValid="form.saved.address"
                @place-changed="onAddressChange($event)"
                @input="onInput('address', $event)"></form-google-autocomplete>

          <form-input
                name="coordinates"
                :label="$t('project', 'Координаты')"
                :value="form.model.coordinates"
                :required="!!$v.form.model.coordinates && !!$v.form.model.coordinates.$params.required"
                :error-message="form.errors.coordinates"
                :isValid="form.saved.coordinates"
                :disabled="!mapIsActive"
                @change="onChange('coordinates', $event)"
                @input="onInput('coordinates', $event)"></form-input>

          <b-button type="submit" variant="primary">{{ $t('project', 'Сохранить местоположение') }}</b-button>
        </form>
      </div>
      <div class="col-lg-6">
        <GmapMap
              :center="mapCenter"
              :options="mapOptions"
              :zoom="10"
              style="width: 100%; height: 400px"
              ref="map"
              @click="mapClicked">
          <GmapMarker
                v-if="form.model.coordinates"
                :position="stringToPosition(form.model.coordinates)"
                :draggable="true"
                @drag="dragged"></GmapMarker>
        </GmapMap>
      </div>
    </div>
  </div>
</template>

<script>
import { FormMixin } from '@/mixins'
import { required, maxLength, coordinates } from '@/validators'
import { FormInput, FormGoogleAutocomplete } from '@/components'
import { MapHelper } from '@/helpers'

export default {
  name: 'project-location',
  mixins: [FormMixin],
  components: {
    FormInput, FormGoogleAutocomplete
  },
  props: ['model'],
  data () {
    return {
      form: {
        model: {
          address: this.model.address,
          coordinates: this.model.coordinates
        },
        serviceMethod: 'patch',
        service: 'admin/projects/' + this.model.id
      },
      mapOptions: {
        streetViewControl: false,
        fullscreenControl: false
      },
      mapCenter: this.model.coordinates ? this.stringToPosition(this.model.coordinates) : { lat: 50.434832, lng: 30.570705 },
      mapIsActive: !!this.model.address
    }
  },
  validations () {
    return {
      form: {
        model: {
          address: {
            maxLength: maxLength(255)
          },
          coordinates: {
            required,
            maxLength: maxLength(255),
            coordinates
          }
        }
      }
    }
  },
  methods: {
    onAddressChange (data) {
      if (typeof data === 'object' && data !== null) {
        this.mapIsActive = true
        if (data.formatted_address) {
          this.form.model.address = data.formatted_address
        }
        if (data.geometry) {
          let coords = data.geometry.location.lat() + ',' + data.geometry.location.lng()
          if (coords) {
            this.onInput('coordinates', coords)
            this.onChange('coordinates', coords)
            this.$nextTick(() => {
              this.setCenter(this.form.model.coordinates)
            })
          }
        }
      }
    },
    mapClicked (e) {
      if (this.mapIsActive === false) {
        return
      }
      this.form.model.coordinates = MapHelper.positionToString(e.latLng)
    },
    dragged (e) {
      if (this.mapIsActive === false) {
        return
      }
      this.form.model.coordinates = MapHelper.positionToString(e.latLng)
    },
    stringToPosition (position) {
      return MapHelper.stringToPosition(position)
    },
    setCenter (position) {
      if (typeof position === 'string') {
        position = this.stringToPosition(position)
      }
      this.mapCenter = position
    }
  }
}
</script>
