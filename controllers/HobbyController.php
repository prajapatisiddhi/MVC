<?php
require_once 'controllers/CrudController.php';
require_once 'models/Hobby.php';

class HobbyController extends CrudController {
   public function __construct() {
        parent::__construct(
            'hobby', 
            'Hobby', 
            FormConfig::$hobby, 
        );
    }
}