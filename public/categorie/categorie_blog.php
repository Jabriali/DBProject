<?php
# Avviamo la sessione
session_start();


# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

# Estraiamo una lista contenente tutte le categorie globali utilizzabili nel sito
$categorie = estraiCategorie($pdo);

?>

<!-- Richiamo l'header e la navbar  -->
<?php require_once '../../views/partials/header.php'; ?>
<?php mostraNavbar() ?>

<div class="container-fluid mh-100 vh-100">
    <div class="w-100 h-100 d-flex flex-row align-items-center justify-content-center flex-wrap">
        <?php foreach ($categorie as $i => $categoria) : ?>
            <div class="w-25 d-flex flex-column justify-content-center align-items-center">
                <a class="text-decoration-none d-flex flex-column  align-items-center" 
                href="categoria.php?id=<?php echo $categoria['categoria_id']?>">
                    <img src="..\images\categorie\<?php echo $categoria['categoria_nome']?>.png" class="w-25 img-fluid img-thumbnail" alt="">
                    <h5 class="mt-3 text-dark"><?php echo $categoria['categoria_nome']?></h5>
                </a>
            </div>
        <?php endforeach ?>
    </div>
</div>

