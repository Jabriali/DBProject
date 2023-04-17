<?php
# Avviamo la sessione
session_start();

# Richiamo il codice presente nella pagina database.php e ottengo l'oggetto PDO per interrogare al database
$pdo = require_once '../../database.php';
require_once '../../query.php';
require_once '../../funzioni.php';

# Array utilizzato per contenere le stringhe che specificano gli errori durante 
# la fase di inserimento dei dati nel form
$errori = [];

$verificato = controlla_verificato($_SESSION['loggato'], $pdo);

# Acquisiamo i dati relativi all'utente loggato dal database
$dati = estraiDatiUtente($pdo, $_SESSION['loggato']);

$nome = "";
$cognome = "";
$email = "";
$Remail = "";
$password = "";
$Rpassword = "";
$Npassword = "";
$telefono = "";
$Rtelefono = "";
$docu_id = "";
$Rdocu_id = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    # Utilizziamo la funzione trim per rimuovere caratteri particolari e spazi bianchi dai dati inseriti nel form
    # All'interno della funzione trim utilizziamo strtolower per trasformare la stringa in caratteri minuscoli
    # Su alcuni campi utilizziamo la funzione ucfirst per mettere in maiuscolo solo il primo carattere della stringa
    $nome = trim(ucfirst($_POST['nome']));
    $cognome = trim(ucfirst($_POST['cognome']));
    $email = trim(strtolower($_POST['email']));
    $Remail = trim(strtolower($_POST['Remail']));
    $password = trim($_POST['password']);
    $Rpassword = trim($_POST['Rpassword']);
    $Npassword = trim($_POST['Npassword']);
    $docu_id = trim($_POST['docu_id']);
    $Rdocu_id = trim($_POST['Rdocu_id']);
    $telefono = trim($_POST['telefono']);
    $Rtelefono = trim($_POST['Rtelefono']);
    $password_hash = $dati['password_hash'];

     # Controllo per verificare che il nome e il cognome non siano maggiori di 50 caratteri
     if($nome && $nome != $dati['nome']) {
        if (strlen($nome) > 50) {
            $nome = null;
            $errori[] = "Il nome inserito è troppo lungo";
        }
    }
    
    if($cognome && $cognome != $dati['cognome']) {
        if(strlen($cognome) > 50) {
            $cognome = null;
            $errori[] = "Il cognome inserito è troppo lungo";
        }
    }

    # Controlliamo che l'utente abbia aggiornato il campo email con un email diversa da quella memorizzata nel database, oppure se
    # l'utente ha inserito una nuova mail nel campo di ripetizione dell'email
    if($email && $email != $dati['email'] || $Remail) {
        if (empty($email) || empty($Remail)) { # Controlliamo che entrambi i campi siano stati compilati
            $errori[] = "Compila tutti i campi email";
            var_dump($email, $Remail);   
        } else {
            if ($email != $Remail) { # Controlliamo che le email inserite siano uguali
                $errori[] = "Le due email inserite devono coincidere";
                } else { # Se le email inserite sono uguali, controlliamo se la nuova email è uguale a quella dell'utente già registrata nel database
                    if ($Remail === $dati['email']) {
                        $Remail = null;    
                        $errori[] = 'Il nuovo indirizzo email inserito deve essere diverso dal precedente';
                    } elseif (strlen($Remail) > 254) { # Controlliamo se l'email è meno lunga di 254 caratteri
                        $Remail = null;
                        $errori[] = "Il tuo indirizzo email è troppo lungo";
                    } elseif (!preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', $Remail)) {
                        # Controlliamo se l'email è valida
                        $Remail = null;    
                        $errori[] = 'Inserisci un indirizzo email valido';
                    } else {
                        $email_duplicata = verificaEmailDuplicata($pdo, $Remail); # Verifichiamo se l'email inserita è duplicata
                        if ($email_duplicata) {
                            $Remail = null;
                            $errori[] = "La nuova email inserita è già registrata";
                        }
                    }
                   
                }
            }
        }

        # Controllo delle password 
        if($password || $Npassword || $Rpassword) {
            if ($password) {
                if (empty($Npassword) || empty($Rpassword)) {
                    $errori[] = "Compila tutti i campi della password";
                }
            } elseif ($Npassword) {
                if (empty($password) || empty($Rpassword)) {
                    $errori[] = "Compila tutti i campi della password";
                }
            } else {
                if (empty($password) || empty($Npassword)) {
                    $errori[] = "Compila tutti i campi della password";
                }
            }

            $hashpw = hash('sha512', $password);
            $hashNpw = hash('sha512', $Npassword);
            $hashRpw = hash('sha512', $Rpassword);
            
            if ($hashpw != $dati['password_hash']) {
                $errori[] = "La password attuale non è corretta";
            }

            if ($hashNpw != $hashRpw) {
                $errori[] = "Le due nuove password non corrispondono";
            }
            else {
                if ($hashNpw === $dati['password_hash']) {
                    $errori[] = "Devi inserire una password diversa da quella attuale";
                } else {
                    # Controllo per verificare che la password sia valida
                    if (!preg_match('/^(?=\P{Ll}*\p{Ll})(?=\P{Lu}*\p{Lu})(?=\P{N}*\p{N})(?=[\p{L}\p{N}]*[^\p{L}\p{N}])[\s\S]{10,}$/', $Npassword)) {
                        $errori[] = 'La password non rispetta i requisiti';
                    }
                    else {
                        $password_hash = $hashNpw;
                    }
                }
            }
        }

        if($docu_id && $docu_id != $dati['docu_id'] || $Rdocu_id) {
            if (empty($docu_id) || empty($Rdocu_id)) {
                $errori[] = "Compila tutti i campi del documento";  
            } else {
                if ($docu_id != $Rdocu_id) {
                    $errori[] = "I due numeri di documento inseriti devono coincidere";
                    } 
                    else {
                        if ($Rdocu_id === $dati['docu_id']) {   
                            $errori[] = 'Il nuovo telefono inserito deve essere diverso dal precedente';
                        } 
                        elseif (!preg_match('/^[A-Za-z0-9]{5,40}$/',$Rdocu_id)) {
                            $errors[] = "Il documento d'identità inserito non è valido. ";
                        }
                        else {
                            $docu_duplicato = verificaDocuDuplicato($pdo, $docu_id);
                            if ($docu_duplicato) {
                                $errori[] = "Il nuovo numero di documento inserito è già stato inserito";
                        }
                    }
                    
                }
            }
        }

        if($telefono && $telefono != $dati['telefono'] || $Rtelefono) {
            if (empty($telefono) || empty($Rtelefono)) {
                $errori[] = "Compila tutti i campi del telefono";  
            } else {
                if ($telefono != $Rtelefono) {
                    $errori[] = "I due numeri di telefono inseriti devono coincidere";
                    } else {
                        if ($Rtelefono === $dati['telefono']) {
                            $Rtelefono = null;    
                            $errori[] = 'Il nuovo telefono inserito deve essere diverso dal precedente';
                        } 
                        elseif (!preg_match('/^[0-9]{6,30}$/', $Rtelefono)) {
                            $errors[] = 'Il numero di telefono non è valido. Deve essere lungo almeno 6 cifre e massimo 30.';
                        } 
                        else {
                            $telefono_duplicato = verificaTelefonoDuplicato($pdo, $telefono);
                            if ($telefono_duplicato) {
                                $Rtelefono = null;
                                $errori[] = "Il nuovo telefono inserito è già stato inserito";
                        }
                    }
                    
                }
            }
        }

        if (empty($errori)) {
            
            if(!isset($_SESSION["verificato"])) {
                if ($telefono != '' && $docu_id != '') {
                    try {
                        $statement = $pdo->prepare("UPDATE utente SET nome = :nome, cognome = :cognome, email = :email,
                        password_hash = :password_hash, telefono = :telefono, docu_id = :docu_id, verificato = :verificato 
                        WHERE username = :username");
                       
                        $statement->bindValue(':username',$_SESSION['loggato']);
                        $statement->bindValue(':nome', $nome);
                        $statement->bindValue(':cognome', $cognome);
                        $statement->bindValue(':email', $email);
                        $statement->bindValue(':password_hash', $password_hash);
                        $statement->bindValue(':telefono', $telefono);
                        $statement->bindValue(':docu_id', $docu_id);
                        $statement->bindValue(':verificato', 1);
                        $statement->execute();
                    } catch (\Throwable $th) {
                        throw $th;
                    }
            
                    if (controlla_verificato($_SESSION['loggato'], $pdo)) {
                        $_SESSION['verificato'] = true;      
                    }

                    header("Location: personalizza_utente.php");
                } 
            }
          
            try {
                $statement = $pdo->prepare("UPDATE utente SET nome = :nome, cognome = :cognome, email = :email,
                password_hash = :password_hash, telefono = :telefono, docu_id = :docu_id WHERE username = :username");
    
                $statement->bindValue(':username',$_SESSION['loggato']);
                $statement->bindValue(':nome', $nome);
                $statement->bindValue(':cognome', $cognome);
                $statement->bindValue(':email', $email);
                $statement->bindValue(':password_hash', $password_hash);
                $statement->bindValue(':telefono', $telefono);
                $statement->bindValue(':docu_id', $docu_id);
            
                $statement->execute();
            } catch (\Throwable $th) {
                throw $th;
            }
           
            header("Location: personalizza_utente.php");
        }
    }
?>

<!-- Richiamo l'header e la navbar utilizzata dai visitatori -->
<?php require_once '../../views/partials/header.php'; ?>
<?php require_once '../../views/utenti/navbar.php'; ?>

<!-- Verificare permanenza dell'input nel form dopo errore -->
<div class="container mt-5 mb-5">
    
    <form method="post">
        <div class="text-center">
            <div>
                <?php if ($dati['avatar'] === null) : ?>
                    <img class="w-25 img-fluid rounded" src="..\images\profiloutenti\default.png" alt="user profile picture">
                <?php else : ?>
                    <img class="user-avatar" src="<?php echo $dati['avatar'] ?>" alt="user profile picture">
                <?php endif ?>
            </div>
            <div class="mt-4 mb-5">
                <input type="file" accept="image/png, image/gif, image/jpeg, image/jpg" id="upload-avatar-input" name="avatar" hidden> 
                <button class="btn btn-dark" type="" id="upload-avatar-button">Change avatar</button>
            </div>
        </div>
        <?php require_once '../../views/partials/controllo.php'; ?>
        <div class="form-group needs-validation mb-3">
            <label for="exampleInputEmail1">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" value="<?php echo $dati['nome']?>">
        </div>
        <div class="form-group mb-3">
            <label for="exampleInputEmail1">Cognome</label>
            <input type="text" class="form-control" id="cognome" name="cognome" placeholder="Cognome" value="<?php echo $dati['cognome']?>">
        </div>
        <div class="form-group mt-5">
            <div class="form-group mb-3">
                <label for="exampleInputEmail1">Indirizzo Email </label>
                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp"
                    placeholder="Inserisci email" value="<?php echo $dati['email']?>">
            </div>
            <div class="form-group mb-3">
                <label for="exampleInputEmail1">Ripeti Indirizzo Email</label>
                <input type="email" class="form-control" id="Remail" name="Remail" aria-describedby="emailHelp"
                    placeholder="Ripeti l'email">
            </div>
        </div>
        
        <div class="form-group mt-5">
            <div class="form-group mb-3">
                <label for="password">Password Attuale</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            </div>
            <div class="form-group mb-3">
                <label for="Npassword">Nuova Password</label>
                <input type="password" class="form-control" id="Npassword" name="Npassword" placeholder="Nuova Password">
            </div>
            <div class="form-group mb-3">
                <label for="Rpassword">Ripeti Nuova Password</label>
                <input type="password" class="form-control" id="Rpassword" name="Rpassword" placeholder="Riepti Nuova Password">
                <div id="passwordHelpBlock" class="form-text">
                    La password deve avere minimo 10 caratteri, almeno una lettera maiuscola, un numero e un carattere speciale.
                </div>
            </div>
        </div>

        <div class="form-group mt-5">
            <div class="form-group mb-3">
                <label for="exampleInputPassword1">Numero Documento</label>
                <input type="text" class="form-control" id="docu_id" name="docu_id" placeholder="Documento ID" 
                    value="<?php echo $dati['docu_id']?>">
            </div>
            <div class="form-group mb-3">
                <label for="exampleInputPassword1">Ripeti Numero Documento</label>
                <input type="text" class="form-control" id="Rdocu_id" name="Rdocu_id" placeholder="Nuovo Documento ID">
            </div>
        </div>

        <div class="form-group mt-5">
        <div class="form-group mb-3">
                <label for="exampleInputPassword1">Numero di Telefono</label>
                <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Numero di Telefono" 
                value="<?php echo $dati['telefono']?>">
            </div>
            <div class="form-group mb-3">
                <label for="exampleInputPassword1">Nuovo Numero di Telefono</label>
                <input type="text" class="form-control" id="Rtelefono" name="Rtelefono" placeholder="Nuovo Numero di Telefono">
            </div>
        </div>
        
        <button type="submit" class="btn btn-info mt-4" id="registrati">Salva</button>
        <a href="cancella_utente.php" class="btn btn-danger mt-4" id="registrati">Cancella Account</button>
    </form>
</div>

<?php require_once '../../views/partials/js.php'; ?>
