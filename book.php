<?php
    //načteme připojení k databázi a inicializujeme session
    require_once 'inc/user.php';


    if (!empty($_GET['id'])){
      $bookQuery=$db->prepare('SELECT b.*, c.name AS category_name, co.name AS country_name FROM books b JOIN categories c ON b.category_id = c.category_id JOIN countries co ON b.country_id = co.country_id WHERE b.book_id = :id LIMIT 1;');
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
        $bookCountryName = $book['country_name'];
        $bookCategoryName = $book['category_name'];
      }else{
        header('Location: index.php');
        exit();
      }
    }

    var_dump($book);


    include 'inc/header.php';
?>

  <p>Kniha</p>
  <?php if (isset($book)): ?>
    <div class="book-details">
      <h2><?php echo htmlspecialchars($bookTitle); ?></h2>
      <?php if (!empty($bookImage)): ?>
        <img src="covers/<?php echo htmlspecialchars($bookImage); ?>" alt="<?php echo htmlspecialchars($bookTitle); ?>" style="max-width:200px;">
      <?php endif; ?>
      <p><strong>Autor:</strong> <?php echo htmlspecialchars($bookAuthor); ?></p>
      <p><strong>Popis:</strong> <?php echo nl2br(htmlspecialchars($bookDescription)); ?></p>
      <p><strong>Rok vydání:</strong> <?php echo htmlspecialchars($bookYear); ?></p>
      <p><strong>Kategorie:</strong> <?php echo htmlspecialchars($bookCategoryName); ?></p>
      <p><strong>Země původu:</strong> <?php echo htmlspecialchars($bookCountryName); ?></p>
    </div>
  <?php endif; ?>

  



<?php
    include 'inc/footer.php';
?>