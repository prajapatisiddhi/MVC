<?php
require_once 'models/BaseModel.php';

class Category extends BaseModel {

    protected $limit = 10;

    public  $limitOptions = [ 10 , 15 , 20 , 25]; 
    
    protected $defaultSort = 'name';

    protected $defaultOrder = 'ASC';

    protected $headerList;
    protected $fields;

    public function __construct() {
        parent::__construct("category");
 
        $this->fields = FormConfig::$category; 
       $this->headerList = ['id', 'name', 'products'];

    }
  
    public function getAllowedSort() {
        return [ 'id', 'name']; 
    }

    public function getSearchColumn() {
        return [ 'id', 'name']; 
    } 
}
