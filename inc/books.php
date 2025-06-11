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

$sql = 'SELECT books.*, categories.name as category_name, countries.name as country_name
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
    echo '<div class="row g-4 mt-3">';
    foreach ($books as $book) {
        echo '<div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">';
        echo    '<div class="card h-100 shadow-sm border-0">';
        echo        '<div class="position-relative">';
        echo            '<img src="covers/' . htmlspecialchars($book['image']) . '" class="card-img-top" style="height: 280px; object-fit: cover;" alt="' . htmlspecialchars($book['title']) . '">';
        echo            '<div class="position-absolute top-0 end-0 m-2">';
        echo                '<span class="badge bg-primary">' . htmlspecialchars($book['category_name']) . '</span>';
        echo            '</div>';
        echo        '</div>';
        echo        '<div class="card-body d-flex flex-column p-3">';
        echo            '<h6 class="card-title fw-bold mb-2 text-truncate" title="' . htmlspecialchars($book['title']) . '">' . htmlspecialchars($book['title']) . '</h6>';
        echo            '<p class="card-text text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>' . htmlspecialchars($book['country_name']) . '</p>';
        if (!empty($book['author'])) {
            echo            '<p class="card-text text-muted small mb-3"><i class="bi bi-person me-1"></i>' . htmlspecialchars($book['author']) . '</p>';
        }
        echo            '<div class="mt-auto">';
        if ($_SESSION['user_role'] == 'admin') {
            echo                '<a href="edit.php?id=' . $book['book_id'] . '" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-pencil me-1"></i>Upravit</a>';
        }
        echo            '</div>';
        echo        '</div>';
        echo    '</div>';
        echo '</div>';
    }
    echo '</div>';
    #endregion výpis příspěvků
} else {
    echo '<div class="row mt-4">';
    echo '<div class="col-12">';
    echo '<div class="alert alert-info text-center border-0 shadow-sm">';
    echo '<i class="bi bi-info-circle fs-1 text-info mb-3"></i>';
    echo '<h5>Nebyly nalezeny žádné knihy</h5>';
    echo '<p class="mb-0">Zkuste změnit kritéria vyhledávání nebo filtry.</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>