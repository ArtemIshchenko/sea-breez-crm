<template>
  <b-form-group
        :state="state"
        class="date-picker"
        :invalid-feedback="inputErrorMessage"
        :valid-feedback="inputSuccessMessage"
        :description="hint">
    <template v-slot:label>
      {{inputLabel}} <span v-if="required" class="text-danger">*</span>
    </template>
    <b-input-group>
      <flat-pickr
            v-model="model"
            :config="configOptions"
            class="form-control"
            :class="{'is-invalid': isInvalid, 'is-valid': isValid}"
            :placeholder="inputPlaceholder"
            :name="name"
            :events="['onChange', 'onClose']"
            @on-change="onChange"
            @on-close="onClose"></flat-pickr>

      <b-input-group-button slot="right">
        <button class="btn btn-primary" type="button" title="Toggle" data-toggle>
          <i class="fa fa-calendar">
            <span aria-hidden="true" class="sr-only">Toggle</span>
          </i>
        </button>
      </b-input-group-button>

    </b-input-group>
  </b-form-group>
</template>

<script>
import { FormFieldMixin } from '@/mixins'
import flatPickr from 'vue-flatpickr-component'
import { DateHelper } from '@/helpers'
import locale from 'flatpickr/dist/l10n/ru.js'

export default {
  name: 'form-date-picker',
  mixins: [FormFieldMixin],
  components: {
    flatPickr
  },
  props: {
    range: {
      type: Boolean,
      default: false
    },
    timestamp: {
      type: Boolean,
      default: false
    },
    datetime: {
      type: Boolean,
      default: false
    },
    required: Boolean,
    config: Object
  },
  data () {
    const config = this.config || {}
    return {
      configOptions: Object.assign(config, {
        wrap: true,
        mode: this.range ? 'range' : 'single',
        enableTime: this.datetime,
        altInput: true,
        altFormat: this.datetime ? 'd.m.Y H:i' : 'd.m.Y',
        dateFormat: 'U',
        locale: locale.ru,
        defaultHour: this.range ? 0 : 12
      }),
      // due to bug in Flatpickr component inintial value of timestamp must be or milliseconds either seconds containig string
      model: !this.range && this.value ? this.value + '' : this.value,
      // this.actualValue prop is used for date and time picker only
      actualValue: this.value,
      timezoneOffset: new Date().getTimezoneOffset() * 60
    }
  },
  watch: {
    value (value, oldValue) {
      if (value !== oldValue) {
        // Fix https://github.com/flatpickr/flatpickr/issues/864
        // needs more data
        this.model = value ? parseInt(value) + this.timezoneOffset + '' : null
      }
    }
  },
  methods: {
    onChange: function (value) {
      if (this.datetime) {
        if (this.range) {
          if (!value || value.length === 0) {
            this.actualValue = null
          } else if (value.length === 2) {
            this.actualValue = this.normalizeValue(value)
          }
        } else {
          this.actualValue = this.normalizeValue(value)
        }
      } else {
        if (this.range) {
          if (!value || value.length === 0) {
            this.$emit('change', null)
          } else if (value.length === 2) {
            this.$emit('change', this.normalizeValue(value))
          }
        } else {
          this.$emit('change', this.normalizeValue(value))
        }
      }
    },
    normalizeValue (value) {
      if (!this.timestamp) return value
      if (this.range) {
        let dates = []
        dates[0] = this.removeTimezoneOffset(value[0])
        dates[1] = this.removeTimezoneOffset(this.setEndOfDay(value[1]))
        return dates
      } else {
        if (Array.isArray(value)) {
          if (value.length === 0) {
            return null
          }
          value = value[0]
        }
        return this.removeTimezoneOffset(value)
      }
    },
    removeTimezoneOffset (date) {
      if (date instanceof Date) {
        date = DateHelper.toTimestamp(date)
      }
      return date - this.timezoneOffset + ''
    },
    setEndOfDay (date) {
      date.setHours(23)
      date.setMinutes(59)
      date.setSeconds(59)
      return date
    },
    onClose (a, b, c) {
      if (this.datetime) {
        if (this.actualValue !== this.value) {
          this.$emit('change', this.actualValue)
        }
      }
    }
  }
}
</script>

<style lang="stylus">
$selectedDayBackground = #14ad9f

@require "../../../node_modules/flatpickr/src/style/flatpickr"

span.flatpickr-day.selected
  font-weight bold

.flatpickr-current-month
  padding-top 0.25*$monthNavHeight
.flatpickr-prev-month, .flatpickr-next-month
  padding-top 12px

.date-picker input[readonly]
  background-color #fff
</style>
