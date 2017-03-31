<template>
  <div>
    <button v-on:click="createForm"
        class="btn btn-success pull-right" >
      <i class="fa fa-plus" aria-hidden="true"></i> Create
    </button>
    <div ref="entryFormDetails" class="modal fade" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">{{formSetting.title}}<span v-if="isLoading.length" class="pull-right"><i  class="fa fa-hourglass-half" aria-hidden="true"></i> Loading...</span></h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-sm-6">
              </div>
              <div class="col-sm-6 align-right hidden">
                <button v-if="readOnly==true && !isLoading.length"
                  v-on:click="$emit('change-entry', 0)"
                  type="button" class="btn btn-default">
                  <i class="fa fa-step-backward" aria-hidden="true"></i>
                </button>
                <button v-if="readOnly==true && !isLoading.length"
                    v-on:click="$emit('change-entry', 1)"
                    type="button"
                    class="btn btn-default">
                  <i class="fa fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button v-if="readOnly==true && !isLoading.length"
                    v-on:click="$emit('change-entry', 2)"
                    type="button"
                    class="btn btn-default">
                  <i class="fa fa-chevron-right" aria-hidden="true"></i>
                </button>
                <button v-if="readOnly==true && !isLoading.length"
                    v-on:click="$emit('change-entry', 3)"
                    type="button"
                    class="btn btn-default">
                  <i class="fa fa-step-forward" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="row" v-if="errorMessage">
              <div class="col-sm-12">
                <div class="alert alert-danger">
                  <strong>Warning!</strong>
                  <div v-html="errorMessage">

                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-12">
                <form class="form-horizontal">
                  <form-input
                    v-on:input-changed="updateFormValue"
                   :read_only="readOnly"
                   :form_disabled="formDisabled"
                   :input_values="formValue"
                   :input_settings="formInputSetting"
                   :is_loading="isLoading"
                    >
                  </form-input>
                  <div class="row">
                    <div class="form-group">
                      <label class="control-label col-sm-3" >Last Updated</label>
                      <div class="col-sm-4"  >
                        <input class="form-control" v-model="updatedAtTimestamp" disabled="true">
                      </div>
                    </div>
                  </div>

                </form>

              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="row" >
              <div class="col-sm-12 text-right" v-if="!readOnly">
                <button v-if="this.entry_id <= 0" class="btn btn-default pull-left" v-on:click="submitForm('addmore')" type="button" v-bind:disabled="isLoading.length ? true: false" >
                  <i class="fa fa-plus-square" aria-hidden="true"></i> Add more
                </button>
                <button class="btn btn-primary " v-on:click="submitForm" type="button" v-bind:disabled="isLoading.length ? true: false" >
                  <span v-if="this.entry_id > 0"><i class="fa fa-save" aria-hidden="true"></i> SAVE</span>
                  <span v-else><i class="fa fa-plus" aria-hidden="true"></i>  CREATE</span>
                </button>
                <button class="btn btn-default" v-on:click="cancelForm" type="button" v-bind:disabled="isLoading.length ? true: false">{{this.entry_id > 0 ? "Close": "Cancel"}}</button>
              </div>
            </div>
            <button v-if="entry_id > 0 && readOnly==true && !isLoading.length"
                v-bind:disabled="isLoading.length ? true: false"
                v-on:click="readOnly=false;formDisabled=false;"
                type="button" class="btn btn-default">
              <i class="fa fa-edit" aria-hidden="true"></i> Edit
            </button>
            <button v-if="(entry_id > 0 && readOnly==true && !isLoading.length)"
                v-bind:disabled="isLoading.length ? true: false"
                v-on:click="deleteEntry"
                type="button"
                class="btn btn-default">
              <i class="fa fa-trash-o" aria-hidden="true"></i> Delete
            </button>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
  </div>
