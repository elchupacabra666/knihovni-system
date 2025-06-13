<?php
  //načteme připojení k databázi a inicializujeme session
  require_once 'inc/user.php';

  if (!empty($_SESSION['user_id'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: index.php');
    exit();
  }

  $errors=[];
  if (!empty($_POST)){
    #region zpracování formuláře
    $userQuery=$db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1;');
    $userQuery->execute([
      ':email'=>trim($_POST['email'])
    ]);
    if ($user=$userQuery->fetch(PDO::FETCH_ASSOC)){

      if (password_verify($_POST['password'],$user['password'])){
        //heslo je platné => přihlásíme uživatele
        $_SESSION['user_id']=$user['user_id'];
        $_SESSION['user_name']=$user['name'];
        $_SESSION['user_role']=$user['role'];
        header('Location: index.php');
        exit();
      }else{
        $errors['email']="Neplatná kombinace emailu a hesla.";
      }

    }else{
      $errors=true;
    }
    #endregion zpracování formuláře
  }

  //vložíme do stránek patičku
  $pageTitle='Přihlášení uživatele';
  include 'inc/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white text-center">
        <h4 class="card-title mb-0">
          <i class="bi bi-box-arrow-in-right me-2"></i>Přihlášení
        </h4>
      </div>
      <div class="card-body p-4">
        <form method="post">
          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">
              <i class="bi bi-envelope me-1"></i>E-mail
            </label>
            <input type="email" name="email" id="email" required 
                   class="form-control <?php echo (!empty($errors)?'is-invalid':''); ?>" 
                   value="<?php echo !empty($_POST['email'])?htmlspecialchars($_POST['email']):'';?>"
                   placeholder="váš@email.cz"/>
            <?php
              echo (!empty($errors['email'])?'<div class="invalid-feedback">'.$errors['email'].'</div>':'');
            ?>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label fw-semibold">
              <i class="bi bi-lock me-1"></i>Heslo
            </label>
            <input type="password" name="password" id="password" required 
                   class="form-control <?php echo ($errors?'is-invalid':''); ?>" 
                   placeholder="Zadejte heslo"/>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
              <i class="bi bi-box-arrow-in-right me-2"></i>Přihlásit se
            </button>
          </div>
        </form>

        <hr class="my-4">

        <div class="text-center">
          <p class="text-muted mb-3">Nemáte účet?</p>
          <a href="password-forget.php" class="btn btn-outline-warning">Zapomenuté heslo</a>
          <div class="d-flex gap-2 justify-content-center">
            <a href="registration.php" class="btn btn-outline-success">
              <i class="bi bi-person-plus me-1"></i>Registrovat se
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