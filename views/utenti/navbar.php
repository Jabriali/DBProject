<?php 
$httpProtocol = !isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on' ? 'http' : 'https';
$base = $httpProtocol.'://'.$_SERVER['HTTP_HOST'].'/';
?>

<nav class="navbar navbar-dark navbar-expand-lg bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand me-5" href="#">Esquive</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link me-2" aria-current="page" href="../index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2" href="<?php echo $base.'progetto/public/blog/personalizza_blog.php'?>">I miei blog</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2" href="<?php echo $base.'progetto/public/categorie/categorie_blog.php'?>">Categorie</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2" href="#">Blog popolari</a>
        </li>
        <li class="nav-item">
          <a class="nav-link me-2" href="#">Post piaciuti</a>
        </li>
      </ul>
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-outline-success me-5" type="submit">Search</button>
      </form>
      <!-- Sistemare dimensione e underscore link -->
      <a href="<?php echo $base.'progetto/public/utenti/personalizza_utente.php'?>" class="link-info me-3"><?php echo $_SESSION['loggato']; ?></a>
      <a href="<?php echo $base.'progetto/public/utenti/disconnessione.php'?>" class="btn btn-light ma-5" role="button">Disconnetti</a>
    </div>
  </div>
</nav>