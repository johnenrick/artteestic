<template>
  <div>
    <div class="col-sm-6  col-sm-offset-6 margin-bottom-20" >
      <form-component :id="formEntryID"
          v-on:entry-updated="entryUpdated"
          v-on:entry-created="entryCreated"
          v-on:entry-deleted="entryDeleted"
          :form_setting="form_setting"
          :form_input_setting="form_input_setting"
          :api="api">
      </form-component>
    </div>
    <div class="col-sm-12">
      <table-component ref="tableComponent"
          v-on:row-clicked="viewEntry"
          :column_setting="column_setting"
          :filter_setting="filter_setting"
          :api="api">
      </table-component>
    </div>
  </div>
</template>
<script>
  export default{
    name: '',
    components: {
      'table-component': require('../table/TableComponent.vue'),
      'form-component': require('../form/FormComponent.vue')
    },
    create(){

    },
    mounted(){
    },
    data(){
      return {
        formEntryID: -1,
        currentRowValue: -1
      }
    },
    props: {
      api: String,
      filter_setting: Array,
      column_setting: Array,
      form_setting: Object,
      form_input_setting: Object
    },
    methods: {
      viewEntry(entryID, rowIndex){
        this.formEntryID = -1
        this.currentRowValue = rowIndex
        setTimeout(() => {
          this.formEntryID = entryID
        }, 50)
      },
      entryDeleted(entryID){
        this.$refs.tableComponent.deleteRow(this.currentRowValue)
      },
      entryUpdated(entryID){
        console.log(this.currentRowValue)
        this.$refs.tableComponent.updateRow(this.currentRowValue, entryID * 1)
      },
      entryCreated(entryID){
        this.$refs.tableComponent.createRow(entryID)
      },
      changeEntry(direction){
        this.$refs.tableComponent.changeRow(direction)
      }
    }

  }
</script>
<style scoped>
  .margin-bottom-20{
    margin-bottom: 20px
  }
</style>
