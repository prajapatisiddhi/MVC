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