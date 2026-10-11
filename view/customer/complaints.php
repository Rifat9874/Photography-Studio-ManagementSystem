<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('customer');
require_once __DIR__ . '/../../model/Complaint.php';
require_once __DIR__ . '/../../model/Booking.php';

$customerId = $_SESSION['user_id'];
$complaint = new Complaint();
$complaintList = $complaint->getComplaintsByCustomer($customerId);
$booking = new Booking();
$bookingList = $booking->getBookingsByCustomer($customerId);

$pageTitle = 'Complaints';
$pageSubtitle = 'Tell us what went wrong and track the reply';
$active = 'complaints';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-main">
	<div class="card">
		<div class="card-head"><h2>My complaints</h2></div>
		<div class="card-body flush mt-1">
			<?php foreach ($complaintList as $c) { ?>
				<div class="ticket">
					<div class="flex between flex-wrap gap-1 items-center">
						<b><?php echo e($c['subject']) ?> <small>#<?php echo e($c['id']) ?><?php echo $c['booking_id'] ? ' &middot; booking #' . e($c['booking_id']) : '' ?></small></b>
						<?php echo status_badge($c['status']) ?>
					</div>
					<small><?php echo e(date_short($c['created_at'])) ?></small>
					<div class="ticket-msg"><?php echo e($c['message']) ?></div>
					<?php if ($c['admin_reply'] != '') { ?><div class="ticket-reply"><small>Admin reply</small><?php echo e($c['admin_reply']) ?></div>
					<?php } else { ?><small class="muted">No reply yet.</small><?php } ?>
				</div>
			<?php } ?>
			<?php if (count($complaintList) == 0) { echo empty_state('No complaint', 'We hope it stays that way.', 'message'); } ?>
		</div>
	</div>
	<div class="card">
		<div class="card-head"><h2>Submit a complaint</h2></div>
		<div class="card-body">
			<form action="<?php echo e(app_url('controller/complaint-handler.php')) ?>" method="post">
				<?php echo csrf_field() ?><input type="hidden" name="action" value="create">
				<div class="field"><label for="booking_id">Related booking (optional)</label>
					<select id="booking_id" name="booking_id">
						<option value="">Not related to a booking</option>
						<?php foreach ($bookingList as $b) { ?><option value="<?php echo e($b['id']) ?>">#<?php echo e($b['id'] . ' - ' . $b['package_name'] . ' (' . date_short($b['booking_date']) . ')') ?></option><?php } ?>
					</select></div>
				<div class="field"><label for="subject" class="req">Subject</label><input type="text" id="subject" name="subject" maxlength="150" required></div>
				<div class="field"><label for="message" class="req">Message</label><textarea id="message" name="message" required></textarea></div>
				<button type="submit" class="btn btn-primary">Submit complaint</button>
			</form>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
