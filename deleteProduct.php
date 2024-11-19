<?php
require 'db.php';
session_start();

$userTemp = $_COOKIE["userTemp"];
$_POST = json_decode(file_get_contents('php://input'), true);

$db = Database::connect();

$queryDown = "DELETE FROM panier WHERE id_item = :id AND userTemp = :userTemp";
$stmt = $db->prepare($queryDown);
$stmt->bindValue(":id", $_GET['id'], PDO::PARAM_INT);
$stmt->bindValue(":userTemp", $userTemp, PDO::PARAM_STR);
$stmt->execute();

header('Location: panier.php');
exit();

Database::disconnect();
