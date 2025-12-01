<?php require_once 'views/include/header.php'; ?>

<?php
$sliders = $sliders ?? [];
?>

<div id="imageSlider" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">
    <?php if (!empty($sliders)): ?>
      <?php foreach ($sliders as $i => $s): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <img src="upload/<?= htmlspecialchars($s['photo']) ?>" 
               class="d-block slider-img" 
               alt="<?= htmlspecialchars($s['name']) ?>">
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="carousel-item active">
        <img src="upload/default.jpg" class="d-block slider-img" alt="Default">
      </div>
    <?php endif; ?>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#imageSlider" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#imageSlider" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<?php require_once 'views/include/footer.php'; ?>
