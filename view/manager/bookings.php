<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('manager');
require_once __DIR__ . '/../../model/Booking.php';

$booking = new Booking();
$bookingList = $booking->getAllBookings();

$pageTitle = 'Booking requests';
$pageSubtitle = 'Approve, assign and follow every booking';
$active = 'bookings';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="flex between flex-wrap gap-2 items-center mb-2">
	<div class="tabs" data-tabs-for="bookTable">
		<button class="tab active" data-status="all">All <span class="count"></span></button>
		<?php foreach (array('pending', 'approved', 'assigned', 'completed', 'rejected', 'cancelled') as $st) { ?><button class="tab" data-status="<?php echo $st ?>"><?php echo ucfirst($st) ?> <span class="count"></span></button><?php } ?>
	</div>
	<a class="btn btn-ghost btn-sm" href="<?php echo e(app_url('controller/export-handler.php?type=bookings')) ?>"><?php echo icon('download') ?>Export CSV</a>
</div>
<div class="card">
	<div class="card-body flush">
		<div class="table-wrap">
		<table id="bookTable" data-table data-placeholder="Search customer, package, location...">
			<thead><tr><th>ID</th><th>Customer</th><th>Package</th><th>Price</th><th>Date</th><th>Time</th><th>Location</th><th>Photographer</th><th>Status</th><th>Actions</th></tr></thead>
			<tbody>
			<?php foreach ($bookingList as $b) { ?>
				<tr data-status="<?php echo e($b['status']) ?>">
					<td>#<?php echo e($b['id']) ?></td>
					<td><div class="cell-user"><?php echo avatar($b['customer_name']) ?><div><b><?php echo e($b['customer_name']) ?></b><small><?php echo e($b['customer_phone']) ?></small></div></div></td>
					<td><b><?php echo e($b['package_name']) ?></b><small class="muted" style="display:block"><?php echo e($b['category_name']) ?></small></td>
					<td data-sort="<?php echo e($b['price']) ?>" class="nowrap num"><?php echo e(money($b['price'])) ?></td>
					<td data-sort="<?php echo e($b['booking_date']) ?>" class="nowrap"><?php echo e(date_short($b['booking_date'])) ?></td>
					<td><?php echo e($b['booking_time']) ?></td>
					<td class="wrap"><?php echo e($b['location']) ?></td>
					<td><?php echo e($b['photographer_name'] ? $b['photographer_name'] : '-') ?></td>
					<td><?php echo status_badge($b['status']) ?></td>
					<td><div class="actions">
						<?php if ($b['status'] == 'pending') { foreach (array('approve' => array('Approve', 'btn-success'), 'reject' => array('Reject', 'btn-danger-soft')) as $act => $cfg) { ?>
							<form class="inline-form" action="<?php echo e(app_url('controller/assign-handler.php')) ?>" method="post">
								<?php echo csrf_field() ?><input type="hidden" name="action" value="<?php echo $act ?>"><input type="hidden" name="id" value="<?php echo e($b['id']) ?>">
								<button class="btn btn-sm <?php echo $cfg[1] ?>" type="submit"><?php echo $cfg[0] ?></button>
							</form>
						<?php } } ?>
						<?php if ($b['status'] == 'approved' || $b['status'] == 'assigned') { ?><a class="btn btn-primary btn-sm" href="assign.php?id=<?php echo e($b['id']) ?>"><?php echo $b['status'] == 'approved' ? 'Assign' : 'Change' ?></a><?php } ?>
						<a class="btn btn-ghost btn-sm" href="<?php echo e(app_url('view/customer/invoice.php?id=' . $b['id'])) ?>" title="Invoice"><?php echo icon('file') ?></a>
						<form class="inline-form" action="<?php echo e(app_url('controller/assign-handler.php')) ?>" method="post" data-confirm="Booking #<?php echo e($b['id']) ?> and its review will be deleted permanently." data-confirm-title="Delete booking?" data-confirm-yes="Delete">
							<?php echo csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo e($b['id']) ?>">
							<button class="btn btn-danger-soft btn-sm" type="submit" title="Delete"><?php echo icon('trash') ?></button>
						</form>
					</div></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		</div>
	</div>
</div>
<?php if (!count($bookingList)) { echo '<div class="card card-pad">' . empty_state('No booking in the system', '', 'bookings') . '</div>'; } ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
