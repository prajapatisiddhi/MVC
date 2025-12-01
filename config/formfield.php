<?php 
require_once 'models/Hobby.php';
require_once 'models/Category.php';
require_once 'models/Product.php';

class FormConfig {

    public static $registration = [
        'f_name'   => ['label'=>'First Name','type'=>'text','rules'=>['required'=>true,'minlength'=>2,'maxlength'=>20]],
        'l_name'   => ['label'=>'Last Name','type'=>'text','rules'=>['required'=>true,'minlength'=>2,'maxlength'=>20]],
        'email'    => ['label'=>'Email','type'=>'email','rules'=>['required'=>true,'email'=>true]],
        'password' => ['label'=>'Password','type'=>'password','rules'=>['required'=>true,'minlength'=>6]],
        'dob'      => ['label'=>'Date of Birth','type'=>'date','rules'=>['required'=>true]],
        'hobby'    => ['label'=>'Hobby','type'=>'checkbox','options'=>[],'rules'=>['required'=>true]],
        'gender'   => ['label'=>'Gender','type'=>'radio','options'=>['male'=>'Male','female'=>'Female','other'=>'Other'],'rules'=>['required'=>true]],
        'photo'    => ['label'=>'Photo','type'=>'file','rules'=>['filetypes'=>['image/jpeg','image/png']]]
    ];

    public static $employee = [
        'name'     => ['label'=>'Name','type'=>'text','rules'=>['required'=>true,'minlength'=>2,'maxlength'=>30]],
        'email'    => ['label'=>'Email','type'=>'email','rules'=>['required'=>true,'email'=>true]],
        'j_date'   => ['label'=>'Joining Date','type'=>'date','rules'=>['required'=>true]],
        'salary'   => ['label'=>'Salary','type'=>'number','rules'=>['required'=>true,'numeric'=>true,'min'=>1]],
        'position' => ['label'=>'Position','type'=>'text','rules'=>['required'=>true]],
        'gender'   => ['label'=>'Gender','type'=>'radio','options'=>['male'=>'Male','female'=>'Female','other'=>'Other'],'rules'=>['required'=>true]],
        'photo'    => ['label'=>'Photo','type'=>'file','rules'=>['filetypes'=>['image/jpeg','image/png']]]
    ];

    public static $product = [
        'name'        => ['label'=>'Product Name','type'=>'text','rules'=>['required'=>true,'minlength'=>2]],
        'price'       => ['label'=>'Price','type'=>'number','rules'=>['required'=>true,'numeric'=>true,'min'=>1]],
        'category'    => ['label'=>'Category','type'=>'checkbox','options'=>[],'rules'=>['required'=>true]],
        'description' => ['label'=>'Description','type'=>'textarea','rules'=>['required'=>true,'minlength'=>5]],
        'photo'       => ['label'=>'Product Photo','type'=>'file','rules'=>['filetypes'=>['image/jpeg','image/png']]]
    ];

    public static $slider = [
        'name'  => ['label'=>'Slider Name','type'=>'text','rules'=>['required'=>true]],
        'photo' => ['label'=>'Slider Photo','type'=>'file','rules'=>['filetypes'=>['image/jpeg','image/png']]]
    ];
    
    public static $hobby = [
        'name' => ['label'=>'Hobby','type'=>'text','rules'=>['required'=>true]]
    ];

   
   public static $category = [
        'name' => ['label'=>'Category','type'=>'text',
                            'rules'=>['required'=>true,'minlength'=>2]
                    ],
        'products' => [
            'label' => 'Products',
            'type' => 'checkbox',
            'options' => []
        ]
    ];


    public static function getHobby(){
        $model = new Hobby();
        $hobby = $model->getAll();
        $options = [];
        foreach($hobby['data'] as $row){
            $options[$row['id']] = $row['name'];
        }
        self::$registration['hobby']['options'] = $options;
    }

    public static function getCategory(){
        $catmodel = new Category();
        $category = $catmodel->getAll();
        $options = [];
        foreach($category['data'] as $row){
            $options[$row['id']] = $row['name'];
        }
        self::$product['category']['options'] = $options;
    }

    public static function getProduct(){
        $prodmodel = new Product();
        $product = $prodmodel->getAll();
        $options = [];
        foreach($product['data'] as $row){
            $options[$row['id']] = $row['name'];
        }
        self::$category['products']['options'] = $options;
        // return self::$product['category'];
    }

}

FormConfig::getHobby();
FormConfig::getCategory();
FormConfig::getProduct();

