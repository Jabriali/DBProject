<?php
# Avviamo la sessione
session_start();


# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

# Controlla che l'URL abbia un parametro nella query che specifichi l'id della categoria. Altrimenti rimanda l'utente all'url specificato 
# come parametro della funzione
controllaURLAccesso("categorie_blog.php");

# Ottengo dall'url l'id della categoria e richiamo la funzione estraiDatiCategoria per ottenere il nome della categoria.
$categoria_id = $_GET['id'];
$categoria = estraiDatiCategoria($pdo, $categoria_id)[0];

# Richiamo la funzione che interroga il database al fine di estrarre tutti i blog che fanno parte della categoria con l'id specificato
# nell'url.
$sottocategorie = estraiSottoCategorie($pdo, $categoria_id);
$blog_senza_sottocategoria = estraisenzaSottocategoria($pdo, $categoria_id);
$blog_per_sottocategoria = [];
$categoria_vuota = false;
foreach ($sottocategorie as $i => $sottocategoria) {
    $blog_per_sottocategoria[$sottocategoria['categoria_nome']] = estraiconSottocategorie($pdo, $categoria_id, $sottocategoria['categoria_id']);
    if (count($blog_per_sottocategoria[$sottocategoria['categoria_nome']]) == 0) {
        $categoria_vuota = true;
    } else {
        $categoria_vuota = false;
    }
}
?>

<!-- Richiamo l'header e la navbar  -->
<?php require_once '../../views/partials/header.php'; ?>
<?php mostraNavbar() ?>

<div class="container-fluid mh-100 vh-100">
    
    
    <?php if (!$blog_senza_sottocategoria && $categoria_vuota) : ?>
        <div class="vh-100 mt-4 d-flex flex-column justify-content-center align-items-center">
            <h1>Ancora nessun blog in questa categoria...</h1>
            <a class="mt-4 btn btn-dark" href="../blog/blog_utente.php">Creane uno</a>
        </div>
    <?php else :?>
        <div class="d-flex mt-4 ms-5 me-5 justify-content-center">
            <h2><?php echo $categoria['categoria_nome']?></h2>
        </div>
        <div class="mt-4 ms-5 me-5 d-flex flex-wrap flex-row justify-content">
            <?php foreach ($blog_senza_sottocategoria as  $blog) : ?>
                    <div class="card rounded mb-5 me-5 ms-5 w-25">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $blog['blog_titolo']?></h5>
                            <p class="mt-3 card-text"><?php echo $blog['blog_descrizione']?></p>
                            <p class="card-text"><b>Creato il </b><?php echo $blog['creazione_data']?></p>
                        </div>
                        <div class="d-flex flew-row justify-content-center card-footer">
                            <a href="../blog/blog.php?id=<?php echo $blog['blog_id']?>"
                                class="btn btn-success me-2">Visualizza</a>
                        </div>
                    </div>
            <?php endforeach?>
            
            <?php foreach ($blog_per_sottocategoria as $nomecategoria => $blogs) : ?>
                <?php foreach ($blogs as $blog) : ?>
                <div class="card rounded mb-5 me-5 ms-5 w-25">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $blog['blog_titolo']?></h5>
                        <p class="mt-3 card-text"><?php echo $blog['blog_descrizione']?></p>
                        <p class="card-text"><b>Creato il </b><?php echo $blog['creazione_data']?></p>
                        <p class="card-text"><b>Sottocategoria: </b><?php echo $nomecategoria?></p>
                    </div>
                    <div class="d-flex flew-row justify-content-center card-footer">
                        <a href="../blog/blog.php?id=<?php echo $blog['blog_id']?>"
                            class="btn btn-success me-2">Visualizza</a>
                    </div>
                </div>
                <?php endforeach?>
            <?php endforeach?>
        </div>
    <?php endif ?>

  


</div>