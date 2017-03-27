// src/auth/index.js
import {router} from '../../router/index'
import ROUTER from '../../router'
import Vue from 'vue'
// URL and endpoint constants
const API_URL = 'http://localhost:3001/'
export default {
  user: {
    userID: 0,
    username: '',
    type: 0
  },
  tokenData: {
    token: null,
    tokenTimer: false,
    verifyingToken: false
  },
  currentPath: false,
  setUser(userID, username, type){
    if(userID === null){
      username = null
      type = null
    }
    this.user.userID = userID * 1
    this.user.username = username
    this.user.type = type * 1
  },
  setToken(token){

    this.tokenData.token = token
    localStorage.setItem('usertoken', token)
    if(token){
      setTimeout(() => {
        let vue = new Vue()
        vue.APIRequest('authenticate/refresh', {}, (response) => {
          this.setToken(response['token'])
        }, (response) => {
          ROUTER.go('/')
        })
      }, 1000 * 60 * 50)
    }
  },
  authenticate(username, password, callback, errorCallback){
    let vue = new Vue()
    let credentials = {
      username: username,
      password: password
    }
    vue.APIRequest('authenticate', credentials, (response) => {
      this.setToken(response.token)
      vue.APIRequest('authenticate/user', {}, (userInfo) => {
        this.setUser(userInfo.id, userInfo.username, userInfo.type)
        if(callback){
          callback(userInfo)
        }
      })

    }, (response, status) => {
      if(errorCallback){
        errorCallback(response, status)
      }
    })
  },
  checkAuthentication(callback){
    this.tokenData.verifyingToken = true
    let token = localStorage.getItem('usertoken')
    if(token){
      this.setToken(token)
      let vue = new Vue()
      vue.APIRequest('authenticate/user', {}, (userInfo) => {
        this.setUser(userInfo.id, userInfo.username, userInfo.type)
        this.tokenData.verifyingToken = false
        if(this.currentPath){
          ROUTER.push({
            path: this.currentPath
          })
        }
      }, (response) => {
        this.setToken(null)
        this.tokenData.verifyingToken = false
        ROUTER.push({
          path: '/'
        })
      })
      return true
    }else{
      this.tokenData.verifyingToken = false
      this.setUser(null)
      return false
    }
  },
  deaunthenticate(){
    localStorage.removeItem('usertoken')
    this.setUser(null)
    let vue = new Vue()
    vue.APIRequest('authenticate/invalidate')
  }
}
