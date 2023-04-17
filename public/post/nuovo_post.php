<?php
# Avviamo la sessione
session_start();

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

controllaLogin();

# Controlla che l'URL abbia un parametro nella query che specifichi l'id del blog e in questo caso anche l'autore. 
# Altrimenti rimanda l'utente all'url specificato come parametro della funzione
controllaURLAccesso(['id', 'autore'], "../index.php");

# Ottengo dall'url l'id della categoria e richiamo la funzione estraiDatiCategoria per ottenere il nome della categoria.
$blog_id = $_GET['id'];
$blog = estraiBlogInfo($pdo, $blog_id)[0];

# Ottengo dall'url l'id della categoria e richiamo la funzione estraiDatiCategoria per ottenere il nome della categoria.
$username = $_GET['autore'];
$utente = estraiDatiUtente($pdo, $username);

$post_titolo = '';
$post_contenuto = '';
$copertina = '';
$foto_footer = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_titolo = trim(ucfirst($_POST['post_titolo']));
    $post_contenuto = $_POST['post_contenuto'];
    $copertina = $_FILES['copertina'] ?? null;
    $foto_footer = $_FILES['photo'] ?? null;

    if (!$post_titolo) {
        $errori[] = 'Il campo titolo è richiesto';
    } 
    elseif (strlen($post_titolo) < 5 || strlen($post_titolo) > 80) {
        $post_titolo = null;
        $errori[] = "Il titolo del post dev'essere incluso tra i 5 e gli 80 caratteri";
    } 
    elseif (!preg_match('/[A-Za-z0-9!?.,-;\s]{5,80}/', $post_titolo)) {
        $post_titolo = null;
        $errori[] = 'Il titolo del tuo blog post contenere solo i seguenti caratteri speciali ? ! . , ; -';
    }

    if (!$post_contenuto) {
        $errori[] = 'Il contenuto del post è richiesto';
    } elseif (!preg_match('/^[A-Za-z0-9!?](?s)((?:[^\n][\n]?)+)/', $post_contenuto)) {
        $errori[] = 'Il contenuto del post può contenere solo i seguenti caratteri speciali ? ! . , ; - |';
    }

    if (!is_dir(__DIR__. '/../images/post')) {
        mkdir(__DIR__. '/../images/post');
    }

    # https://www.studentstutorial.com/php/php-insert-image
    if ($_FILES['copertina'] && $_FILES['copertina']['tmp_name']) {
        $url = time().$blog_id.$username;
        $percorso_copertina = 'images/post/'. $url.'/'.$_FILES['copertina']['name'];
        if(strlen($percorso_copertina) > 400) {
            $errori[] = 'Il nome del file caricato è troppo lungo';
        }   
    } else {
        $percorso_copertina = null;
    }

    if ($_FILES['foto_footer'] && $_FILES['foto_footer']['tmp_name']) {
        $url = time().$blog_id.$username;
        $percorso_foto_footer = 'images/post/'. $url.'/'.$_FILES['foto_footer']['name'];
    } else {
        $percorso_foto_footer  = null;
    }
 
    if (empty($errori)) {
        if($percorso_copertina) {
            mkdir(dirname(__DIR__ .'/../'.$percorso_copertina));
            move_uploaded_file($_FILES['copertina']['tmp_name'], __DIR__ . '/../' . $percorso_copertina);
        }

        if($percorso_foto_footer) {
            move_uploaded_file($_FILES['foto_footer']['tmp_name'], __DIR__ . '/../' . $percorso_foto_footer);
        }
        
        try {
            $statement = $pdo->prepare("INSERT INTO post (post_titolo, post_contenuto, post_data, copertina, foto_footer, num_commenti, num_like, post_writer, blog_id)
            VALUES (:post_titolo, :post_contenuto, :post_data, :percorso_copertina, :percorso_foto_footer, :num_commenti, :num_like, :post_writer, :blog_id)");
            $statement->bindValue(':post_titolo', $post_titolo);
            $statement->bindValue(':post_contenuto', $post_contenuto);
            $statement->bindValue(':post_data', gmdate("Y-m-d H:i:s"));
            $statement->bindValue(':percorso_copertina', $percorso_copertina);
            $statement->bindValue(':percorso_foto_footer', $percorso_foto_footer);
            $statement->bindValue(':num_commenti', 0);
            $statement->bindValue(':num_like', 0);
            $statement->bindValue(':post_writer', $username);
            $statement->bindValue(':blog_id', $blog_id);

            $statement->execute();
            $post_id = $pdo->lastInsertId();
        } catch (\Throwable $th) {
            throw $th;
        }

        header("Location: ../post/post.php?id=$post_id");
    }
}

?>

<!-- Richiamo l'header e la navbar utilizzata dai visitatori -->
<?php require_once '../../views/partials/header.php'; ?>
<?php require_once '../../views/utenti/navbar.php'; ?>

<div class="container mt-5 mb-5">
    <h1 class="mb-4 d-flex justify-content-center">Nuovo post</h1>

    <form method="post" enctype="multipart/form-data">
        <?php require_once '../../views/partials/controllo.php'; ?>

        <div class="form-group needs-validation mb-3">
            <label for="post_titolo">Titolo del Post</label>
            <input type="text" class="form-control" id="post_titolo" name="post_titolo" placeholder="Titolo">
        </div>

        <div class="form-group needs-validation mb-3">
            <textarea class="form-control" id="post_contenuto" name="post_contenuto" rows="30" cols="5" autofocus spellcheck="true"></textarea>
        </div>

        <div class="form-group needs-validation mb-3 d-flex flex-column ">
            <label class="mb-2" for="copertina">Immagine di Copertina</label>
            <input type="file" accept="image/png, image/gif, image/jpeg, image/jpg" id="copertina" name="copertina"> 
        </div>

        <div class="form-group needs-validation mb-3 d-flex flex-column">
            <label class="mb-2" for="foto-secondaria">Immagine di Footer</label>
            <input type="file" accept="image/png, image/gif, image/jpeg, image/jpg" id="foto_footer" name="foto_footer"> 
        </div>

        <button type="submit" class="btn btn-info mt-4" id="posta">Crea</button>
    </form>
</div>