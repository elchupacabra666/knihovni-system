<?php  require_once 'inc/user.php';

  if ($_SESSION['role'] != 'admin'){
    header('Location: index.php');
    exit();
  }


  $today = date("d.m.y"); 



  include 'inc/header.php';
?>
<form method="post">
    <input type="hidden" name="book_id" id="bookId" value="<?php echo $bookId;?>" />

    <label for="bookSearch">Hledat knihu:</label>
    <input type="text" id="bookSearch" placeholder="Název knihy nebo jméno autora..." autocomplete="off">

    <div id="suggestions" class="suggestions"></div>
    <br>
    <label for="userSearch">Email uživatele:</label>
    <input type="email" id="email" placeholder="Zadejte email uživatele..." autocomplete="off">
    
    <br>

    <p>Výpůčka platí ode dne <?php echo $today?> do <?php echo date("d.m.y", strtotime("+1 month"))?></p>


    <button type="submit" class="btn btn-primary">Uložit</button>
    <a href="index.php" class="btn btn-light">Zrušit</a>
</form>


    </main>
    <script src="assets/search_books.js"></script>
  </body>
</html>