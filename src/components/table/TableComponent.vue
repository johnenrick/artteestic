<template >
  <div class="section">
    <div class="row">
      <div class="col-sm-3">
        <div class="">
          <label v-if="isLoading.length" class="control-label">
            <i  class="fa fa-hourglass-half" aria-hidden="true"></i> Loading...
          </label>
        </div>
      </div>
      <div class="col-sm-9 text-right">
        <form v-if="filter_setting" class="form-inline">
          <div class="form-group">
            <select  v-model="currentFilter" class="form-control">
              <template v-for="(filterSetting, index) in filterSettings">
                <option v-bind:value="index"
                    v-bind:placeholder="filterSetting['placeholder']"
                    v-bind:type="filterSetting['type']">{{filterSetting["name"]}}
                </option>
              </template>
            </select>
          </div>
          <div class="form-group">
            <input v-model="filterValue"
                class="form-control"
                v-bind:name="filterSettings[currentFilter]['name']"
                v-bind:placeholder="filterSettings[currentFilter]['placeholder']"
                >
          </div>
          <button v-on:click="retrieveData(2)"
              type="button"
              class="btn btn-primary "
              v-bind:disabled="isLoading.length ? true : false">
            <i class="fa fa-filter" aria-hidden="true"></i> Filter
          </button>
        </form>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12 ">
        <table class="table  table-striped table-condensed table-bordered table-hover table-condensed table-responsive">
          <thead>
            <tr v-for="(columnSetting, parentIndex) in columnSettings">
              <th  v-for="(column, index) in columnSetting"
                  v-on:click="changeSort(parentIndex, index,$event)"
                  v-bind:rowspan="((typeof column['rowspan'] === 'undefined') ? 2 :  column['rowspan'])"
                  v-bind:colspan="((typeof column['colspan'] === 'undefined') ? 1 :  column['colspan'])"
                  v-bind:class="column['width_class']"
                  class="text-uppercase text-bold"
                  >
                {{column["name"]}}
                <!-- <i v-if="column['rowspan'] !== 1" class="fa pull-right" v-bind:class="columnSettings[parentIndex][index]['sort'] | sortIndicator" aria-hidden="true"></i> -->
                <!-- {{testmessage[0]["m1"]}} -->
                <!-- v-bind:class="[(column['sort'] === 'asc')? 'fa-sort-up': ((column['sort'] === 'desc') ? 'fa-sort-down': 'fa-sort'),  ]" -->
              </th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(row, index) in tableData">
              <tr  v-on:click="rowClicked(index)">
                <template v-for="col in linearColumnSettings">
                  <td v-if="col['db_name']" v-bind:class="'col-type-'+col['type']">
                    <img v-if="col['type'] === 'image'" v-bind:src="col['directory'] + row[col['db_name']]">
                    </img>
                    <input v-else-if="col['type'] === 'checkbox'"
                        type='checkbox'
                        v-bind:value="col['db_name'] | dataFormat(col, row)"
                        v-model="row[col['db_name']]"
                        onclick="return false">
                    <span v-else >
                      {{col['db_name'] | dataFormat(col, row)}}
                    </span>
                  </td>
                </template>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

    </div>

</div>
</template>

