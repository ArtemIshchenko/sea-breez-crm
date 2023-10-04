<template>
  <gmap-map
        :options="mapOptions"
        :center="{ lat: 50.434832, lng: 30.570705 }"
        :zoom="10"
        style="width: 100%; height: 75vh;"
        ref="map">
    <gmap-info-window
          :options="infoWindowOptions"
          :position="infoWindow.position"
          :opened="infoWindow.isOpen"
          @closeclick="infoWindow.isOpen=false">
      <div v-html="infoWindow.content"></div>
    </gmap-info-window>
    <gmap-marker v-for="(marker, index) in markers"
      :position="marker.position"
      clickable
        @click="openInfoWindow(marker, index)"
      :key="index"></gmap-marker>
  </gmap-map>
</template>

<script>
import { MapHelper, StringHelper, DateHelper } from '@/helpers'

export default {
  name: 'projects-map',
  props: ['projects'],
  data () {
    return {
      markers: [],
      infoWindow: {
        content: null,
        position: null,
        isOpen: false
      },
      infoWindowOptions: {
        maxWidth: 400
      },
      mapOptions: {
        streetViewControl: false,
        fullscreenControl: false
      }
    }
  },
  mounted () {
    this.$gmapApiPromiseLazy().then(() => {
      this.initMap()
    })
  },
  methods: {
    initMap () {
      this.markers = []
      if (this.projects && this.projects.length) {
        // eslint-disable-next-line
        const bounds = new google.maps.LatLngBounds()
        for (let project of this.projects) {
          const position = MapHelper.stringToPosition(project.coordinates)
          if (!position) {
            continue
          }
          let info = '<h5><a target="_blank" href="' +
            this.$router.resolve({ name: 'project/details', params: { id: project.id } }).href + '">' +
            StringHelper.escapeHtml(project.title) + '</a></h5>'
          if (project.author) {
            info += ('<strong>Автор:</strong> <a target="_blank" href="' +
              this.$router.resolve({ name: 'user/details', params: { id: project.author.id } }).href + '">' +
              StringHelper.escapeHtml(project.author.first_name + ' ' + project.author.last_name) + '</a>' +
              '<br />')
          }
          info += '<strong>' + this.$t('project', 'Заказчик') + ':</strong> ' + StringHelper.escapeHtml(project.client) + '<br />'
          info += '<strong>Дедлайн:</strong> ' + DateHelper.toHumanStr(project.date)

          this.markers.push({ position, info })
          bounds.extend(position)
        }
        this.$refs.map.$mapPromise.then((map) => {
          map.fitBounds(bounds)
        })
      }
    },
    openInfoWindow (marker, index) {
      this.infoWindow.position = marker.position
      this.infoWindow.content = marker.info
      this.infoWindow.isOpen = true
    }
  }
}
</script>

<style lang="scss">
.gm-style {
  font: inherit;
  text-decoration: inherit;

  .gm-style-iw {
    font-weight: inherit;
    padding: 5px;
  }
}
</style>
