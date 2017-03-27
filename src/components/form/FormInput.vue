<template>
    <div v-bind:class="'row'" v-bind:style="formGroupStyle">

      <div style="padding-left:15px;margin-top:-10px;"> <span style="background:white"> {{group_name}} </span> </div>
      <template v-for="(inputSetting, index) in input_settings">
        <div v-bind:class="'col-sm-'+inputSetting['col']" v-bind:style="input_settings[index]['style']">
          <template v-if="index.indexOf('form_group')===-1">
            <template v-if="inputSetting['type']==='checkbox_option'">
              <label>
                <input type="checkbox"
                  v-model="input_values[inputSetting['db_name']]"
                  v-bind:style="form_disabled ? inputSetting['input_style']['style']:inputSetting['input_style']['active_style']"
                  v-bind:disabled="form_disabled"
                  v-on:click="
                  $emit('input-changed', inputSetting['db_name'], (read_only || is_loading.length || inputSetting['read_only']) ? !input_values[inputSetting['db_name']] : input_values[inputSetting['db_name']])"
                  >
                <span v-bind:style="inputSetting['label_style']">{{inputSetting['label']}}</span>
              </label>
            </template>

            <template v-else-if="inputSetting['type']==='hidden'">
              <input
                v-on:change="$emit('input-changed', inputSetting['db_name'], $event.target.value)"
                v-bind:value="input_values[inputSetting['db_name']]"
                v-bind:name="inputSetting['db_name']"
                type="hidden"
                class="form-control">
            </template>
            <div v-else class="form-group">
              <label class="control-label" v-bind:class="'col-sm-'+inputSetting['col_label'] " v-bind:style="inputSetting['label_style']">{{inputSetting['label']}}</label>
              <div v-bind:class="inputSetting['col_input']" >
                <template v-if="inputSetting['type']==='checkbox'" >
                  <input type="checkbox"
                  v-model="input_values[inputSetting['db_name']]"
                  v-bind:style="form_disabled ? {}:inputSetting['input_style']['active_style']"
                  v-bind:disabled="form_disabled"
                  v-on:click="$emit('input-changed', inputSetting['db_name'], (read_only || is_loading.length || inputSetting['read_only']) ? !input_values[inputSetting['db_name']] : input_values[inputSetting['db_name']])"
                  class="">
                </template>
                <template v-else-if="inputSetting['type']==='select'">
                  <select
                    v-on:change="$emit('input-changed', inputSetting['db_name'], $event.target.value)"
                    v-bind:value="input_values[inputSetting['db_name']]" class="form-control"
                    v-bind:style="form_disabled ? inputSetting['input_style']['style']:inputSetting['input_style']['active_style']"
                    v-bind:disabled="form_disabled || read_only || is_loading.length || inputSetting['read_only'] ? true :false">

                    <template v-for="option in inputSetting['select_option']['options']">
                      <option v-bind:value="option['value']"  v-bind:selected="input_values[inputSetting['db_name']]===option['value']">{{option["text"]}}</option>
                    </template>
                  </select>
                </template>
                <template v-else-if="inputSetting['type']==='dummy'">
                </template>
                <template v-else-if="inputSetting['type']==='textarea'">
                  <textarea
                    v-on:keydown="(read_only || is_loading.length || inputSetting['read_only']) ? $event.preventDefault() : true"
                    v-on:change="$emit('input-changed', inputSetting['db_name'], $event.target.value)"
                    v-model="input_values[inputSetting['db_name']]"
                    v-bind:name="inputSetting['db_name']"
                    v-bind:type="inputSetting['type']"
                    class="form-control"
                    v-bind:placeholder="inputSetting['placeholder']"
                    v-bind:style="form_disabled ? inputSetting['input_style']['style']:inputSetting['input_style']['active_style']"
                    v-bind:disabled="form_disabled">
                  </textarea>
                </template>
                <template v-else>
                  <input
                    v-on:keydown="(read_only || is_loading.length || inputSetting['read_only']) ? $event.preventDefault() : ''"
                    v-on:change="$emit('input-changed', inputSetting['db_name'], $event.target.value);"
                    v-bind:value="input_values[inputSetting['db_name']]"
                    v-bind:name="inputSetting['db_name']"
                    type="text"
                    class="form-control"
                    v-bind:class="inputSetting['type']"
                    v-bind:placeholder="inputSetting['placeholder']"
                    v-bind:style="form_disabled ? inputSetting['input_style']['style']:inputSetting['input_style']['active_style']"
                    v-bind:disabled="form_disabled">
                </template>
              </div>
            </div>
          </template>
          <template v-else>
              <div class="form-group">
                <form-input-recursive
                  v-on:input-changed="updateFormValue"
                  :group_name="inputSetting['group_name']"
                  :read_only="read_only"
                  :form_disabled="form_disabled"
                  :input_values="input_values"
                  :input_settings="inputSetting['input_setting']"
                  :border_style="inputSetting['border_style']"
                  :disabled="form_disabled"
                  :is_loading="is_loading"
                  >
                </form-input-recursive>
              </div>
          </template>
        </div>
      </template>
    </div>
</template>
<script>
  export default{
    beforeCreate: function () {
      this.$options.components.formInputRecursive = require('./FormInput.vue')
    },
    created(){
      this.initStyle()
    },
    data(){
      let data = {
        formGroupStyle: {}
      }
      return data
    },
    props: {
      group_name: String,
      input_settings: Object,
      input_values: Object,
      read_only: Boolean,
      form_disabled: Boolean,
      border_style: String,
      background: String,
      is_loading: Array
    },
    methods: {
      initStyle(){
        let groupStyle = {}
        if(this.border_style === 'all'){
          groupStyle['border-style'] = 'solid'
          groupStyle['border-width'] = '1px'
          groupStyle['border-color'] = '#c2c2c2'
        }else if(this.border_style === 'top-down'){
          groupStyle.border = 'dash'
        }
        groupStyle['margin-left'] = '0px'
        groupStyle['margin-right'] = '0px'
        this.formGroupStyle = groupStyle
      },
      updateFormValue(name, value){
        this.$emit('input-changed', name, value)
      },
      underscoreToWord(string){
        return ((string).replace(/_/g, ' ')).replace(/\w\S*/g, function(string){
          return string.charAt(0).toUpperCase() + string.substr(1).toLowerCase()
        })
      }
    }
  }
</script>
<style>
  input.decimal{
    text-align: right;
  };
  input.number{
    text-align: right;
  }
</style>
