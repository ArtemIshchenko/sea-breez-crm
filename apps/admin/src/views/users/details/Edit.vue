<template>
  <div class="animated fadeIn">
    <div class="row position-relative">
      <template>
        <div class="col-xl-9">
          <div class="d-flex align-items-end" >
            <div class="w-100" >
              <form-input
                class="w-100"
                name="guid"
                :label="$t('project', 'guid Пользователя')"
                :error-message="form.errors.guid || form.errors.contact_guid"
                :isValid="form.saved.guid"
                v-model="form.oneC.guid"></form-input>
            </div>

            <div class="form-group">
              <button v-if="form.oneC.valid" class="btn btn-primary ml-2" type="button" @click="submit1CInfo">{{ $t('project', 'Сохранить')}}</button>
              <button v-else class="btn btn-success ml-2" type="button" @click="sendGuid">{{ $t('project', 'Связать') }}</button>
              <div style="width: 100%;height: 20px" v-if="[false,true].includes(form.saved.guid) || form.errors.guid"></div>
            </div>

          </div>
        </div>
        <div class="col-xl-3 desk_1c" v-if="form.oneC.partner || form.oneC.name">
          <div class="pt-xl-4 py-2">
            <div  >
              <div class="header_1c">
                <h4 class="m-0">{{ $t('project', 'Информация из 1с') }}</h4>
              </div>
              <div class="info_1c">
                <dl class="row mb-0">
                  <template v-if="form.oneC.partner">
                    <dt class="col-md-6">{{ $t('project', 'Партнер') }}:</dt>
                    <dd class="col-md-6">{{form.oneC.partner}}</dd>
                  </template>
                  <template v-if="form.oneC.name">
                    <dt class="col-md-6">{{ $t('project', 'Имя') }}:</dt>
                    <dd class="col-md-6">{{form.oneC.name}}</dd>
                  </template>
                </dl>
              </div>

            </div>
          </div>
        </div>
      </template>

      <div class="col-xl-9">
        <form @submit="onSubmit($event)">
          <form-input
            name="first_name"
            :label="$t('user', 'Имя')"
            :value="form.model.first_name"
            :required="!!$v.form.model.first_name && !!$v.form.model.first_name.$params.required"
            :error-message="form.errors.first_name"
            @change="onChange('first_name', $event)"
            @input="onInput('first_name', $event)"></form-input>

          <form-input
            name="middle_name"
            :label="$t('user', 'Отчество')"
            :value="form.model.middle_name"
            :required="!!$v.form.model.middle_name && !!$v.form.model.middle_name.$params.required"
            :error-message="form.errors.middle_name"
            @change="onChange('middle_name', $event)"
            @input="onInput('middle_name', $event)"></form-input>

          <form-input
            name="last_name"
            :label="$t('user', 'Фамилия')"
            :required="!!$v.form.model.last_name && !!$v.form.model.last_name.$params.required"
            :value="form.model.last_name"
            :isValid="form.saved.last_name"
            :error-message="form.errors.last_name"
            @change="onChange('last_name', $event)"
            @input="onInput('last_name', $event)"></form-input>

          <form-input
            name="email"
            :label="$t('user', 'Email адрес')"
            :value="form.model.email"
            :error-message="form.errors.email"
            @change="onChange('email', $event)"
            @input="onInput('email', $event)"></form-input>
          <template>
            <form-radio
              name="provider"
              :label="$t('user', 'Пользователь компании')"
              :value="form.model.provider"
              :required="!!$v.form.model.provider && !!$v.form.model.provider.$params.required"
              :error-message="form.errors.provider"
              :options="form.provider_options"
              @change="onChange('provider', $event)"
              @input="onInput('provider', $event)">
            </form-radio>
          </template>
          <form-input
            name="company"
            :label="$t('user', 'Компания')"
            :required="!!$v.form.model.company && !!$v.form.model.company.$params.required"
            :value="form.model.company"
            :error-message="form.errors.company"
            @change="onChange('company', $event)"
            @input="onInput('company', $event)"></form-input>
          <template v-if="form.model.role === 'customer'">
            <b-form-group v-slot="{ ariaDescribedby }">
              <b-form-radio-group
                v-model="form.model.business_type"
                :options="form.business_options"
                :aria-describedby="ariaDescribedby"
                name="business_type"
              ></b-form-radio-group>
            </b-form-group>

            <form-input
              name="business_id"
              :label="form.model.business_type === 'company' ? $t('user', 'ЕГРПОУ') : $t('user', 'ИНН')"
              :value="form.model.business_id"
              :required="!!$v.form.model.business_id && !!$v.form.model.business_id.$params.required"
              :isValid="form.saved.business_id"
              :error-message="form.errors.business_id"
              @change="onChange('business_id', $event)"
              @input="onInput('business_id', $event)"></form-input>
            <form-input
              name="website"
              :label="$t('user', 'Сайт Компании')"
              :value="form.model.website"
              :required="!!$v.form.model.website && !!$v.form.model.website.$params.required"
              :isValid="form.saved.website"
              :error-message="form.errors.website"
              @change="onChange('website', $event)"
              @input="onInput('website', $event)"></form-input>
            <form-input
              name="address"
              :label="$t('user', 'Адрес')"
              :value="form.model.address"
              :required="!!$v.form.model.address && !!$v.form.model.address.$params.required"
              :isValid="form.saved.address"
              :error-message="form.errors.address"
              @change="onChange('address', $event)"
              @input="onInput('address', $event)"></form-input>
          </template>
          <form-masked
            name="phone"
            :label="$t('user', 'Номер рабочего телефона')"
            :value="form.model.phone"
            :required="!!$v.form.model.phone && !!$v.form.model.phone.$params.required"
            placeholder="+38 (067) 888-88-88"
            placeholder-char="+"
            type="tel"
            :isValid="form.saved.phone"
            :error-message="form.errors.phone"
            @change="onChange('phone', $event)"
            @input="onInput('phone', $event)"
          ></form-masked>

          <form-masked
            name="mobile_phone"
            :label="$t('user', 'Номер мобильного телефона')"
            :value="form.model.mobile_phone"
            :required="!!$v.form.model.mobile_phone && !!$v.form.model.mobile_phone.$params.required"
            :isValid="form.saved.mobile_phone"
            :error-message="form.errors.mobile_phone"
            @change="onChange('mobile_phone', $event)"
            @input="onInput('mobile_phone', $event)"></form-masked>
          <form-simple-select
            name="role"
            :label="$t('user', 'Роль пользователя')"
            :options="roles"
            :value="form.model.role"
            :error-message="form.errors.role"
            @change="onChange('role', $event)"
            @input="onInput('role', $event)"></form-simple-select>

          <button type="submit" class="btn btn-primary">{{ $t('service', 'Сохранить') }}</button>
        </form>
      </div>

    </div>
  </div>
