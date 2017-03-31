<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Pluralizer;

class APIController extends Controller
{
    protected $model = null;
    protected $validation = array();
    protected $test = null;
    protected $response = array(
      "data" => null,
      "error" => array(),// {status, message}
      "debug" => null,
      "request_timestamp" => 0
    );
    protected $tableColumns = null;
    protected $notRequired = array();
    protected $defaultValue = array();
    protected $requiredForeignTable = null;//children that are always retrieve, singular
    protected $editableForeignTable = array();
    protected $foreignTable = array();

    public function test(){

    }
    public function output(){
      $this->response["request_timestamp"] = date("Y-m-d h:i:s");
      return response()->json($this->response);
      // sleep(2);

      // echo json_encode($this->response);
    }
    public function create(Request $request){
      $this->createEntry($request->all());
      return $this->output();
    }
    public function retrieve(Request $request){
      $this->retrieveEntry($request->all());
      return $this->output();
    }
    public function update(Request $request){
      $this->updateEntry($request->all());

      return $this->output();
    }
    public function delete(Request $request){
      $this->deleteEntry($request->all());
      return $this->output();
    }
    /**
      Check if form fields are valid
      ouput : true - if no errors
              output() - if errors found.
    */
    public function isValid($request, $action = "create"){
      /*Require all fields*/
      unset($this->tableColumns[0]);
      array_pop($this->tableColumns);//deleted at
      array_pop($this->tableColumns);//updated at
      array_pop($this->tableColumns);//created at

      foreach($this->tableColumns as $column){
        $this->validation[$column] = (isset($this->validation[$column])) ? $this->validation[$column] : "";
        if(!in_array($column, $this->notRequired) && !isset($this->defaultValue[$column])){//requiring all filed by default
          $this->validation[$column] = $this->validation[$column].($this->validation[$column] ? "| ":"")."required";
        }
      }
      if(count($this->validation)){
        foreach($this->validation as $validationKey => $validationValue){

          if($action == "update"){
            if( strpos( $validationValue, "unique" ) !== false ) { //check if rule has unique
                $rules = explode("|", $this->validation[$validationKey]);
                foreach($rules as $ruleKey => $ruleValue){ //find the unique rule

                  if(strpos( $ruleValue, "unique" ) !== false){
                    $rules[$ruleKey] = Rule::unique(str_replace("unique:", "", $ruleValue), $validationKey)->ignore($request["id"], "id");
                  }
                }
                $this->validation[$validationKey] = $rules;

            }
          }
          if(strpos( $validationKey, "_id" ) !== false){
            $table = str_plural(str_replace("_id", "", $validationKey));
            $this->validation[$validationKey] = $this->validation[$validationKey]."|exists:".$table.",id";

          }
        }
        $validator = Validator::make($request, $this->validation);
        if ($validator->fails()) {
            $this->response["error"]["status"] = 100;
            $this->response["error"]["message"] = $validator->errors()->toArray();
            return false;
        }else{
          return true;
        }
      }
    }
    public function createEntry($request){
      $tableColumns = $this->model->getTableColumns();
      $this->tableColumns = $tableColumns;
      if(!$this->isValid($request, "create")){
        return $this->output();
      }

      unset($tableColumns[0]);
      foreach($tableColumns as $columnName){
        if(isset($request[$columnName])){
          $this->model->$columnName = $request[$columnName];
        }else if(isset($this->defaultValue[$columnName])){
          $this->model->$columnName = $this->defaultValue[$columnName];
        }
      }
      ;
      $this->model->save();
      $childID = array();
      if($this->model->id && $this->editableForeignTable){
        foreach($this->editableForeignTable as $childTable){
          if(isset($request[$childTable]) && $request[$childTable]){
            $child = $request[$childTable];
            if(count(array_filter(array_keys($child), 'is_string')) > 0){//associative
              if(!isset($childID[$childTable])){
                $childID[$childTable] = array();
              }
              $result = $this->model->find($this->model->id)->$childTable()->create($child);
              $childID[$childTable] = $result["id"];
            }else{
              foreach($child as $childValue){
                if(!isset($childID[$childTable])){
                  $childID[$childTable] = array();
                }
                $result = $this->model->find($this->model->id)->$childTable()->create($childValue);
                $childID[$childTable][] = $result["id"];
              }
            }
          }
        }
        $response = $this->model->id;
        if(count($childID)){
          $childID["id"] = $this->model->id;;
          $response = $childID;
        }
        $this->response["data"] = $response;
        return $response;
      }else{
        if($this->model->id){
          $this->response["data"] = $this->model->id;
          return true;
        }else{
          $this->response["error"]["status"] = 1;
          $this->response["error"]["message"] = "Failed to create entry in database";
          return false;
        }
      }

    }
    public function retrieveEntry($request){
      if(isset($request["id"])){
         $this->model = $this->model->where("id", "=", $request["id"]);
      }else{
        (isset($request['condition'])) ? $this->addCondition($request['condition']) : null;
        (isset($request['sort'])) ? $this->addOrderBy($request['sort']) : null;
        (isset($request['offset'])) ? $this->model->offset($request['offset']) : null;
        (isset($request['limit'])) ? $this->model = $this->model->limit($request['limit']) : null;
      }

      if($this->requiredForeignTable){
        $this->model = $this->model->with($this->requiredForeignTable);
      }
      if(isset($request['with_foreign_table'])){
        $foreignTable = array();
        foreach($request['with_foreign_table'] as $tempForeignTable){
          if(in_array($tempForeignTable, $this->foreignTable)){
            $foreignTable[] = $tempForeignTable;
          }
        }
        if(count($foreignTable)){
          $this->model = $this->model->with($foreignTable);
        }
      }
      if(isset($request['with_soft_delete'])){
        $this->model = $this->model->withTrashed();
      }
      $result = $this->model->get();
      if($result){
        $this->response["data"] = $result->toArray();
        if(isset($request["id"])){
          $this->response["data"] = $this->response["data"][0];
        }
      }else{
        $this->response["error"][] = [
          "status" => 200,
          "message" => "No Result"
        ];
      }
      return $result;
    }
    public function updateEntry($request){
      $tableColumns = $this->model->getTableColumns();

      $this->tableColumns = $tableColumns;
      $this->isValid($request, "update");
      $this->model = $this->model->find($request["id"]);
      foreach($this->tableColumns as $columnName){
        if(isset($request[$columnName])){
          $this->model->$columnName = $request[$columnName];//setting attribute
        }else if(isset($this->defaultValue[$columnName])){
          $this->model->$columnName = $this->defaultValue[$columnName];
        }
      }

      $result = $this->model->save();
      if($result && $this->editableForeignTable){
        $childID = array();
        foreach($this->editableForeignTable as $childTable){
          if(isset($request[$childTable]) && $request[$childTable]){
            $child = $request[$childTable];
            if(count(array_filter(array_keys($child), 'is_string')) > 0){//associative
              if(!isset($childID[$childTable])){
                $childID[$childTable] = array();
              }
              $result = false;
              if(isset($child["id"]) && $child["id"]*1) {//update
                $pk = $child["id"];
                unset($child["id"]);
                $result = $this->model->find($this->model->id)->$childTable()->find($pk)->update($child);
              }else{
                $result = $this->model->find($this->model->id)->$childTable()->create($child)->id;
              }
              $childID[$childTable] = $result;
            }else{
              foreach($child as $childValue){
                if(!isset($childID[$childTable])){
                  $childID[$childTable] = array();
                }
                $result = false;
                if(isset($childValue["id"]) && $childValue["id"]*1) {//update
                  $pk = $childValue["id"];
                  unset($childValue["id"]);
                  $result = $this->model->find($this->model->id)->$childTable()->find($pk)->update($childValue);
                }else{
                  $result = $this->model->find($this->model->id)->$childTable()->create($childValue)->id;
                }
                $childID[$childTable][] = $result;
              }
            }
          }
        }

        $response = $this->model->id;
        if(count($childID)){
          $childID["id"] = $response;
          $response = $childID;
        }
        $this->response["data"] = $response;
        return $response;
      }else{
        if($result){
          $this->response["data"] = $result;
        }else{
          $this->response["error"] = "Failed to update entry";
        }
      }
    }
    public function deleteEntry($request){
      $validator = Validator::make($request, ["id" => "required"]);
      if ($validator->fails()) {
        $this->response['debug'] = $request["id"]."asdasd";
          $this->response["error"]["status"] = 100;
          $this->response["error"]["message"] = $validator->errors()->toArray();
          return false;
      }
      $this->response['debug'] = $request["id"];
      $this->response["data"] = $this->model->destroy($request["id"]);
    }
    public function addCondition($conditions){
      /*
        column, clause, value
      */
      if($conditions){
        foreach($conditions as $condition){
          /*Table.Column, Clause, Value*/
          $condition["clause"] = (isset($condition["clause"])) ? $condition["clause"] : "=";
          $condition["value"] = (isset($condition["value"])) ? $condition["value"] : null;
          switch($condition["clause"]){
            default :
              $this->model = $this->model->where($condition["column"], $condition["clause"], $condition["value"]);
          }
        }
      }
    }
    public function addOrderBy($sort)
    {
      foreach($sort as $column => $order){
        $this->model = $this->model->orderBy($column, $order);
      }
    }
}
