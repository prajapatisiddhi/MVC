<?php
require_once 'models/BaseModel.php';

class Slider extends BaseModel {

    protected $limit = 15;

    public  $limitOptions = [10 , 15, 20]; 
    
    protected $defaultSort = 'name';

    protected $defaultOrder = 'ASC';

    protected $headerList;
    protected $fields;

    public function __construct() {
        parent::__construct("slider");
 
        $this->fields = FormConfig::$hobby;  
        $this->headerList = ['id', 'name' , 'photo'];
    }
  
    public function getAllowedSort() {
        return [ 'id', 'name']; 
    }

    public function getSearchColumn() {
        return [ 'id', 'name']; 
    }
}

