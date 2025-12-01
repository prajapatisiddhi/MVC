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
