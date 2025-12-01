<?php
require_once 'models/BaseModel.php';

class Employee extends BaseModel {

    protected $limit = 6;

    public  $limitOptions = [10, 20, 30, 40, 5 , 2 , 3 , 6]; 
    
    protected $defaultSort = 'name';

    protected $defaultOrder = 'ASC';

    protected $headerList;
    protected $fields;

    public function __construct() {
        parent::__construct("employee");
        $this->fields = FormConfig::$employee;  
        $this->headerList = ['id', 'name', 'email', 'j_date', 'salary', 'gender', 'position', 'photo'];
    }
  
    public function getAllowedSort() {
        return [ 'name' , 'email' ]; 
    }

    public function getSearchColumn() {
        return [ 'email', 'gender', 'position']; 
    } 
}