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
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
  }

            
  include 'inc/header.php';  

  
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-info text-white">
        <h4 class="card-title mb-0">
            <i class="bi bi-person-circle me-2"></i>
            <?php echo htmlspecialchars($name); ?>
        </h4>
        <small class="opacity-75">
            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($email); ?>
        </small>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock me-2"></i>Aktivní výpůjčky
                </h5>
            </div>
            <div class="card-body">
                <?php
                $activeLoans = array_filter($loans, function($loan) {
                    return $loan['returned'] == 0;
                });
                
                if (!empty($activeLoans)): ?>
                    <div class="row g-3">
                        <?php foreach ($activeLoans as $loan): ?>
                            <div class="col-12">
                                <div class="card border-start border-warning border-3">
                                    <div class="card-body p-3">
                                        <h6 class="card-title fw-bold">
                                            <i class="bi bi-book me-1"></i>
                                            <?php echo htmlspecialchars($loan['title']); ?>
                                        </h6>
                                        <div class="row text-muted small">
                                            <div class="col-6">
                                                <i class="bi bi-calendar-plus me-1"></i>
                                                Od: <?php echo date("d.m.Y", strtotime($loan['start_date'])); ?>
                                            </div>
                                            <div class="col-6">
                                                <i class="bi bi-calendar-x me-1"></i>
                                                Do: <?php echo date("d.m.Y", strtotime($loan['end_date'])); ?>
                                            </div>
                                        </div>
                                        
                                        <?php if (strtotime($loan['end_date']) < strtotime(date('Y-m-d'))): ?>
                                            <div class="alert alert-danger mt-2 mb-2 py-2">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <small><strong>Kniha ještě nebyla vrácena!</strong></small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                                            <div class="d-flex gap-2 mt-2">
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="extend_loan_id" value="<?php echo $loan['loan_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-arrow-clockwise me-1"></i>Prodloužit
                                                    </button>
                                                </form>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="finish_loan_id" value="<?php echo $loan['loan_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-check-circle me-1"></i>Ukončit
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 mb-2"></i>
                        <p class="mb-0">Žádné aktivní výpůjčky</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-check-circle me-2"></i>Historie výpůjček
                </h5>
            </div>
            <div class="card-body">
                <?php
                $returnedLoans = array_filter($loans, function($loan) {
                    return $loan['returned'] == 1;
                });
                
                if (!empty($returnedLoans)): ?>
                    <div class="row g-3">
                        <?php foreach ($returnedLoans as $loan): ?>
                            <div class="col-12">
                                <div class="card border-start border-secondary border-3">
                                    <div class="card-body p-3">
                                        <h6 class="card-title">
                                            <i class="bi bi-book me-1"></i>
                                            <?php echo htmlspecialchars($loan['title']); ?>
                                        </h6>
                                        <div class="row text-muted small">
                                            <div class="col-6">
                                                <i class="bi bi-calendar-plus me-1"></i>
                                                Od: <?php echo date("d.m.Y", strtotime($loan['start_date'])); ?>
                                            </div>
                                            <div class="col-6">
                                                <i class="bi bi-calendar-check me-1"></i>
                                                Do: <?php echo date("d.m.Y", strtotime($loan['end_date'])); ?>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-success">
                                                <i class="bi bi-check me-1"></i>Vráceno
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-archive fs-1 mb-2"></i>
                        <p class="mb-0">Žádná historie výpůjček</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>