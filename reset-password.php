<?php

  require_once 'inc/user.php';

  if (!empty($_SESSION['user_id'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: index.php');
    exit();
  }
  $code = $_GET['token'];
  $invalidCode=false;
  $invalidPassword=false;
  $errors=[];

  if (!empty($_REQUEST) && !empty($code)){
    $query=$db->prepare('SELECT * FROM forgotten_passwords WHERE code=:code LIMIT 1;');
    $query->execute([
      ':code'=>$code,
    ]);

    if ($existingRequest=$query->fetch(PDO::FETCH_ASSOC)){
      //zkontrolujeme, jestli je kód ještě platný
      if (strtotime($existingRequest['created'])<(time()-24*3600)){//kontrola, jestli není kód starší než 24 hodin
        $invalidCode=true;
      }
    } else {
      $invalidCode=true;
    }

    $newUserPassword = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!empty($_POST) && !$invalidCode){

      if (empty($newUserPassword)) {
        $errors['password'] = 'Zadejte heslo.';
      } elseif (mb_strlen($newUserPassword) < 8) {
        $errors['password3'] = 'Heslo musí mít alespoň 8 znaků.';
      } elseif (!preg_match('/[A-Z]/', $newUserPassword)) {
        $errors['password4'] = 'Heslo musí obsahovat alespoň jedno velké písmeno.';
      } elseif (!preg_match('/[0-9]/', $newUserPassword)) {
        $errors['password5'] = 'Heslo musí obsahovat alespoň jedno číslo.';
      } elseif (!preg_match('/[\W_]/', $newUserPassword)) {
        $errors['password6'] = 'Heslo musí obsahovat alespoň jeden speciální znak.';
      }

      if (empty($password2)) {
        $errors['password2'] = 'Zadejte heslo znovu.';
      } elseif ($newUserPassword !== $password2) {
        $errors['password2'] = 'Hesla se neshodují.';
      }


      if (empty($errors) && !$invalidCode){

        $saveQuery=$db->prepare('UPDATE users SET password=:password WHERE user_id=:user LIMIT 1;');
        $saveQuery->execute([
          ':user'=>$existingRequest['user_id'],
          ':password'=>password_hash($_POST['password'], PASSWORD_DEFAULT)
        ]);


        $forgottenDeleteQuery=$db->prepare('DELETE FROM forgotten_passwords WHERE user_id=:user;');
        $forgottenDeleteQuery->execute([':user'=>$existingRequest['user_id']]);


        $userQuery=$db->prepare('SELECT * FROM users WHERE user_id=:user LIMIT 1;');
        $userQuery->execute([
          ':user'=>$existingRequest['user_id']
        ]);
        $user=$userQuery->fetch(PDO::FETCH_ASSOC);

        $_SESSION['user_id']=$user['user_id'];
        $_SESSION['user_name']=$user['name'];
        $_SESSION['user_role']=$user['role'];

        //přesměrování na homepage
        header('Location: index.php');
        exit();
      }

    }
  }

include './inc/header.php';

?>
<div class="container my-4">
    <form method="post" class="p-4 border rounded bg-light shadow-sm" style="max-width:400px;margin:auto;">
        <h2 class="mb-4 text-center">Reset hesla</h2>

        <div class="alert alert-info border-0 mb-4">
          <i class="bi bi-info-circle me-2"></i>
          Heslo musí mít alespoň 8 znaků, obsahovat alespoň jedno velké písmeno, jedno číslo a jeden speciální znak.
        </div>
        <?php if (!$invalidCode): ?>
            <?php
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<div class="alert alert-danger m-2">' . htmlspecialchars($error) . '</div>';
                }
            }
            ?>

            <div class="mb-3">
                <label for="password" class="form-label">Nové heslo</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password2" class="form-label">Potvrzení hesla</label>
                <input type="password" name="password2" id="password2" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">Změnit heslo</button>
        <?php else: ?>
            <p class="text-danger m-2">Kód pro obnovu hesla již není platný.</p>
            <a href="login.php" class="btn btn-outline-secondary m-2 w-100">Zpět na přihlášení</a>
        <?php endif; ?>
    </form>
</div>

<?php include './inc/footer.php';