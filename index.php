<?php
require 'db.php';
session_start();

if (!isset($_COOKIE['userTemp'])) {
    setcookie(
        'userTemp',
        uniqid(),
        [
            'expires' => time() + 86400 * 30,
            'secure' => true,
            'httponly' => true,
        ]
    );
    // $_SESSION['userTemp'] = uniqid();
} else {
    // $_SESSION['userTemp'] = $_SESSION['userTemp'];
    $_COOKIE['userTemp'] = $_COOKIE['userTemp'];
}

$db = Database::connect();

// récupérer les catégories
$query = "SELECT * FROM categories";
$categs = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// récupérer les produits
$query2 = "SELECT * FROM items";
$products = $db->query($query2)->fetchAll(PDO::FETCH_ASSOC);

$query3 = "SELECT * FROM boisson";
$stmt = $db->prepare($query3);
$stmt->execute();
$boissons = $stmt->fetchAll(PDO::FETCH_ASSOC);

Database::disconnect();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Burger Code</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <link href='http://fonts.googleapis.com/css?family=Holtwood+One+SC' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .personnalisation-options {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .option-group {
            margin-bottom: 10px;
        }
        
        .option-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        
        .supplement-prix {
            font-size: 0.8em;
            color: #666;
        }
        
        .legumes-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
        }
    </style>
</head>

<body>
    <div class="container site">

        <div style="text-align:center; display:flex; justify-content:center; align-items:center" class="text-logo">
            <h1>Burger Doe</h1>
            <a href="panier.php" class="bi bi-basket3 cart-icon"> </a>
        </div>

        <nav>
            <ul class="nav nav-pills" role="tablist">
                <?php foreach ($categs as $categ) {
                    if ($categ['id'] == 1) {
                        $active = 'active';
                    } else {
                        $active = null;
                    } ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $active ?>"
                            data-bs-toggle="pill"
                            data-bs-target="#tab<?= $categ['id'] ?>"
                            role="tab">
                            <?= $categ['name'] ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </nav>

        <div class="tab-content">
            <?php foreach ($categs as $categ) {
                if ($categ['id'] == 1) {
                    $active = 'active';
                } else {
                    $active = null;
                } ?>
                <div class="tab-pane <?= $active ?>" id="tab<?= $categ['id'] ?>" role="tabpanel">
                    <div class="row">
                        <?php foreach ($products as $product) {
                            if ($product['category'] == $categ['id']) { ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="img-thumbnail">
                                        <img src="images/<?= $product['image'] ?>" class="img-fluid" alt="...">
                                        <div class="price"><?= number_format($product['price'], 2, ',', ' ') ?> €</div>
                                        <div class="caption">
                                            <h4><?= $product['name'] ?></h4>
                                            <p><?= $product['description'] ?></p>
                                            
                                            <!-- Formulaire de personnalisation -->
                                            <div class="personnalisation-options">
                                                <form class="form-personnalisation" data-product-id="<?= $product['id'] ?>">
                                                    <div class="option-group">
                                                        <label>Sauce :</label>
                                                        <select name="sauce" class="form-select sauce-select">
                                                            <option value="">Choisir une sauce</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="option-group">
                                                        <label>Fromage :</label>
                                                        <select name="fromage" class="form-select fromage-select">
                                                            <option value="">Choisir un fromage</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="option-group">
                                                        <label>Légumes supplémentaires :</label>
                                                        <div class="legumes-options">
                                                            <!-- Les cases à cocher seront ajoutées dynamiquement -->
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="prix-total mt-2">
                                                        Prix total : <span class="prix-base"><?= number_format($product['price'], 2, ',', ' ') ?></span> € 
                                                        <span class="prix-supplements"></span>
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-order mt-2">
                                                        <span class="bi-cart-fill"></span> Commander
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    
    <script>
    // Chargement des options de personnalisation
    document.addEventListener('DOMContentLoaded', function() {
        fetch('personnalisation.php')
            .then(response => response.json())
            .then(data => {
                // Remplir les sélecteurs de sauces
                const sauceSelects = document.querySelectorAll('.sauce-select');
                data.sauces.forEach(sauce => {
                    const option = `<option value="${sauce.id}" data-prix="${sauce.prix_supplement}">
                        ${sauce.nom} ${sauce.prix_supplement > 0 ? `(+${sauce.prix_supplement.toFixed(2)} €)` : ''}
                    </option>`;
                    sauceSelects.forEach(select => select.insertAdjacentHTML('beforeend', option));
                });
                
                // Remplir les sélecteurs de fromages
                const fromageSelects = document.querySelectorAll('.fromage-select');
                data.fromages.forEach(fromage => {
                    const option = `<option value="${fromage.id}" data-prix="${fromage.prix_supplement}">
                        ${fromage.nom} ${fromage.prix_supplement > 0 ? `(+${fromage.prix_supplement.toFixed(2)} €)` : ''}
                    </option>`;
                    fromageSelects.forEach(select => select.insertAdjacentHTML('beforeend', option));
                });
                
                // Ajouter les options de légumes
                const legumesContainers = document.querySelectorAll('.legumes-options');
                data.legumes.forEach(legume => {
                    const checkbox = `
                        <div class="form-check">
                            <input class="form-check-input legume-checkbox" type="checkbox" 
                                value="${legume.id}" data-prix="${legume.prix_supplement}" 
                                id="legume${legume.id}">
                            <label class="form-check-label" for="legume${legume.id}">
                                ${legume.nom} ${legume.prix_supplement > 0 ? `(+${legume.prix_supplement.toFixed(2)} €)` : ''}
                            </label>
                        </div>`;
                    legumesContainers.forEach(container => container.insertAdjacentHTML('beforeend', checkbox));
                });
            });
            
        // Gérer les changements de prix
        document.querySelectorAll('.form-personnalisation').forEach(form => {
            const updatePrixTotal = () => {
                const prixBase = parseFloat(form.querySelector('.prix-base').textContent.replace(',', '.'));
                let supplements = 0;
                
                // Prix de la sauce
                const sauceSelect = form.querySelector('.sauce-select');
                if (sauceSelect.selectedOptions[0]) {
                    supplements += parseFloat(sauceSelect.selectedOptions[0].dataset.prix || 0);
                }
                
                // Prix du fromage
                const fromageSelect = form.querySelector('.fromage-select');
                if (fromageSelect.selectedOptions[0]) {
                    supplements += parseFloat(fromageSelect.selectedOptions[0].dataset.prix || 0);
                }
                
                // Prix des légumes
                form.querySelectorAll('.legume-checkbox:checked').forEach(checkbox => {
                    supplements += parseFloat(checkbox.dataset.prix || 0);
                });
                
                // Mettre à jour l'affichage
                if (supplements > 0) {
                    form.querySelector('.prix-supplements').textContent = 
                        ` (dont suppléments : +${supplements.toFixed(2)} €)`;
                } else {
                    form.querySelector('.prix-supplements').textContent = '';
                }
            };
            
            form.addEventListener('change', updatePrixTotal);
            
            // Gérer la soumission du formulaire
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const productId = this.dataset.productId;
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('sauce_id', this.querySelector('.sauce-select').value);
                formData.append('fromage_id', this.querySelector('.fromage-select').value);
                
                const legumesIds = Array.from(this.querySelectorAll('.legume-checkbox:checked'))
                    .map(cb => cb.value);
                formData.append('legumes_ids', legumesIds);
                
                fetch('personnalisation.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Produit ajouté au panier avec personnalisation !');
                        // Optionnel : recharger le panier
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erreur lors de l\'ajout au panier');
                    }
                });
            });
        });
    });
    </script>
</body>
</html>