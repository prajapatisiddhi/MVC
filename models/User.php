<?php
require_once 'models/BaseModel.php';
require_once 'config/formfield.php';

class Registration extends BaseModel {

    protected $limit = 1;

    public  $limitOptions = [5 , 10 ,15,20,25 , 1]; 

    protected $defaultSort = 'f_name';

    protected $defaultOrder = 'ASC';

    protected $headerList;
    protected $fields;

    public function __construct() {
        parent::__construct("registration"); 
        $this->fields = FormConfig::$registration;  
        $this->headerList = ['id', 'f_name', 'l_name', 'email', 'dob', 'gender', 'hobby', 'photo'];
    }

    public function getAllowedSort() {
        return [ 'id','f_name','l_name','email']; 
    }

    public function getSearchColumn() {
        return [ 'id','f_name','l_name','email']; 
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT id, f_name, l_name, email, password FROM {$this->tableName} WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    } 
}