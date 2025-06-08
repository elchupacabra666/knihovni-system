<?php


require_once 'inc/user.php';


# v pripade vice filtru by bylo lepsi udelat jeden sql dotaz, do kteryho se pripisuje podle parametru v url
# filtry


$conditions = [];
$params = [];

// Search by title
if (!empty($_GET['search'])) {
    $conditions[] = 'books.title LIKE :search';
    $params[':search'] = '%' . $_GET['search'] . '%';
}

// Filter by category
if (!empty($_GET['category'])) {
    $conditions[] = 'books.category_id = :category';
    $params[':category'] = $_GET['category'];
}

// Filter by country
if (!empty($_GET['country'])) {
    $conditions[] = 'books.country_id = :country';
    $params[':country'] = $_GET['country'];
}

$sql = 'SELECT books.*, categories.name, countries.name
        FROM books
        JOIN categories USING (category_id)
        JOIN countries USING (country_id)';

if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY books.title;';

$query = $db->prepare($sql);
$query->execute($params);

$books = $query->fetchAll(PDO::FETCH_ASSOC);



include 'inc/filter.php';


if (!empty($books)) {
    #region výpis knih
    echo '<div class="row">';
    foreach ($books as $book) {
        echo '<div class="book-card" style="width: 8rem;">';
        echo    '<img src=covers/' . htmlspecialchars($book['image']) . ' class="card-img-top">';
        echo    '<div class="card-body">';
        echo      '<h2 class="h6 fw-bold">' . htmlspecialchars($book['title']) . '</h2>';
        if ($_SESSION['user_role'] == 'admin') {
            echo      '<a href="edit.php?id='.$book['book_id'].'" class="btn-sm btn-primary ">Upravit</a>';
        }
        echo    '</div>';
        echo '</div>';
    }
    echo '</div>';
    #endregion výpis příspěvků
} else {
    echo '<div class="alert alert-info">Nebyly nalezeny žádné knihy.</div>';
}
