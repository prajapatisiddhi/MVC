     <?php if (!empty($row[$col])): ?>
                <img src="upload/<?= htmlspecialchars($row[$col]) ?>" height="50">
            <?php else: ?>
                <span class="text-muted">No Image</span>
            <?php endif; ?>