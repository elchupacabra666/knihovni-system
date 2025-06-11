<?php
  //načteme připojení k databázi a inicializujeme session
  require_once 'inc/user.php';

  if (!empty($_SESSION['user_id'])){
    //uživatel už je přihlášený, nemá smysl, aby se registroval
    header('Location: index.php');
    exit();
  }

  $errors=[];
  if (!empty($_POST)){
    #region zpracování formuláře
    #region kontrola jména

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $newUserPassword = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Kontrola jména
    if (empty($name)) {
      $errors['name'] = 'Zadejte jméno uživatele.';
    } elseif (mb_strlen($name) > 100) {
      $errors['name'] = 'Jméno nesmí být delší než 100 znaků.';
    }

    // Kontrola emailu
    if (empty($email)) {
      $errors['email'] = 'Zadejte email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Neplatný email.';
    } elseif (mb_strlen($email) > 255) {
      $errors['email'] = 'Email nesmí být delší než 255 znaků.';
    } else {
      $checkQuery = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
      $checkQuery->execute([':email' => $email]);
      if ($checkQuery->rowCount() > 0) {
        $errors['email'] = 'Uživatel s tímto emailem již existuje.';
      }
    }

    // Kontrola hesla
    if (empty($newUserPassword)) {
      $errors['password'] = 'Zadejte heslo.';
    } elseif (mb_strlen($newUserPassword) < 8) {
      $errors['password'] = 'Heslo musí mít alespoň 8 znaků.';
    } elseif (!preg_match('/[A-Z]/', $newUserPassword)) {
      $errors['password'] = 'Heslo musí obsahovat alespoň jedno velké písmeno.';
    } elseif (!preg_match('/[0-9]/', $newUserPassword)) {
      $errors['password'] = 'Heslo musí obsahovat alespoň jedno číslo.';
    } elseif (!preg_match('/[\W_]/', $newUserPassword)) {
      $errors['password'] = 'Heslo musí obsahovat alespoň jeden speciální znak.';
    }

    if (empty($password2)) {
      $errors['password2'] = 'Zadejte heslo znovu.';
    } elseif ($newUserPassword !== $password2) {
      $errors['password2'] = 'Hesla se neshodují.';
    }


    #endregion kontrola hesla

    if (empty($errors)){
      //zaregistrování uživatele
      $password=password_hash($_POST['password'],PASSWORD_DEFAULT);

      $query=$db->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, "member");');
      $query->execute([
        ':name'=>$name,
        ':email'=>$email,
        ':password'=>$password
      ]);

      //uživatele rovnou přihlásíme
      $_SESSION['user_id']=$db->lastInsertId();
      $_SESSION['user_name']=$name;

      //přesměrování na homepage
      header('Location: index.php');
      exit();
    }

    #endregion zpracování formuláře
  }


  //vložíme do stránek patičku
  include 'inc/header.php';
  $pageTitle='Registrace nového uživatele';
?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-success text-white text-center">
        <h4 class="card-title mb-0">
          <i class="bi bi-person-plus me-2"></i>Registrace
        </h4>
      </div>
      <div class="card-body p-4">
        <div class="alert alert-info border-0 mb-4">
          <i class="bi bi-info-circle me-2"></i>
          Heslo musí mít alespoň 8 znaků, obsahovat alespoň jedno velké písmeno, jedno číslo a jeden speciální znak.
        </div>

        <form method="post">
          <div class="mb-3">
            <label for="name" class="form-label fw-semibold">
              <i class="bi bi-person me-1"></i>Jméno či přezdívka
            </label>
            <input type="text" name="name" id="name" required 
                   class="form-control <?php echo (!empty($errors['name'])?'is-invalid':''); ?>"
                   value="<?php echo !empty($name)?htmlspecialchars($name):'';?>" 
                   placeholder="Zadejte své jméno"/>
            <?php
              echo (!empty($errors['name'])?'<div class="invalid-feedback">'.$errors['name'].'</div>':'');
            ?>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">
              <i class="bi bi-envelope me-1"></i>E-mail
            </label>
            <input type="email" name="email" id="email" required 
                   class="form-control <?php echo (!empty($errors['email'])?'is-invalid':''); ?>"
                   value="<?php echo !empty($email)?htmlspecialchars($email):'';?>" 
                   placeholder="váš@email.cz"/>
            <?php
              echo (!empty($errors['email'])?'<div class="invalid-feedback">'.$errors['email'].'</div>':'');
            ?>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label fw-semibold">
              <i class="bi bi-lock me-1"></i>Heslo
            </label>
            <input type="password" name="password" id="password" required 
                   class="form-control <?php echo (!empty($errors['password'])?'is-invalid':''); ?>" 
                   placeholder="Zadejte heslo"/>
            <?php
              echo (!empty($errors['password'])?'<div class="invalid-feedback">'.$errors['password'].'</div>':'');
            ?>
          </div>

          <div class="mb-4">
            <label for="password2" class="form-label fw-semibold">
              <i class="bi bi-lock-fill me-1"></i>Potvrzení hesla
            </label>
            <input type="password" name="password2" id="password2" required 
                   class="form-control <?php echo (!empty($errors['password2'])?'is-invalid':''); ?>" 
                   placeholder="Zadejte heslo znovu"/>
            <?php
              echo (!empty($errors['password2'])?'<div class="invalid-feedback">'.$errors['password2'].'</div>':'');
            ?>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success btn-lg">
              <i class="bi bi-person-plus me-2"></i>Registrovat se
            </button>
          </div>
        </form>

        <hr class="my-4">

        <div class="text-center">
          <p class="text-muted mb-3">Již máte účet?</p>
          <div class="d-flex gap-2 justify-content-center">
            <a href="login.php" class="btn btn-outline-primary">
              <i class="bi bi-box-arrow-in-right me-1"></i>Přihlásit se
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i>Zpět
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
  //vložíme do stránek patičku
  include 'inc/footer.php';
?>