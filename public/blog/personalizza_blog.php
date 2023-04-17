<?php
# Avviamo la sessione
session_start();

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

# Si controlla se l'utente ha effettuato il login, controllando che nella variabile globale $_SESSION 
# Sia contenuta la coppia chiave-valore "Loggato"-<username>
controllaAutorizzati();

# Verifichiamo se è presente il valore id nella query GET, necessario per ottenere le informazioni sul blog
controllaURLAccesso(["id"], "blog_utente.php");
$blog_id = $_GET['id'];
$dati = estraiBlogInfo($pdo, $blog_id)[0];

# Controlliamo che l'username dell'utente loggato corrisponda all'username del creatore del blog
controllaAccessoAmministrazione($dati['blog_founder']);

# Estraiamo una lista contenente tutti i temi globali utilizzabili nel sito 
$template = estraiTemplate($pdo);

# Estraiamo una lista contenente tutti i Coautori del blog
$coautori = estraiCoautori($pdo, $blog_id);

# Estraiamo una lista contenente tutte le categorie globali utilizzabili nel sito
$categorie = estraiCategorie($pdo);

# Estraiamo le categorie associate al blog. La funzione interroga il database restituendo un solo array associativo
# nel caso al blog sia associata solo la categoria principale. Nel caso al blog sia associata anche la categoria
# secondaria viene restituito un array contenente due array associativi formati da {categoria_id, categoria_nome} per
# entrambe le categorie impostate. 
$categoria_blog = estraiCategoriaBlog($pdo, $blog_id)[0] ?? null;

if ($categoria_blog) {
    $categoria_principale = estraiDatiCategoria($pdo, $categoria_blog['categoria_id'])[0];
    if($categoria_blog['sub_id']) {
        $categoria_secondaria = estraiDatiCategoria($pdo, $categoria_blog['sub_id'])[0];
    } else {
        $categoria_secondaria = false;
    }

    # Infine, una volta appurato se al blog è stata associata una categoria, interroghiamo il database per ottenere una lista di tutte 
    # le sottocategorie della categoria principale.
    if($categoria_principale) {
        $sottocategorie = estraiSottoCategorie($pdo, $categoria_principale['categoria_id']);
    }   
} else {
    $categoria_principale = null;
}

# Array utilizzato per contenere le stringhe che specificano gli errori durante 
# la fase di inserimento dei dati nel form
$errori = [];

