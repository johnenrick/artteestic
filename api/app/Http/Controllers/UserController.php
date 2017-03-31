<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User as Item;

class UserController extends APIController
{
  function __construct(){
    $this->model = new Item();
    $this->editableForeignTable = array(
      "user_information"
    );
    $this->foreignTable = array(
      "user_type",
      "user_information"
    );
  }
}
