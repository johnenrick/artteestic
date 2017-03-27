<template>
  <div class="row">
    <div class="col-sm-6">
      <h4>Position Management</h4>
    </div>

    <div class="col-sm-12">
      <common-module :form_setting="formSetting" :form_input_setting="formInputSetting" :column_setting="tableColumnSetting" :api="api"></common-module>
    </div>
  </div>
</template>
<script>
  export default{
    name: 'BusinessPositionManagement',
    components: {
      'common-module': require('../../components/common_module/CommonModule.vue')
    },
    create(){

    },
    mounted(){

    },
    data(){
      let tableFilterSetting = [
        {
          name: 'description'
        }
      ]
      let tableColumnSetting = [
        {
          name: 'description'
        },
        {
          name: 'rank'
        }
      ]
      let formSetting = {
        title: 'Positions'
      }
      let formInputSetting = {
        description: {},
        rank: {
          type: 'number',
          default_value: 5
        }
      }
      console.log(formSetting)
      return {
        api: 'business_position',
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
