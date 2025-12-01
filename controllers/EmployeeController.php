<?php
require_once 'controllers/CrudController.php';
require_once 'models/Employee.php';

class EmployeeController extends CrudController {
    public function __construct() {
        parent::__construct(
            'employee',  
            'Employee',  
            FormConfig::$employee, 
        );
        
    }
}