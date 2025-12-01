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