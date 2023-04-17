<?php
# Avviamo la sessione
session_start();

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

# Controlla che l'URL abbia un parametro nella query che specifichi l'id del blog. Altrimenti rimanda l'utente all'url specificato 
# come parametro della funzione
controllaURLAccesso(["id"], "blog_utente.php");

# Ottengo dall'url l'id della categoria e richiamo la funzione estraiDatiCategoria per ottenere il nome della categoria.
$blog_id = $_GET['id'];
$blog = estraiBlogInfo($pdo, $blog_id)[0];

# Estraiamo tutti i post del blog in base all'id ottenuto in precedenza
$posts = estraiBlogPost($pdo, $blog_id);

$contenuti = [];
$date = [];
foreach ($posts as $i => $post) {
    $contenuti[] = strlen($post['post_contenuto']) > 100 ? substr($post['post_contenuto'],0,100)."..." : $post['post_contenuto'];
    $date[] = substr($post['post_data'],0,10);
}

# Estraiamo una lista contenente tutti i Coautori del blog
$coautori_info = estraiCoautori($pdo, $blog_id);
$autore = controllaAutore($coautori_info, $blog['blog_founder']);

?>

<!-- Richiamo l'header e la navbar  -->
<?php require_once '../../views/partials/header.php'; ?>
<?php mostraNavbar() ?>

<?php if ($blog['template'] == "Chiaro") :?>
    <?php require_once '../../views/blog/template_chiaro.php' ?>
<?php else:?>
    <?php require_once '../../views/blog/template_scuro.php' ?>
<?php endif?>

<?php require_once '../../views/partials/js.php'; ?>
