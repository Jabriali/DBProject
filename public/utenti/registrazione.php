<?php
# Avviamo la sessione
session_start();

# Si controlla se l'utente ha effettuato il login, controllando che nella variabile globale $_SESSION 
# Sia contenuta la coppia chiave-valore "Loggato"-<username>
if(isset($_SESSION["loggato"])) {
    header("Location: index.php");
}

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';

# Array utilizzato per contenere le stringhe che specificano gli errori durante 
# la fase di inserimento dei dati nel form
$errori = [];

$username = '';
$nome = '';
$cognome = '';
$email = '';
$password = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    # Utilizziamo la funzione trim per rimuovere caratteri particolari e spazi bianchi dai dati inseriti nel form
    # All'interno della funzione trim utilizziamo strtolower per trasformare la stringa in caratteri minuscoli
    # Su alcuni campi utilizziamo la funzione ucfirst per mettere in maiuscolo solo il primo carattere della stringa
    $username = trim(strtolower($_POST['username']));
    $nome = trim(ucfirst($_POST['nome']));
    $cognome = trim(ucfirst($_POST['cognome']));
    $email = trim(strtolower($_POST['email']));
    $password = trim($_POST['password']);

    # Effettuiamo l'hash, attraverso il metodo crittografico SHA512, della password inserita nel form
    $hash = hash('sha512', $password);
 

    if (!$nome) {
        $errori[] = 'Il campo nome è richiesto';
    }

    if (!$cognome) {
        $errori[] = 'Il campo cognome è richiesto';
    }

    # Controllo per verificare che il nome e il cognome non siano maggiori di 50 caratteri
    if (strlen($nome) > 50 || strlen($cognome) > 50) {
        $nome = NULL;
        $cognome = NULL;
        $errori[] = "Il nome e/o il cognome sono troppo lunghi";
    }

    # Controlli per verificare se i campi del form non siano vuoti
    if (!$username) {
        $errori[] = 'Il campo username è richiesto';
    } else {
        # Controllo per verificare se esistono username duplicati nel database
        $utente_duplicato = verificaUtenteDuplicato($pdo, $username);

        # Se l'array utente_duplicato non è vuoto, allora inserisco la stringa di errore nell'array errori
        if ($utente_duplicato) {
            $username = null;
            $errori[] = "Nome utente già registrato";
        } else {
            # Controllo per verificare che l'username sia compreso tra 5 e 20 caratteri
            if (strlen($username) < 5 || strlen($username) >= 20) {
                $username = NULL;
                $errori[] = "L'username dev'essere incluso tra i 5 e i 20 caratteri";
            }
            # Controllo per verificare che l'utente non abbia caratteri speciali apparte il punto, il trattino e il trattino basso
            if (!preg_match('/^[A-Za-z0-9_.-]{2,36}$/', $username)) {
                $username = NULL;
                $errori[] = 'Il tuo nome utente può contenere solo i seguenti caratteri speciali . - _';
            }
        }
    }

    if (!$email) {
        $errori[] = 'Il campo email è richiesto';
    } else {
        # Controllo per verificare se esistono mail duplicate nel database
        $email_duplicata = verificaEmailDuplicata($pdo, $email);

        # Se l'array email_duplicata non è vuoto, allora inserisco la stringa di errore nell'array errori
        if ($email_duplicata) {
            $email = null;
            $errori[] = "Email già registrata";
        } else {
            # Controllo per verificare che la mail sia più corta di 254 caratteri
            if (strlen($email) > 254) {
                $email = NULL;
                $errori[] = "Il tuo indirizzo email è troppo lungo";
            }
            # Controllo per verificare l'email sia valida
            if (!preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', $email)) {
                $email = NULL;    
                $errori[] = 'Inserisci un indirizzo email valido';
            }
        }
    }

    if (!$password) {
        $errori[] = 'Il campo password è richiesto';
    }

    # Controllo per verificare che la password sia valida 
    if (!preg_match('/^(?=\P{Ll}*\p{Ll})(?=\P{Lu}*\p{Lu})(?=\P{N}*\p{N})(?=[\p{L}\p{N}]*[^\p{L}\p{N}])[\s\S]{10,}$/', $password)) {
        $errori[] = 'La password non è valida';
    }

    # Controllo che l'array errori sia vuoto. Se è vuoto inserisco l'utente nel database, altrimenti mostro gli errori
    if (empty($errori)) {
        try {
            $statement = $pdo->prepare("INSERT INTO utente (username, nome, cognome, email, password_hash)
                VALUES (:username, :nome, :cognome, :email, :password_hash)");
            $statement->bindValue(':username', $username);
            $statement->bindValue(':nome', $nome);
            $statement->bindValue(':cognome', $cognome);
            $statement->bindValue(':email', $email);
            $statement->bindValue(':password_hash', $hash);

            $statement->execute();
        } catch (\Throwable $th) {
            throw $th;
        }
        
        $_SESSION['loggato'] = $username;
        $username = $_SESSION['username'];

        header("Location: registrazione_2.php");}
    }

?>

<!-- Richiamo l'header e la navbar utilizzata dai visitatori -->
<?php require_once '../../views/partials/header.php'; ?>
<?php require_once '../../views/utenti/navbar_visitatore.php'; ?>

<!-- Verificare permanenza dell'input nel form dopo errore -->
<div class="container mt-5 mb-5">

    <?php require_once '../../views/partials/controllo.php'; ?>
    
    <form method="post">
        <div class="form-group mb-3">
            <label for="exampleInputEmail1">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo $username ?>" required>
        </div>
        <div class="form-group needs-validation mb-3">
            <label for="exampleInputEmail1">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" value="<?php echo $nome ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="exampleInputEmail1">Cognome</label>
            <input type="text" class="form-control" id="cognome" name="cognome" placeholder="Cognome" value="<?php echo $cognome?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="exampleInputEmail1">Indirizzo Email</label>
            <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp"
                placeholder="Enter email" value="<?php echo $email ?>" required>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <div id="passwordHelpBlock" class="form-text">
                La password deve avere minimo 10 caratteri, almeno una lettera maiuscola, un numero e un carattere speciale.
            </div>
        </div>
        
        <button type="submit" class="btn btn-info mt-4" id="registrati">Registrati</button>
    </form>
</div>

<?php require_once '../../views/partials/js.php'; ?>
