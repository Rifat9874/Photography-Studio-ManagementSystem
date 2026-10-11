<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role(array('customer', 'manager', 'admin'));
require_once __DIR__ . '/../../model/Booking.php';

$booking = new Booking();
$b = $booking->getBookingById(isset($_GET['id']) ? (int)$_GET['id'] : 0);
// a customer may only open his own invoice
if ($b == null || ($_SESSION['role'] == 'customer' && $b['customer_id'] != $_SESSION['user_id'])) {
	flash_error("Invoice not found");
	redirect('index.php');
}
$pageTitle = 'Invoice #' . str_pad($b['id'], 5, '0', STR_PAD_LEFT);
$active = $_SESSION['role'] == 'customer' ? 'my-bookings' : 'bookings';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="actions mb-2 no-print">
	<button class="btn btn-primary btn-sm" onclick="window.print()"><?php echo icon('printer') ?>Print / save as PDF</button>
	<a class="btn btn-ghost btn-sm" href="javascript:history.back()">Back</a>
</div>
<div class="invoice">
	<div class="inv-head">
		<div class="inv-brand"><span class="brand-mark"><?php echo icon('camera') ?></span>Photo Studio</div>
		<div class="inv-title"><h2>INVOICE</h2><small>No. <?php echo e(str_pad($b['id'], 5, '0', STR_PAD_LEFT)) ?> &middot; <?php echo e(date_short($b['created_at'])) ?></small><br><?php echo status_badge($b['status']) ?></div>
	</div>
	<div class="inv-cols">
		<div><small>Billed to</small><b><?php echo e($b['customer_name']) ?></b><br><span class="muted"><?php echo e($b['customer_email']) ?><br><?php echo e($b['customer_phone']) ?></span></div>
		<div><small>Shoot details</small><b><?php echo e(date_pretty($b['booking_date'])) ?></b><br><span class="muted"><?php echo e($b['booking_time']) ?><br><?php echo e($b['location']) ?></span></div>
	</div>
	<table>
		<thead><tr><th>Description</th><th>Duration</th><th class="right">Amount</th></tr></thead>
		<tbody><tr>
			<td><b><?php echo e($b['package_name']) ?></b><br><small><?php echo e($b['category_name']) ?> &middot; <?php echo e($b['inclusions']) ?></small></td>
			<td><?php echo e($b['duration']) ?></td><td class="right num"><?php echo e(money($b['price'])) ?></td>
		</tr></tbody>
	</table>
	<div class="inv-total"><div>
		<div class="sum-row"><span>Photographer</span><b><?php echo e($b['photographer_name'] ? $b['photographer_name'] : 'To be assigned') ?></b></div>
		<div class="sum-total"><span>Total</span><b class="num"><?php echo e(money($b['price'])) ?></b></div>
	</div></div>
	<div class="inv-foot">Thank you for choosing Photo Studio. This invoice was generated on <?php echo date('d M Y') ?>.</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
