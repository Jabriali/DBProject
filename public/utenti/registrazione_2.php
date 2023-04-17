<?php
session_start();

 if(isset($_SESSION["verificato"])) {
    header("Location: index.php");
}

$pdo = require_once '../../database.php';
require_once '../../funzioni.php';
require_once '../../query.php';

$errori = [];

$telefono = '';
$docu_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono = trim($_POST['telefono']);
    $docu_id = trim(strtoupper($_POST['docu_id']));
  
    $telefono_duplicato = verificaTelefonoDuplicato($pdo, $telefono);

    if ($telefono_duplicato) {
        $errori[] = "Telefono già registrato";
    }

    $docu_duplicato = verificaDocuDuplicato($pdo, $docu_id);
    
    if ($docu_duplicato) {
        $errori[] = "Documento d'identità già registrato";
    }

    if (!$telefono) {
        $errori[] = 'Il campo telefono è richiesto';
    }
    
    if (!$docu_id) {
        $errori[] = 'Il campo documento è richiesto';
    }

    if (!preg_match('/^[0-9]{6,30}$/', $telefono)) {
        $errors[] = 'Il numero di telefono non è valido. Deve essere lungo almeno 6 cifre e massimo 30.';
    }

    if (!preg_match('/^[A-Za-z0-9]{5,40}$/',$docu_id)) {
        $errors[] = "Il documento d'identità inserito non è valido. ";
    }

    if (empty($errori)) {
       
        try {
            $statement = $pdo->prepare("UPDATE utente SET telefono = :telefono, docu_id = :docu_id, verificato = :verificato
                    WHERE username = :username");
            $statement->bindValue(':telefono', $telefono);
            $statement->bindValue(':docu_id', $docu_id);
            $statement->bindValue(':verificato', 1);
            $statement->bindValue(':username', $_SESSION['loggato']);

            $statement->execute();
        } catch (\Throwable $th) {
            throw $th;
        }
       

        if (controlla_verificato($_SESSION['loggato'], $pdo)) {
           $_SESSION['verificato'] = true;      
        }
        header("Location: ../index.php");
    }
}

?>

<?php require_once '../../views/partials/header.php'; ?>
<?php 
    if(isset($_SESSION["loggato"])) {
        require_once '../../views/utenti/navbar.php';
    } else {
        require_once '../../views/utenti/navbar_visitatore.php';
    }
?>

<div class="container mt-5 mb-5">

    <?php require_once '../../views/partials/controllo.php'; ?>

    <form method="post">
        <h4>Per verificare il tuo account inserisci un numero di telefono e un documento d'identità validi. 
        </h4>
        <h6>Verificare il tuo account permette di sbloccare innumerevoli vantaggi, come la possibilità di
            commentare un post e di interagire con altri utenti.</h6>
        <div class="form-group needs-validation mt-3 mb-3">
            <label for="exampleInputEmail1">Numero di telefono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Telefono">
        </div>
        <div class="form-group mb-3">
            <label for="exampleInputEmail1">Documento d'identità</label>
            <input type="text" class="form-control" id="docu_id" name="docu_id" placeholder="Numero documento">
        </div>
        <button type="submit" class="btn btn-info mt-2" id="registrati">Conferma</button>
        <a href="../index.php" class="btn btn-dark mt-2" role="button">Salta</a>
    </form>
</div>

<?php require_once '../../views/partials/js.php'; ?>
