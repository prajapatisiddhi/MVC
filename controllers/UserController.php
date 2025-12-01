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