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