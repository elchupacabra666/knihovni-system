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
          <label for="search"></label>
          <input class="form-control" name="search" id="search" type="search" 
             value="'.htmlspecialchars($_GET['search']).'"placeholder="Zadej název knihy, autora…"  />
          <input type="submit" value="OK" class="d-none" />
          
        </form>';
