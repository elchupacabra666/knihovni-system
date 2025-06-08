<?php
    //načteme připojení k databázi a inicializujeme session
    require_once 'inc/user.php';

    // categories

    $categoryQuery=$db->prepare('SELECT * FROM categories ORDER BY category_id;');
    $categoryQuery->execute();
    $categories=$categoryQuery->fetchAll(PDO::FETCH_ASSOC);

    $categoryName = '';
    $categoryId = '';
    if (!empty($_GET['edit_category'])) {
        $categoryId = $_GET['edit_category'];
        // Find the category with this ID
        foreach ($categories as $cat) {
            if ($cat['category_id'] == $categoryId) {
                $categoryName = $cat['name'];
                break;
            }
        }
    }

    $errors=[];
    // Save category (edit)

    if (!empty($_POST['id']) && !empty($_POST['name'])) {

        $categoryName = trim($_POST['name']);
        $categoryId = $_POST['id'];
        if (empty($categoryName)) {
            $errors['category'] = 'Zadejte název kategorie.';
        } else if (mb_strlen($categoryName) > 100) {
            $errors['category'] = 'Název kategorie nesmí být delší než 100 znaků.';
        } 
        
        if (empty($errors['category'])) {
            $updateQuery = $db->prepare('UPDATE categories SET name = :name WHERE category_id = :id');
            $updateQuery->execute([':name' => $categoryName,
                                   ':id' => $categoryId]);
            header('Location: admin.php?tab=categories&edit_category='.$categoryId);
            exit;
        }

    }

    // Delete category
    if (!empty($_POST['delete_category_id'])) {
        $deleteQuery = $db->prepare('DELETE FROM categories WHERE category_id = :id');
        $deleteQuery->execute([':id' => $_POST['delete_category_id']]);
        header('Location: admin.php?tab=categories');
        exit;
    }

    // přídání kategorie
    if (!empty($_POST['new_category_name'])) {
        $newCategoryName = trim($_POST['new_category_name']);
        if (empty($newCategoryName)) {
            $errors['new_category'] = 'Zadejte název nové kategorie.';
        } else if (mb_strlen($newCategoryName) > 100) {
            $errors['new_category'] = 'Název kategorie nesmí být delší než 100 znaků.';
        } 

        if (empty($errors['new_category'])) {
            $insertQuery = $db->prepare('INSERT INTO categories (name) VALUES (:name)');
            $insertQuery->execute([':name' => $newCategoryName]);
            header('Location: admin.php?tab=categories');
            exit;
        }
    }

    // načtení zemí
    $countryQuery = $db->prepare('SELECT * FROM countries ORDER BY country_id;');
    $countryQuery->execute();
    $countries = $countryQuery->fetchAll(PDO::FETCH_ASSOC);

    $countryName = '';
    $countryId = '';
    if (!empty($_GET['edit_country'])) {
        $countryId = $_GET['edit_country'];
        foreach ($countries as $c) {
            if ($c['country_id'] == $countryId) {
                $countryName = $c['name'];
                break;
            }
        }
    }

    // Uložení změn země
    if (!empty($_POST['country_id']) && !empty($_POST['country_name'])) {
        $countryName = trim($_POST['country_name']);
        $countryId = $_POST['country_id'];
        if (empty($countryName)) {
            $errors['country'] = 'Zadejte název země.';
        } else if (mb_strlen($countryName) > 50) {
            $errors['country'] = 'Název země nesmí být delší než 50 znaků.';
        }

        if (empty($errors['country'])) {
            $updateQuery = $db->prepare('UPDATE countries SET name = :name WHERE country_id = :id');
            $updateQuery->execute([':name' => $countryName, ':id' => $countryId]);
            header('Location: admin.php?tab=countries&edit_country='.$countryId);
            exit;
        }
    }

    // Smazání země
    if (!empty($_POST['delete_country_id'])) {
        $deleteQuery = $db->prepare('DELETE FROM countries WHERE country_id = :id');
        $deleteQuery->execute([':id' => $_POST['delete_country_id']]);
        header('Location: admin.php?tab=countries');
        exit;
    }

    // Přidání nové země
    if (!empty($_POST['new_country_name'])) {
        $newCountryName = trim($_POST['new_country_name']);
        if (empty($newCountryName)) {
            $errors['new_country'] = 'Zadejte název nové země.';
        } else if (mb_strlen($newCountryName) > 50) {
            $errors['new_country'] = 'Název země nesmí být delší než 50 znaků.';
        } 

        if (empty($errors['new_country'])) {
            $insertQuery = $db->prepare('INSERT INTO countries (name) VALUES (:name)');
            $insertQuery->execute([':name' => $newCountryName]);
            header('Location: admin.php?tab=countries');
            exit;
        }
    }


    include 'inc/header.php';

