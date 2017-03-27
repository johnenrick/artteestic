import Vue from 'vue'
import Router from 'vue-router'
import CONFIG from '../config'
import AUTH from 'services/auth/index'
import ModuleRoutes from './module_routes'
import Helpers from './helpers'
import Services from './services'
global.jQuery = require('jquery')
global.$ = global.jQuery
require('bootstrap/dist/css/bootstrap.min.css')
require('bootstrap')
require('font-awesome/css/font-awesome.css')
AUTH.checkAuthentication()
Vue.use(Router)
export default new Router({
  routes: ModuleRoutes.routes
})
