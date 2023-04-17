<?php if (!empty($errori)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errori as $errore): ?>
            <div><?php echo $errore ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>