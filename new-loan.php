<?php  require_once 'inc/user.php';

  if ($_SESSION['user_role'] != 'admin'){
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

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-success text-white">
        <h4 class="card-title mb-0">
          <i class="bi bi-plus-circle me-2"></i>Nová výpůjčka
        </h4>
      </div>
      <div class="card-body p-4">
        <form method="post">
          <input type="hidden" name="bookId" id="bookId" value="<?php echo $bookId;?>" />

          <div class="row g-4">
            <div class="col-12">
              <label for="bookSearch" class="form-label fw-semibold">
                <i class="bi bi-book me-1"></i>Vyhledat knihu
              </label>
              <div class="position-relative">
                <input name="bookSearch" id="bookSearch" type="text" 
                       placeholder="Název knihy nebo jméno autora..." required
                       class="form-control <?php echo (!empty($errors['book'])?'is-invalid':''); ?>" 
                       autocomplete="off" value="<?php echo htmlspecialchars(@$bookTitle)?>">
                <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                <?php
                  if (!empty($errors['book'])){
                    echo '<div class="invalid-feedback">'.$errors['book'].'</div>';
                  }
                ?>
                <div id="suggestions" class="suggestions position-absolute w-100 bg-white border rounded-bottom shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
              </div>
            </div>

            <div class="col-12">
              <label for="userSearch" class="form-label fw-semibold">
                <i class="bi bi-person me-1"></i>Email uživatele
              </label>
              <div class="position-relative">
                <input name="userSearch" type="email" id="userSearch" 
                       placeholder="Zadejte email klienta..." required autocomplete="off"
                       class="form-control <?php echo (!empty($errors['client'])?'is-invalid':''); ?>"
                       value="<?php echo htmlspecialchars(@$clientEmail); ?>">
                <i class="bi bi-envelope position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                <?php
                  if (!empty($errors['client'])){
                    echo '<div class="invalid-feedback">'.$errors['client'].'</div>';
                  }
                ?>
              </div>
            </div>

            <div class="col-12">
              <div class="alert alert-info border-0">
                <i class="bi bi-calendar-event me-2"></i>
                <strong>Doba výpůjčky:</strong> 
                Od <?php echo $today?> do <?php echo date("d.m.y", strtotime("+1 month"))?>
                <small class="d-block mt-1 text-muted">
                  Standardní doba výpůjčky je 1 měsíc
                </small>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="d-flex gap-2 justify-content-end">
            <a href="index.php" class="btn btn-outline-secondary">
              <i class="bi bi-x-circle me-1"></i>Zrušit
            </a>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-circle me-1"></i>Vytvořit výpůjčku
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="assets/search_books.js"></script>

<?php
  include 'inc/footer.php';
?>