<?php 
session_start();

$pdo = require_once '../../database.php';

try {
    $statement = $pdo->prepare('DELETE FROM utente WHERE username = :username');
    $statement->bindValue(':username', $_SESSION['loggato']);
    $statement->execute();
} catch (\Throwable $th) {
    throw $th;
}

session_destroy();
header("Location: registrazione.php");
