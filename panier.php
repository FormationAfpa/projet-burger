<?php
require 'db.php';
session_start();

$db = Database::connect();

// Jointure pour récupérer les informations des produits présents dans le panier
if (isset($_SESSION['user_id'])) {
    $user = $_SESSION['user_id'];
} else {
    $user = $_COOKIE['userTemp'];
}
$query =
    "SELECT *
    FROM panier
    JOIN items
    ON panier.id_item = items.id
    WHERE userTemp = :userTemp";
$stmt = $db->prepare($query);
$stmt->execute([':userTemp' => $user]);
$productsCart = $stmt->fetchAll(PDO::FETCH_ASSOC);

Database::disconnect();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
    <link rel="stylesheet" href="styles.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link href='http://fonts.googleapis.com/css?family=Holtwood+One+SC' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
</head>

<body>
    <div class="cart ">
        <?php if (!$productsCart) { ?>
            <div class="alert alert-danger" role="alert" style="text-align:center;">
                Votre panier est vide !
            </div>
        <?php } ?>

        <div class="cart-container">
            <?php if ($productsCart) { ?>
                <div class="row justify-content-between">
                    <div class="col-12">
                        <div class="">
                            <div class="">
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
                                        foreach ($productsCart as $productCart) {
                                            if (isset($_GET["newTotal"])) {
                                                $totalTtc = $_GET["newTotal"];
                                            } else {
                                                $totalTtc += $productCart['prix'] * $productCart['qte'];
                                            }
                                        ?>
                                            <tr>
                                                <th scope="row">
                                                    <a href="deleteProduct.php?id=<?= $productCart['id'] ?>">
                                                        <i class="bi bi-archive"></i>
                                                    </a>
                                                </th>
                                                <td>
                                                    <img src="images/<?= $productCart['image'] ?>" alt="Product" style="width:100px">
                                                </td>
                                                <td>
                                                    <a href=""></a><br>
                                                    <span><small><?= $productCart['name'] ?></small></span>
                                                </td>
                                                <td id="price<?= $productCart['id'] ?>"><?= number_format($productCart['price'], 2, ',', ' ') ?> €</td>
                                                <td>
                                                    <div class="quantity"
                                                        style="display:flex; justify-content:center; align-items:center">

                                                        <a href="#" onclick="decreaseQte(<?= $productCart['id'] ?>)" id="decrease<?= $productCart['id'] ?>"
                                                            style="border:none; background-color:white; text-decoration:none; color:black">
                                                            <span
                                                                style="font-size:40px; margin-right:10px; margin-left:10px">-</span>
                                                        </a>
                                                        <span id="qtpanier<?= $productCart['id'] ?>">
                                                            <?= $productCart['qte'] ?>
                                                        </span>
                                                        <a href="#" onclick="increaseQte(<?= $productCart['id'] ?>)" style="border:none; background-color:white; text-decoration:none;  color:black">
                                                            <span
                                                                style="font-size:40px; margin-left:10px; margin-right:10px">+</span>
                                                        </a>
                                                    </div>
                                                </td>
                                                <td class="total-ligne<?= $productCart['id'] ?>" id="total-ligne<?= $productCart['id'] ?>"><?= number_format($productCart['price'] * $productCart['qte'], 2, ',', ' ') ?> €</td>
                                            </tr>
                                        <?php
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Coupon -->
                    <div class="col-12 col-lg-6">
                        <div class=" mb-30">
                            <h6>Avez vous un coupon?</h6>
                            <p>Entrer le code de la remise</p>

                            <?php if (isset($_GET["invalid"])) { ?>
                                <div class="alert alert-danger" role="alert">
                                    Attention : le code remise saisi est incorrect !
                                </div>
                            <?php } ?>

                            <?php if (isset($_GET["newTotal"])) { ?>
                                <div class="alert alert-primary" role="alert">
                                    Vous avez ajouté un code de réduction !
                                </div>
                            <?php } ?>
                            <!-- Coupon -->

                            <div class="coupon-form">
                                <form action="couponREQ.php" method="POST">
                                    <input type="text" class="form-control" name="code" placeholder="Entrer le code">
                                    <input type="hidden" class="form-control" name="total" value="<?= $totalTtc ?>">
                                    <button type="submit" class="btn btn-primary"
                                        style="margin-top:20px">Valider</button>
                                </form>
                            </div>
                            <br>

                            <!-- Coupon -->

                        </div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class=" mb-30">
                            <h5 class="mb-3">Total panier</h5>
                            <div class="">
                                <table class="table mb-3">
                                    <tbody>
                                        <tr>
                                            <td>Total produit HT</td>
                                            <td id='HT'><?= number_format($totalTtc / 1.10, 2, ',', ' ') ?> €</td>
                                        </tr>
                                        <tr>
                                            <td>TVA</td>
                                            <td id="TVA"><?= number_format($totalTtc / 1.10 * 0.10, 2, ',', ' ') ?> €</td>
                                        </tr>
                                        <?php if (isset($_SESSION['coupon'])) { ?>
                                            <tr>
                                                <td>Remise</td>
                                                <td><?php
                                                    echo $_SESSION['coupon'];
                                                } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>TOTAL TTC</td>
                                                <td id='TTC'><?= number_format($totalTtc, 2, ',', ' ') ?> €</td>
                                            </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            <?php } ?>
            <a class="btn btn-primary" href="index.html"><span class="bi-arrow-left"></span> Retour</a>
        </div>
    </div>
    <script>
        function deleteProduct(id) {
            if (confirm('Etes-vous sûr de vouloir supprimer ce produit de votre panier ?')) {
                // fetch('deleteProduct.php', {
                //         method: 'POST',
                //         headers: {
                //             'Content-Type': 'application/json'
                //         },
                //         body: JSON.stringify({
                //             id: id
                //         })
                //     })
                //     .then(response => response.json())
                //     .then((data) => {
                //         // document.querySelector(``).innerHTML = data.;
                //     })
                //     .catch(error => console.error(error));
            } else {

            }
        }

        function decreaseQte(id) {
            let getQte = document.querySelector(`#qtpanier${id}`).innerHTML;
            // let getPrice = document.querySelector(`#price${id}`).innerHTML;

            fetch('updateQte.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                        // 'Content-Type': 'application/x-www-form-urlencoded'
                        // ↑ deuxième manière d'encoder, nous on utilise le json
                    },
                    body: JSON.stringify({
                        id: id,
                        qt: getQte,
                        action: 'decrease',
                    })
                })
                .then(response => response.json())
                .then((data) => {
                    if (data.qte == 0) {
                        let link = document.querySelector(`#decrease${id}`);
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                        })
                    } else {
                        document.querySelector(`#qtpanier${id}`).innerHTML = data.qte;
                        // document.querySelector(`#total-ligne${id}`).innerHTML = getPrice * data.qte;
                    }
                })
                .catch(error => console.error(error))
        };

        function increaseQte(id) {
            let getQte = document.querySelector(`#qtpanier${id}`).innerHTML;
            fetch('updateQte.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        qt: getQte,
                        action: 'increase',
                    })
                })
                .then(response => response.json())
                .then((data) => {
                    document.querySelector(`#qtpanier${id}`).innerHTML = data.qte;
                })
                .catch(error => console.error(error))
        };
    </script>
</body>

</html>