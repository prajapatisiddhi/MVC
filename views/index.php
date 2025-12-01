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
