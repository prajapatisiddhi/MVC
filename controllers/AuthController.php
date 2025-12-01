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