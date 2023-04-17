<?php
# Avviamo la sessione
session_start();

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

# Controlla che l'URL abbia un parametro nella query che specifichi l'id del post. 
# Altrimenti rimanda l'utente all'url specificato come parametro della funzione
controllaURLAccesso(['id'], "../index.php");

# Ottengo dall'url l'id del blog e del post e richiamo la funzione estraiBlogInfo per ottenere il nome del blog.
$post_id = $_GET['id'];
$post = estraiPostInfo($pdo, $post_id)[0];
$blog = estraiBlogInfo($pdo, $post['blog_id']);

$commenti = estraiCommentiPost($pdo, $post_id);
$date = [];
foreach ($commenti as $i => $commento) {
    $date[] = substr($commento['commento_data'],0,10);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (array_key_exists('inserisciCommento', $_POST)) {
        $commento = $_POST['inserisciCommento'][0];
        $autore = $_POST['inserisciCommento'][1];
        inserisciCommento($pdo, $commento, $autore, $_GET['id']);
        aggiornaNumeroCommenti($pdo, $_POST['inserisciCommento'][2], $_GET['id']);
    }
}
?>

<!-- Richiamo l'header e la navbar  -->
<?php require_once '../../views/partials/header.php'; ?>
<?php mostraNavbar() ?>

<img class="img w-100" style="height: 450px; object-fit: cover" src="../<?php echo $post['copertina']?>" alt="">
<div class="container d-flex flex-column align-items-center">
    <h1 class="mt-4 display-5 text-center"><?php echo $post['post_titolo']?></h1>
    <div class="mt-3 ms-5 me-5 mb-5 w-50 content">
        <?php echo $post['post_contenuto']?>
    </div>
    <?php if ($post['foto_footer']) :?>
        <img class="img w-50 mb-5" src="../<?php echo $post['foto_footer']?>" alt="">
    <?php endif?>
</div>

<div class="container">
    <div class="h4 pb-2 mb-4 text-dark border-bottom border-dark d-flex justify-content-between">
        <div>
            Commenti (<span id="contatore-commenti"><?php echo $post['num_commenti']?></span>)
        </div>
        <div>
        <?php if(isset($_SESSION['loggato'])) :?>
            <button class="btn btn-dark">Like</button>
            (<?php echo $post['num_like']?>)
        <?php else :?>
            <?php echo $post['num_like']?>
            Likes
        <?php endif?>
        </div>
    </div>

    <?php if(isset($_SESSION['loggato'])) :?>
        <div class="form-group needs-validation mb-4">
            <textarea class="form-control commento" id="<?php echo $post_id?>" name="<?php echo $commento['commento_writer']?>" avatar="<?php echo $commento['avatar']?>" placeholder="Inserisci un commento"></textarea>
        </div>
    <?php endif?>
    <div class="mb-5" id="lista-commenti">
        <?php foreach ($commenti as $i => $commento) :?>
            <div class="commento-<?php echo $commento['commento_id']?> d-flex flex-column mt-3">
                <div class="d-flex flex-row">
                    <?php if (!$commento['avatar']) : ?>
                        <img class="me-3 img-fluid rounded" style="width: 50px;" src="..\images\profiloutenti\default.png">
                    <?php else : ?>
                        <img class="user-avatar" src="<?php echo $commento['avatar'] ?>">
                    <?php endif ?>
                    <div class="d-flex flex-row align-items-center text-primary">
                        <?php echo $commento['commento_writer']?> 
                        <?php if ($_SESSION['loggato'] == $commento['commento_writer'] || ) : ?>
                            <span><a class="cancella-commento text-danger" id=""<?php echo $commento['commento_id']?>" href="">x</a></span>
                        <?php endif ?>
                    </div>
                </div>
                <div>
                    <?php echo $commento['commento_contenuto']?>
                </div>
                <p>
                <?php echo $date[$i]?>
                </p>
            </div>
        <?php endforeach?>
    </div>

</div>


<?php require_once '../../views/partials/js.php'; ?>
<script src="../js/ajaxpost.js"></script>