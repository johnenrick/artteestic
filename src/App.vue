<template>
  <div id="app">
    <div v-if="!tokenData.verifyingToken">
      <nav class="navbar navbar-default " v-bind:class="user.type === 3? 'navbar-fixed-top half-width push-half-right' : ''">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Abacus</a>
          </div>
          <div v-if="user.userID" class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-list-alt" aria-hidden="true"></i> Inventory <i class="fa fa-caret-down" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu">
                  <li><router-link to=""><i class="fa fa-suitcase" aria-hidden="true"></i> Product Management</router-link></li>
                  <li><router-link to=""><i class="fa fa-tasks" aria-hidden="true"></i> Stock Adjustment</router-link></li>
                  <li><router-link to=""><i class="fa fa-truck" aria-hidden="true"></i> Supplier</router-link></li>
                  <li role="separator" class="divider"></li>
                  <li><router-link to=""><i class="fa fa-window-maximize" aria-hidden="true"></i> Shelves</router-link></li>
                  <li><router-link to=""><i class="fa fa-tag" aria-hidden="true"></i> Units</router-link></li>
                </ul>
              </li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-id-badge" aria-hidden="true"></i> Employee <i class="fa fa-caret-down" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu">
                  <li><router-link to="employeeManagement"><i class="fa fa-tasks" aria-hidden="true"></i> Employee Management</router-link></li>
                  <li><router-link to=""><i class="fa fa-clock-o" aria-hidden="true"></i> Daily Time Record</router-link></li>
                  <li><router-link to=""><i class="fa fa-money" aria-hidden="true"></i> Payroll</router-link></li>
                  <li role="separator" class="divider"></li>
                  <li><router-link to="businessPositionManagement"><i class="fa fa-black-tie" aria-hidden="true"></i> Positions</router-link></li>
                </ul>
              </li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-table" aria-hidden="true"></i> Reports <i class="fa fa-caret-down" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu">
                  <li><router-link to=""><i class="fa fa-file-text-o" aria-hidden="true"></i> X - Reading</router-link></li>
                  <li><router-link to=""><i class="fa fa-file-text-o" aria-hidden="true"></i> Z - Reading</router-link></li>
                  <li><router-link to=""><i class="fa fa-file-text-o" aria-hidden="true"></i> E Journal</router-link></li>
                  <li role="separator" class="divider"></li>
                  <li><router-link to=""><i class="fa fa-file-text-o" aria-hidden="true"></i> Sales Summary</router-link></li>
                  <li><router-link to=""><i class="fa fa-file-text-o" aria-hidden="true"></i> Inventory Summary</router-link></li>
                </ul>
              </li>

            </ul>
            <ul class="nav navbar-nav navbar-right">
              <li v-if="user.userID" class="dropdown">
                <a href="#"
                    class="dropdown-toggle"
                    data-toggle="dropdown"
                    role="button" aria-haspopup="true"
                    aria-expanded="false">
                  Hi <span class="text-uppercase">{{user.username}}</span>! <i class="fa fa-caret-down" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu">
                  <li><router-link to=""><i class="fa fa-user" aria-hidden="true"></i> Profile Settings</router-link></li>
                  <li role="separator" class="divider"></li>
                  <li><a  @click="logOut" ><i class="fa fa-power-off" aria-hidden="true"></i> Log Out</a></li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <div class="container-fluid">
        <transition >
          <router-view ></router-view>
        </transition>
      </div>
    </div>
    <div v-else>
      Loading please wait. {{tokenData.verifyingToken}}
    </div>
  </div>
</template>

<script>
import ROUTER from './router'
import AUTH from './services/auth'
export default {
  name: 'app',
  mounted(){
  },
  data(){
    return {
      user: AUTH.user,
      tokenData: AUTH.tokenData,
      currentRoute: ROUTER.currentRoute.name
    }
  },
  methods: {
    logOut(){
      AUTH.deaunthenticate()
      ROUTER.push({
        path: '/'
      })
    }
  }
}
</script>

<style scoped>
.half-width{
  width: 50%
}
.push-half-right{
  margin-left: 50%
}
.nav a{
  font-size: 15px
}
.dropdown-menu li a{
  padding: 10px;
}
</style>
