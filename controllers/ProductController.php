<?php
require_once 'controllers/CrudController.php';
require_once 'models/Product.php';

class ProductController extends CrudController {
    public function __construct() {
        parent::__construct(
            'product', 
            'Product', 
            FormConfig::$product, 
        );
    }
}