<?php
// Decorative "product preview" cards for the hero sections ($mockGlass = true on dark backgrounds).
$stats = isset($stats) ? $stats : array('rating' => 0, 'reviews' => 0);
$rating = $stats['rating'] > 0 ? number_format($stats['rating'], 1) : '5.0';
?>
<div class="mock-stage<?php echo !empty($mockGlass) ? ' mock-glass' : '' ?>" aria-hidden="true">
	<div class="mock-card mock-main">
		<div class="mock-row">
			<span class="mock-ic"><?php echo icon('camera') ?></span>
			<div class="grow"><b>Wedding Premium</b><small>Sat, 24 Oct &middot; Morning &middot; Banani, Dhaka</small></div>
			<span class="badge badge-assigned">Assigned</span>
		</div>
		<div class="steps mock-steps">
			<div class="step done"><span class="step-dot"><?php echo icon('check') ?></span><span class="step-line"></span></div>
			<div class="step done"><span class="step-dot"><?php echo icon('check') ?></span><span class="step-line"></span></div>
			<div class="step now"><span class="step-dot">3</span><span class="step-line"></span></div>
			<div class="step"><span class="step-dot">4</span></div>
		</div>
		<div class="mock-row mock-foot">
			<?php echo avatar('Israt Jahan', '', 'sm') ?>
			<div class="grow"><b>Israt Jahan</b><small>Your photographer</small></div>
			<b class="num">Tk 25,000</b>
		</div>
	</div>
	<div class="mock-card mock-rate">
		<small>Customer rating</small>
		<div class="mock-score"><?php echo $rating ?><span> / 5</span></div>
		<?php echo stars((float)$rating) ?>
	</div>
	<div class="mock-card mock-ok">
		<span class="mock-ic ok"><?php echo icon('check') ?></span>
		<div><b>Booking approved</b><small>Photographer assigned just now</small></div>
	</div>
</div>