<script>
import Vue from 'vue'
export default {
  created(){
    this.initFilterSetting()
    this.initColumnSetting()

  },
  mounted(){
    setTimeout(() => {
      this.retrieveData()
    }, 1)

  },
  data(){
    let apiLink = {
      create: this.api + '/create',
      retrieve: this.api + '/retrieve',
      update: this.api + '/update',
      delete: this.api + '/delete'
    }
    return {
      message: 'Hello Table',
      tableData: [],
      columnSettings: {},
      linearColumnSettings: {},
      filterSettings: {},
      currentFilter: 0,
      filterValue: '',
      isLoading: [],
      currentRow: -1,
      apiLink: apiLink

    }
  },
  props: {
    column_setting: Array,
    filter_setting: Array,
    api: String,
    timestamp: Boolean
  },
  watch: {
    currentRow: function(row){
      this.rowClicked(row)
    }
  },
  methods: {
    changeRow(direction){
      switch(direction * 1){
        case 0: // first
          this.currentRow = 0
          break
        case 1:// previous
          if(this.currentRow){
            this.currentRow = this.currentRow - 1
          }
          break
        case 2:// next
          if(this.currentRow < this.tableData.length - 1){
            this.currentRow = this.currentRow + 1
          }
          break
        case 3:// last
          this.currentRow = this.tableData.length - 1
          break
      }
    },
    createRow(rowID){
      this.isLoading.push('createRow')
      let requestOption = {
        id: rowID
      }
      this.APIRequest(this.apiLink.retrieve, requestOption, (response) => {
        // success callback

        let result = response
        if(result.data){
          this.tableData.push(result.data)
          // Vue.set(this.tableData, rowID, )
        }
        this.isLoading.pop()
      })
    },
    updateRow(row, entryID){
      this.isLoading.push('updateRow')
      let requestOption = {
        id: this.tableData[row]['id']
      }
      this.APIRequest(this.apiLink.retrieve, requestOption, (response) => {
        // success callback
        let result = response
        if(result.data){
          if(this.tableData[row]['id'] * 1 === entryID * 1){
            Vue.set(this.tableData, row, result.data)
          }else{
            for(let x = 0; x < this.tableData.length; x++){
              if(this.tableData[x]['id'] * 1 === entryID){
                Vue.set(this.tableData, x, result.data)
                break
              }
            }
          }
        }
        this.isLoading.pop()
      })
    },
    deleteRow(row){
      this.tableData.splice(row, 1)
    },
    rowClicked(rowIndex){
      this.$emit('row-clicked', this.tableData[rowIndex]['id'], rowIndex)
    },
    retrieveData(mode){
      this.isLoading.push('retrieveData')
      mode = mode || 0
      if(mode === 1){ // search

      }else{
        let requestOption = {
        }
        if(mode === 2 && this.filterValue !== ''){ // filter
          let condition = {
            column: this.filterSettings[this.currentFilter]['name'],
            value: '%' + this.filterValue + '%',
            clause: this.filterSettings[this.currentFilter]['clause']
          }
          requestOption.condition = [condition]
        }
        this.APIRequest(this.apiLink.retrieve, requestOption, (response) => {
          // success callback
          this.tableData = []
          for(let row in response.data){
            this.tableData.push(response.data[row])
          }
          this.isLoading.pop()
        }, (response) => {
        })
      }
    },
    changeSort(parentIndex, index, $event){
      switch(this.columnSettings[parentIndex][index]['sort']){
        case 'asc':
          this.columnSettings[parentIndex][index]['sort'] = 'desc'
          break
        case 'desc':
          this.columnSettings[parentIndex][index]['sort'] = ''
          break
        default:
          this.columnSettings[parentIndex][index]['sort'] = 'asc'
      }
    },
    initFilterSetting(){
      if(typeof this.filter_setting === 'undefined'){
        return false
      }
      let filterSetting = this.filter_setting
      for(let x = 0; x < filterSetting.length; x++){
        filterSetting[x]['db_name'] = (typeof filterSetting[x]['db_name'] === 'undefined') ? this.$options.filters.underscoreCase(filterSetting[x]['name']) : filterSetting[x]['db_name']
        filterSetting[x]['placeholder'] = (typeof filterSetting[x]['placeholder'] === 'undefined') ? filterSetting[x]['name'] : filterSetting[x]['placeholder']
        filterSetting[x]['type'] = (typeof filterSetting[x]['type'] === 'undefined') ? 'text' : filterSetting[x]['type']
        if(typeof filterSetting[x]['clause'] === 'undefined'){

          switch(filterSetting[x]['type']){
            case 'text':
              filterSetting[x]['clause'] = 'like'
              break
            default:
              filterSetting[x]['clause'] = '='
          }
        }else{
          filterSetting[x]['clause'] = filterSetting[x]['clause']
        }
        Vue.set(filterSetting[x], 'value', (typeof filterSetting[x]['default'] === 'undefined') ? '' : filterSetting[x]['default'])
      }
      this.filterSettings = filterSetting
    },
    initColumnSetting(){
      let initializedData = this.column_setting
      let dummyLinear = []
      if(initializedData.length){ // check if it has value
        if(initializedData[0].length === undefined){ // check if object not array;one row thead
          for(let x = 0; x < initializedData.length; x++){
            this.initColumnSettingDefault(initializedData[x])
          }
          dummyLinear = initializedData.slice(0)
          initializedData = [initializedData]
        }else if(initializedData[0].length){ // multiple row thead
          let dsrCounter = 0 // DummySecondRowCounter
          for(let x = 0; x < initializedData[0].length; x++){ // loop first row thead
            this.initColumnSettingDefault(initializedData[0][x])
            if(initializedData[0][x]['colspan']){ // thead with subrow
              let y = 0
              for(y = 0; y < initializedData[0][x]['colspan']; y++){ // changing linear order
                if(initializedData.length > 1 && initializedData[1].length > 0){
                  this.initColumnSettingDefault(initializedData[1][dsrCounter])
                  dummyLinear.push(initializedData[1][dsrCounter])
                  dsrCounter++
                }
              }
            }else{
              dummyLinear.push(initializedData[0][x])
            }
          }
          for(dsrCounter = 0; dsrCounter < initializedData[1].length; dsrCounter++){ // create linear array
            initializedData[1][dsrCounter]['db_name'] = (typeof initializedData[1][dsrCounter]['db_name'] === 'undefined') ? this.$options.filters.underscoreCase(initializedData[1][dsrCounter]['name']) : initializedData[1][dsrCounter]['db_name']
            dummyLinear.push(initializedData[1][dsrCounter])
          }
        }
      }
      if(this.timestamp !== false){
        let timestamp = {
          name: 'Last Updated',
          db_name: 'updated_at',
          type: 'timestamp',
          width_class: 'small-width'
        }
        dummyLinear.push(timestamp)
        initializedData[0].push(timestamp)
      }
      Vue.set(this._data, 'linearColumnSettings', dummyLinear)
      Vue.set(this._data, 'columnSettings', initializedData)
      // this.linearColumnSettings = dummyLinear
      // this.columnSettings = initializedData
    },
    initColumnSettingDefault(initializedData){
      initializedData['db_name'] = (typeof initializedData['db_name'] === 'undefined') ? this.$options.filters.underscoreCase(initializedData['name']) : initializedData['db_name']
      let defaultWidthClass = ''
      if(initializedData['type'] === 'number' || initializedData['type'] === 'decimal' || initializedData['type'] === 'checkbox' || initializedData['type'] === 'y/n'){
        defaultWidthClass = 'small-width'
      }
      initializedData['width_class'] = (typeof initializedData['width_class'] === 'undefined') ? defaultWidthClass : initializedData['width_class']
      if(initializedData['colspan'] === undefined){
        Vue.set(initializedData, 'sort', (typeof initializedData['sort'] === 'undefined') ? 0 : initializedData['sort'])
      }else{
        initializedData['rowspan'] = 1
      }

    }
  },
  filters: {
    sortIndicator(value){
      switch(value){
        case 'asc':
          return 'fa-sort-up'
        case 'desc':
          return 'fa-sort-down'
        default:
          return 'fa-sort'
      }

    },
    underscoreCase(value){
      return (value).toLocaleLowerCase().replace(/ /g, '_')
    },
    dataFormat(colName, colSetting, rowValue){
      let type = colSetting['type']
      let value = (typeof rowValue[colName] !== undefined) ? rowValue[colName] : null
      if(colName.indexOf('.') !== -1){
        let colNames = colName.split('.')
        let traverseColumn = rowValue
        for(let x in colNames){
          if(traverseColumn[colNames[x]]){
            traverseColumn = traverseColumn[colNames[x]]
          }else{
            traverseColumn = null
            break
          }
        }
        value = traverseColumn
      }
      if(typeof colSetting['value_function'] !== 'undefined'){
        value = colSetting['value_function'](rowValue)
      }
      switch(type){
        case 'decimal':
          rowValue[colName] = value
          value = (value * 1).toFixed(2)
          break
        case 'checkbox':
          rowValue[colName] = value
          value = value * 1 > 0
          break
        case 'y/n':
          value = value ? 'Y' : 'N'
          break
        default:
          rowValue[colName] = value
          break
      }

      return value
    }
  }
}
</script>

<style lang="css">

table thead th{
  text-align:center
}
table th.text-center,
table td.text-center {
    text-align: center
}

table th.text-right,
table td.text-right {
    text-align: right
}

table th.small-width {
    width: 80px
}
.col-type-decimal{
  text-align:right
}
.col-type-number{
  text-align:right
}
.col-type-timestamp{
  text-align:right
}
.vcenter{
  vertical-align: middle
}
.form-inline{
  margin-bottom: 15px
}
</style>
