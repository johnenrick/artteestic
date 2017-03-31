import AUTH from 'services/auth'

let beforeEnter = (to, from, next) => {
  // TODO Redirect if no token when token is required in meta.tokenRequired
  AUTH.currentPath = to.path
  if(AUTH.user.userID || to.meta.tokenRequired === false){
    next()
  }else{
    if(to.name !== 'login'){
      if(!AUTH.tokenData.verifyingToken){
        next({
          path: '/'
        })
      }
    }else{
      if(!AUTH.tokenData.verifyingToken){
        next()
      }
    }
  }
}
export default{
  routes: [
    {
      path: '/',
      name: 'login',
      component: resolve => require(['modules/home/LogIn.vue'], resolve),
      beforeEnter: (to, from, next) => {
        AUTH.currentPath = to.path
        if(AUTH.user.userID){
          let path = '/cashier'
          console.log(AUTH.user.type)
          if(AUTH.user.type === 1){ // admin
            path = '/admin'
          }
          next({
            path: path
          })
        }else{
          if(!AUTH.tokenData.verifyingToken){
            next()
          }
        }
      }
    }, {
      path: '/admin',
      name: 'admin',
      component: resolve => require(['modules/home/AdminHome.vue'], resolve),
      beforeEnter: beforeEnter,
      meta: {
        tokenRequired: true
      }
    },
    {
      path: '/cashier',
      name: 'cashier',
      component: resolve => require(['modules/cashier/Cashier.vue'], resolve),
      beforeEnter: beforeEnter
    },
    {
      path: '/employeeManagement',
      name: 'employeeManagement',
      component: resolve => require(['modules/employee/EmployeeManagement.vue'], resolve),
      beforeEnter: beforeEnter
    },
    {
      path: '/businessPositionManagement',
      name: 'businessPositionManagement',
      component: resolve => require(['modules/employee/BusinessPositionManagement.vue'], resolve),
      beforeEnter: beforeEnter
    }
  ]
}
