<?php
    session_start();
    
    require_once '../views/partials/header.php'; 

    if(isset($_SESSION["loggato"])) {
        require_once '..//views/utenti/navbar.php';
    } else {
        require_once '../views/utenti/navbar_visitatore.php';
    }

?>



</body>
</html>