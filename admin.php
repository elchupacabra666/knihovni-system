<?php
    //načteme připojení k databázi a inicializujeme session
    require_once 'inc/user.php';

    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
    
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

    if (!empty($_POST['category_id']) && !empty($_POST['category_name'])) {

        $categoryName = trim($_POST['category_name']);
        $categoryId = $_POST['category_id'];
        if (empty($categoryName)) {
            $errors['category'] = 'Zadejte název kategorie.';
        } else if (mb_strlen($categoryName) > 100) {
            $errors['category'] = 'Název kategorie nesmí být delší než 100 znaků.';
        } else {
            //duplo
            $checkQuery = $db->prepare('SELECT * FROM categories WHERE name = :name LIMIT 1');
            $checkQuery->execute([':name' => $categoryName]);
            if ($checkQuery->rowCount() > 0) {
                $errors['category'] = 'Tato kategorie již existuje.';
            }
        }
        
        if (empty($errors['category'])) {
            $updateQuery = $db->prepare('UPDATE categories SET name = :name WHERE category_id = :id');
            $updateQuery->execute([':name' => $categoryName,
                                   ':id' => $categoryId]);
            header('Location: admin.php?tab=categories');
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
        } else {
    
            $checkQuery = $db->prepare('SELECT * FROM categories WHERE name = :name LIMIT 1');
            $checkQuery->execute([':name' => $newCategoryName]);
            if ($checkQuery->rowCount() > 0) {
                $errors['new_category'] = 'Tato kategorie již existuje.';
            }
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
        } else {
    
            $checkQuery = $db->prepare('SELECT * FROM countries WHERE name = :name LIMIT 1');
            $checkQuery->execute([':name' => $countryName]);
            if ($checkQuery->rowCount() > 0) {
                $errors['new_country'] = 'Tato země již existuje.';
            }
        }   

        if (empty($errors['country'])) {
            $updateQuery = $db->prepare('UPDATE countries SET name = :name WHERE country_id = :id');
            $updateQuery->execute([':name' => $countryName, ':id' => $countryId]);
            header('Location: admin.php?tab=countries');
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
        } else {
            // duplo
            $checkQuery = $db->prepare('SELECT * FROM countries WHERE name = :name LIMIT 1');
            $checkQuery->execute([':name' => $newCountryName]);
            if ($checkQuery->rowCount() > 0) {
                $errors['new_country'] = 'Tato země již existuje.';
                
            }
        }

        if (empty($errors['new_country'])) {
            $insertQuery = $db->prepare('INSERT INTO countries (name) VALUES (:name)');
            $insertQuery->execute([':name' => $newCountryName]);
            header('Location: admin.php?tab=countries');
            exit;
        }
    }

    // Načtení uživatelů
    $userQuery = $db->prepare('SELECT * FROM users ORDER BY user_id;');
    $userQuery->execute();
    $users = $userQuery->fetchAll(PDO::FETCH_ASSOC);

    // Přidání nového uživatele
    if (!empty($_POST['new_user_name']) && !empty($_POST['new_user_email'])  && !empty($_POST['new_user_password'])) {
        $newUserName = trim($_POST['new_user_name'] ?? '');
        $newUserEmail = trim($_POST['new_user_email'] ?? '');
        $newUserPassword = $_POST['new_user_password'] ?? '';

        if (empty($newUserName)) {
            $errors['new_user_name'] = 'Zadejte jméno uživatele.';
        } elseif (mb_strlen($newUserName) > 100) {
            $errors['new_user_name'] = 'Jméno nesmí být delší než 100 znaků.';
        }

        if (empty($newUserEmail)) {
            $errors['new_user_email'] = 'Zadejte email.';
        } elseif (!filter_var($newUserEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['new_user_email'] = 'Neplatný email.';
        } elseif (mb_strlen($newUserEmail) > 255) {
            $errors['new_user_email'] = 'Email nesmí být delší než 255 znaků.';
        } else {
            $checkQuery = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $checkQuery->execute([':email' => $newUserEmail]);
            if ($checkQuery->rowCount() > 0) {
                $errors['new_user_email'] = 'Uživatel s tímto emailem již existuje.';
            }
        }

        if (empty($newUserPassword)) {
            $errors['new_user_password'] = 'Zadejte heslo.';
        } 
        if (mb_strlen($newUserPassword) < 8) {
            $errors['new_user_password_length'] = 'Heslo musí mít alespoň 8 znaků.';
        }
        if (!preg_match('/[A-Z]/', $newUserPassword)) {
            $errors['new_user_password_upper'] = 'Heslo musí obsahovat alespoň jedno velké písmeno.';
        }
        if (!preg_match('/[0-9]/', $newUserPassword)) {
            $errors['new_user_password_number'] = 'Heslo musí obsahovat alespoň jedno číslo.';
        }
        if (!preg_match('/[\W_]/', $newUserPassword)) {
            $errors['new_user_password_special'] = 'Heslo musí obsahovat alespoň jeden speciální znak.';
        }

        $newUserPassword2 = $_POST['new_user_password2'] ?? '';

        if (empty($newUserPassword2)) {
            $errors['new_user_password2'] = 'Zadejte heslo znovu.';
        } elseif ($newUserPassword !== $newUserPassword2) {
            $errors['new_user_password2'] = 'Hesla se neshodují.';
        }

        if (
            empty($errors['new_user_name']) &&
            empty($errors['new_user_email']) &&
            empty($errors['new_user_role']) &&
            empty($errors['new_user_password']) &&
            empty($errors['new_user_password_length']) &&
            empty($errors['new_user_password_upper']) &&
            empty($errors['new_user_password_number']) &&
            empty($errors['new_user_password_special'])
        ) {
            $hashedPassword = password_hash($newUserPassword, PASSWORD_DEFAULT);
            $insertQuery = $db->prepare('INSERT INTO users (name, email, role, password) VALUES (:name, :email, "member", :password)');
            $insertQuery->execute([
            ':name' => $newUserName,
            ':email' => $newUserEmail,
            ':role' => $newUserRole,
            ':password' => $hashedPassword
            ]);
            header('Location: admin.php?tab=users');
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

                <!-- Přidání nové kategorie -->
                <form method="post" class="form-inline mb-3">
                    <div class="form-group">
                        <label class="m-2"for="new_category_name">Přidání nové kategorie: </label>
                        <input type="text" name="new_category_name" class="form-control<?php echo !empty($errors['new_category']) ? ' is-invalid' : ''; ?>" placeholder="Nová kategorie" required>
                        <?php if (!empty($errors['new_category'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['new_category']; ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary ml-2">Přidat</button>
                </form>
                      
                <!-- Výpiš všech kategorií -->
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Název</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?> <!-- každou kategorii buď vypíšu normálně s buttonem na upravení/smazání, nebo jí vypíšu jako input (když jsem v editaci jakoby) -->
                            <?php foreach ($categories as $category): ?>
                                <?php if (!empty($_GET['edit_category']) && $_GET['edit_category'] == $category['category_id']): ?>
                                    <tr>
                                        <form method="post">
                                            <input type="hidden" id="category_id" name="category_id" value="<?php echo htmlspecialchars($category['category_id']); ?>"/>
                                            <td><?php echo $category['category_id']; ?></td>
                                            <td>
                                                <input type="text" id="category_name" name="category_name" required class="form-control<?php echo !empty($errors['category']) ? ' is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($categoryName); ?>">
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
            </div>
            <div class="tab-pane fade show <?php echo @$_GET['tab']=='countries'?'active':''?>" id="countries">
                <!-- Přidání nové země -->
                <form method="post" class="form-inline mb-3">
                    <div class="form-group">
                        <label for="new_country_name" class="m-2">Přídání nové země:</label>
                        <input type="text" name="new_country_name" class="form-control<?php echo !empty($errors['new_country']) ? ' is-invalid' : ''; ?>" placeholder="Nová země" required>
                        <?php if (!empty($errors['new_country'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['new_country']; ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary ml-2">Přidat</button>
                </form>

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
            </div>
            <div class="tab-pane fade show <?php echo @$_GET['tab']=='users'?'active':''?>" id="users">
                <!-- Přidání nového uživatele -->
                <form method="post" class="mb-3">
                    <div class="form-group mr-2">
                        <label for="new_user_name" class="m-2">Jméno:</label>
                        <input type="text" name="new_user_name" class="m-2 form-control<?php echo !empty($errors['new_user_name']) ? ' is-invalid' : ''; ?>" placeholder="Jméno" required>
                        <label for="new_user_email" class="m-2">Email:</label>
                        <input type="email" name="new_user_email" class="m-2 form-control<?php echo !empty($errors['new_user_email']) ? ' is-invalid' : ''; ?>" placeholder="Email" required>
                        <?php if (!empty($errors['new_user_email'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $errors['new_user_email']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors['new_user_role'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $errors['new_user_role']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group mr-2">
                        <label for="new_user_password" class="m-2">Heslo:</label>
                        <input type="password" name="new_user_password" class="form-control<?php echo (!empty($errors['new_user_password']) || !empty($errors['new_user_password_length']) || !empty($errors['new_user_password_upper']) || !empty($errors['new_user_password_number']) || !empty($errors['new_user_password_special'])) ? ' is-invalid' : ''; ?>" placeholder="Heslo" required>
                        <?php if (!empty($errors['new_user_password'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $errors['new_user_password']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors['new_user_password_length'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $errors['new_user_password_length']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors['new_user_password_upper'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $errors['new_user_password_upper']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors['new_user_password_number'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $errors['new_user_password_number']; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors['new_user_password_special'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $errors['new_user_password_special']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group mr-2">
                        <label for="new_user_password2" class="m-2">Heslo znovu:</label>
                        <input type="password" name="new_user_password2" class="form-control<?php echo !empty($errors['new_user_password2']) ? ' is-invalid' : ''; ?>" placeholder="Zadejte heslo znovu" required>
                        <?php if (!empty($errors['new_user_password2'])): ?>
                            <div class="invalid-feedback d-block"><?php echo $errors['new_user_password2']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Přidat</button>
                    </div>
                </form>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Jméno</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td>
                                        <a href="?tab=users&edit_user=<?php echo htmlspecialchars($user['user_id']); ?>" class="btn btn-sm btn-warning">Upravit</a>
                                        <a href="loans.php?user=<?php echo htmlspecialchars($user['user_id']); ?>" class="btn btn-sm btn-info">Výpůjčky</a>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Opravdu smazat?');">
                                            <input type="hidden" name="delete_user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Smazat</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">Žádní uživatelé</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
    //vložíme do stránek patičku
    include 'inc/footer.php';