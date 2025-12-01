## config/formfield.php

```php
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


## helpers/helper.php
<?php  
class Helper {
    public static function headerLink($col, $label, $currentSort, $currentOrder, $search, $module = '', $action = 'list', $limit = 5  , $allowedSort ) {
        if(!in_array($col , $allowedSort)){
            return $label;
        }
        $newOrder = 'ASC';
        if ($currentSort === $col) {
            $newOrder = ($currentOrder === 'ASC') ? 'DESC' : 'ASC';
        }
        $q = "?module=" . urlencode($module) 
            . "&action=" . urlencode($action) 
            . "&sort=" . urlencode($col) 
            . "&order=" . urlencode($newOrder);
        if ($search !== '') {
            $q .= "&search=" . urlencode($search);
        }
        if (!empty($limit)) {
            $q .= "&limit=" . urlencode($limit);
        }
        $q .= "&page=1";
        return "<a href='{$q}' class='text-decoration-none'>{$label}</a>";
    }
}
?>

## controllers/BaseController.php
<?php
class BaseController {
    protected $model;  
    protected $fields; 
    protected $viewForm; 
    protected $viewIndex; 
    protected $redirectBase; 
    protected $moduleName; 

    public function index() {
        $result = $this->model->getAll();
        extract($result);

        $module       = $this->moduleName ?? ($_GET['module'] ?? '');

        require $this->viewIndex;
    }

        public function delete() {
            if (isset($_GET['id'])) {
                $this->model->delete($_GET['id']);
                header("Location: {$this->redirectBase}&msg=deleted");
            } else {
                header("Location: {$this->redirectBase}&msg=error");
            }
            exit;
        }
        
   protected function validate($data, $id) {
        $errors = [];
        foreach ($this->fields as $name => $config) {
            if ($config['type'] === 'file' && empty($data[$name]) && !$id) {
                $errors[$name] = "Please upload {$config['label']}.";
            } elseif ($config['type'] === 'checkbox' && empty($data[$name])) {
                $errors[$name] = "Please select at least one {$config['label']}.";
            } elseif (!in_array($config['type'], ['file', 'checkbox', 'radio']) && empty(trim($data[$name] ?? ''))) {
                $errors[$name] = "Please enter {$config['label']}.";
            }
        }
        return $errors;
    }
}

## controllers/UserController.php
<?php
require_once 'controllers/CrudController.php';
require_once 'models/User.php';

class UserController extends CrudController {
    public function __construct() {
        parent::__construct(
            'registration', 
            'Registration', 
            FormConfig::$registration, 
        );
    }

    public function save() {
        $id = $_REQUEST['id'] ?? null; 
        $data = []; 
        $errors = []; 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST; 

            $data = $this->handleFileUploads($data, $id);

            if (isset($data['existing_photo']) && !empty($data['existing_photo'])) {
                $data['photo'] = $data['existing_photo'];
            }
            unset($data['existing_photo']);

            $errors = $this->validate($data, $id);

            // Password hash for registration
            $pwd = $data['password'] ?? '';
            if ($id && $pwd === '') {
                // keep current password
            } elseif ($pwd !== '') {
                $data['password'] = password_hash($pwd, PASSWORD_DEFAULT);
            }

            if (empty($errors)) {
                $final = [];
                foreach ($this->fields as $name => $config) {
                    $val = $data[$name] ?? null; 
                    if ($config['type'] === 'checkbox' && is_array($val)) {
                        $val = implode(',', $val);
                    }
                    if ($name === 'password' && $id && ($val === '' || $val === null)) continue;
                    $final[$name] = $val;
                }

                $this->model->save($final, $id);

                $msg = $id ? 'updated' : 'added';
                header("Location: {$this->redirectBase}&msg={$msg}");
                exit;
            }
        } elseif ($id) {
            $data = $this->model->getById($id);
        }

        $fields = $this->fields;
        $module = $this->moduleName ?? ($_GET['module'] ?? ''); 
        require $this->viewForm;
    }

    protected function handleFileUploads($data, $id = null) {
        foreach ($this->fields as $name => $config) { 
            if ($config['type'] === 'file') { 
                if (!empty($_FILES[$name]['name'])) { 
                   $folder = "upload/" . $this->moduleName;   

                    if (!is_dir($folder)) { 
                        mkdir($folder); 
                    }
                    $filename = time() . '_' . basename($_FILES[$name]['name']);
                    $targetPath = $folder . "/" . $filename; 
                    move_uploaded_file($_FILES[$name]['tmp_name'], $targetPath);
                    $data[$name] = $this->moduleName . "/" . $filename;
                } elseif ($id) {
                    $data[$name] = $_POST['old' . $name] ?? '';
                }
            }
        }
        return $data;
    }
}

## controllers/EmployeeController.php
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

## controllers/ProductController.php
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

## controllers/HobbyController.php
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

## controllers/CategoryController.php
<?php
require_once 'controllers/CrudController.php';
require_once 'models/Category.php';
require_once 'models/Product.php';

class CategoryController extends CrudController {
   public function __construct() {
        parent::__construct(
            'category', 
            'Category', 
            FormConfig::$category, 
        );
    }

   public function save() {
        $id = $_REQUEST['id'] ?? null; 
        $data = []; 
        $errors = []; 

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST; 

            $final = ['name' => $data['name']];

            $this->model->save($final , $id);


             if (!$id) {
                // $categoryModel = new Category(); 
                // $conn = $categoryModel->getConnection();
                $id = $conn->insert_id;
            }

            $selectedProducts = $data['products'] ?? [];
            $this->updateProductsCategory($id , $selectedProducts);

            $msg = $id ? 'updated' : 'added';
                header("Location: {$this->redirectBase}&msg={$msg}");
                exit;
        }
        elseif($id){
            $data = $this->model->getById($id);

            $data['products'] = $this->getProductCategory($id);
        }

        $fields = $this->fields;
        $module = $this->moduleName ?? ($_GET['module'] ?? ' ');

        
        // $productModel = new Product();
        // $allProducts = $productModel->getAll(); 
        require $this->viewForm;
    }

    private function updateProductsCategory($categoryId , $selectedProducts){

        $productModel = new Product();
        $conn = $productModel->getConnection();

        //replace ae mysql no function 6e string if null (category) means category ma ? vado part aave to tene remove kari dese
        //String na data ne modify kare 6e 
        // $stmt = $conn->prepare("UPDATE product SET category = REPLACE(IFNULL (category , ' ' ) , ? , ' ' )");
        $stmt = $conn->prepare("UPDATE product SET category = REPLACE(category , ? , ' ')");
        $cid = (string)$categoryId;
        $stmt->bind_param("s" , $cid);
        $stmt->execute();

        foreach($selectedProducts as $pid){
            $stmt = $conn->prepare("SELECT category FROM product WHERE id = ?");
            $stmt->bind_param("i" , $pid);
            $stmt->execute();
            $catstr = $stmt->get_result()->fetch_assoc()['category'] ?? '';

            $cats = array_filter(explode(',' , $catstr));
            if(!in_array($categoryId , $cats)){
                $cats[] = $categoryId;
            }

            $newCats = implode(',' , $cats);

            $stmt= $conn->prepare("UPDATE product SET category=? WHERE id=?");
            $stmt->bind_param("si" , $newCats , $pid);
            $stmt->execute();
        }
    }

    private function getProductCategory($categoryId){

        $productModel = new Product();
        $conn = $productModel->getConnection();
        $selected = [];

        $stmt = $conn->prepare("SELECT id , category FROM product");
        $stmt->execute();

        $res= $stmt->get_result();
        while($row = $res->fetch_assoc()){
            $cats = explode(',' , $row['category'] ?? '');
            if(in_array($categoryId , $cats)){
                $selected[] = $row['id'];
            }
        }
        return $selected;
    }
}
## controllers/SliderController.php
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

## controllers/CrudController.php
<?php
require_once 'controllers/BaseController.php';
require_once 'config/formfield.php';

class CrudController extends BaseController {
    public function __construct($module, $modelClass, $fields) {
        $this->model        = new $modelClass();  
        $this->fields       = $fields;  
        $this->moduleName   = $module; 
        $this->viewForm     = 'views/form.php';
        $this->viewIndex    = 'views/index.php';
        $this->redirectBase = "index.php?module=$module&action=index"; 
    }

        public function save() {
        $id = $_REQUEST['id'] ?? null;
        $data = [];
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data = $this->handleFileUploads($data, $id);

            if (isset($data['existing_photo']) && !empty($data['existing_photo'])) {
                $data['photo'] = $data['existing_photo'];
            }
            unset($data['existing_photo']);
            
            $errors = $this->validate($data, $id);

            if (empty($errors)) {
                
                $final = [];
                foreach ($this->fields as $name => $config) {
                    $val = $data[$name] ?? null;
                    if ($config['type'] === 'checkbox' && is_array($val)) {
                        $val = implode(',', $val);
                    }
                    $final[$name] = $val;
                }

                $this->model->save($final, $id); 
  
                $msg = $id ? 'updated' : 'added';
                header("Location: {$this->redirectBase}&msg={$msg}");
                exit;
            }
        } elseif ($id) {
            $data = $this->model->getById($id);
        }

        $fields = $this->fields;
        $module = $this->moduleName ?? ($_GET['module'] ?? '');
        require $this->viewForm;
    }

     protected function handleFileUploads($data, $id = null) {
        foreach ($this->fields as $name => $config) {  
            if ($config['type'] === 'file') {
                if (!empty($_FILES[$name]['name'])) { 
                   $folder = "upload/" . $this->moduleName;   
                    if (!is_dir($folder)) { 
                        mkdir($folder);  
                    }
                    $filename = time() . '_' . basename($_FILES[$name]['name']);
                    $targetPath = $folder . "/" . $filename; 
                    move_uploaded_file($_FILES[$name]['tmp_name'], $targetPath);
                    $data[$name] = $this->moduleName . "/" . $filename;
                } elseif ($id) {
                    $data[$name] = $_POST['old' . $name] ?? '';
                }
            }
        }
        return $data;
    }
}

## controllers/AuthController.php
<?php
require_once 'models/User.php';

class AuthController {
    private $userModel;
    public function __construct() {
        $this->userModel = new Registration(); // User model  object
        if (session_status() === PHP_SESSION_NONE) session_start();
    }
    public function index() {

        require 'views/login/login.php';
    }
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?module=auth&action=index");
            exit;
        }
        $email = trim($_POST['email'] ?? ''); 
        $password = $_POST['password'] ?? ''; 
        if ($email === '' || $password === '') {
            header("Location: index.php?module=auth&action=index&error=Please enter email and password");
            exit;
        }
        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            header("Location: index.php?module=auth&action=index&error=Invalid.if you dont have account please create first.");
            exit;
        }
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => trim(($user['f_name'] ?? '') . ' ' . ($user['l_name'] ?? '')),
            'email' => $user['email']
        ];
        // header("Location: index.php?module=slider&action=index&msg=login_success");
        header("Location: index.php?module=slider&action=show&msg=login_success");
        exit;
    }
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        session_destroy();
        header("Location: index.php?module=auth&action=index&msg=logged_out");
        exit;
    }
}

## models/Database.php
<?php
class Database {
    public $conn;

    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "student");
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}
?>

## models/BaseModel.php
<?php
require_once 'models/Database.php';

class BaseModel{
    protected $conn;
    protected $tableName;
    protected $headerList;
    protected $fields; 
    protected $limit; 
    protected $defaultSort; 
    protected $defaultOrder; 

    public function __construct( $tableName) {
        $db = new Database();
        $this ->conn = $db->conn;
        $this->tableName = $tableName;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->tableName} WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function save($data, $id = null) {

        $cols = array_keys($data); 
        $vals = array_values($data); 
        $set = implode(', ', array_map(fn($col) => "$col = ?", $cols));  

        if ($id) {
            // update
            $sql = "UPDATE {$this->tableName} SET $set WHERE id = ?";
            $types = str_repeat('s', count($vals)) . 'i'; 
            $vals[] = $id;
        } else {
            $types = str_repeat('s', count($vals)); 
            $sql = "INSERT INTO {$this->tableName} SET $set"; 
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$vals); 
        return $stmt->execute();
    }

   public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->tableName} WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }


    public function getList() { 
        

        $limit = $this->getLimit();
        
        $search = $this->getSearch();

        $sort = $_GET['sort'] ?? $this->defaultSort;
        $order = strtoupper($_GET['order'] ?? $this->defaultOrder); 
        $page = max(1, intval($_GET['page'] ?? 1)); 
        $offset = ($page - 1) * $limit; 

          if (!in_array($sort, $this->getAllowedSort())) {
            $sort = 'id'; 
        }


        $order = ($order === 'DESC') ? 'DESC' : 'ASC'; 
        $where = "";
        $params = [];
        $types = "";

        if ($search !== '') {
            $searchItem = "%$search%";
            $whereCols = $this->getSearchColumn();
            $where = "WHERE " . implode(" OR ", array_map(fn($col) => "$col LIKE ?", $whereCols));
            $params = array_fill(0, count($whereCols), $searchItem);
            $types = str_repeat('s', count($params));
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM {$this->tableName} {$where}");
        if ($where) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $totalrow = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt->close();

        $totalPage = max(1, ceil($totalrow / $limit));
        $sql = "SELECT * FROM {$this->tableName} {$where} ORDER BY {$sort} {$order} LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        if ($where) {
            $stmt->bind_param($types . 'ii', ...array_merge($params, [$limit, $offset]));
        } else {
            $stmt->bind_param('ii', $limit, $offset);
        }
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return compact('data', 'totalPage', 'page', 'search', 'sort', 'order', 'limit');
    }

  public function getAll() {
    $result = $this->getList();
    $data = $result['data'];

    foreach ($data as &$row) {
        foreach ($this->fields as $fieldName => $config) {
            if (!isset($row[$fieldName])) continue;

            // ✅ Checkbox (IDs stored as CSV)
            if ($config['type'] === 'checkbox' && !empty($row[$fieldName])) {
                $ids = explode(',', $row[$fieldName]);
                $labels = [];

                // अगर config में options पहले से है → use it
                if (!empty($config['options'])) {
                    foreach ($ids as $id) {
                        $id = trim($id);
                        $labels[] = $config['options'][$id] ?? $id;
                    }
                } 
                // नहीं है तो related model से निकालो (dynamic)
                else {
                    $relatedTable = $fieldName; // hobby/category/products जैसा
                    $stmt = $this->conn->prepare("SELECT id, name FROM {$relatedTable} WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")");
                    $types = str_repeat('i', count($ids));
                    $stmt->bind_param($types, ...$ids);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($r = $res->fetch_assoc()) {
                        $labels[] = $r['name'];
                    }
                }

                $row[$fieldName] = implode(', ', $labels);
            }

            // ✅ Radio (single ID → Label)
            if ($config['type'] === 'radio' && !empty($row[$fieldName])) {
                $val = $row[$fieldName];
                $row[$fieldName] = $config['options'][$val] ?? $val;
            }
        }

        // ✅ Special Case: Category → Products reverse relation
        if ($this->tableName === "category") {
            $catId = $row['id'];
            $prodnames = [];
            $stmt = $this->conn->prepare("SELECT name FROM product WHERE FIND_IN_SET(?, category)");
            $stmt->bind_param("i", $catId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $prodnames[] = $r['name'];
            }
            $row['products'] = implode(", ", $prodnames);
        }
    }

    $result['data'] = $data;
    return $result;
}


    public function getConnection(){
        return $this->conn;
    }

    public function Header() {
        $headers = [];
        foreach ($this->headerList as $col) {
            if ($col === 'id') {
                $headers['id'] = 'ID';
                continue;
            }
            $label = $this->fields[$col]['label'] ?? ucfirst(str_replace('_',' ', $col));
            $headers[$col] = $label;
        }
        return $headers;
    }
    
    public function getLimit() {
        
        $module = $this->tableName;

        if (isset($_GET['limit'])) {
            $_SESSION[$module]['limit'] = (int)$_GET['limit'];
        }

        if (!isset($_SESSION[$module]['limit'])) {
            $_SESSION[$module]['limit'] = $this->limit; 

        }
    
        $limit = $_SESSION[$module]['limit'];
    
        $options = $this->limitOptions;

        if (!in_array($limit, $options)) {
            $limit = $this->limit;
            $_SESSION[$module]['limit'] = $limit;
        }
    
        return $limit;
    }

    public function getSearch() {
        $searchKey = $this->tableName . "_search";

        if (isset($_GET[$searchKey])) {
            $_SESSION[$searchKey] = trim($_GET[$searchKey]);
        }

        if (!isset($_SESSION[$searchKey])) {
            $_SESSION[$searchKey] = '';
        }

        return $_SESSION[$searchKey];
    }
}



## models/User.php
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

## models/Employee.php
<?php
require_once 'models/BaseModel.php';

class Employee extends BaseModel {

    protected $limit = 6;
    public  $limitOptions = [10, 20, 30, 40, 5 , 2 , 3 , 6]; 
    protected $defaultSort = 'name';
    protected $defaultOrder = 'ASC';
    protected $headerList;
    protected $fields;
    public function __construct() {
        parent::__construct("employee");
        $this->fields = FormConfig::$employee;  
        $this->headerList = ['id', 'name', 'email', 'j_date', 'salary', 'gender', 'position', 'photo'];
    }
    public function getAllowedSort() {
        return [ 'name' , 'email' ]; 
    }
    public function getSearchColumn() {
        return [ 'email', 'gender', 'position']; 
    } 
}

## models/Product.php
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

## models/Hobby.php
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

## models/Category.php
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

## models/Slider.php
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


## views/include/header.php
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modular CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="views/assest/css/style.css">
</head>
<body>
    <!-- check kare 6e ke session pela thi chalu 6e ke nyi nathi chalu to chalu kari dese -->
    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php?module=slider&action=show">MVC Model System</a>
            <div class="d-flex">
                <?php if (!empty($_SESSION['user'])): ?> <!-- check kare 6e ke user set 6e ke nyi login thyo 6e ke nyi-->

                    <!-- Slider -->
                    <div class="btn-group me-3">
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            Slider
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?module=slider&action=save">Add Slider</a></li>
                            <li><a class="dropdown-item" href="index.php?module=slider&action=index">List Slider</a></li>
                        </ul>
                    </div>

                    <!-- Registration -->
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            Registration
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?module=registration&action=save">Add User</a></li>
                            <li><a class="dropdown-item" href="index.php?module=registration&action=index">List Users</a></li>
                        </ul>
                    </div>

                    <!-- Employee -->
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                            Employee
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?module=employee&action=save">Add Employee</a></li>
                            <li><a class="dropdown-item" href="index.php?module=employee&action=index">List Employees</a></li>
                        </ul>
                    </div>

                    <!-- Product -->
                    <div class="btn-group me-3">
                        <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown">
                            Product
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?module=product&action=save">Add Product</a></li>
                            <li><a class="dropdown-item" href="index.php?module=product&action=index">List Products</a></li>
                        </ul>
                    </div>

                    <!-- Hobby -->
                    <div class="btn-group me-3">
                        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                            Hobby
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?module=hobby&action=save">Add Hobby</a></li>
                            <li><a class="dropdown-item" href="index.php?module=hobby&action=index">List Hobby</a></li>
                        </ul>
                    </div>

                    <!-- Category -->
                    <div class="btn-group me-3">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                            Category
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?module=category&action=save">Add Category</a></li>
                            <li><a class="dropdown-item" href="index.php?module=category&action=index">List Category</a></li>
                        </ul>
                    </div>

                    <!-- Home -->
                    <!-- <a class="btn btn-outline-light me-2" href="index.php?module=slider&action=show">Home</a> -->

                    <!-- logout -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['user']['name'] ?: $_SESSION['user']['email']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="index.php?module=auth&action=logout">Logout</a></li>
                        </ul>
                    </div>

                    <!-- login & registration -->
                <?php else: ?>
                    <a class="btn btn-outline-light me-2" href="index.php?module=auth&action=index">Login</a>
                    <a class="btn btn-primary" href="index.php?module=registration&action=save">Register</a>
                <?php endif; ?>
            </div>
        </div>
</nav>

<div class="container">
<?php
        //dynamic message array
        $messages = [
            'added'           => ['type' => 'success', 'text' => 'Record added successfully.'],
            'updated'         => ['type' => 'success', 'text' => 'Record updated successfully.'],
            'deleted'         => ['type' => 'success', 'text' => 'Record deleted successfully.'],
            'login_success'   => ['type' => 'success', 'text' => 'Login successful.'],
            'logged_out'      => ['type' => 'info',    'text' => 'Logged out.'],
            'login_required'  => ['type' => 'warning', 'text' => 'Please login to continue.'],
            'registered'      => ['type' => 'success', 'text' => 'Account created successfully. Please login.'],
        ];

        //get the message key from the url daynamic kar diya ise
        $msg = $_GET['msg'] ?? '';
        if ($msg && isset($messages[$msg])) {
            $alert = $messages[$msg];
            echo "<div class='alert alert-{$alert['type']}'>{$alert['text']}</div>";
        }
?>

## views/include/footer.php
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="views/assest/js/script.js"></script>
</body>
</html>

## views/assest/css/style.css
.slider-img {
    width: 100%;
    height: 350px;       
    object-fit: cover;   
    border-radius: 10px;
}

## views/assest/js/script.js
$(function(){

    $("form").on("submit", function(e){ //form tag par submit event listner add karyo 6e jayere user save karse tyare aa function call back thase
        let valid = true; //form valid 6e ke nyi te check karse koi validation fail thase to te false kari dese
        $(".js-error").remove(); //koi duplicate error hoy pela thi tene remove kare e
        $("input, select, textarea").css("border","");

        function showError($field, message, isGroup=false){ // aek function banayu helper 
            //$field jquery no object banayo message error text isGroup radio/checkbox jeva groupt field mate
            valid = false;

            if(isGroup){//radio/checkbox mate
                $field.closest(".mb-3").append(
                    `<div class="text-danger mt-1 js-error">${message}</div>`
                );
                $field.closest(".mb-3").find("label, .form-check").css("color","red"); 
            } else {
                //single field like number , text , textarea
                $field.css("border","1px solid red");
                $field.closest(".mb-3").append(
                    `<div class="text-danger mt-1 js-error">${message}</div>`
                );
            }
        }

        //php ma aapelo aek json 6e je badhi entry par loop fervse
        $.each(window.formConfig, function(name, config){ //field name
            let $f = $(`[name='${name}'], [name='${name}[]']`);
            let rules = config.rules || {}; //rules object na hoy to te empty thayi jase
            let val = "";

            if($f.is(":radio")){
                //radio button mate /name selected 6e to and non selected to () empty
                val = $(`[name='${name}']:checked`).val() || "";
            }
            else if($f.is(":checkbox")){
                val = $(`[name='${name}[]']:checked`).map(function(){return this.value;}).get();
            }
            else if($f.attr("type")==="file"){
                val = $f[0].files.length ? $f[0].files[0] : null; //file choose kari 6e to peli file no object otherwise null
            }
            else{
                val = $f.val() ? $f.val().trim() : "";
            }

            // required
            if(rules.required){
                if($f.is(":checkbox") && val.length===0){
                    showError($f.first(), `${config.label} is required`, true);
                } else if($f.is(":radio") && !val){
                    showError($f.first(), `${config.label} is required`, true);
                } else if($f.attr("type")==="file" && !val){
                    showError($f, `${config.label} is required`);
                } else if(!$f.is(":checkbox") && !$f.is(":radio") && $f.attr("type")!=="file" && !val){
                    showError($f, `${config.label} is required`);
                }
            }

            // minlength
            if(rules.minlength && val && val.length < rules.minlength){
                showError($f, `${config.label} must be at least ${rules.minlength} characters`);
            }

            // numeric + min
            if(rules.numeric && val){
                if(isNaN(val)){
                    showError($f, `${config.label} must be a number`);
                } else if(rules.min && Number(val) < rules.min){
                    showError($f, `${config.label} must be greater than ${rules.min}`);
                }
            }

            // email
            if(rules.email && val){
                let pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!pattern.test(val)){
                    showError($f, "Invalid email format");
                }
            }

            // filetypes
            if(rules.filetypes && val){
                if($.inArray(val.type, rules.filetypes) === -1){
                    showError($f, `${config.label} must be ${rules.filetypes.join(", ")}`);
                }
            }
        });

        if(!valid) e.preventDefault();
    });

});

## views/index.php
<?php
require_once 'views/include/header.php';
require_once 'helpers/helper.php';

//header function and getHeaderList function
$headers = $this->model->Header();

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><?= ucfirst($module) ?> List</h2>
    <a href="index.php?module=<?= $module ?>&action=save" class="btn btn-success">+ Add New</a>
</div>

<form method="get" class="row align-items-center mb-3" action="index.php">
    <!-- Hidden fields -->
    <input type="hidden" name="module" value="<?= $module ?>">
    <input type="hidden" name="action" value="index">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
    <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
    <input type="hidden" name="page" value="<?= $page ?>">
    

    <!-- Search bar -->
    <div class="col-md-6 d-flex">
        <input type="text" name="<?= $module ?>_search" class="form-control me-2"
       placeholder="Search...<?= ucfirst($module) ?>"
       value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
    </div>

    <!-- limit.php file and tenu dropdown  -->
    <?php  require 'limit/limit.php';?>


</form>

<table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
        <tr>
            <?php foreach ($headers as $col => $label): ?>
                <th><?= Helper::headerLink($col, $label, $sort, $order, $search, $module, 'index' , $limit , $this->model->getAllowedSort()) ?></th>

            <?php endforeach; ?>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
            <tr>
                <?php foreach (array_keys($headers) as $col): ?>
                   <td>
                     <?php
                       $config = $this->fields[$col] ?? [];
                       $type = $config['type'] ?? 'text';

                       $file = __DIR__ . "/fields/{$type}.php";
                                           
                       if(file_exists($file)){
                            require $file;
                       }
                       
                       else{
                            require "fields/text.php";
                       }
                     ?>
              </td>
            
                <?php endforeach; ?>
                <td>
                    <a href="index.php?module=<?= $module ?>&action=save&id=<?= $row['id'] ?>" class="btn btn-sm btn-primary" title="edit">Edit</a>
                    <a href="index.php?module=<?= $module ?>&action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="delete">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<nav>
    <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $totalPage; $p++): ?>
            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link"
                   href="?module=<?= $module ?>&action=index&page=<?= $p ?>&sort=<?= urlencode($sort) ?>&order=<?= urlencode($order) ?>&limit=<?= $limit ?><?= $search ? '&search=' . urlencode($search) : '' ?>">
                    <?= $p ?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>

<!-- footer.php -->
<?php require_once 'views/include/footer.php'; ?>


## views/form.php
<?php require_once 'views/include/header.php'; ?>

<h2 class="mb-4"><?= $id ? 'Edit' : 'Add New' ?> <?= ucfirst($module) ?></h2>

<form method="POST" enctype="multipart/form-data" class="border p-4 rounded bg-light">
    <input type="hidden" name="id" value="<?= htmlspecialchars($data['id'] ?? '') ?>">

    <?php foreach ($this->fields as $name => $config): ?>
        <div class="mb-3">
            <label class="form-label fw-bold"><?= $config['label'] ?>:</label>

            <?php if ($config['type'] === 'radio'): ?>
                <?php foreach ($config['options'] as $val => $label): ?>
                    <div class="form-check form-check-inline">
                        <input type="radio"
                               id="<?= $name . '_' . $val ?>"
                               name="<?= $name ?>"
                               value="<?= $val ?>"
                               class="form-check-input"
                               <?= ($data[$name] ?? '') === $val ? 'checked' : '' ?>>
                        <label class="form-check-label" for="<?= $name . '_' . $val ?>"><?= $label ?></label>
                    </div>
                <?php endforeach; ?>

            <?php elseif ($config['type'] === 'checkbox'): ?>
                <?php
                if(!empty($data[$name])){
                    $checkedValues = is_array($data[$name]) ? $data[$name] : explode(',', $data[$name]);
                }else{
                    $checkedValues = [];
                }
                
                foreach ($config['options'] as $val => $label):
                ?>
                    <div class="form-check form-check-inline">
                        <input type="checkbox"
                               id="<?= $name . '_' . $val ?>"
                               name="<?= $name ?>[]"
                               value="<?= $val ?>"
                               class="form-check-input"
                               <?= in_array($val, $checkedValues) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="<?= $name . '_' . $val ?>"><?= $label ?></label>
                    </div>
                <?php endforeach; ?>

            <?php elseif ($config['type'] === 'select'): ?>
                <select name="<?= $name ?>" class="form-control">
                    <option value="">-- Select <?= $config['label'] ?> --</option>
                        <?php foreach ($config['options'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($data[$name] ?? '') == $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            <?php elseif ($config['type'] === 'textarea'): ?>
                <textarea name="<?= $name ?>" class="form-control"><?= htmlspecialchars($data[$name] ?? '') ?></textarea>


            <?php elseif ($config['type'] === 'file'): ?>
                <input type="file" name="<?= $name ?>" class="form-control">
                <?php if (!empty($data[$name])): ?>
                    <img src="upload/<?= htmlspecialchars($data[$name]) ?>" height="80" class="mt-2">
                    <!-- edit na time ae je old photo hoy tej re jato na re aena mate -->
                    <input type="hidden" name="old<?= $name ?>" value="<?= $data[$name] ?>">
                <?php endif; ?>

            <?php else: ?>
                <input type="<?= $config['type'] ?>" name="<?= $name ?>" class="form-control"
                       value="<?= $config['type']==='password' ? '' : htmlspecialchars($data[$name] ?? '') ?>">
                <?php if ($config['type']==='password' && !empty($id)): ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($errors[$name])): ?>
                <div class="text-danger mt-1"><?= $errors[$name] ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <button class="btn btn-primary"><?= $id ? 'Update' : 'Save' ?></button>
    <a href="index.php?module=<?= $module ?>&action=index" class="btn btn-secondary">Cancel</a>
</form>

<script>
    window.formConfig = <?= json_encode($this->fields) ?>;
</script>


<?php require_once 'views/include/footer.php'; ?>

## views/fields/checkbox.php
    <?php
            $vals = !empty($row[$col]) ? explode(',' , $row[$col]) : [] ;
            $labels = [];
            foreach($vals as $v){
                //options ma value exists thase to te label ma conver thayi jase
                if(isset($config['options'][$v])){
                     $labels[] = $config['options'][$v];
                }else{ //use value
                    $labels[] = $v;
                }
            }
            echo htmlspecialchars(implode(',' , $labels));
    ?>

## views/fields/date.php
    <?php
        if(!empty($row[$col])){
            echo date("d-m-y" , strtotime($row[$col]));
        }else{
            echo "-";
        }
    ?>

## views/fields/file.php
        <?php if (!empty($row[$col])): ?>
                <img src="upload/<?= htmlspecialchars($row[$col]) ?>" height="50">
            <?php else: ?>
                <span class="text-muted">No Image</span>
            <?php endif; ?>

## views/fields/radio.php
    <?php
        if(!empty($config['options']) && isset($config['options'][$row[$col]])){
            echo htmlspecialchars($config['options'][$row[$col]]);
        }else{
            echo htmlspecialchars($row[$col]);
        }
    ?>

## views/fields/text.php

<?= htmlspecialchars($row[$col]) ?>

## views/limit/limit.php
<?php
$options = $this->model->limitOptions ;
if (!in_array($limit, $options)) {
    $options[] = $limit;
}
?>
<div class="col-md-3 ms-auto d-flex align-items-center justify-content-end">
    <label for="limit" class="me-2 mb-0">Records per page:</label>
    <select name="limit" id="limit" class="form-select w-auto" onchange="this.form.submit()">
        <?php foreach ($options as $opt): ?>
            <option value="<?= $opt ?>" <?= ($limit == $opt) ? 'selected' : '' ?>>
                <?= $opt ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>


## views/slider/slider.php
<?php require_once 'views/include/header.php'; ?>

<?php
$sliders = $sliders ?? [];
?>

<div id="imageSlider" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">
    <?php if (!empty($sliders)): ?>
      <?php foreach ($sliders as $i => $s): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <img src="upload/<?= htmlspecialchars($s['photo']) ?>" 
               class="d-block slider-img" 
               alt="<?= htmlspecialchars($s['name']) ?>">
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="carousel-item active">
        <img src="upload/default.jpg" class="d-block slider-img" alt="Default">
      </div>
    <?php endif; ?>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#imageSlider" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#imageSlider" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<?php require_once 'views/include/footer.php'; ?>

#views/login/login.php
<?php require_once 'views/include/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-3">Login</h3>

        <?php if (!empty($_GET['error'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?module=auth&action=login">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" >
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" >
          </div>
          <button class="btn btn-primary w-100" >Login</button>
        </form>

        <hr class="my-4">
        <p class="text-muted mb-2">New here?</p>
        <a class="btn btn-outline-secondary w-100" href="index.php?module=registration&action=save">Create your account</a>
      </div>
    </div>
  </div>
</div>

<?php require_once 'views/include/footer.php'; ?>

#views/logout.php
<?php
session_start();
session_destroy();
header("Location: index.php?module=auth&action=index&msg=logged_out");
exit;

##index.php main file:-
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
 
$modules = [
    'auth' => [
        'controller' => 'AuthController',
        'model'      => 'User'  
    ],
    'registration' => [
        'controller' => 'UserController',
        'model'      => 'User'   
    ],
    'employee' => [
        'controller' => 'EmployeeController',
        'model'      => 'Employee'
    ],
    'product' => [
        'controller' => 'ProductController',
        'model'      => 'Product'
    ],
    'category' => [
        'controller' => 'CategoryController',
        'model' => 'Category'
    ],
    'hobby' => [
        'controller' => 'HobbyController',
        'model' => 'Hobby'
    ],
    'slider' => [
        'controller' => 'SliderController',
        'model' => 'Slider'
    ]

];

$module = $_GET['module'] ?? 'auth';
$action = $_GET['action'] ?? 'index';

$isLoggedIn = !empty($_SESSION['user']);

$public = [
    'auth' => ['index', 'login', 'logout'],
    'registration' => ['save']
];

if (!$isLoggedIn) {
    $allowed = isset($public[$module]) && in_array($action, $public[$module], true);
    if (!$allowed) {
        header("Location: index.php?module=auth&action=index&msg=login_required");
        exit;
    }
}

if ($isLoggedIn && $module === 'auth' && $action === 'index') {
    header("Location: index.php?module=registration&action=index");
    exit;
}

if (!isset($modules[$module])) {
    die("Unknown module: " . htmlspecialchars($module));
}

$controllerClass = $modules[$module]['controller'];
$modelClass      = $modules[$module]['model'];

require_once __DIR__ . "/controllers/{$controllerClass}.php";
require_once __DIR__ . "/models/{$modelClass}.php";

if (!class_exists($controllerClass)) {
    die("Controller '$controllerClass' not found.");
}

$controller = new $controllerClass();

if (!method_exists($controller, $action)) {
    die("Unknown action: " . htmlspecialchars($action));
}

$controller->$action();