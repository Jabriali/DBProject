<?php

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=progetto', 'root', '');

// Mostrano gli errori nel caso di problemi con le interrogazioni del database
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

return $pdo;