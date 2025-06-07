<?php  require_once 'inc/user.php';

  if ($_SESSION['role'] != 'admin'){
    header('Location: index.php');
    exit();
  }

  $today = date("d.m.y"); 

  
  $bookId = $bookTitle = $clientEmail = '';
  $currentUserId = $_SESSION['user_id'];

  $errors=[];
  if (!empty($_POST)){
    #region zpracování formuláře

    $bookId = trim($_POST['bookId'] ?? '');
    $bookTitle = trim($_POST['bookSearch'] ?? '');

    if (empty($bookId) || empty($bookTitle)) {
        $errors['book'] = 'Musíte vybrat knihu ze seznamu.';
    } else {
        // check jestli je kniha dostupna a jestli exisuje
        $bookQuery = $db->prepare('SELECT * FROM books WHERE book_id = :id AND available = 1 LIMIT 1;');
        $bookQuery->execute([':id' => $bookId]);
        $book = $bookQuery->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            $errors['book'] = 'Zvolená kniha není k dispozici!';
        } elseif (strcasecmp($book['title'], $bookTitle) !== 0) {
            // id nesedi s nazvem (musel to napsat manualne nebo to zkazit)
            $errors['book'] = 'Vybraná kniha neodpovídá názvu. Vyberte knihu ze seznamu.';
        }
    }

    $clientEmail = trim($_POST['userSearch'] ?? '');

    if (empty($clientEmail)) {
      $errors['client'] = 'Musíte zadat email klienta.';
    } else {
      if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['client'] = 'Neplatný formát emailu.';
      } else {
        $userQuery = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1;');
        $userQuery->execute([':email' => $clientEmail]);
        $client = $userQuery->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
          $errors['client'] = 'Klient s tímto emailem neexistuje.';
        }
      }
    }
    

    

    if (empty($errors)){


      #region uložení nového
      $saveQuery = $db->prepare('INSERT INTO loans (user_id, start_date, end_date, returned, book_id, last_edit_starts_at, last_edit_starts_by_user) VALUES (:user_id, :start_date, :end_date, 0, :book_id, NULL, NULL);');
      $saveQuery->execute([
        ':user_id' => $client['user_id'],
        ':start_date' => date('Y.m.d'),
        ':end_date' => date('Y.m.d', strtotime('+1 month')),
        ':book_id' => $bookId,
      ]);

      $updateBook = $db->prepare('UPDATE books SET available = 0 WHERE book_id = :id');
      $updateBook->execute([':id' => $bookId]);
      #endregion uložení nového
      

      header('Location: index.php');
      exit();
    }
    #endregion zpracování formuláře
  }

// v realite by tam pouzivali ctecku, ktera automaticky naskenuje carovy kod knihy a karticky usera  
                          
  include 'inc/header.php';  

  
?>
      <form method="post">

        <input type="hidden" name="bookId" id="bookId" value="<?php echo $bookId;?>" />

        <div class="form-group">
          <label for="bookSearch">Hledat knihu:</label>
          <input name="bookSearch" id="bookSearch" type="text" placeholder="Název knihy nebo jméno autora..." required
            class="form-control <?php echo (!empty($errors['book'])?'is-invalid':''); ?>" autocomplete="off"
            value="<?php echo htmlspecialchars(@$bookTitle)?>">
          <?php
            if (!empty($errors['book'])){
              echo '<div class="invalid-feedback">'.$errors['book'].'</div>';
            }
          ?>
          <div id="suggestions" class="suggestions"></div>
        </div>

        <div class="form-group">
          <label for="userSearch">Email uživatele:</label>
          <input name="userSearch" type="email" id="userSearch" placeholder="Zadejte email klienta..." required autocomplete="off"
            class="form-control <?php echo (!empty($errors['client'])?'is-invalid':''); ?>"
            value="<?php echo htmlspecialchars(@$clientEmail); ?>">
          <?php
            if (!empty($errors['client'])){
              echo '<div class="invalid-feedback">'.$errors['client'].'</div>';
            }
          ?>
        </div>

        <div class="form-group">
          <p>Výpůčka platí ode dne <?php echo $today?> do <?php echo date("d.m.y", strtotime("+1 month"))?></p>
        </div>

        <button type="submit" class="btn btn-primary">Uložit</button>
        <a href="index.php" class="btn btn-light">Zrušit</a>
      </form>


    </main>
    <script src="assets/search_books.js"></script>
  </body>
</html>