<template>
  <iframe
        width="100%"
        height="400"
        frameborder="0" style="border:0;"
        :src="mapUrl"
        :allowfullscreen="allowFullscreen"></iframe>
</template>

<script>
import { mapState } from 'vuex'

export default {
  name: 'embed-map',
  props: {
    marker: String,
    allowFullscreen: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    ...mapState('app', {
      googleApiKey: state => state.googleApiKey
    }),
    mapUrl () {
      let url = 'https://www.google.com/maps/embed/v1/place?key=' + this.googleApiKey
      if (this.marker) {
        url += ('&q=' + this.marker)
      }
      return url
    }
  }
}
</script>
