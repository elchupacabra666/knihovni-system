<?php
  
  require_once 'inc/user.php';

  if ($_SESSION['user_role'] != 'admin'){
    header('Location: index.php');
    exit();
  }

  //pomocné proměnné pro přípravu dat do formuláře
  $bookId = '';
  $bookTitle = '';
  $bookDescription = '';
  $bookCategoryId = '';
  $bookImage = '';
  $bookAuthor = '';
  $bookYear = '';
  $bookCountryId = '';
  $bookUpdatedAt = '';

  #region načtení existující knihy z DB
  if (!empty($_GET['id'])){
    $bookQuery=$db->prepare('SELECT * FROM books WHERE book_id=:id LIMIT 1;');
    $bookQuery->execute([':id'=>$_GET['id']]);
    if ($book=$bookQuery->fetch(PDO::FETCH_ASSOC)){
      //naplníme pomocné proměnné daty knihy
      $bookId = $book['book_id'];
      $bookTitle = $book['title'];
      $bookDescription = $book['description'];
      $bookCategoryId = $book['category_id'];
      $bookImage = $book['image'];
      $bookAuthor = $book['author'];
      $bookYear = $book['year'];
      $bookCountryId = $book['country_id'];
      $bookUpdatedAt = $book['updated_at'];
    }else{
      header('Location: index.php');
      exit();
    }
  }
  #endregion načtení existujícího příspěvku z DB

  if (!empty($_POST['delete']) && $bookId) {
    // Smaž obrázek, pokud není defaultní
    if (!empty($bookImage) && $bookImage !== 'default.jpg') {
        $imagePath = __DIR__ . '/covers/' . $bookImage;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    // Smaž knihu z databáze
    $deleteQuery = $db->prepare('DELETE FROM books WHERE book_id = :id');
    $deleteQuery->execute([':id' => $bookId]);
    header('Location: index.php');
    exit();
  }

  $errors=[];
  if (!empty($_POST)){
    #region zpracování formuláře
    #region kontrola kategorie
    if (!empty($_POST['category'])){

      $categoryQuery=$db->prepare('SELECT * FROM categories WHERE category_id=:category LIMIT 1;');
      $categoryQuery->execute([
        ':category'=>$_POST['category']
      ]);
      if ($categoryQuery->rowCount()==0){
        $errors['category']='Zvolená kategorie neexistuje!';
        $bookCategoryId='';
      }else{
        $bookCategoryId=$_POST['category'];
      }

    }else{
      $errors['category']='Musíte vybrat kategorii.';
    }
    #endregion kontrola kategorie

    if (!empty($_POST['country'])){

      $countryQuery=$db->prepare('SELECT * FROM countries WHERE country_id=:country LIMIT 1;');
      $countryQuery->execute([
        ':country'=>$_POST['country']
      ]);
      if ($countryQuery->rowCount()==0){
        $errors['country']='Zvolená země neexistuje!';
        $bookCountryId='';
      }else{
        $bookCountryId=$_POST['country'];
      }

    }else{
      $errors['country']='Musíte vybrat zemi.';
    }

    #region kontrola textu
    $bookTitle=trim(@$_POST['title']);
    if (empty($bookTitle)){
      $errors['title']='Musíte zadat název knihy.';
    } else if (mb_strlen($bookTitle)  > 100) {
      $errors['title']='Název je moc dlouhý.';
    }
    #endregion kontrola textu

    $bookDescription = trim(@$_POST['description']);
    if (empty($bookDescription)){
      $errors['description']='Musíte zadat popis knihy.';
    } else if (mb_strlen($bookDescription) > 65530) {
      $errors['description']='Popis je moc dlouhý.';
    }

    #region kontrola roku vydání
    $bookYear = trim(@$_POST['year']);
    if (empty($bookYear)) {
      $errors['year'] = 'Musíte zadat rok vydání.';
    } else if (!is_numeric($bookYear) || $bookYear < 0 || $bookYear >= 10000) {
      $errors['year'] = 'Zadejte platný rok vydání.';
    }
    #endregion kontrola roku vydání

    #region kontrola autora
    $bookAuthor = trim(@$_POST['author']);
    if (empty($bookAuthor)) {
      $errors['author'] = 'Musíte zadat autora.';
    } else if (mb_strlen($bookAuthor) > 100) {
      $errors['author']='Jméno autora je moc dlouhé.';
    }
    #endregion kontrola autora

    if (!empty($_FILES['image']['name'])) {

      // size check 
      if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
        $errors['image'] = 'Soubor je příliš velký. Maximální velikost je 2MB.';
      }

      // extension check
      if (empty($errors['image'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileMimeType = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($fileExtension, $allowedExtensions) || !in_array($fileMimeType, $allowedMimeTypes)) {
          $errors['image'] = 'Nepovolený formát obrázku.';
        }
      }

      if (empty($errors['image'])) {
        $uploadDirectory = __DIR__ . '/covers/'; 
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }
        
        $oldImage = $bookImage;
        $bookImage = uniqid() . '_' . bin2hex(random_bytes(5)) . '.' . $fileExtension;
        $newName = $uploadDirectory . $bookImage;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $newName)) {
            $errors['image'] = 'Nepodařilo se nahrát obrázek.';
        } else if (!empty($oldImage) && file_exists($uploadDirectory . $oldImage) && $oldImage != "default.jpg") {
          unlink($uploadDirectory . $oldImage);
        }
      }
    } else if (!$bookId) {
    $bookImage = 'default.jpg';
} 

    if (empty($errors)){
      #region uložení dat

      if ($bookId){
        #region aktualizace 
        $saveQuery=$db->prepare('UPDATE books SET title=:title, description=:description, category_id=:category, image=:image, author=:author, year=:year, country_id=:country
                                  WHERE book_id=:id AND updated_at = :updated_at LIMIT 1;');
        $saveQuery->execute([
          ':title'=>$bookTitle,
          ':description'=>$bookDescription,
          ':category'=>$bookCategoryId,
          ':image'=>$bookImage,
          ':author'=>$bookAuthor,
          ':year'=>$bookYear,
          ':country'=>$bookCountryId,
          ':id'=>$bookId,
          ':updated_at'=>$_POST['updated_at']
        ]);

        if ($saveQuery->rowCount() == 0) {
              $checkQuery = $db->prepare('SELECT title, description, category_id, image, author, year, country_id FROM books WHERE book_id = :id');
              $checkQuery->execute([':id' => $bookId]);
              $current = $checkQuery->fetch(PDO::FETCH_ASSOC);

              if (
                  $current &&
                  $current['title'] === $bookTitle &&
                  $current['description'] === $bookDescription &&
                  $current['category_id'] == $bookCategoryId &&
                  $current['image'] === $bookImage &&
                  $current['author'] === $bookAuthor &&
                  $current['year'] == $bookYear &&
                  $current['country_id'] == $bookCountryId
              ) {
                  
                  header('Location: index.php');
                  exit();
              }

          $errors['optimistic_lock'] = 'Kniha byla mezitím změněna jiným uživatelem. Zkuste to znovu.'.htmlspecialchars($_POST['updated_at']);
        }
        #endregion aktualizace
      }else{
        #region uložení nové knihy
          $saveQuery = $db->prepare('INSERT INTO books (title, description, category_id, image, author, year, country_id) VALUES (:title, :description, :category, :image, :author, :year, :country);');
          $saveQuery->execute([
            ':title' => $bookTitle,
            ':description' => $bookDescription,
            ':category' => $bookCategoryId,
            ':image' => $bookImage,
            ':author' => $bookAuthor,
            ':year' => $bookYear,
            ':country' => $bookCountryId
          ]);
        #endregion uložení nové knihy
      }

      #endregion uložení dat
      #region přesměrování
      if (empty($errors)) {
        header('Location: index.php');
        exit();
      }

      
      #endregion přesměrování
    }
    #endregion zpracování formuláře
  }

  //vložíme do stránek hlavičku
  if ($bookId){
    $pageTitle='Úprava knihy';
  }else{
    $pageTitle='Nová kniha';
  }

  include 'inc/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white">
        <h4 class="card-title mb-0">
          <i class="bi bi-<?php echo $bookId ? 'pencil' : 'plus-circle'; ?> me-2"></i>
          <?php echo $bookId ? 'Úprava knihy' : 'Nová kniha'; ?>
        </h4>
      </div>
      <div class="card-body p-4">
        <?php if (!empty($errors['optimistic_lock'])): ?>
          <div class="alert alert-danger"><?php echo $errors['optimistic_lock']; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?php echo htmlspecialchars($bookId);?>" />
          <input type="hidden" name="updated_at" value="<?php echo htmlspecialchars($bookUpdatedAt); ?>" />

          <div class="row g-3">
            <div class="col-md-8">
              <label for="title" class="form-label fw-semibold">
                <i class="bi bi-book me-1"></i>Název knihy
              </label>
              <input type="text" name="title" id="title" required 
                     class="form-control <?php echo (!empty($errors['title'])?'is-invalid':''); ?>" 
                     value="<?php echo htmlspecialchars($bookTitle)?>" 
                     placeholder="Zadejte název knihy">
              <?php
                if (!empty($errors['title'])){
                  echo '<div class="invalid-feedback">'.$errors['title'].'</div>';
                }
              ?>
            </div>

            <div class="col-md-4">
              <label for="year" class="form-label fw-semibold">
                <i class="bi bi-calendar me-1"></i>Rok vydání
              </label>
              <input type="number" name="year" id="year" required 
                     class="form-control <?php echo (!empty($errors['year'])?'is-invalid':''); ?>" 
                     value="<?php echo htmlspecialchars($bookYear); ?>" 
                     placeholder="2024" min="0" max="9999">
              <?php
                if (!empty($errors['year'])){
                  echo '<div class="invalid-feedback">'.$errors['year'].'</div>';
                }
              ?>
            </div>

            <div class="col-12">
              <label for="author" class="form-label fw-semibold">
                <i class="bi bi-person me-1"></i>Autor
              </label>
              <input type="text" name="author" id="author" required 
                     class="form-control <?php echo (!empty($errors['author'])?'is-invalid':''); ?>" 
                     value="<?php echo htmlspecialchars($bookAuthor); ?>" 
                     placeholder="Jméno autora">
              <?php
                if (!empty($errors['author'])){
                  echo '<div class="invalid-feedback">'.$errors['author'].'</div>';
                }
              ?>
            </div>

            <div class="col-12">
              <label for="description" class="form-label fw-semibold">
                <i class="bi bi-text-paragraph me-1"></i>Popis knihy
              </label>
              <textarea name="description" id="description" required rows="4"
                        class="form-control <?php echo (!empty($errors['description'])?'is-invalid':''); ?>" 
                        placeholder="Stručný popis obsahu knihy..."><?php echo htmlspecialchars($bookDescription)?></textarea>
              <?php
                if (!empty($errors['description'])){
                  echo '<div class="invalid-feedback">'.$errors['description'].'</div>';
                }
              ?>
            </div>

            <div class="col-md-6">
              <label for="category" class="form-label fw-semibold">
                <i class="bi bi-tags me-1"></i>Kategorie
              </label>
              <select name="category" id="category" required 
                      class="form-select <?php echo (!empty($errors['category'])?'is-invalid':''); ?>">
                <option value="">Zvolte kategorii</option>
                <?php
                  $categoryQuery=$db->prepare('SELECT * FROM categories ORDER BY name;');
                  $categoryQuery->execute();
                  $categories=$categoryQuery->fetchAll(PDO::FETCH_ASSOC);
                  if (!empty($categories)){
                    foreach ($categories as $category){
                      echo '<option value="'.$category['category_id'].'" '.($category['category_id']==$bookCategoryId?'selected="selected"':'').'>'.htmlspecialchars($category['name']).'</option>';
                    }
                  }
                ?>
              </select>
              <?php
                if (!empty($errors['category'])){
                  echo '<div class="invalid-feedback">'.$errors['category'].'</div>';
                }
              ?>
            </div>

            <div class="col-md-6">
              <label for="country" class="form-label fw-semibold">
                <i class="bi bi-globe me-1"></i>Země
              </label>
              <select name="country" id="country" required 
                      class="form-select <?php echo (!empty($errors['country'])?'is-invalid':''); ?>">
                <option value="">Zvolte zemi</option>
                <?php
                  $countryQuery=$db->prepare('SELECT * FROM countries ORDER BY name;');
                  $countryQuery->execute();
                  $countries=$countryQuery->fetchAll(PDO::FETCH_ASSOC);
                  if (!empty($countries)){
                    foreach ($countries as $country){
                      echo '<option value="'.$country['country_id'].'" '.($country['country_id']==$bookCountryId?'selected="selected"':'').'>'.htmlspecialchars($country['name']).'</option>';
                    }
                  }
                ?>
              </select>
              <?php
                if (!empty($errors['country'])){
                  echo '<div class="invalid-feedback">'.$errors['country'].'</div>';
                }
              ?>
            </div>

            <div class="col-12">
              <label for="image" class="form-label fw-semibold">
                <i class="bi bi-image me-1"></i>Náhled knihy
              </label>
              <?php if (!empty($bookImage)){ ?>
                <div class="mb-3">
                  <div class="border rounded p-2 bg-light d-inline-block">
                    <img src="covers/<?php echo htmlspecialchars($bookImage); ?>" 
                         style="max-width:150px; max-height:200px; object-fit: cover;" 
                         alt="Náhled obálky" class="rounded">
                  </div>
                  <div class="text-muted small mt-1">Aktuální obrázek</div>
                </div>
              <?php } ?>
              <input type="file" name="image" id="image" accept="image/*"
                     class="form-control <?php echo (!empty($errors['image']) ? 'is-invalid' : ''); ?>" />
              <div class="form-text">
                <i class="bi bi-info-circle me-1"></i>
                Podporované formáty: JPG, PNG, GIF, WebP. Maximální velikost: 2MB
                <?php if (!$bookId): ?>
                  <br><i class="bi bi-lightbulb me-1"></i>
                  Pokud nenahraje obrázek, použije se výchozí náhled
                <?php endif; ?>
              </div>
                <?php
                if (!empty($errors['image'])) {
                  echo '<div class="invalid-feedback">' . $errors['image'] . '</div>';
                }
                ?>
            </div>
          </div>

          <hr class="my-4">
          <div class="d-flex justify-content-between align-items-center">
                
            <?php if ($bookId): ?>
            <button type="submit" class="btn btn-danger" name="delete" value="1">
              <i class="bi bi-x me-1"></i>
              Smazat knihu
            </button>
            <?php endif; ?>
          
                
            <div class="d-flex gap-2 justify-content-end">
              <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Zrušit
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>
                <?php echo $bookId ? 'Aktualizovat' : 'Vytvořit'; ?>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
  //vložíme do stránek patičku
  include 'inc/footer.php';
?>