<?php echo render_flash(); ?>
<script>window.PS = { url: <?php echo json_encode(rtrim(app_url(), '/')) ?>, role: '', nav: [] };</script>
<script src="<?php echo e(app_url('js/script.js')) ?>?v=<?php echo (int)@filemtime(__DIR__ . '/../js/script.js') ?>"></script>
</body>
</html>
