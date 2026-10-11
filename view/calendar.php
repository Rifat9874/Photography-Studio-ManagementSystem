<?php
$pageTitle = 'Calendar';
$pageSubtitle = 'Click a day to see its bookings';
$active = 'calendar';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="cal-layout">
	<div class="card" style="margin-bottom:0">
		<div class="cal-head">
			<h2 id="calTitle">Loading...</h2>
			<div class="cal-nav">
				<button class="icon-btn" id="calPrev" aria-label="Previous month"><?php echo icon('chev-left') ?></button>
				<button class="btn btn-ghost btn-sm" id="calToday">Today</button>
				<button class="icon-btn" id="calNext" aria-label="Next month"><?php echo icon('chev-right') ?></button>
			</div>
		</div>
		<div class="cal-grid" id="calendar"></div>
	</div>
	<div class="card" style="margin-bottom:0">
		<div class="card-head"><h2 id="calDayTitle">Day</h2></div>
		<div class="card-body" id="calDay"></div>
	</div>
</div>
<div class="legend mt-2">
	<?php foreach (array('pending', 'approved', 'assigned', 'completed', 'cancelled') as $st) { ?>
		<span class="legend-item"><i style="background:var(--c-<?php echo $st ?>)"></i><?php echo ucfirst($st) ?></span>
	<?php } ?>
	<?php if ($_SESSION['role'] == 'photographer') { ?><span class="legend-item"><i style="background:#0d9488"></i>Free slot</span><?php } ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
