import Vue from 'vue'
/***
  Be sure to name the helper properly inroder to avoid conflicting on methods in Vue Modules and Components
*/
Vue.mixin({
  methods: {
    sampleHelper(){
      return 'hello world'
    }
  }
})
