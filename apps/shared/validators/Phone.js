import { regex } from 'vuelidate/lib/validators/common'
export default regex('phone', /^[0-9\- ]+$/)
