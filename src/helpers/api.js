import CONFIG from '../config'
import Vue from 'vue'
import AUTH from '../services/auth'
import ROUTER from '../router'
Vue.mixin({
  mounted(){

  },
  methods: {
    APIRequest(link, parameter, callback, errorCallback){
      let tokenStringParam = (AUTH.tokenData.token) ? '?token=' + AUTH.tokenData.token : ''
      let request = jQuery.post(CONFIG.API_URL + link + tokenStringParam, parameter, (response) => {
        if(callback){
          callback(response)
        }
      }).fail((jqXHR) => {
        switch(jqXHR.status){
          case 401: // Unauthorized
            if(link === 'authenticate' || 'authenticate/user'){ // if error occured during authentication request
              if(errorCallback){
                errorCallback(jqXHR.responseJSON, jqXHR.status)
              }
            }else{
              ROUTER.push('login')
            }
            break
          default:
            if(errorCallback){
              errorCallback(jqXHR.responseJSON, jqXHR.status)
            }
        }
      })
      return request
    }
  }
})
