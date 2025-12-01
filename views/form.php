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
