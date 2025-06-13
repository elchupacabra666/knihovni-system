<?php
  require_once 'inc/user.php';

$errors = [];
$success = '';

if (!empty($_POST['email'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
      $errors['email'] = 'Zadejte email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Neplatný email.';
    }

    if (empty($errors)) {
        $query = $db->prepare('SELECT * FROM users WHERE email=:email LIMIT 1;');
        $query->execute([':email' => $email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $code = bin2hex(random_bytes(10));

            $saveQuery=$db->prepare('INSERT INTO forgotten_passwords (user_id, code) VALUES (:user, :code)');
            $saveQuery->execute([
              ':user'=>$user['user_id'],
              ':code'=>$code
            ]);

            $requestQuery=$db->prepare('SELECT * FROM forgotten_passwords WHERE user_id=:user AND code=:code ORDER BY forgotten_password_id DESC LIMIT 1;');
            $requestQuery->execute([
              ':user'=>$user['user_id'],
              ':code'=>$code
            ]);
            $request=$requestQuery->fetch(PDO::FETCH_ASSOC); 

            $resetLink = 'https://eso.vse.cz/~novp45/semestralka/reset-password.php?token=' . urlencode($code);

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "From: noreply@knihovnaSNS.cz\r\n";
            mail($email, 'Obnova hesla', 'Kliknete na tento odkaz pro obnoveni hesla: ' . $resetLink, $headers);
        }
    }
}
$pageTitle = 'Přihlášení';
include './inc/header.php';
?>

  <h2 class="mb-4 mt-3">Obnova zapomenutého hesla</h2>
  <?php
    if (@$_GET['mailed']=='ok'){

      echo '<p class="mb-3">Zkontrolujte svoji e-mailovou schránku a klikněte na odkaz, který vám byl zaslán mailem.</p>';
      echo '<a href="index.php" class="btn btn-light m-2">Zpět na homepage</a>';

    }else{
  ?>
      <form method="post" class="p-3">
        <div class="form-group mb-3">
          <label for="email" class="mb-1">E-mail:</label>
          <input type="email" name="email" id="email" required class="form-control <?php echo ($errors?'is-invalid':''); ?> mb-2"
                 value="<?php echo htmlspecialchars($_POST['email'] ?? '')?>"/>
          <?php
            echo ($errors?'<div class="invalid-feedback">Neplatný e-mail.</div>':'');
          ?>
        </div>
        <button type="submit" class="btn btn-primary m-2">Zaslat e-mail k obnově hesla</button>
        <a href="login.php" class="btn btn-light m-2">Přihlásit se</a>
        <a href="index.php" class="btn btn-light m-2">Zrušit</a>
      </form>
  <?php
    }
  
  include './inc/footer.php';
  ?>