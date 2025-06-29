<?php  require_once 'inc/user.php';

  if ($_SESSION['user_role'] != 'admin') {
    header('Location: index.php');
    exit();
  }

  $loanQuery = $db->prepare('SELECT loans.*, books.*, users.* FROM loans LEFT JOIN books ON loans.book_id = books.book_id LEFT JOIN users ON loans.user_id = users.user_id ORDER BY loans.end_date;');
  $loanQuery->execute();
  $loans = $loanQuery->fetchAll(PDO::FETCH_ASSOC);

    include 'inc/header.php';
?>


<h2>Výpůjčky uživatelů</h2>
<div class="table-responsive">
  <table class="table table-hover">
      <thead class="table-light">
          <tr>
              <th>ID</th>
              <th>Kniha</th>
              <th>Uživatel</th>
              <th>Od</th>
              <th>Do</th>
              <th>Stav</th>
          </tr>
      </thead>
      <tbody>
          <?php if (!empty($loans)): ?>
              <?php foreach ($loans as $loan): ?>
                  <tr>
                      <td><?php echo htmlspecialchars($loan['loan_id']); ?></td>
                      <td><?php echo htmlspecialchars($loan['title']); ?></td>
                      <td><?php echo htmlspecialchars($loan['name']). ' - '. htmlspecialchars($loan['email']); ?></td>
                      <td><?php echo date("d.m.Y", strtotime($loan['start_date'])); ?></td>
                      <td><?php echo date("d.m.Y", strtotime($loan['end_date'])); ?></td>
                      <td>
                          <span class="badge <?php echo $loan['returned'] == 0 ? 'bg-danger' : 'bg-success'; ?>">
                              <?php echo $loan['returned']?'Aktivní':'Vráceno' ;?>
                          </span>
                      </td>
                  </tr>
              <?php endforeach; ?>
          <?php else: ?>
              <tr><td colspan="6" class="text-center text-muted">Žádné výpůjčky</td></tr>
          <?php endif; ?>
      </tbody>
      
  </table>
</div>









<?php include 'inc/footer.php';