</template>

<script>
import { FormInput, FormRadio, FormMasked, FormSimpleSelect } from '@/components'
import { required, maxLength, email, numeric } from '@/validators'
import { FormMixin } from '@/mixins'

export default {
  name: 'user-edit',
  mixins: [ FormMixin ],
  components: {
    FormRadio,
    FormInput,
    FormSimpleSelect,
    FormMasked
  },
  computed: {
    user () {
      return this.$store.state.user
    },
    roles () {
      const result = {}
      Object.entries(this.$params.user.roles).forEach(([key, value]) => {
        result[key] = this.$t('user', value)
      })
      return result
    }
  },
  watch: {
    'form.oneC.guid' () {
      this.form.oneC.valid = false
      this.form.saved.guid = null
      this.form.errors.guid = ''
    }
  },
  props: {
    model: {
      type: Object,
      required: true
    }
  },
  created () {
    this.form.errors.guid = null
    this.form.saved.guid = null
  },
  data () {
    return {
      form: {
        oneC: {
          valid: false,
          partner: '',
          name: '',
          owner_guid: '',
          contact_guid: '',
          guid: this.model.contact_guid,
          customer_link: this.model.links_1c.customer_link,
          admin_link: this.model.links_1c.admin_link
        },
        model: {
          first_name: this.model.first_name,
          middle_name: this.model.middle_name,
          last_name: this.model.last_name,
          company: this.model.company,
          business_id: this.model.business_id,
          business_type: this.model.business_type || 'company',
          address: this.model.address,
          website: this.model.website,
          email: this.model.email,
          phone: this.model.phone,
          mobile_phone: this.model.mobile_phone,
          role: this.model.role,
          guid: this.model.contact_guid,
          provider: this.model.provider
        },
        business_options: [
          { text: this.$t('project', 'Компания'), value: 'company' },
          { text: this.$t('user', 'Частный предприниматель'), value: 'private' }
        ],
        provider_options: [
        ],
        service: 'admin/users/' + this.model.id,
        serviceMethod: 'patch',
        afterSave: (data) => {
          this.profileUpdated(data)
        }
      }
    }
  },
  validations () {
    let webUrl = /^((https?|ftp|smtp):\/\/)?(www.)?[a-z0-9-_]+(\.[a-z]{2,}){1,3}(#?\/?[a-zA-Z0-9#]+)*\/?(\?[a-zA-Z0-9-_]+=[a-zA-Z0-9-%]+&?)?$/
    let edprouReg = /^[0-9]{8}$/
    let innReg = /^[0-9]{10}$/
    // let mobileRegMin = /^[0-9]{10}$/
    let mobileRegFull = /^\+38 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}$/
    const mustBeReg = (param) => (value) => !value || param.test(value)
    const conditionalRequired = (role) => (value) => { return this.form.model.role !== role || (value !== null && value) }
    const checkGuidRequireness = () => { return !(this.form.model.role === 'manager' && !this.model.contact_guid) }
    return {
      form: {
        model: {
          guid: {
            guid: checkGuidRequireness
          },
          first_name: {
            maxLength: maxLength(255)
          },
          middle_name: {
            maxLength: maxLength(255)
          },
          last_name: {
            required,
            maxLength: maxLength(255)
          },
          email: {
            required,
            maxLength: maxLength(255),
            email
          },
          provider: {
            required: conditionalRequired('customer')
          },
          company: {
            required,
            maxLength: maxLength(255)
          },
          phone: {
            format: mustBeReg(mobileRegFull),
            maxLength: maxLength(255)
          },
          mobile_phone: {
            required,
            format: mustBeReg(mobileRegFull),
            maxLength: maxLength(255)
          },
          role: {
            required
          },
          address: {
            maxLength: maxLength(255)
          },
          business_id: {
            numeric,
            format: mustBeReg(this.form.model.business_type === 'company' ? edprouReg : innReg)
          },
          website: {
            url_be: mustBeReg(webUrl),
            maxLength: maxLength(255)
          }
        }
      }
    }
  },
  methods: {
    checkGuidValidity (data) {
      if (this.form.model.role === 'customer') {
        if (data && data.contacts && data.contacts.length) {
          this.validGuid(data.contacts[0])
        } else {
          this.invalidGuid()
        }
      } else {
        if (data && data.user_name) {
          this.validGuid(data)
        } else {
          this.invalidGuid()
        }
      }
    },
    invalidGuid (error_message = this.$t('project', 'По Данному guid ничего не найдено')) {
      this.form.oneC.partner = ''
      this.form.oneC.name = ''
      this.form.oneC.owner_guid = ''
      this.form.oneC.contact_guid = ''
      this.form.oneC.valid = true
      this.form.oneC.valid = false
      this.form.saved.guid = false
      this.addError('guid', error_message)
    },
    validGuid (data) {
      this.form.oneC.partner = data.owner || ''
      this.form.oneC.name = data.name || data.user_name || ''
      this.form.oneC.owner_guid = data['owner-guid'] || ''
      this.form.oneC.contact_guid = data['contact-guid'] || ''
      this.form.oneC.valid = true
      this.form.saved.guid = null
      this.addError('guid', '')
    },
    saved1C () {
      this.form.saved.guid = true
      this.form.oneC.valid = false
      this.form.model.guid = this.form.oneC.guid
      this.model.contact_guid = this.form.oneC.guid
    },
    async sendGuid () {
      if (!this.form.oneC.guid) {
        this.invalidGuid(this.$t('project', 'guid не может быть пустым'))
      } else {
        try {
          const response = await this.$http.patch('admin/users/check-guid', {
            guid: this.form.oneC.guid,
            link: this.form.model.role === 'customer' ? this.form.oneC.customer_link : this.form.oneC.admin_link
          })
          if (response.data) {
            if (response.data.success === 'success') {
              this.checkGuidValidity(response.data.data)
            } else {
              this.invalidGuid()
            }
          }
        } catch (e) {

        }
      }
    },
    async submit1CInfo () {
      try {
        const response = await this.$http.patch(this.form.service + '/store-1c', {
          contact_guid: this.form.model.role === 'customer' ? this.form.oneC.contact_guid : this.form.oneC.guid,
          owner_guid: this.form.oneC.owner_guid
        })
        if (response.data) {
          this.saved1C()
        }
      } catch (e) {
        if (e.response && e.response.status === 422 && Array.isArray(e.response.data)) {
          e.response.data.forEach(({ field, message }) => {
            if (field && message) {
              this.invalidGuid(message)
            }
          }, this)
        } else {
          this.$notifyError(this.$t('project', 'Данные не были сохранены.') + ' ' + e.message)
        }
      }
    },
    profileUpdated (data) {
      this.$notifySuccess(this.$t('project', 'Данные пользователя успешно изменены.'))
      this.$emit('updated', data)
    }
  }
}
</script>
<style>
.info_1c{
  border: 1px solid #20a8d8;
  padding: 3px 10px;
  border-radius: 0 0 5px 5px;
}
.header_1c{
  background: #20a8d8;
  color: #fff;
  padding: 4px 10px;
}
@media (min-width: 1200px) {
  .desk_1c{
    position: absolute;
    right: 0;
  }
}

</style>
