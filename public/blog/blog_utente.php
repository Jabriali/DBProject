<?php 
session_start();

# Si controlla se l'utente ha effettuato il login, controllando che nella variabile globale $_SESSION 
# Sia contenuta la coppia chiave-valore "Loggato"-<username>
if(!isset($_SESSION["loggato"])) {
    header("Location: ../utenti/login.php");
}

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';

# Richiamo la funzione che esegue la query per estrarre tutte le informazioni sui blog creati dall'utente
$blogs = estraiBlogUtente($pdo, $_SESSION['loggato']);
$blog_amministrati = estraiBlogAmministrati($pdo, $_SESSION['loggato']);
$descrizioni = [];
$date = [];

foreach ($blogs as $i => $blog) {
    $descrizioni[] = strlen($blog['blog_descrizione']) > 50 ? substr($blog['blog_descrizione'],0,50)."..." : $blog['blog_descrizione'];
    $date[] = substr($blog['creazione_data'],0,10);
}

foreach ($blog_amministrati as $i => $blog) {
    $descrizioni[] = strlen($blog['blog_descrizione']) > 50 ? substr($blog['blog_descrizione'],0,50)."..." : $blog['blog_descrizione'];
    $date[] = substr($blog['creazione_data'],0,10);
}

?>

<!-- Richiamo l'header e la navbar utilizzata dai visitatori -->
<?php require_once '../../views/partials/header.php'; ?>
<?php require_once '../../views/utenti/navbar.php'; ?>


<main class="text-center">
    <h1 class="mt-4">Crea Blog</h1>

    <?php if (isset($_SESSION['verificato'])) : ?>
    <a href="crea_blog.php" class="mt-2 mb-2 btn btn-dark">Nuovo blog</a>
    <?php endif ?>

    <div class="mt-4 d-flex flex-wrap flex-row justify-content">
        <?php foreach ($blogs as $i => $blog) : ?>
            <div class="card rounded mb-5 me-5 ms-5 w-25">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $blog['blog_titolo']?></h5>
                    <p class="mt-3 card-text"><?php echo $descrizioni[$i]?></p>
                    <p class="card-text"><b>Creato il </b><?php echo $date[$i]?></p>
                    <p class="card-text"><b>Creato da </b><?php echo $blog['blog_founder']?></p>
                    <p class="card-text"><b>Template </b><?php echo $blog['template']?></p>
                </div>
                <div class="d-flex flew-row card-footer">
                    <a href="blog.php?id=<?php echo $blog['blog_id']?>" class="btn btn-success me-2">Visualizza</a>
                    <a href="personalizza_blog.php?id=<?php echo $blog['blog_id']?>" class="btn btn-primary me-2">Personalizza</a>
                    <a href="cancella_blog.php?id=<?php echo $blog['blog_id']?>" class="btn btn-danger">Cancella</a>
                  
                </div>
            </div>
        <?php endforeach ?>

        <?php foreach ($blog_amministrati as $i => $blog) : ?>
            <div class="card rounded mb-5 me-5 ms-5 w-25">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $blog['blog_titolo']?></h5>
                    <p class="mt-3 card-text"><?php echo $descrizioni[$i]?></p>
                    <p class="card-text"><b>Creato il </b><?php echo $date[$i]?></p>
                    <p class="card-text"><b>Creato da </b><?php echo $blog['blog_founder']?></p>
                    <p class="card-text"><b>Template </b><?php echo $blog['template']?></p>
                </div>
                <div class="d-flex flew-row justify-content-center card-footer">
                    <a href="blog.php?id=<?php echo $blog['blog_id']?>" class="btn btn-success me-2">Visualizza</a>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</main>