<?php
require_once 'models/BaseModel.php';

class Hobby extends BaseModel {

    protected $limit = 15;

    public  $limitOptions = [10 , 15, 20]; 
    
    protected $defaultSort = 'name';

    protected $defaultOrder = 'ASC';

    protected $headerList;
    protected $fields;

    public function __construct() {
        parent::__construct("hobby");
 
        $this->fields = FormConfig::$hobby;  
        $this->headerList = ['id', 'name'];
    }
  
    public function getAllowedSort() {
        return [ 'id', 'name']; 
    }

    public function getSearchColumn() {
        return [ 'id', 'name']; 
    }
}

