		</main>
		<footer class="app-foot">Photo Studio Management System &middot; <?php echo date('Y') ?></footer>
	</div>
</div>

<?php echo render_flash(); ?>

<!-- confirm dialog (used by every delete / cancel button) -->
<div class="modal" id="confirmModal" hidden>
	<div class="modal-card">
		<div class="modal-icon"><?php echo icon('alert') ?></div>
		<h3 id="confirmTitle">Are you sure?</h3>
		<p id="confirmText">This action cannot be undone.</p>
		<div class="modal-actions">
			<button type="button" class="btn btn-ghost" id="confirmNo">Cancel</button>
			<button type="button" class="btn btn-danger" id="confirmYes">Yes, continue</button>
		</div>
	</div>
</div>

<!-- quick search / command palette (Ctrl + K) -->
<div class="palette" id="palette" hidden>
	<div class="palette-card">
		<div class="palette-input"><?php echo icon('search') ?><input type="text" id="paletteInput" placeholder="Jump to a page..." autocomplete="off"></div>
		<div class="palette-list" id="paletteList"></div>
	</div>
</div>

<script>
	window.PS = {
		url: <?php echo json_encode(rtrim(app_url(), '/')) ?>,
		role: <?php echo json_encode($_SESSION['role']) ?>,
		nav: <?php
			$pal = array();
			foreach ($navList as $n) { $pal[] = array('label' => $n[1], 'href' => app_url($n[2])); }
			$pal[] = array('label' => 'Profile', 'href' => app_url('view/profile.php'));
			$pal[] = array('label' => 'Change password', 'href' => app_url('view/change-password.php'));
			echo json_encode($pal);
		?>
	};
</script>
<script src="<?php echo e(app_url('js/script.js')) ?>?v=<?php echo (int)@filemtime(__DIR__ . '/../js/script.js') ?>"></script>
</body>
</html>
