<?php
  
  require_once 'inc/user.php';

  if ($_SESSION['role'] != 'admin'){
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
    }else{
      header('Location: index.php');
      exit();
    }
  }
  #endregion načtení existujícího příspěvku z DB

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
            mkdir($uploadDirectory, 0770, true);
        }
        
        $oldImage = $bookImage;
        $bookImage = uniqid() . '_' . bin2hex(random_bytes(5)) . '.' . $fileExtension;
        $newName = $uploadDirectory . $bookImage;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $newName)) {
            $errors['image'] = 'Nepodařilo se nahrát obrázek.';
        } else if (!empty($oldImage) && file_exists($uploadDirectory . $oldImage)) {
          unlink($uploadDirectory . $oldImage);
        }
      }
    } else if (!$bookId) {
    // If creating a new book and no image uploaded
    $errors['image'] = "Musíte nahrát obrázek knihy.";
} 

    if (empty($errors)){
      #region uložení dat

      if ($bookId){
        #region aktualizace existujícího příspěvku
        $saveQuery=$db->prepare('UPDATE books SET title=:title, description=:description, category_id=:category, image=:image, author=:author, year=:year, country_id=:country
                                  WHERE book_id=:id LIMIT 1;');
        $saveQuery->execute([
          ':title'=>$bookTitle,
          ':description'=>$bookDescription,
          ':category'=>$bookCategoryId,
          ':image'=>$bookImage,
          ':author'=>$bookAuthor,
          ':year'=>$bookYear,
          ':country'=>$bookCountryId,
          ':id'=>$bookId
        ]);
        #endregion aktualizace existujícího příspěvku
      }else{
        #region uložení nového příspěvku
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
        #endregion uložení nového příspěvku
      }

      #endregion uložení dat
      #region přesměrování

        header('Location: index.php');
        exit();

      
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

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $bookId;?>" />

    <div class="form-group">
      <label for="title">Název knihy:</label>
      <input type="text" name="title" id="title" required class="form-control <?php echo (!empty($errors['title'])?'is-invalid':''); ?>" value="<?php echo htmlspecialchars($bookTitle)?>"></input>
      <?php
        if (!empty($errors['title'])){
          echo '<div class="invalid-feedback">'.$errors['title'].'</div>';
        }
      ?>
    </div>

    <div class="form-group">
      <label for="description">Popis knihy:</label>
      <textarea name="description" id="description" required class="form-control <?php echo (!empty($errors['description'])?'is-invalid':''); ?>"><?php echo htmlspecialchars($bookDescription)?></textarea>
      <?php
        if (!empty($errors['description'])){
          echo '<div class="invalid-feedback">'.$errors['description'].'</div>';
        }
      ?>
    </div>

    
    <div class="form-group">
      <label for="year">Rok vydání:</label>
      <input type="number" name="year" id="year" required class="form-control <?php echo (!empty($errors['year'])?'is-invalid':''); ?>" value="<?php echo htmlspecialchars($bookYear); ?>">
      <?php
        if (!empty($errors['year'])){
          echo '<div class="invalid-feedback">'.$errors['year'].'</div>';
        }
      ?>
    </div>

    <div class="form-group">
      <label for="author">Autor:</label>
      <input type="text" name="author" id="author" required class="form-control <?php echo (!empty($errors['author'])?'is-invalid':''); ?>" value="<?php echo htmlspecialchars($bookAuthor); ?>">
      <?php
        if (!empty($errors['author'])){
          echo '<div class="invalid-feedback">'.$errors['author'].'</div>';
        }
      ?>
    </div>

    <div class="form-group">
      <label for="category">Kategorie:</label>
      <select name="category" id="category" required class="form-control <?php echo (!empty($errors['category'])?'is-invalid':''); ?>">
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


    <div class="form-group">
      <label for="country">Země:</label>
      <select name="country" id="country" required class="form-control <?php echo (!empty($errors['country'])?'is-invalid':''); ?>">
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
    <div class="form-group">
      <label for="image">Náhled knížky:</label>
      <?php if (!empty($bookImage)){ ?>
          <div>
              <img src="covers/<?php echo htmlspecialchars($bookImage); ?>">
          </div>
      <?php } ?>
      <input type="file" name="image" id="image" class="form-control <?php echo (!empty($errors['image']) ? 'is-invalid' : ''); ?>" />
      <?php if (!empty($errors['image'])) { ?>
          <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
      <?php }; ?>
    </div>


    <button type="submit" class="btn btn-primary">Uložit</button>
    <a href="index.php" class="btn btn-light">Zrušit</a>
  </form>

<?php
  //vložíme do stránek patičku
  include 'inc/footer.php';