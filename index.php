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
<html>

<head>
    <title>Burger Code</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <link href='http://fonts.googleapis.com/css?family=Holtwood+One+SC' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
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
                                        <div class="price"><?= number_format($product['price'], 2, ',', ' ') ?></div>
                                        <div class="caption">
                                            <h4><?= $product['name'] ?></h4>
                                            <p><?= $product['description'] ?></p>

                                            <?php
                                            if ($product['choice'] == 1) {
                                            ?>

                                                <select class="form-control" id="taille<?= $product['id'] ?>" name="taille">

                                                <?php

                                                foreach ($boissons as $taille) {
                                                    if ($product['category'] == $taille['id_category']) {
                                                        echo "<option value='" . $taille['taille'] . "'>" . $taille['taille'] . "</option>";
                                                    }
                                                }

                                                $query3 = "SELECT * FROM choix WHERE id_item = :id";
                                                $stmt = $db->prepare($query3);
                                                $stmt->execute([":id" => $product['id']]);
                                                $choix = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($choix as $gout) {
                                                    echo "<option value='" . $gout['nom_choix'] . "'>" . $gout['nom_choix'] . "</option>";
                                                }
                                            }
                                                ?>
                                                </select>
                                                <br>
                                                <a href="panierREQ.php?id_item=<?= $product['id'] ?>&prix=<?= $product['price'] ?>" class="btn btn-order" role="button"><span class="bi-cart-fill"></span> Commander</a>
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
</body>

</html>