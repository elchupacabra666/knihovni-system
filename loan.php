<?php  require_once 'inc/user.php';

  if ($_SESSION['role'] != 'admin'){
    header('Location: index.php');
    exit();
  }

  $today = date("d.m.y"); 

  

  $currentUserId = $_SESSION['user_id'];

  $loanId = '';
  $bookId = '';
  $bookName = '';
  $clientEmail = '';
  $clientId = '';
  $startDate = '';
  $endDate = '';
  $lastEdit = '';
  $lastEditBy = '';
  
  #region načtení existující výpůjčky z DB
  if (!empty($_GET['id'])) {
    $loanQuery = $db->prepare('SELECT loans.*, users.email, now() > last_edit_starts_at + INTERVAL 5 MINUTE AS edit_expired 
                              FROM loans LEFT JOIN users ON users.user_id = loans.last_edit_starts_by_user WHERE loan_id = :id LIMIT 1;');
    $loanQuery->execute([':id' => $_GET['id']]);
    if ($loan = $loanQuery->fetch(PDO::FETCH_ASSOC)) {
        // Naplníme pomocné proměnné daty výpůjčky
        $loanId = $loan['loan_id'];
        $bookId = $loan['book_id'];
        $clientId = $loan['user_id'];
        $startDate = $loan['start_date'];
        $endDate = $loan['end_date'];
        $lastEdit = $loan['	last_edit_starts_at'];
        $lastEditBy = $loan['last_edit_starts_by_user'];

        // Načteme email uživatele podle user_id
        $clientQuery = $db->prepare('SELECT email FROM users WHERE user_id = :id LIMIT 1;');
        $clientQuery->execute([':id' => $userId]);
        if ($client = $userQuery->fetch(PDO::FETCH_ASSOC)) {
            $clientEmail = $user['email'];
        } else {
            $clientEmail = ''; // wtf case
        }
        
        if (
           !empty($lastEdit) && 								//toto zboží je právě upravováno
           $lastEditBy != $currentUser &&     	//úpravu provádí jiný než aktuálně přihlášený uživatel
           !$loan['edit_expired']																	  //zámek ještě nevypršel
        ){
          //zobrazíme uživateli informaci o tom, kdo zboží aktuálně upravuje
          die("The goods is currently edited by ".$loan['email']); // TODO: udelat lepeji
        }

        $stmt = $db->prepare("UPDATE loans SET last_edit_starts_at=NOW(), last_edit_starts_by_user=:user WHERE id=:id");
        $stmt->execute([':user'=> $currentUserId, ':id'=> $_GET['id']]);
    } else {
        header('Location: index.php');
        exit();
    }

  }
  #endregion načtení existující výpůjčky z DB


  echo '<p>'.$_POST['bookSearch'].'</p>';
  echo '<p>'.$_POST['loanId'].'</p>';

  $errors=[];
  if (!empty($_POST)){
    #region zpracování formuláře
    if (empty($bookTitle = trim($_POST['bookSearch']))) {
      $errors['bookTitle'] = 'Musíte zadat knihu';
    } 
    $bookQuery=$db->prepare('SELECT * FROM books WHERE title=:book AND available=1 LIMIT 1;'); //asi lepsi pouzit zadavani ISBN
    $bookQuery->execute([        // v realite by tam pouzivali ctecku, ktera automaticky naskenuje carovy kod knihy a karticky usera                          
      ':book'=>$_POST['bookSearch']
    ]);
    if ($bookQuery->rowCount()==0){
      $errors['book']='Zvolená kniha není k dispozici!';
      $bookCountryId='';
    } else {
      $bookId = $book['book_id'];
      $bookName = $book['title'];
    }


    if (empty($clientEmail = trim(@$_POST['userSearch']))) {

    }


    

    if (empty($errors)){
      #region uložení dat

      if ($bookId){
        #region aktualizace 
        $saveQuery=$db->prepare('UPDATE books SET title=:title, description=:description, category_id=:category, image=:image, author=:author, year=:year, country_id=:country
                                  WHERE book_id=:id LIMIT 1;');
        $saveQuery->execute([
          ':title'=>$bookTitle,
          ':description'=>$bookDescription,
          ':category'=>$bookCategoryId,
          ':image'=>$bookImagePath,
          ':author'=>$bookAuthor,
          ':year'=>$bookYear,
          ':country'=>$bookCountryId,
          ':id'=>$bookId
        ]);
        #endregion aktualizace
      }else{
        #region uložení nového
          $saveQuery = $db->prepare('INSERT INTO books (title, description, category_id, image, author, year, country_id) VALUES (:title, :description, :category, :image, :author, :year, :country);');
          $saveQuery->execute([
            ':title' => $bookTitle,
            ':description' => $bookDescription,
            ':category' => $bookCategoryId,
            ':image' => $bookImagePath,
            ':author' => $bookAuthor,
            ':year' => $bookYear,
            ':country' => $bookCountryId
          ]);
        #endregion uložení nového
      }

      #endregion uložení dat
      #region přesměrování
        header('Location: index.php');
        exit();
      #endregion přesměrování
    }
    #endregion zpracování formuláře
  }





  include 'inc/header.php';
?>
<form method="post">
    <input type="hidden" name="loanId" id="loanId" value="<?php echo $loanId;?>" />

    <label for="bookSearch">Hledat knihu:</label>
    <input name="bookSearch" id="bookSearch" type="text" id="bookSearch" placeholder="Název knihy nebo jméno autora..." required
        class="form-control <?php echo (!empty($errors['book'])?'is-invalid':''); ?>" autocomplete="off" 
        value="<?php echo htmlspecialchars(@$bookTitle)?>"></input>
      <?php
        if (!empty($errors['title'])){
          echo '<div class="invalid-feedback">'.$errors['title'].'</div>';
        }
      ?>
    <div id="suggestions" class="suggestions"></div>
    <br>
    <label for="userSearch">Email uživatele:</label>
    <input name="userSearch" type="email" id="userSearch" placeholder="Zadejte email uživatele..." autocomplete="off">
    
    <br>

    <p>Výpůčka platí ode dne <?php echo $today?> do <?php echo date("d.m.y", strtotime("+1 month"))?></p>


    <button type="submit" class="btn btn-primary">Uložit</button>
    <a href="index.php" class="btn btn-light">Zrušit</a>
</form>


    </main>
    <script src="assets/search_books.js"></script>
  </body>
</html>