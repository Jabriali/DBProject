<?php
# Avviamo la sessione
session_start();

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

controllaLogin();

# Controlla che l'URL abbia un parametro nella query che specifichi l'id del blog e del post. 
# Altrimenti rimanda l'utente all'url specificato come parametro della funzione
controllaURLAccesso(['post_id', 'blog_id'], "../blog/blog_utente.php");

# Ottengo dall'url l'id del blog e del post e richiamo la funzione estraiBlogInfo per ottenere il nome del blog.
$post_id = $_GET['post_id'];
$post = estraiPostInfo($pdo, $post_id)[0];

$blog_id = $_GET['blog_id'];
$blog = estraiBlogInfo($pdo, $blog_id)[0];

# Estraiamo una lista contenente tutti i Coautori del blog
$coautori_info = estraiCoautori($pdo, $blog_id);
$autore = controllaAutore($coautori_info, $blog['blog_founder']);

unlink(__DIR__.'/../'.$post['copertina']);
unlink(__DIR__.'/../'.$post['foto_footer']);
# https://stackoverflow.com/questions/10746695/php-remove-characters-after-last-occurrence-of-a-character-in-a-string
$percorso_cartella = substr($post['copertina'], 0, strrpos($post['copertina'], '/'));
rmdir(__DIR__ .'/../'.$percorso_cartella);

try {
    $statement = $pdo->prepare('DELETE FROM post WHERE post_id = :id');
    $statement->bindValue(':id', $post_id);
    $statement->execute();
} catch (\Throwable $th) {
    throw $th;
}

header("Location: ../blog/blog.php?id=$blog_id");

?>