<?php require_once 'views/include/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-3">Login</h3>

        <?php if (!empty($_GET['error'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?module=auth&action=login">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" >
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" >
          </div>
          <button class="btn btn-primary w-100" >Login</button>
        </form>

        <hr class="my-4">
        <p class="text-muted mb-2">New here?</p>
        <a class="btn btn-outline-secondary w-100" href="index.php?module=registration&action=save">Create your account</a>
      </div>
    </div>
  </div>
</div>

<?php require_once 'views/include/footer.php'; ?>
