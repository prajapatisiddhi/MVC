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