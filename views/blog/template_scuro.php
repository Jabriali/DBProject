<?php 
$template = estraiTemplateInfo($pdo, $blog['template'])[0];
?>

<div class="container-fluid <?php echo $template['blog_sfondo']?> <?php echo $template['blog_colorefont']?>">
    <div class="blog-info d-flex flex-column align-items-center"> 
        <h1 class="mt-4 <?php echo $template['blog_font']?>-2"> 
            <?php echo $blog['blog_titolo'] ?>
        </h1>  
        <p class="lead" >
            <?php echo $blog['blog_descrizione'] ?>
        </p>
        <?php if($autore):?>    
            <a href="../post/nuovo_post.php?id=<?php echo $blog['blog_id']?>&autore=<?php echo $_SESSION['loggato']?>" class="btn btn-info">Nuovo post</a>
        <?php endif ?>
    </div>

    <div class="mt-4 d-flex flex-row flex-wrap justify-content-left">
        <?php foreach ($posts as $i => $post) :?>
            <div class="card bg-dark border border-white mt-2 mb-5 me-5 ms-5 w-25">
                <img src="../<?php echo $post['copertina']?>" class="card-img-top" style="height: 220px;" >
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo $post['post_titolo']?>
                        <?php if($autore):?>    
                            <a href="../post/rimuovi_post.php?post_id=<?php echo $post['post_id']?>&blog_id=<?php echo $blog_id?>" class="text-danger text-decoration-none">x</a>
                        <?php endif ?>
                    </h5> 
                    <p class="card-text"><?php echo $contenuti[$i]?></p>
                    <a class="btn btn-info" href="../post/post.php?id=<?php echo $post['post_id']?>">Visualizza</a>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <span><?php echo $date[$i]?></span>
                    <span><?php echo $post['post_writer']?></span>
                    <span>Like <?php echo $post['num_like']?>  Commenti <?php echo $post['num_commenti']?></span>
                </div>
            </div> 
        <?php endforeach ?>
    </div>
</div>
