<?php
require_once 'inc/user.php'; // include your DB connection

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];

if (!empty($_POST)) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password)) {
        $errors['current_password'] = 'Zadejte aktuální heslo.';
    }
    if (empty($new_password)) {
        $errors['new_password'] = 'Zadejte nové heslo.';
    }

    if (empty($new_password)) {
        $errors['new_password'] = 'Zadejte heslo.';
    } elseif (mb_strlen($new_password) < 8) {
        $errors['new_password'] = 'Heslo musí mít alespoň 8 znaků.';
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $errors['new_password'] = 'Heslo musí obsahovat alespoň jedno velké písmeno.';
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $errors['new_password'] = 'Heslo musí obsahovat alespoň jedno číslo.';
    } elseif (!preg_match('/[\W_]/', $new_password)) {
        $errors['new_password'] = 'Heslo musí obsahovat alespoň jeden speciální znak.';
    }
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Potvrďte nové heslo.';
    }
    if (empty($errors['new_password']) && empty($errors['confirm_password']) && $new_password !== $confirm_password) {
        $errors['confirm_password'] = 'Nová hesla se neshodují.';
    }

    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        $stmt = $db->prepare('SELECT users.password FROM users WHERE user_id = :id');
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $db->prepare('UPDATE users SET password = :password WHERE user_id = :id LIMIT 1');
            $update->execute([
                ':password' => $hashed_password,
                ':id' => $user_id
            ]);
            header('Location: index.php');
            exit();
        } else {
            $errors['current_password'] = 'Aktuální heslo není správné.';
        }
    }
}

include 'inc/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-warning text-dark">
        <h4 class="card-title mb-0">
          <i class="bi bi-key me-2"></i>Změna hesla
        </h4>
      </div>
      <div class="card-body p-4">
        <div class="alert alert-info border-0">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Požadavky na heslo:</strong>
          <ul class="mb-0 mt-2">
            <li>Alespoň 8 znaků</li>
            <li>Alespoň jedno velké písmeno</li>
            <li>Alespoň jedno číslo</li>
            <li>Alespoň jeden speciální znak</li>
          </ul>
        </div>

        <form method="post">
          <div class="mb-3">
            <label for="current_password" class="form-label fw-semibold">
              <i class="bi bi-lock me-1"></i>Aktuální heslo
            </label>
            <input type="password" name="current_password" id="current_password" required
              class="form-control <?php echo (!empty($errors['current_password']) ? 'is-invalid' : ''); ?>"
              placeholder="Zadejte aktuální heslo" />
            <?php
            echo (!empty($errors['current_password']) ? '<div class="invalid-feedback">' . $errors['current_password'] . '</div>' : '');
            ?>
          </div>

          <div class="mb-3">
            <label for="new_password" class="form-label fw-semibold">
              <i class="bi bi-key me-1"></i>Nové heslo
            </label>
            <input type="password" name="new_password" id="new_password" required
              class="form-control <?php echo (!empty($errors['new_password']) ? 'is-invalid' : ''); ?>"
              placeholder="Zadejte nové heslo" />
            <?php
            echo (!empty($errors['new_password']) ? '<div class="invalid-feedback">' . $errors['new_password'] . '</div>' : '');
            ?>
          </div>

          <div class="mb-4">
            <label for="confirm_password" class="form-label fw-semibold">
              <i class="bi bi-check-circle me-1"></i>Potvrzení nového hesla
            </label>
            <input type="password" name="confirm_password" id="confirm_password" required
              class="form-control <?php echo (!empty($errors['confirm_password']) ? 'is-invalid' : ''); ?>"
              placeholder="Zadejte nové heslo znovu" />
            <?php
            echo (!empty($errors['confirm_password']) ? '<div class="invalid-feedback">' . $errors['confirm_password'] . '</div>' : '');
            ?>
          </div>

          <div class="d-flex gap-2 justify-content-end">
            <a href="index.php" class="btn btn-outline-secondary">
              <i class="bi bi-x-circle me-1"></i>Zrušit
            </a>
            <button type="submit" class="btn btn-warning">
              <i class="bi bi-check-circle me-1"></i>Změnit heslo
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'inc/footer.php'; ?>