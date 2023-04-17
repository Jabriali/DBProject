<?php
session_start();

if(isset($_SESSION["loggato"])) {
    header("Location: ../index.php");  
}

$pdo = require_once '../../database.php';
require_once '../../funzioni.php';
require_once '../../query.php';

$errori = [];

$username = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(strtolower($_POST['username']));
    $password = trim($_POST['password']);

    $registrato = verificaUtenteDuplicato($pdo, $username);

    if (!$registrato) {
        $errori[] = "L'username non è registrato";
    }

    if($registrato) {
        $password_hash = estraiPasswordHash($pdo, $username);
        $hash = hash('sha512', $password);

        if ($hash != $password_hash) {
            $errori[] = 'La password inserita non è corretta. Riprova!';
        }
    }

    if (empty($errori)) {
        crea_sessione($username);

        if (controlla_verificato($_SESSION['loggato'], $pdo)) {
            $_SESSION['verificato'] = true;      
        }
        header("Location: ../index.php");
    }
}

?>

<?php require_once '../../views/partials/header.php'; ?>
<?php require_once '../../views/utenti/navbar_visitatore.php'; ?>

<div class="container mt-5 mb-5">

    <?php require_once '../../views/partials/controllo.php'; ?>
    
    <form method="post">
        <div class="form-group mb-3">
            <label for="exampleInputEmail1">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
        </div>
        
        <button type="submit" class="btn btn-info mt-4" id="login">Login</button>
    </form>
</div>

<?php require_once '../../views/partials/js.php'; ?>


<?php require_once '../../views/partials/js.php'; ?>
