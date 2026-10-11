<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('admin');
require_once __DIR__ . '/../../model/Complaint.php';

$complaint = new Complaint();
$complaintList = $complaint->getAllComplaints();

$pageTitle = 'Complaint management';
$pageSubtitle = 'Reply to customers and close the loop';
$active = 'complaints';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="tabs mb-2" data-tabs-for="ticketList">
	<button class="tab active" data-status="all">All <span class="count"></span></button>
	<?php foreach (array('open', 'in progress', 'resolved', 'closed') as $st) { ?><button class="tab" data-status="<?php echo $st ?>"><?php echo ucfirst($st) ?> <span class="count"></span></button><?php } ?>
</div>
<div id="ticketList" data-list data-page-size="5" data-placeholder="Search subject, customer...">
<?php foreach ($complaintList as $c) { ?>
	<div class="card" data-item data-status="<?php echo e($c['status']) ?>">
		<div class="ticket" style="border:0">
			<div class="flex between flex-wrap gap-2 items-center">
				<div class="cell-user"><?php echo avatar($c['customer_name'], $c['customer_pic']) ?><div><b><?php echo e($c['subject']) ?></b><small><?php echo e($c['customer_name']) ?> &middot; <?php echo e($c['customer_email']) ?> &middot; <?php echo e(date_short($c['created_at'])) ?><?php echo $c['booking_id'] ? ' &middot; booking #' . e($c['booking_id']) : '' ?></small></div></div>
				<?php echo status_badge($c['status']) ?>
			</div>
			<div class="ticket-msg"><?php echo e($c['message']) ?></div>
			<form action="<?php echo e(app_url('controller/complaint-handler.php')) ?>" method="post">
				<?php echo csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?php echo e($c['id']) ?>">
				<div class="form-grid">
					<div class="field full"><label for="reply<?php echo e($c['id']) ?>">Your reply</label><textarea id="reply<?php echo e($c['id']) ?>" name="admin_reply" placeholder="Write the reply for the customer"><?php echo e($c['admin_reply']) ?></textarea></div>
					<div class="field"><label for="st<?php echo e($c['id']) ?>">Status</label>
						<select id="st<?php echo e($c['id']) ?>" name="status"><?php foreach (array('open', 'in progress', 'resolved', 'closed') as $st) { ?><option value="<?php echo $st ?>" <?php echo $c['status'] == $st ? 'selected' : '' ?>><?php echo $st ?></option><?php } ?></select></div>
				</div>
				<div class="actions"><button type="submit" class="btn btn-primary btn-sm">Save reply</button></div>
			</form>
			<form class="inline-form" style="margin-top:8px;display:block" action="<?php echo e(app_url('controller/complaint-handler.php')) ?>" method="post" data-confirm="This complaint will be deleted permanently." data-confirm-title="Delete complaint?" data-confirm-yes="Delete">
				<?php echo csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo e($c['id']) ?>">
				<button class="btn btn-danger-soft btn-sm" type="submit"><?php echo icon('trash') ?>Delete</button>
			</form>
		</div>
	</div>
<?php } ?>
</div>
<?php if (!count($complaintList)) { echo '<div class="card card-pad">' . empty_state('No complaint submitted yet', '', 'message') . '</div>'; } ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
