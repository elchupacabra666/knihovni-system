<?php 


    # filtr na kategorie
echo '<form method="get" id="filterForm">
        <label for="category">Kategorie:</label>
        <select name="category" id="category" onchange="document.getElementById(\'filterForm\').submit();">
        <option value="">Všechny kategorie</option>';

$categories = $db->query('SELECT * FROM categories ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);
if (!empty($categories)) {
    foreach ($categories as $category) {
        echo '<option value="' . $category['category_id'] . '"'; //u category_id nemusí být ošetření speciálních znaků, protože jde o číslo
        if ($category['category_id'] == @$_GET['category']) {
            echo ' selected="selected" ';
        }
        echo '>' . htmlspecialchars($category['name']) . '</option>';
    }
}

echo '  </select>';

echo '<label for="country">Země:</label>
        <select name="country" id="country" onchange="document.getElementById(\'filterForm\').submit();">
        <option value="">Všechny země</option>';

$countries = $db->query('SELECT * FROM countries;')->fetchAll(PDO::FETCH_ASSOC);
if (!empty($countries)) {
    foreach ($countries as $country) {
        echo '<option value="' . htmlspecialchars($country['country_id']) . '"'; //u country musi byt osetreni
        if ($country['country_id'] == @$_GET['country']) {
            echo ' selected="selected" ';
        }
        echo '>' . htmlspecialchars($country['name']) . '</option>';
    }
}


echo '  </select>
          <label for="bookSearch"></label>
          <input class="form-control" name="search" id="bookSearch" type="text" autocomplete="off" 
             value="'.htmlspecialchars($_GET['search']).'"placeholder="Název knihy nebo jméno autora..."  />
          <input type="hidden" name="book_id" id="bookId" value="<?php echo $bookId;?>" />

          <div id="suggestions" style="border:1px solid #ccc; max-height:150px; overflow-y:auto;"></div>
          
        </form>';

echo    '<script src="assets/search_books.js"></script>';
