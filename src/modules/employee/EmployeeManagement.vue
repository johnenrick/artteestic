<template>
  <div class="row">
    <div class="col-sm-6">
      <h4>Position Management</h4>
    </div>
    <div class="col-sm-12">
      <common-module :form_setting="formSetting" :form_input_setting="formInputSetting" :table_setting="tableSetting" :column_setting="tableColumnSetting" :api="api"></common-module>
    </div>
  </div>
</template>
<script>
  export default{
    name: 'EmployeeManagement',
    components: {
      'common-module': require('../../components/common_module/CommonModule.vue')
    },
    create(){

    },
    mounted(){

    },
    data(){
      let tableSetting = {
        foreign_table: [
          'user_type',
          'user_information'
        ]
      }
      let tableFilterSetting = [
        {
          name: 'username'
        },
        {
          name: 'email'
        },
        {
          name: 'user_type_id',
          value: '1',
          always: true
        }
      ]
      let tableColumnSetting = [
        {
          name: 'id'
        },
        {
          name: 'username'
        },
        {
          name: 'email'
        },
        {
          name: 'type',
          db_name: 'user_type.description'
        }
      ]
      let formSetting = {
        title: 'Employee Information'
      }
      let formInputSetting = {
        form_group_credential: {
          group_name: 'User Credentials',
          input_setting: {
            username: {
              col: 7,
              col_label: 4
            },
            password: {
              type: 'password',
              col: 5,
              col_label: 4
            },
            email: {
              type: 'email',
              col: 7,
              col_label: 4
            }
          }
        },
        form_group_employee_information: {
          group_name: 'Employee Information',
          input_setting: {
            first_name: {
              col: 6,
              col_label: 4
            },
            middle_name: {
              col: 6,
              col_label: 4
            },
            last_name: {
              col: 6,
              col_label: 4
            },
            contact_number: {
              col: 6,
              col_label: 4
            },
            business_position: {
              type: 'select',
              select_option: {
                option_function: () => {

                }
              }
            },
            email: {
              type: 'email',
              col: 7,
              col_label: 4
            }
          }
        }
      }
      console.log(formSetting)
      return {
        api: 'user',
        tableSetting: tableSetting,
        tableColumnSetting: tableColumnSetting,
        formSetting: formSetting,
        formInputSetting: formInputSetting
      }
    },
    props: {
    },
    methods: {
    }

  }
</script>
<style scoped>

</style>
