<?php
# Avviamo la sessione
session_start();

# Si controlla se l'utente ha effettuato il login, controllando che nella variabile globale $_SESSION 
# Sia contenuta la coppia chiave-valore "Loggato"-<username>
if(!isset($_SESSION["loggato"]) && (!isset($_SESSION['verificato']))) {
    header("Location: ../utenti/login.php");
}

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

# Estraiamo tutti i nomi dei templati disponibili dal database
$template = estraiTemplate($pdo);

# Estraiamo una lista contenente tutte le categorie globali utilizzabili nel sito
$categorie = estraiCategorie($pdo);

# Array utilizzato per contenere le stringhe che specificano gli errori durante 
# la fase di inserimento dei dati nel form
$errori = [];

$blog_titolo = '';
$blog_descrizione = '';
$tema_scelto = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    # Utilizziamo la funzione trim per rimuovere caratteri particolari e spazi bianchi dai dati inseriti nel form
    # All'interno della funzione trim utilizziamo strtolower per trasformare la stringa in caratteri minuscoli
    # Su alcuni campi utilizziamo la funzione ucfirst per mettere in maiuscolo solo il primo carattere della stringa
    $blog_titolo = trim(ucfirst($_POST['blog_titolo']));
    $blog_descrizione = trim($_POST['blog_descrizione']);
    $tema_scelto = (isset($_POST['Template'])) ? trim(ucfirst($_POST['Template'])) : "Chiaro";
    $categoria_associata = $_POST['categoria'];

    if (!$blog_titolo) {
        $errori[] = 'Il campo titolo è richiesto';
    } 
    elseif (strlen($blog_titolo) < 5 || strlen($blog_titolo) > 45) {
        $blog_titolo = null;
        $errori[] = "Il titolo del blog dev'essere incluso tra i 5 e i 45 caratteri";
    } 
    elseif (!preg_match('/[A-Za-z0-9!?.,-;\s]{5,45}/', $blog_titolo)) {
        $blog_titolo = null;
        $errori[] = 'Il titolo del tuo blog può contenere solo i seguenti caratteri speciali ? ! . , ; -';
    }
    
    if ($blog_descrizione) {
        if (strlen($blog_descrizione) > 160) {
            $blog_descrizione = null;
            $errori[] = "La descrizione inserita è troppo lunga. Massimo 160 caratteri";
        } 
        elseif (!preg_match('/^[A-Za-z0-9!?]{0,160}(?s)((?:[^\n][\n]?)+)/', $blog_descrizione)) {
            $blog_descrizione = null;
            $errori[] = 'La descrizione del tuo blog può contenere solo i seguenti caratteri speciali ? ! . , ; - |';
        }
    }

    # Controllo che esista la chiave categoria associata nell'array $_POST
    if($categoria_associata) {

        # Creo un arrray contenete tutti gli id delle categorie principali e secondarie esistenti
        $categorie_esistenti = [];
        foreach($categorie as $categoria) {
            $categorie_esistenti[] = $categoria['categoria_id'];
        }
        
        # Verifico che l'id della categoria associata corrisponda ad uno delle categorie già esistenti
        if(!in_array($categoria_associata, $categorie_esistenti)){
            $errori[]="La categoria selezionata non è valida";
        }
    } else {
        $errori[]="Non hai impostato nessuna categoria";
    }

    if (empty($errori)) {
        try {
            $statement = $pdo->prepare("INSERT INTO blog (blog_titolo, blog_descrizione, creazione_data, blog_founder, template)
            VALUES (:blog_titolo, :blog_descrizione, :creazione_data, :blog_founder, :template)");
            $statement->bindValue(':blog_titolo', $blog_titolo);
            $statement->bindValue(':blog_descrizione', $blog_descrizione);
            $statement->bindValue(':creazione_data', gmdate("Y-m-d H:i:s"));
            $statement->bindValue(':blog_founder', $_SESSION['loggato']);
            $statement->bindValue(':template', $tema_scelto);

            $statement->execute();
            $blog_id = $pdo->lastInsertId();
        } catch (\Throwable $th) {
            throw $th;
        }

        associaBlogCategoria($pdo, $categoria_associata, $blog_id);

        header("Location: blog_utente.php");
    }
}
?>

<!-- Richiamo l'header e la navbar utilizzata dai visitatori -->
<?php require_once '../../views/partials/header.php'; ?>
<?php require_once '../../views/utenti/navbar.php'; ?>

<!-- Verificare permanenza dell'input nel form dopo errore -->
<div class="container mt-5 mb-5">
    <form method="post">

        <?php require_once '../../views/partials/controllo.php'; ?>

        <h1 class="mb-4">Nuovo Blog</h1>
        <div class="form-group needs-validation mb-3">
            <label for="blog_titolo">Titolo del Blog</label>
            <input type="text" class="form-control" id="blog_titolo" name="blog_titolo" placeholder="Nome" value="">
        </div>
        <div class="form-group mb-3">
            <label for="blog_descrizione">Descrizione del blog (Max 160 caratteri)</label>
            <textarea class='form-control' id="blog_descrizione" name="blog_descrizione" cols="1" rows="1" placeholder="Inserisci una descrizione del blog"></textarea>
        </div>
        <div class="form-group needs-validation mb-3">
            <label class="mb-2" for="Template">Template</label>
            <?php foreach ($template as $i => $t) : ?>
                <div class="radio-element">
                    <input class="me-1 mb-2" type="radio" id="<?php echo $t['template_name']?>" name="Template" value="<?php echo $t['template_name']?>">
                    <label for="<?php echo $t['template_name']?>"><?php echo $t['template_name']?></label>
                </div>
            <?php endforeach ?>
        </div>

        <div class="d-flex flex-column w-25 mt-2 scegli-categoria">  
                <?php if ($categorie) : ?>
                    <label for="categoria">Scegli la Categoria principale</label> 
                    <select class="mt-2" id="categoria" name="categoria">
                        <?php foreach ($categorie as $i => $c) : ?>
                            <option value="<?php echo $c['categoria_id']?>"><?php echo $c['categoria_nome']?></option>
                        <?php endforeach ?>
                    </select>
                <?php endif ?>
        </div>
        

        <button type="submit" class="btn btn-info mt-4" id="login">Crea</button>
    </form>
</div>

<?php require_once '../../views/partials/js.php'; ?>
