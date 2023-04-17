<?php
# Avviamo la sessione
session_start();

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

# Si controlla se l'utente ha effettuato il login, controllando che nella variabile globale $_SESSION 
# Sia contenuta la coppia chiave-valore "Loggato"-<username>
controllaAutorizzati();

# Controlla che l'URL abbia un parametro nella query che specifichi l'id del blog e in questo caso anche l'autore. 
# Altrimenti rimanda l'utente all'url specificato come parametro della funzione
controllaURLAccesso(['id'], "blog_utente.php"); 

# Estraiamo tutti i dati relativi al blog tramite l'id ottenuto attraverso la funzione frammentaURL
$dati = estraiBlogInfo($pdo, $_GET['id'])[0];

# Verifico che l'utente loggato corrisponda al creatore del blog
if ($_SESSION['loggato'] != $dati['blog_founder']) {
    header("Location: blog_utente.php");
}

try {
    $statement = $pdo->prepare('DELETE FROM blog WHERE blog_id = :id');
    $statement->bindValue(':id', $dati['blog_id']);
    $statement->execute();
} catch (\Throwable $th) {
    throw $th;
}

header("Location: blog_utente.php");

