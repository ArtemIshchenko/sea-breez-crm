<template>
  <div class="app">
    <AppHeader fixed>
      <SidebarToggler class="d-lg-none" display="md" mobile />
      <!-- <b-link class="navbar-brand" to="#">
        <img class="navbar-brand-full" src="img/brand/logo.svg" width="89" height="25" alt="CoreUI Logo">
        <img class="navbar-brand-minimized" src="img/brand/sygnet.svg" width="30" height="30" alt="CoreUI Logo">
      </b-link> -->
      <SidebarToggler class="d-md-down-none" display="lg" />
      <HeaderNav></HeaderNav>
    </AppHeader>
    <div class="app-body">
      <notifications />
      <AppSidebar fixed>
        <SidebarHeader/>
        <SidebarForm/>
        <SidebarNav :navItems="translateNav"></SidebarNav>
        <SidebarFooter/>
        <SidebarMinimizer/>
      </AppSidebar>
      <main class="main">
        <Breadcrumb :list="list"/>
        <div class="container-fluid">
          <router-view></router-view>
        </div>
      </main>
    </div>
    <TheFooter>
      <!--footer-->
      <div>
        <span>Color-way &copy; 2018-2023</span>
      </div>
      <div class="ml-auto">
        <span>Developed by</span>
        <a type="button">
          <dev-logo></dev-logo>
        </a>
      </div>
    </TheFooter>
  </div>
</template>

<script>
import { nav } from '@/config'
import { Header as AppHeader, SidebarToggler, Sidebar as AppSidebar, SidebarFooter, SidebarForm, SidebarHeader, SidebarMinimizer, Footer as TheFooter, Breadcrumb } from '@coreui/vue'
import { DevLogo } from '@/components'
import SidebarNav from './SidebarNav'
import HeaderNav from './HeaderNav'

export default {
  name: 'DefaultContainer',
  components: {
    AppHeader,
    HeaderNav,
    AppSidebar,
    TheFooter,
    Breadcrumb,
    SidebarForm,
    SidebarFooter,
    SidebarToggler,
    SidebarHeader,
    SidebarNav,
    SidebarMinimizer,
    DevLogo
  },
  data () {
    return {
      nav: nav.items
    }
  },
  computed: {
    name () {
      return this.$route.name
    },
    list () {
      const result = []
      const list = this.$route.matched.filter((route) => (route.name || route.meta.label) && route.meta.hideBreadcrumb !== true)
      list.forEach((el) => {
        if (el.name) {
          el.name = this.$t('project', el.name)
        }
        if (el.meta.label) {
          el.meta.label = this.$t('project', el.meta.label)
        }
        result.push(el)
      })
      return result
    },
    translateNav () {
      const result = []
      this.nav.forEach((el) => {
        el.name = this.$t('project', el.name)
        result.push(el)
      })
      return result
    }
  }
}
</script>
