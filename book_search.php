<?php

require_once 'inc/db.php';
header('Content-Type: application/json');

$query = $_GET['query'] ?? '';
$query = trim($query);

if ($query !== '') {
    $bookQuery = $db->prepare('SELECT book_id, title, author, year FROM books WHERE title LIKE :title OR author LIKE :author LIMIT 10');
    $bookQuery->execute([
        ':title'=>'%'.$query.'%',
        ':author'=>'%'.$query.'%'
    ]);
    $result = $bookQuery->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
}