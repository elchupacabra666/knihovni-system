<!DOCTYPE html>
<html lang="cs">

<head>
  <title><?php echo (!empty($pageTitle) ? $pageTitle . ' - ' : '') ?>Knihovna</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="inc/custom.css">
</head>

<body>
  <header class="bg-dark shadow-sm">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="navbar-brand d-flex align-items-center">
          <a href="index.php" class="text-white text-decoration-none">
            <i class="bi bi-book-fill me-2 fs-3"></i>
            <h1 class="h3 mb-0">Knihovna</h1>
          </a>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <div class="navbar-nav ms-auto">
              <?php if (!empty($_SESSION['user_id'])): ?>
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                  <i class="bi bi-person-circle me-2"></i>
                  <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="loans.php?user=<?php echo $_SESSION['user_id']; ?>">
                      <i class="bi bi-journal-bookmark me-2"></i>Výpůjčky uživatele
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="password-change.php">
                      <i class="bi bi-key me-2"></i>Změnit heslo
                    </a>
                  </li>
                  <?php if ($_SESSION['user_role'] == 'admin'): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Administrace</h6></li>
                    <li>
                      <a class="dropdown-item" href="new-loan.php">
                        <i class="bi bi-plus-circle me-2"></i>Nová výpůjčka
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="admin.php">
                        <i class="bi bi-gear me-2"></i>Správce
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="edit.php">
                        <i class="bi bi-book-half me-2"></i>Nová kniha
                      </a>
                    </li>
                  <?php endif; ?>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <a class="dropdown-item text-danger" href="logout.php">
                      <i class="bi bi-box-arrow-right me-2"></i>Odhlásit se
                    </a>
                  </li>
                </ul>
              </div>
              <?php else: ?>
              <a href="login.php" class="nav-link">
                <i class="bi bi-box-arrow-in-right me-2"></i>Přihlásit se
              </a>
              <a href="registration.php" class="nav-link">
                <i class="bi bi-person-plus me-2"></i>Registrovat se
              </a>
            <?php endif; ?>
          </div>
        </div>
      </nav>
    </div>
  </header>
  <main class="container pt-4">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>