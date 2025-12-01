<?php
require_once 'models/BaseModel.php';
require_once 'models/Product.php';

class Product extends BaseModel {

    protected $limit = 1;     

    public  $limitOptions = [1, 2, 3, 4, 5]; 

    protected $defaultSort = 'price'; 
 
    protected $defaultOrder = 'DESC';
    
    protected $headerList;
    protected $fields;

    public function __construct() {
        parent::__construct("product");
        $this->fields = FormConfig::$product; 
        $this->headerList = ['id','name','price','category','description','photo'];
    }

    public function delete($id) {
        if($id <= 1) {
            throw new InvalidArgumentException("Invalid ID provided for deletion.");
        }
        
        return parent::delete($id); 
    }

    public function getAllowedSort() {
        return ['id' , 'name']; 
    }

    public function getSearchColumn() {
        return [ 'id','name','price']; 
    }
}
