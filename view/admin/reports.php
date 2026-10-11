<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('admin');
require_once __DIR__ . '/../../model/Report.php';

$report = new Report();
$counts = $report->statusCounts();
$totals = $report->revenueTotals();
$byCat = $report->byCategory();
$topPkg = $report->topPackages(5);
$topPg = $report->topPhotographers(5);
$total = array_sum($counts);
$done = $counts['completed'];
$rate = $total > 0 ? round($done / $total * 100) : 0;
$cancelRate = $total > 0 ? round(($counts['cancelled'] + $counts['rejected']) / $total * 100) : 0;
$maxCat = 1; foreach ($byCat as $c) { $maxCat = max($maxCat, (float)$c['revenue']); }
$maxPkg = 1; foreach ($topPkg as $p) { $maxPkg = max($maxPkg, (int)$p['bookings']); }

$pageTitle = 'Reports';
$pageSubtitle = 'Revenue, demand and photographer performance';
$active = 'reports';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="actions mb-2">
	<a class="btn btn-ghost btn-sm" href="<?php echo e(app_url('controller/export-handler.php?type=revenue')) ?>"><?php echo icon('download') ?>Revenue CSV</a>
	<a class="btn btn-ghost btn-sm" href="<?php echo e(app_url('controller/export-handler.php?type=bookings')) ?>"><?php echo icon('download') ?>Bookings CSV</a>
	<a class="btn btn-ghost btn-sm" href="<?php echo e(app_url('controller/export-handler.php?type=users')) ?>"><?php echo icon('download') ?>Users CSV</a>
</div>

<div class="grid grid-4">
	<?php
	echo stat_card('Confirmed revenue', $totals['confirmed'], 'wallet', 's-green', 'confirmed_revenue', 'money');
	echo stat_card('Collected (completed)', $totals['collected'], 'check', 's-blue', 'collected_revenue', 'money');
	echo stat_card('Pipeline (pending)', $totals['pipeline'], 'clock', 's-amber', '', 'money');
	echo stat_card('Average rating', $report->averageRating(), 'star', '', '', 'rating');
	?>
</div>

<div class="grid grid-2">
	<div class="card"><div class="card-head"><div><h2>Revenue by month</h2><p>Confirmed bookings, last 6 months</p></div></div><div class="card-body"><div class="chart" data-chart="revenue"><div class="chart-empty">Loading chart...</div></div></div></div>
	<div class="card"><div class="card-head"><div><h2>Bookings per month</h2><p>All requests by shoot date</p></div></div><div class="card-body"><div class="chart" data-chart="bookings"><div class="chart-empty">Loading chart...</div></div></div></div>
</div>

<div class="grid grid-3">
	<div class="card"><div class="card-head"><h2>Revenue by category</h2></div><div class="card-body">
		<?php foreach ($byCat as $c) { ?>
			<div class="bar-row" style="grid-template-columns:90px 1fr 80px"><span><?php echo e($c['name']) ?></span><div class="bar-track"><i style="width:<?php echo round($c['revenue'] / $maxCat * 100) ?>%"></i></div><b><?php echo e(money($c['revenue'])) ?></b></div>
		<?php } ?>
	</div></div>
	<div class="card"><div class="card-head"><h2>Most booked packages</h2></div><div class="card-body">
		<?php foreach ($topPkg as $p) { ?>
			<div class="bar-row" style="grid-template-columns:120px 1fr 30px"><span title="<?php echo e($p['category_name']) ?>"><?php echo e($p['name']) ?></span><div class="bar-track"><i style="width:<?php echo round($p['bookings'] / $maxPkg * 100) ?>%"></i></div><b><?php echo e($p['bookings']) ?></b></div>
		<?php } ?>
	</div></div>
	<div class="card"><div class="card-head"><h2>Health</h2></div><div class="card-body">
		<div class="sum-row"><span>Completion rate</span><b><?php echo $rate ?>%</b></div>
		<div class="sum-row"><span>Cancelled / rejected</span><b><?php echo $cancelRate ?>%</b></div>
		<div class="sum-row"><span>Total reviews</span><b><?php echo e($report->totalReviews()) ?></b></div>
		<div class="sum-row"><span>Total bookings</span><b><?php echo $total ?></b></div>
	</div></div>
</div>

<div class="card">
	<div class="card-head"><h2>Photographer leaderboard</h2></div>
	<div class="card-body flush mt-1"><div class="table-wrap"><table>
		<thead><tr><th>#</th><th>Photographer</th><th>Specialization</th><th>Shoots</th><th>Completed</th><th>Rating</th></tr></thead>
		<tbody>
		<?php foreach ($topPg as $i => $p) { ?>
			<tr><td><?php echo $i + 1 ?></td>
				<td><div class="cell-user"><?php echo avatar($p['name'], $p['profile_pic']) ?><b><?php echo e($p['name']) ?></b></div></td>
				<td><span class="tag"><?php echo e($p['specialization']) ?></span></td>
				<td class="num"><?php echo e($p['total_shoots']) ?></td><td class="num"><?php echo e($p['completed']) ?></td>
				<td><?php echo $p['review_count'] ? stars($p['avg_rating']) . ' <b>' . e($p['avg_rating']) . '</b>' : '<span class="muted">No reviews</span>' ?></td></tr>
		<?php } ?>
		</tbody></table></div></div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
