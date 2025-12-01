<?php
require_once 'controllers/CrudController.php';
require_once 'models/Slider.php';

class SliderController extends CrudController {
   public function __construct() {
        parent::__construct(
            'slider', 
            'Slider', 
            FormConfig::$slider, 
        );
    }

    public function show() {
        $sliders = $this->model->getAll()['data'];
        require 'views/slider/slider.php';
    }
}