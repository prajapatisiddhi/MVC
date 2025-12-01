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
