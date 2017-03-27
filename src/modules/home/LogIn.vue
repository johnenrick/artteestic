<template>
  <div class="container">
    <div class="row">
      <div class="col-sm-6 col-sm-offset-3">
        <div class="panel panel-primary">
          <div class="panel-heading">Welcome to Abacus Point of Sale System!</div>
          <div class="panel-body">
            <div v-if="errorMessage !== '' && !isLoading" class="alert alert-danger">
              <strong>Failed!</strong> {{errorMessage}}
            </div>
            <form class="form-horizontal">
              <div class="form-group ">
                <label  class="col-md-2 control-label">Username</label>
                <div class="col-md-10">
                  <input v-model="username"
                      v-bind:disabled="isLoading"
                      type="text"
                      class="form-control"
                      placeholder="Username">
                </div>
              </div>
              <div class="form-group">
                <label class="col-md-2 control-label">Password</label>
                <div class="col-md-10">
                  <input v-model="password"
                      v-bind:disabled="isLoading"
                      type="password" class="form-control" placeholder="Password">
                </div>
              </div>
              <div class="form-group">
                <div class="col-sm-12">
                  <button
                      @click="signIn"
                      v-bind:disabled="isLoading"
                      type="button"
                      class="btn btn-primary pull-right"
                      >
                    {{isLoading ? 'Signing in...' : 'Sign In'}}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import ROUTER from '../../router'
import AUTH from '../../services/auth'
export default {
  name: 'LogIn',
  mounted(){
  },
  data(){
    return {
      username: 'ryan',
      password: 'secret',
      isLoading: false,
      errorMessage: ''
    }
  },
  methods: {
    signIn(){
      this.isLoading = true
      AUTH.authenticate(this.username, this.password, (response) => {
        this.isLoading = false
        ROUTER.go({
          path: '/'
        })
      }, (response, status) => {
        this.errorMessage = (status === 401) ? 'Username and password mismatched' : 'Cannot log in. Contact developer if error persist.'
        this.isLoading = false
      })
    }
  }
}
</script>
<style>
</style>