$blog_titolo = '';
$blog_descrizione = '';
$coautore_inserito = '';
$tema_scelto = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (array_key_exists('rimuoviCoautore', $_POST)) {
        cancellaAutore($pdo, $_POST['rimuoviCoautore'], $_GET['id']);
    } elseif (array_key_exists('rimuoviCategoria', $_POST)) {
        cancellaBlogCategoria($pdo, $_POST['rimuoviCategoria'], $_GET['id']);
    } elseif (array_key_exists('cancellaSottocategoria', $_POST)) {
        cancellaBlogSottocategoria($pdo, $_GET['id']);
    }

    if(isset($_POST['info'])) {
        # Utilizziamo la funzione trim per rimuovere caratteri particolari e spazi bianchi dai dati inseriti nel form
        # All'interno della funzione trim utilizziamo strtolower per trasformare la stringa in caratteri minuscoli
        # Su alcuni campi utilizziamo la funzione ucfirst per mettere in maiuscolo solo il primo carattere della stringa
        $blog_titolo = trim(ucfirst($_POST['blog_titolo']));
        $blog_descrizione = trim($_POST['blog_descrizione']);
        $tema_scelto = (isset($_POST['Template'])) ?  trim(ucfirst($_POST['Template'])) : $dati['template'];

        if (!$blog_titolo) {
            $errori[] = 'Il campo titolo è richiesto';
        } 
        elseif (strlen($blog_titolo) < 5 || strlen($blog_titolo) > 45) {
            $blog_titolo = null;
            $errori[] = "Il titolo del blog dev'essere incluso tra i 5 e i 45 caratteri";
        } 
        elseif (!preg_match('/[A-Za-z0-9!?.,-;\s]{5,45}/', $blog_titolo)) {
            var_dump($blog_titolo);
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

        if (empty($errori)) {
            try {
                $statement = $pdo->prepare("UPDATE blog SET blog_titolo = :blog_titolo, blog_descrizione = :blog_descrizione, 
                    template = :tema_scelto WHERE blog_id = :blog_id");
    
                $statement->bindValue(':blog_id',$blog_id);
                $statement->bindValue(':blog_titolo',$blog_titolo);
                $statement->bindValue(':blog_descrizione', $blog_descrizione);
                $statement->bindValue(':tema_scelto', $tema_scelto);
            
                $statement->execute();
            } catch (\Throwable $th) {
                throw $th;
            }
    
            header("Location: personalizza_blog.php?id=$blog_id");
        }

    }

    if(isset($_POST['inserisci_coautore'])){
        $coautore_inserito = trim(strtolower($_POST['coautore']));
      
        if ($coautore_inserito) {
            # Controllo per verificare che l'utente non abbia caratteri speciali apparte il punto, il trattino e il trattino basso
            if (!preg_match('/^[A-Za-z0-9_.-]{2,36}$/', $coautore_inserito)) {
               $coautore_inserito = null;
               $errori[] = "L'username del coautore inserito non è valido";
           } elseif  (strlen($coautore_inserito) < 5 || strlen($coautore_inserito) >= 20) {
               $coautore_inserito = null;
               $errori[] = "L'username del coautore inserito non è valido";
           } else {
               # Controllo per verificare se esistono username duplicati nel database
               $utente_duplicato = verificaUtenteDuplicato($pdo, $coautore_inserito);
   
               if (!$utente_duplicato) {
                   $errori[] = "L'username del coautore inserito non esiste";
               }
   
               foreach ($coautori as $coautore) {
                   if ($coautore['username'] == $coautore_inserito) {
                       $errori[] = "L'utente è già un coautore del blog";
                   }
               }
           } 
        } else {
            $errori[] = "Il campo coautore non può essere vuoto";
        }
        
        if (empty($errori)) {
            inserisciCoautore($pdo, $coautore_inserito, $blog_id);
            header("Location: personalizza_blog.php?id=$blog_id");
        }
    }

    if(isset($_POST['associa_categoria'])){
        $categoria_associata = $_POST['categoria'] ?? $_POST['sottocategoria']; 
       
        # Controllo che esista la chiave categoria associata nell'array $_POST
        if($categoria_associata) {

            # Creo un arrray contenete tutti gli id delle categorie principali e secondarie esistenti
            $categorie_esistenti = [];
            foreach($categorie as $categoria) {
                $categorie_esistenti[] = $categoria['categoria_id'];
            }

            if ($sottocategorie) {
                foreach($sottocategorie as $sottocategoria) {
                    $categorie_esistenti[] = $sottocategoria['categoria_id'];
                }
            }
            
            # Verifico che l'id della categoria associata corrisponda ad uno delle categorie già esistenti
            if(!in_array($categoria_associata, $categorie_esistenti)){
                $errori[]="La categoria selezionata non è valida";
            }
        } else {
            $errori[]="Non hai impostato nessuna categoria";
        }
        
        if (empty($errori)) {
            if ($categoria_associata <= 12) {
                associaBlogCategoria($pdo, $categoria_associata, $blog_id);
            } else {
                associaSottocategoria($pdo, $categoria_associata, $blog_id);
            }
            
            header("Location: personalizza_blog.php?id=$blog_id");
        }
    }

}
?>

<!-- Richiamo l'header e la navbar utilizzata dai visitatori -->
<?php require_once '../../views/partials/header.php'; ?>
<?php require_once '../../views/utenti/navbar.php'; ?>


<div class="container mt-5 mb-5">
    <h1 class="mb-4">Personalizza il blog <?php echo $dati['blog_titolo']?></h1>
    <?php require_once '../../views/partials/controllo.php'; ?>

    <h2 class="mb-3">Informazioni Generali</h2>
    <form method="post">
        <div class="form-group needs-validation mb-3">
            <label for="blog_titolo">Titolo del Blog</label>
            <input type="text" class="form-control" id="blog_titolo" name="blog_titolo" placeholder="Nome"
                value="<?php echo $dati['blog_titolo']?>">
        </div>

        <div class="form-group mb-3">
            <label for="blog_descrizione">Descrizione del blog</label>
            <textarea class='form-control' id="blog_descrizione" name="blog_descrizione" cols="1" rows="1"
                placeholder="Inserisci una descrizione del blog"><?php echo $dati['blog_descrizione'] ?></textarea>
        </div>

        <div class="form-group needs-validation mb-3">
            <label class="mb-2" for="Template">Template</label>
            <?php foreach ($template as $i => $t) : ?>
            <div class="radio-element">
                <input class="me-1 mb-2" type="radio" id="<?php echo $t['template_name']?>" name="Template"
                    value="<?php echo $t['template_name']?>">
                <label for="<?php echo $t['template_name']?>"><?php echo $t['template_name']?></label>
            </div>
            <?php endforeach ?>
        </div>

        <button type="submit" name="info" class="btn btn-info mt-2">Salva</button>
    </form>

    <h2 class="mt-5 mb-3">Coautori</h2>
    <form method="post">
        <div class="form-group needs-validation mb-3">
                <?php if ($coautori) : ?>
                    <?php foreach ($coautori as $i => $coautore) : ?>
                    <span class="mt-2 me-1 ms-1 <?php echo $coautore['username']?>-span"><a href="<?php echo $blog_id ?>" class="text-decoration-none cancella_couatore"
                            id="<?php echo $coautore['username']?>"><?php echo $coautore['username'] ?> x</a></span>
                    <?php endforeach ?>
                <?php endif ?>
       
            <div class="d-flex align-items-center mt-2">
                <input type="text" class="w-25 form-control" id="coautore" name="coautore" maxlength="20"
                    placeholder="Username del coautore">
            </div>
        </div>

        <button type="submit" name="inserisci_coautore" class="btn btn-info mt-2">Inserisci</button>
    </form>

    <h2 class="mt-5 mb-3">Categorie</h2>
    <form method="post">
        <div class="form-group needs-validation mb-3">
            <?php if ($categoria_blog) : ?>
                <?php if (!$categoria_blog['sub_id']) : ?>
                    <p class="categoria-container-<?php echo $categoria_principale['categoria_id']?> mt-2">
                        <span class="me-1 ms-1">
                            <b>Categoria principale:</b>
                            <a href="<?php echo $blog_id ?>" class="text-decoration-none cancella_categoria-principale"
                                id="<?php echo $categoria_principale['categoria_id']?>"><?php echo $categoria_principale['categoria_nome'] ?> 
                            <span class="text-danger">x</span> </a>
                        </span>
                    </p>
                <?php else: ?>
                    <p class="categoria-container-<?php echo $categoria_principale['categoria_id']?> mt-2">
                        <span class="me-1 ms-1">
                            <b>Categoria principale:</b>
                            <?php echo $categoria_principale['categoria_nome'] ?>
                        </span>
                    </p>
                <?php endif ?>
                
                <?php if ($categoria_secondaria) : ?>
                    <p class="categoria-container-<?php echo $categoria_secondaria['categoria_id']?> mt-1">
                    <span class="me-1 ms-1">
                        <b>Sottocategoria:</b>
                        <a href="<?php echo $blog_id ?>" class="text-decoration-none cancella_categoria-secondaria"
                            id="<?php echo $categoria_secondaria['categoria_id']?>"><?php echo $categoria_secondaria['categoria_nome'] ?> 
                        <span class="text-danger">x</span></a>
                    </span>
                </p>
                <?php endif ?>
            <?php endif ?>
           
            <div class="d-flex flex-column w-25 mt-2 scegli-categoria">
                <?php if ($categorie and !$categoria_principale) : ?>
                    <select class="mt-2" id="categoria" name="categoria">
                        <label for="categoria">Scegli la Categoria principale</label> 
                        <?php foreach ($categorie as $i => $c) : ?>
                            <option value="<?php echo $c['categoria_id']?>"><?php echo $c['categoria_nome']?></option>
                        <?php endforeach ?>
                    </select>
                <?php  elseif ($categorie and !$categoria_secondaria) :?>
                    <label for="categoria">Scegli la Sotto Categoria</label>
                    <select class="mt-2" id="sottocategoria" name="sottocategoria">
                        <?php foreach ($sottocategorie as $i => $categoria) : ?>
                            <option value="<?php echo $categoria['categoria_id']?>"><?php echo $categoria['categoria_nome']?></option>
                        <?php endforeach ?>
                    </select>
                <?php endif ?>
            </div>
        </div>

        <button type="submit" id="associa" name="associa_categoria" class="btn btn-info mt-1">Associa</button>
    </form>
</div>

<?php require_once '../../views/partials/js.php'; ?>
<script src="../js/personalizza_blog.js"></script>