?>

    <div class="container">
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link <?php echo @$_GET['tab']=='categories' || empty(@$_GET['tab'])?'active':''?>" href="?tab=categories" data-toggle="tab">Kategorie</a></li>
            <li class="nav-item"><a class="nav-link <?php echo @$_GET['tab']=='countries'?'active':''?>" href="?tab=countries" data-toggle="tab">Země</a></li>
            <li class="nav-item"><a class="nav-link <?php echo @$_GET['tab']=='users'?'active':''?>" href="?tab=users" data-toggle="tab">Uživatelé</a></li>
        </ul>

        

        <div class="tab-content pt-3">
            <div class="tab-pane fade show <?php echo @$_GET['tab']=='categories' || empty(@$_GET['tab'])?'active':''?>" id="categories">

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Název</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <?php if (!empty($_GET['edit_category']) && $_GET['edit_category'] == $category['category_id']): ?>
                                    <tr>
                                        <form method="post">
                                            <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($category['category_id']); ?>"/>
                                            <td><?php echo $category['category_id']; ?></td>
                                            <td>
                                                <input type="text" id="name" name="name" required class="form-control<?php echo !empty($errors['category']) ? ' is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($categoryName); ?>">
                                                <?php if (!empty($errors['category'])): ?>
                                                    <div class="invalid-feedback"><?php echo $errors['category']; ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="submit" name="save_category" class="btn btn-sm btn-success">Uložit</button>
                                                <a href="?tab=categories" role="button" class="btn btn-sm btn-secondary">Zrušit</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td>
                                            <a role="button" href="?tab=categories&edit_category=<?php echo htmlspecialchars($category['category_id']); ?>" class="btn btn-sm btn-warning">Upravit</a>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Opravdu smazat?');">
                                                <input type="hidden" name="delete_category_id" value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Smazat</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">Žádné kategorie</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Přidání nové kategorie -->
                <form method="post" class="form-inline mb-3">
                    <div class="form-group">
                        <input type="text" name="new_category_name" class="form-control<?php echo !empty($errors['new_category']) ? ' is-invalid' : ''; ?>" placeholder="Nová kategorie" required>
                        <?php if (!empty($errors['new_category'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['new_category']; ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary ml-2">Přidat</button>
                </form>
            </div>
            <div class="tab-pane fade show <?php echo @$_GET['tab']=='countries'?'active':''?>" id="countries">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Název</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($countries)): ?>
                            <?php foreach ($countries as $country): ?>
                                <?php if (!empty($_GET['edit_country']) && $_GET['edit_country'] == $country['country_id']): ?>
                                    <tr>
                                        <form method="post">
                                            <input type="hidden" name="country_id" value="<?php echo htmlspecialchars($country['country_id']); ?>"/>
                                            <td><?php echo $country['country_id']; ?></td>
                                            <td>
                                                <input type="text" name="country_name" required class="form-control<?php echo !empty($errors['country']) ? ' is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($countryName); ?>">
                                                <?php if (!empty($errors['country'])): ?>
                                                    <div class="invalid-feedback"><?php echo $errors['country']; ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="submit" class="btn btn-sm btn-success">Uložit</button>
                                                <a href="?tab=countries" class="btn btn-sm btn-secondary">Zrušit</a>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($country['country_id']); ?></td>
                                        <td><?php echo htmlspecialchars($country['name']); ?></td>
                                        <td>
                                            <a href="?tab=countries&edit_country=<?php echo htmlspecialchars($country['country_id']); ?>" class="btn btn-sm btn-warning">Upravit</a>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Opravdu smazat?');">
                                                <input type="hidden" name="delete_country_id" value="<?php echo htmlspecialchars($country['country_id']); ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Smazat</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">Žádné země</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Přidání nové země -->
                <form method="post" class="form-inline mb-3">
                    <div class="form-group">
                        <input type="text" name="new_country_name" class="form-control<?php echo !empty($errors['new_country']) ? ' is-invalid' : ''; ?>" placeholder="Nová země" required>
                        <?php if (!empty($errors['new_country'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['new_country']; ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary ml-2">Přidat</button>
                </form>
            </div>
            <div class="tab-pane fade show <?php echo @$_GET['tab']=='users'?'active':''?>" id="users">
                <p>users</p>
            </div>
        </div>
    </div>
<?php
    //vložíme do stránek patičku
    include 'inc/footer.php';