// Methods to prevent double clicking on asynchronous actions
// E.g.
// ... methods: {
//   async asyncMethod () {
//     if (this.isLoadingRequest('asyncMethodID')) {
//       return
//     }
//     try {
//       this.startLoadingRequest('asyncMethodID')
//       await this.asyncAction()
//       this.stopLoadingRequest(requestName)
//     } catch (e) {
//       this.stopLoadingRequest(requestName)
//     }
//   },
// }

import { mapMutations } from 'vuex'

const methods = {
  ...mapMutations('app', [
    'startLoadingRequest',
    'stopLoadingRequest'
  ]),
  isLoadingRequest (name) {
    const check = this.$store.getters['app/isLoadingRequest']
    return check(name)
  }
}

export default {
  methods
}
