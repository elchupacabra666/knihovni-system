<?php
$query_categories = $db->prepare('SELECT * FROM categories ORDER BY name');
$query_categories->execute();
$categories = $query_categories->fetchAll(PDO::FETCH_ASSOC);

$query_countries = $db->prepare('SELECT * FROM countries ORDER BY name');
$query_countries->execute();
$countries = $query_countries->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light border-0">
        <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Filtry a vyhledávání</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label"><i class="bi bi-search me-1"></i>Vyhledat podle názvu</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                       placeholder="Zadejte název knihy...">
            </div>
            
            <div class="col-md-4">
                <label for="category" class="form-label"><i class="bi bi-tags me-1"></i>Kategorie</label>
                <select class="form-select" id="category" name="category">
                    <option value="">Všechny kategorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" 
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $category['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="country" class="form-label"><i class="bi bi-globe me-1"></i>Země</label>
                <select class="form-select" id="country" name="country">
                    <option value="">Všechny země</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?php echo $country['country_id']; ?>" 
                                <?php echo (isset($_GET['country']) && $_GET['country'] == $country['country_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($country['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Vyhledat
                    </button>
                    <a href="?" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Resetovat
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>