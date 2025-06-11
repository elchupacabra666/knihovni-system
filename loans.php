<?php  require_once 'inc/user.php';

  if ($_SESSION['user_role'] != 'admin' && $_SESSION['user_id'] != $_GET['user']){
    header('Location: index.php');
    exit();
  }

  $currentUserId = $_SESSION['user_id'];

  
  $loanQuery = $db->prepare('SELECT loans.*, books.* FROM loans LEFT JOIN books ON loans.book_id = books.book_id WHERE loans.user_id = :id ORDER BY loans.end_date;');
  $loanQuery->execute([':id' => $_GET['user']]);
  $loans = $loanQuery->fetchAll(PDO::FETCH_ASSOC);

  $userQuery = $db->prepare('SELECT users.* FROM users WHERE users.user_id = :id LIMIT 1;');
  $userQuery->execute([':id' => $_GET['user']]);
  $user = $userQuery->fetch(PDO::FETCH_ASSOC);
  $name = $user['name'];
  $email = $user['email'];
  


  if (!empty($_POST['extend_loan_id']) && $_SESSION['user_role'] == 'admin') {
    // prodlouzeni vypujcky pres post
    $extendLoanId = $_POST['extend_loan_id'];

    $stmt = $db->prepare('UPDATE loans SET end_date = DATE_ADD(end_date, INTERVAL 1 MONTH) WHERE loan_id = :id');
    $stmt->execute([':id' => $extendLoanId]);
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
  }

  if (!empty($_POST['finish_loan_id']) && $_SESSION['user_role'] == 'admin') {
    // prodlouzeni vypujcky pres post
    $finishLoanId = $_POST['finish_loan_id'];
    $q = $db->prepare('SELECT loans.book_id FROM loans WHERE loan_id = :id LIMIT 1');
    $q->execute([':id' => $finishLoanId]);
    $book=$q->fetch(PDO::FETCH_ASSOC);
    $bookId = $book['book_id'];

    $stmt = $db->prepare('UPDATE loans SET returned = 1 WHERE loan_id = :id');
    $stmt->execute([':id' => $finishLoanId]);

    var_dump($loan);

    $stmt = $db->prepare('UPDATE books SET available = 1 WHERE book_id = :book_id');
    $stmt->execute([':book_id' => $bookId]);
    if (0) {header("Location: ".$_SERVER['REQUEST_URI']);
    exit();}
  }

                          
  include 'inc/header.php';  

  
?>
  <h2>Uživatel: <?php echo $name?> (<?php echo $email?>)</h2>

  <h3>Aktivní výpůjčky</h3>
    <?php
      echo '<div>';
      foreach ($loans as $loan) {
        if ($loan['returned'] == 0) {

          echo '<li>';
          echo  'Kniha: '.$loan['title'];
          echo  '<br>';
          echo  'Od: '.date("d.m.Y", strtotime($loan['start_date']));
          echo  '<br>';
          echo  'Do: '.date("d.m.Y", strtotime($loan['end_date']));
          if (strtotime($loan['end_date']) < strtotime(date('Y-m-d'))) {
            echo '<br><p class="text-danger">Kniha ještě nebyla vrácena!</p>';
          }
          echo '</li>';
          
          if ($_SESSION['user_role'] == 'admin') {
            echo '<form method="post" style="display:inline;">
                <input type="hidden" name="extend_loan_id" value="'.$loan['loan_id'].'">
                <button type="submit" class="btn-sm btn-success">Prodloužit</button>
            </form>';
            echo '<form method="post">
                <input type="hidden" name="finish_loan_id" value="'.$loan['loan_id'].'">
                <button type="submit" class="btn-sm btn-danger">Ukončit</button>
            </form>';
          }
        }
      }
      echo '</div>';
    ?>

  <h3>Neaktivní výpůjčky</h3>

    <?php
      echo '<div>';
      foreach ($loans as $loan) {
        if ($loan['returned'] == 1) {

          echo '<li>';
          echo  'Kniha: '.$loan['title'];
          echo  '<br>';
          echo  'Od: '.date("d.m.Y", strtotime($loan['start_date']));
          echo  '<br>';
          echo  'Do: '.date("d.m.Y", strtotime($loan['end_date']));
          echo '</li>';
          
        }
      }
      echo '</div>';
    ?>

    </main>
    <script src="assets/search_books.js"></script>
  </body>
</html>