</template>
<script>
  import Vue from 'vue'
  let bootboxOb = require('../../assets/js/min/bootbox.min.js')
  let bootbox = bootboxOb.bootbox
  export default{
    name: '',
    created(){
      this.initFormSetting()
      this.formInputSetting = this.initFormInputSetting(this.form_input_setting)
    },
    components: {
      'form-input': require('./FormInput.vue')
    },
    mounted(){
    },
    data(){
      let apiLink = {
        create: this.api + '/create',
        retrieve: this.api + '/retrieve',
        update: this.api + '/update',
        delete: this.api + '/delete'
      }
      return {
        entry_id: 0, // -1 create, 0 no action
        formSetting: {},
        formInputSetting: {},
        formValue: {},
        isLoading: [],
        readOnly: this.read_only,
        formDisabled: true,
        formValuePath: {},
        updatedAtTimestamp: '',
        showEntryRequest: null,
        apiLink: apiLink,
        errorMessage: false
      }
    },
    props: {
      id: Number,
      form_setting: Object,
      form_input_setting: Object,
      api: String,
      read_only: Boolean
    },
    watch: {
      entry_id: function(value){
        console.log(value)
        value = value * 1
        if(value === -1){ // create
          for(let key in this.formValue){
            if(key !== 'id'){
              this.updateFormValue(key, null)
              let setting = this.formInputSetting
              for(let pathIndex in this.formValuePath[key]){
                setting = setting[this.formValuePath[key][pathIndex]]['input_setting']
              }

              if(setting[key]['select_option'] && setting[key]['select_option']['option_function']){
                setting[key]['select_option']['option_function'](setting, this)
              }
            }
          }
          this.updatedAtTimestamp = ''
          this.readOnly = false
          this.formDisabled = false
        }
      },
      id: function(value){
        this.entry_id = value
        value = value * 1
        if(value * 1 > 0){
          this.showEntry()
        }
        if(value === 0){
          this.formDisabled = true
          this.cancelForm()
        }else{
          this.formDisabled = false
        }
      },
      read_only: function(value){
        this.readOnly = value
      }
    },
    methods: {
      initFormSetting(){
        if(typeof this.form_setting === 'undefined'){
          return false
        }
        this.formSetting = this.form_setting
        this.formSetting.title === undefined ? Vue.set(this.formSetting, 'title', this.formSetting.apiLink + ' Detail') : null
      },
      updateFormValue(name, value){
        if(name === 'id') return false
        let setting = this.formInputSetting
        for(let pathIndex in this.formValuePath[name]){
          setting = setting[this.formValuePath[name][pathIndex]]['input_setting']
        }
        let formattedValue = this.dataFormat(value, setting[name])
        Vue.set(this.formValue, name, formattedValue)

        if(setting[name]['value_changed']){
          setting[name]['value_changed'](this.formValue, this)
        }
      },
      createForm(){
        this.entry_id = -1
        jQuery(this.$refs.entryFormDetails).modal('show')
      },
      deleteEntry(){
        this.isLoading.push('deleteEntry')
        let requestOption = {
          'id': this.entry_id
        }
        this.APIRequest(this.apiLink.delete, requestOption, (response) => {
          //  success callback
          let result = response
          if(result.data){
            this.$emit('entry-deleted', this.entry_id)
            this.entry_id = -1
            this.cancelForm()
          }else{
          }
          this.isLoading.pop()

        })
        //
        // if(!this.formValue['locked']){
        //   bootbox.confirm({
        //     size: 'small',
        //     title: 'Are you sure you want to delete this entry?',
        //     message: 'Once this entry is deleted, it can no longer be recovered',
        //     buttons: {
        //       cancel: {
        //         label: 'No',
        //         className: 'btn-default'
        //       },
        //       confirm: {
        //         label: 'Yes, delete this entry',
        //         className: 'btn-danger'
        //       }
        //     },
        //     callback: (result) => {
        //       if(result){
        //         let requestOption = {
        //           'id': this.entry_id
        //         }
        //         this.APIRequest(this.apiLink.delete, requestOption, (response) => {
        //           //  success callback
        //           let result = response.body
        //           if(result.data){
        //             this.$emit('entry-deleted', this.entry_id)
        //             this.entry_id = -1
        //             this.cancelForm()
        //           }else{
        //           }
        //           this.isLoading.pop()
        //         })
        //       }else{
        //         this.isLoading.pop()
        //       }
        //     }
        //   })
        // }else{
        //   bootbox.alert('This entry is locked! Contact the admin regarding this matter', () => {
        //     this.isLoading.pop()
        //   })
        // }
      },
      showEntry(){
        this.isLoading.push('showEntry')
        if(this.showEntryRequest){
          this.showEntryRequest.abort()
        }
        let requestOption = {
          'id': this.entry_id
        }
        this.readOnly = true
        jQuery(this.$refs.entryFormDetails).modal('show')
        //  this.emptyFormValue()
        this.showEntryRequest = this.APIRequest(this.apiLink.retrieve, requestOption, (response) => {
          //  success callback
          let result = response
          if(result.data){
            for(let key in this.formValue){
              if(key !== 'id' && typeof result.data[key] !== 'undefined'){
                this.updateFormValue(key, result.data[key])
                let setting = this.formInputSetting
                for(let pathIndex in this.formValuePath[key]){
                  setting = setting[this.formValuePath[key][pathIndex]]['input_setting']
                }
                if(setting[key]['select_option'] && setting[key]['select_option']['option_function']){
                  setting[key]['select_option']['option_function'](setting, this)
                }
              }
            }
            this.updatedAtTimestamp = result.data['updated_at']
          }
          this.showEntryRequest = null
          this.isLoading.pop()
        })
      },
      emptyFormValue(){

        for(let x in this.formValue){
          if(x !== 'id'){
            this.updateFormValue(x, null)
          }
        }
        this.updatedAtTimestamp = ''
      },
      submitForm(option){
        if(typeof option === 'undefined'){
          option = ''
        }
        this.isLoading.push('submitForm')
        let requestOption = this.formValue
        let link = this.apiLink.create
        if(this.entry_id > 0){
          requestOption.id = this.entry_id
          link = this.apiLink.update
        }
        this.APIRequest(link, requestOption, (response) => {
          //  success callback
          let result = response
          if(result.data){
            if(this.entry_id === -1){
              this.entry_id = result.data
              this.$emit('entry-created', this.entry_id)
              if(option === 'addmore'){
                this.entry_id = -1
                this.emptyFormValue()
              }else{
                this.updatedAtTimestamp = result.request_timestamp
                this.readOnly = true
              }
            }else{
              this.$emit('entry-updated', this.entry_id)
              this.updatedAtTimestamp = result.request_timestamp
              this.readOnly = true
            }

          }else{
            this.errorMessage = ''
            if(result.error.status === 100){
              for(var x in result.error.message){
                this.errorMessage += '<i class="fa  fa-chevron-right" aria-hidden="true"></i> ' + result.error.message[x][0] + '<br>'
              }
            }
          }
          this.isLoading.pop()
        })
      },
      cancelForm(){
        if(this.entry_id === -1){
          this.emptyFormValue()

          this.entry_id = 0
          this.formDisabled = true
        }
        this.readOnly = true
        jQuery(this.$refs.entryFormDetails).modal('hide')
      },
      initFormInputSetting(formInputSetting, path){
        if(typeof path === 'undefined'){
          path = []
        }
        let currentPath = path.slice(0)
        let settings = {}
        for(let dbName in formInputSetting){
          let input = {}
          if(dbName.indexOf('dummy') > -1){ // init dummy
            input.type = 'dummy'
            input.label = '_'
            input.label_style = {
              color: 'white'
            }
            input.col = (formInputSetting[dbName]['col']) ? formInputSetting[dbName]['col'] * 1 : 12
            input.col_label = (formInputSetting[dbName]['col_label']) ? formInputSetting[dbName]['col_label'] : (input.col === 6 ? 6 : 3)
            input.col_input = 'col-sm-' + (12 - input.col_label)
            settings[dbName] = input
          }else if(dbName.indexOf('form_group') === -1){ // init input
            Vue.set(input, 'disabled', false)
            Vue.set(input, 'name', (formInputSetting[dbName]['name']) ? formInputSetting[dbName]['name'] : this.underscoreToWord(dbName))

            Vue.set(input, 'db_name', dbName)
            Vue.set(input, 'type', (formInputSetting[dbName]['type']) ? formInputSetting[dbName]['type'] : 'text')
            Vue.set(input, 'default_value', ((typeof formInputSetting[dbName]['default_value'] !== 'undefined') ? formInputSetting[dbName]['default_value'] : null))
            input.label = (formInputSetting[dbName]['label']) ? formInputSetting[dbName]['label'] : input.name
            input.label_style = (formInputSetting[dbName]['label_style']) ? formInputSetting[dbName]['label_style'] : {}

            input.input_style = {}
            input.input_style_dummy = formInputSetting[dbName]['input_style'] ? formInputSetting[dbName]['input_style'] : {}
            input.input_style_dummy.active_style = (input.input_style_dummy.active_style) ? input.input_style_dummy.active_style : {}
            input.input_style_dummy.style = (input.input_style_dummy.style) ? input.input_style_dummy.style : {}
            input.input_style.style = input.input_style_dummy.style
            input.input_style.active_style = $.extend(input.input_style_dummy.active_style, input.input_style.style)
            //  input.input_style.active_style = input.input_style.style(input.input_style.style)

            input.read_only = formInputSetting[dbName]['read_only'] ? formInputSetting[dbName]['read_only'] : false
            input.value_changed = (formInputSetting[dbName]['value_changed']) ? formInputSetting[dbName]['value_changed'] : null
            Vue.set(input, 'style', ((typeof formInputSetting[dbName]['style'] !== 'undefined') ? formInputSetting[dbName]['style'] : null))
            let defaultPlaceholder = ''
            switch(input.type){
              case 'number':
                defaultPlaceholder = 0
                break
              case 'decimal':
                defaultPlaceholder = '0.00'
                break
              default:
                defaultPlaceholder = input.name
            }
            input.placeholder = (formInputSetting[dbName]['placeholder']) ? formInputSetting[dbName]['placeholder'] : defaultPlaceholder
            input.col = (formInputSetting[dbName]['col']) ? formInputSetting[dbName]['col'] * 1 : 12
            input.col_label = (formInputSetting[dbName]['col_label']) ? formInputSetting[dbName]['col_label'] * 1 : (input.col === 6 ? 6 : 3)
            input.col_input = 'col-sm-' + (12 - input.col_label)
            if(input.col_label === 12){
              input.label_style['text-align'] = 'left'
              input.col_input = 'col-sm-10 col-sm-offset-2'
            }
            if(input.type === 'select'){
              input.select_option = formInputSetting[dbName]['select_option']
              input.select_option.datetime_updated = false
              Vue.set(input.select_option, 'options', formInputSetting[dbName]['select_option']['options'] ? formInputSetting[dbName]['select_option']['options'] : {})
              if(typeof input.select_option.options === 'undefined'){
                input.select_option.options = {}
              }

            }
            Vue.set(this.formValue, dbName, input['default_value'])// set form value's default
            this.formValuePath[dbName] = currentPath.slice(0)
            settings[dbName] = input
          }else{ // form group
            settings[dbName] = formInputSetting[dbName]
            settings[dbName].col = (formInputSetting[dbName]['col']) ? formInputSetting[dbName]['col'] * 1 : 12
            settings[dbName].group_name = formInputSetting[dbName]['group_name'] ? formInputSetting[dbName]['group_name'] : ''
            currentPath.push(dbName)
            Vue.set(settings[dbName], 'input_setting', this.initFormInputSetting(formInputSetting[dbName]['input_setting'], currentPath))

          }
        }
        return settings
      },
      underscoreToWord(string){
        return ((string).replace(/_/g, ' ')).replace(/\w\S * /g, function(string){
          return string.charAt(0).toUpperCase() + string.substr(1).toLowerCase()
        })
      },
      dataFormat(value, inputSetting, self){
        let type = inputSetting['type']
        let colValue = value
        switch(type){
          case 'decimal':
            return ((colValue !== '' && colValue !== null && typeof colValue !== 'undefined') ? colValue * 1 : inputSetting['default_value']) * 1
          case 'number':
            return (colValue !== '' && colValue !== null && typeof colValue !== 'undefined') ? colValue * 1 : inputSetting['default_value']
          case 'checkbox':
            return value * 1 ? 1 : 0
          case 'checkbox_option':

            return (value * 1) ? 1 : 0
          case 'select':
            return (colValue !== null) ? colValue * 1 : (inputSetting['default_value'])
          default:
            return colValue
        }
      }
    }

  }
</script>
<style scoped>
</style>
