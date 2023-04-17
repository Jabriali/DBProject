<?php


function randomString($n)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $str .= $characters[$index];
    }

    return $str;
}

function controlla_verificato($username, $pdo) {
    $statement = $pdo->prepare("SELECT verificato FROM utente WHERE username = :username LIMIT 1");
    $statement->bindValue(':username', $username);
    $statement->execute();
    $verificato = $statement->fetchColumn();

    if ($verificato) {
        return true;
    } else {
        return false;
    }
}

function crea_sessione($username)
{
    session_start();
    $_SESSION['loggato'] = $username;
}

function frammentaURL($url) {
    if (strpos($url, "?")) {
        $url = ltrim($url, '?');
        $query_info = explode('?', $url)[1];
        $coppie = explode('&', $query_info);
        $campi = [];
        foreach ($coppie as $coppia) {
            $coppiavalore = explode('=', $coppia);
            $campi[$coppiavalore[0]] = $coppiavalore[1];
        }
    
        return $campi;
    } else {
        return false;
    }
}

function mostraNavbar() {
    if(isset($_SESSION["loggato"])) {
        require_once '../../views/utenti/navbar.php';
    } else {
        require_once '../../views/utenti/navbar_visitatore.php';
    }
}

function controllaLogin() {
    if(!isset($_SESSION["loggato"])) {
        header("Location: ../utenti/login.php");  
    }
}

function controllaAutorizzati() {
    if(!isset($_SESSION["loggato"]) && (!isset($_SESSION['verificato']))) {
        header("Location: ../utenti/login.php");
    }
}

function controllaURLAccesso($chiavi, $url) {
    foreach ($chiavi as $chiave) {
        if (!isset($_GET[$chiave])) {
            header("Location: $url");
        }
    }
}

function controllaAccessoAmministrazione($fondatore) {
    if ($_SESSION['loggato'] != $fondatore) {
        header("Location: blog_utente.php");
    }
}

function controllaAutore($coautori_info, $blog_founder) {
    # Ottimizzo i dati estratti dal database creando un array di stringhe contenente solo gli username degli autori;
    $coautori = [];
    foreach ($coautori_info as $coautore) {
        $coautori[] = $coautore['username'];
    }

    # Controlliamo che l'utente si un utente loggato e non un visitatore. Se l'utente è loggato, allora verifico che la variabile di sessione
    # $_SESSION['loggato'] contenga l'username del creatore del blog, autorizzando l'utente a postare sul blog. 
    # Altrimenti, verifico che l'username dell'utente loggato sia nella lista di coautori del blog. 
    if(isset($_SESSION['loggato'])) {
        if($_SESSION['loggato'] == $blog_founder) {
            return true;
        } else {
            if (in_array($_SESSION['loggato'], $coautori)) {
                return true;
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
}