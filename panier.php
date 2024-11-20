<?php
require 'db.php';
require_once 'recommandations.php';
session_start();

try {
    $db = Database::connect();

    // Récupérer l'identifiant de l'utilisateur (connecté ou temporaire)
    if (isset($_SESSION['user_id'])) {
        $user = $_SESSION['user_id'];
    } else {
        $user = $_COOKIE['userTemp'];
    }

    // Jointure pour récupérer les informations des produits présents dans le panier
    $query =
        "SELECT p.*, i.name, i.image, i.description
        FROM panier p
        JOIN items i ON p.id_item = i.id
        WHERE p.userTemp = :userTemp";
    $stmt = $db->prepare($query);
    $stmt->execute([':userTemp' => $user]);
    $productsCart = $stmt->fetchAll(PDO::FETCH_ASSOC);

    Database::disconnect();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération du panier: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - Burger Doe</title>
    <link rel="stylesheet" href="styles.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link href='http://fonts.googleapis.com/css?family=Holtwood+One+SC' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <style>
        .recommendations-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .recommendations-title {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .recommendations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        
        .recommendation-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .recommendation-card:hover {
            transform: translateY(-5px);
        }
        
        .recommendation-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .recommendation-price {
            color: #28a745;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .recommendation-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .recommendation-button:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <div class="cart">
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger" role="alert" style="text-align:center;">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <?php if (empty($productsCart)) { ?>
            <div class="alert alert-info" role="alert" style="text-align:center;">
                Votre panier est vide !
            </div>
        <?php } else { ?>
            <div class="cart-container">
                <div class="row justify-content-between">
                    <div class="col-12">
                        <table class="table table-bordered mb-30">
                            <thead>
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Produit</th>
                                    <th scope="col">Prix unitaire</th>
                                    <th scope="col">Quantité</th>
                                    <th scope="col">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalTtc = 0;
                                foreach ($productsCart as $product) {
                                    $totalTtc += $product['prix'] * $product['qte'];
                                ?>
                                    <tr id="product-<?php echo $product['id']; ?>">
                                        <th scope="row">
                                            <button onclick="removeProduct(<?php echo $product['id']; ?>)" class="btn btn-link text-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </th>
                                        <td>
                                            <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="width:100px">
                                        </td>
                                        <td>
                                            <span><?php echo $product['name']; ?></span><br>
                                            <small><?php echo $product['description']; ?></small>
                                        </td>
                                        <td><?php echo number_format($product['prix'], 2, ',', ' '); ?> €</td>
                                        <td>
                                            <div class="quantity d-flex justify-content-center align-items-center">
                                                <button onclick="updateQuantity(<?php echo $product['id']; ?>, 'decrease')" class="btn btn-link">-</button>
                                                <span id="quantity-<?php echo $product['id']; ?>" class="mx-2"><?php echo $product['qte']; ?></span>
                                                <button onclick="updateQuantity(<?php echo $product['id']; ?>, 'increase')" class="btn btn-link">+</button>
                                            </div>
                                        </td>
                                        <td id="total-<?php echo $product['id']; ?>"><?php echo number_format($product['prix'] * $product['qte'], 2, ',', ' '); ?> €</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="coupon-section mb-30">
                            <h6>Avez-vous un coupon ?</h6>
                            <form id="coupon-form" class="d-flex gap-2">
                                <input type="text" id="coupon-code" class="form-control" placeholder="Entrez votre code">
                                <button type="submit" class="btn btn-primary">Appliquer</button>
                            </form>
                            <div id="coupon-message" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="cart-summary">
                            <h5>Récapitulatif</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total:</span>
                                <span id="subtotal"><?php echo number_format($totalTtc, 2, ',', ' '); ?> €</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Remise:</span>
                                <span id="discount">0,00 €</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Total:</strong>
                                <strong id="total"><?php echo number_format($totalTtc, 2, ',', ' '); ?> €</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section "Vous pourriez aussi aimer" -->
            <div class="recommendations-section">
                <h3 class="recommendations-title">Vous pourriez aussi aimer</h3>
                <div class="recommendations-grid">
                    <?php
                    // Récupérer le premier produit du panier pour les recommandations
                    $firstProduct = reset($productsCart);
                    $recommendations = getRecommandations($firstProduct['id_item']);
                    
                    foreach ($recommendations as $product) {
                        ?>
                        <div class="recommendation-card">
                            <img src="images/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="recommendation-image">
                            <h4><?= htmlspecialchars($product['name']) ?></h4>
                            <p class="recommendation-price"><?= number_format($product['price'], 2, ',', ' ') ?> €</p>
                            <button class="recommendation-button" 
                                    onclick="window.location.href='panierREQ.php?id_item=<?= $product['id'] ?>&prix=<?= $product['price'] ?>'">
                                <i class="bi bi-cart-plus"></i> Ajouter au panier
                            </button>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        <?php } ?>
        
        <a class="btn btn-primary mt-3" href="index.php"><span class="bi-arrow-left"></span> Retour aux produits</a>
    </div>

    <script>
        // Fonction pour mettre à jour la quantité
        function updateQuantity(productId, action) {
            const quantityElement = document.getElementById(`quantity-${productId}`);
            const currentQuantity = parseInt(quantityElement.textContent);
            
            let newQuantity = currentQuantity;
            if (action === 'increase') {
                newQuantity++;
            } else if (action === 'decrease' && currentQuantity > 1) {
                newQuantity--;
            }

            if (newQuantity !== currentQuantity) {
                $.ajax({
                    url: 'updateQte.php',
                    method: 'POST',
                    data: {
                        productId: productId,
                        quantity: newQuantity
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.success) {
                            quantityElement.textContent = newQuantity;
                            document.getElementById(`total-${productId}`).textContent = data.productTotal + ' €';
                            document.getElementById('subtotal').textContent = data.cartTotal + ' €';
                            document.getElementById('total').textContent = data.cartTotal + ' €';
                        }
                    },
                    error: function() {
                        alert('Erreur lors de la mise à jour de la quantité');
                    }
                });
            }
        }

        // Fonction pour supprimer un produit
        function removeProduct(productId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
                $.ajax({
                    url: 'deleteProduct.php',
                    method: 'POST',
                    data: { productId: productId },
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.success) {
                            document.getElementById(`product-${productId}`).remove();
                            document.getElementById('subtotal').textContent = data.cartTotal + ' €';
                            document.getElementById('total').textContent = data.cartTotal + ' €';
                            
                            if (data.cartEmpty) {
                                location.reload();
                            }
                        }
                    },
                    error: function() {
                        alert('Erreur lors de la suppression du produit');
                    }
                });
            }
        }

        // Gestion des coupons
        document.getElementById('coupon-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const code = document.getElementById('coupon-code').value;
            
            $.ajax({
                url: 'couponREQ.php',
                method: 'POST',
                data: {
                    code: code,
                    total: parseFloat(document.getElementById('subtotal').textContent)
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        document.getElementById('discount').textContent = data.discount + ' €';
                        document.getElementById('total').textContent = data.newTotal + ' €';
                        document.getElementById('coupon-message').innerHTML = 
                            '<div class="alert alert-success">Coupon appliqué avec succès!</div>';
                    } else {
                        document.getElementById('coupon-message').innerHTML = 
                            '<div class="alert alert-danger">Code coupon invalide</div>';
                    }
                },
                error: function() {
                    document.getElementById('coupon-message').innerHTML = 
                        '<div class="alert alert-danger">Erreur lors de l\'application du coupon</div>';
                }
            });
        });
        
        // Fonction pour charger dynamiquement les recommandations
        function loadRecommendations(productId) {
            fetch(`recommandations.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.recommendations.length > 0) {
                        const container = document.querySelector('.recommendations-grid');
                        container.innerHTML = data.recommendations.map(product => `
                            <div class="recommendation-card">
                                <img src="images/${product.image}" 
                                     alt="${product.name}" 
                                     class="recommendation-image">
                                <h4>${product.name}</h4>
                                <p class="recommendation-price">${product.price.toFixed(2).replace('.', ',')} €</p>
                                <button class="recommendation-button" 
                                        onclick="window.location.href='panierREQ.php?id_item=${product.id}&prix=${product.price}'">
                                    <i class="bi bi-cart-plus"></i> Ajouter au panier
                                </button>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => console.error('Erreur lors du chargement des recommandations:', error));
        }
        
        // Charger les recommandations lors du chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const firstProduct = document.querySelector('[data-product-id]');
            if (firstProduct) {
                loadRecommendations(firstProduct.dataset.productId);
            }
        });
    </script>
</body>

</html>