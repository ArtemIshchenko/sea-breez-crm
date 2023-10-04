import { regex } from 'vuelidate/lib/validators/common'
export default regex('coordinates', /^(-?\d{1,2}(\.{1}\d+)?,{1}-?\d{1,3}(\.{1}\d+)?)?$/)
