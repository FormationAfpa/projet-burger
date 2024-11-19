<?php
require 'verifRole.php';
require '../db.php';

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
}

function deleteProduct($id)
{
    $db = DataBase::connect();
    $query = "DELETE FROM items WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);

    header('Location: index.php');
}

if (!empty($_GET['confirm']) && $_GET['confirm'] == 1) {
    deleteProduct($id);
}

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
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <h1 class="text-logo"> Burger Code </h1>
    <div class="container admin">
        <div class="row">
            <h1><strong>Supprimer un item</strong></h1>
            <br>
            <br>
            <p class="alert alert-warning">Etes vous sur de vouloir supprimer ?</p>
            <div class="form-actions">
                <a href="delete.php?id=<?= $id ?>&confirm=1" class="btn btn-warning">Oui</a>
                <a class="btn btn-secondary" href="index.php">Non</a>
            </div>
        </div>
    </div>
</body>

</html>