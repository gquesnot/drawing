import Vue from 'vue'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import App from '~/components/App'


import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import '~/plugins'
import '~/components'

import resource from 'vue-resource'

Vue.use(BootstrapVue)
Vue.use(resource)
Vue.config.productionTip = false


/* eslint-disable no-new */
new Vue({
  i18n,
  store,
  router,
  ...App
})
