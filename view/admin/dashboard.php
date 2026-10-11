<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('admin');
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../model/Category.php';
require_once __DIR__ . '/../../model/Complaint.php';
require_once __DIR__ . '/../../model/Report.php';

$user = new User();
$userList = $user->getAllUsers();
$category = new Category();
$complaint = new Complaint();
$complaintList = $complaint->getAllComplaints();
$report = new Report();
$counts = $report->statusCounts();
$totals = $report->revenueTotals();

$roles = array('customer' => 0, 'photographer' => 0, 'manager' => 0, 'admin' => 0);
foreach ($userList as $u) { if (isset($roles[$u['role']])) $roles[$u['role']]++; }
$recent = array_slice(array_reverse($userList), 0, 5);

$pageTitle = 'Admin dashboard';
$pageSubtitle = 'The whole studio at a glance';
$active = 'dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-4">
	<?php
	echo stat_card('Total users', count($userList), 'users', '', 'total_users');
	echo stat_card('Bookings', array_sum($counts), 'bookings', 's-blue', 'bookings');
	echo stat_card('Confirmed revenue', $totals['confirmed'], 'wallet', 's-green', 'confirmed_revenue', 'money');
	echo stat_card('Open complaints', $complaint->countByStatus('open'), 'message', 's-rose', 'open_complaints');
	?>
</div>

<div class="grid grid-main">
	<div class="card"><div class="card-head"><div><h2>Revenue by month</h2><p>Last 6 months, confirmed bookings</p></div><a class="btn btn-soft btn-sm" href="reports.php">Full reports</a></div>
		<div class="card-body"><div class="chart" data-chart="revenue"><div class="chart-empty">Loading chart...</div></div></div></div>
	<div class="card"><div class="card-head"><div><h2>Booking status</h2></div></div><div class="card-body"><div data-chart="status" class="chart-empty">Loading chart...</div></div></div>
</div>

<div class="grid grid-3">
	<div class="card">
		<div class="card-head"><h2>Users by role</h2></div>
		<div class="card-body">
			<?php $maxRole = max(1, max($roles)); foreach ($roles as $r => $n) { ?>
				<div class="bar-row"><span><?php echo e(ucfirst($r)) ?></span><div class="bar-track"><i style="width:<?php echo round($n / $maxRole * 100) ?>%"></i></div><b><?php echo $n ?></b></div>
			<?php } ?>
		</div>
	</div>
	<div class="card">
		<div class="card-head"><h2>Newest members</h2></div>
		<div class="card-body flush mt-1"><div class="list">
			<?php foreach ($recent as $u) { ?><div class="list-item"><?php echo avatar($u['name'], $u['profile_pic']) ?><div class="grow"><b><?php echo e($u['name']) ?></b><small><?php echo e(ucfirst($u['role'])) ?></small></div></div><?php } ?>
		</div></div>
	</div>
	<div class="card">
		<div class="card-head"><h2>Latest complaints</h2><a class="btn btn-soft btn-sm" href="complaints.php">Manage</a></div>
		<div class="card-body flush mt-1">
			<?php if (!count($complaintList)) { echo empty_state('No complaint', '', 'check'); } ?>
			<div class="list">
			<?php foreach (array_slice($complaintList, 0, 4) as $c) { ?>
				<div class="list-item"><div class="grow"><b><?php echo e($c['subject']) ?></b><small><?php echo e($c['customer_name']) ?></small></div><?php echo status_badge($c['status']) ?></div>
			<?php } ?>
			</div>
		</div>
	</div>
</div>
<p id="ajax_note" class="muted"></p>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
