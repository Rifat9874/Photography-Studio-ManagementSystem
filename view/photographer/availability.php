<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('photographer');
require_once __DIR__ . '/../../model/Availability.php';

$pid = $_SESSION['user_id'];
$availability = new Availability();
$slotList = $availability->getSlotsWithBookingState($pid);

$editSlot = null;
if (isset($_GET['edit_id'])) {
	$editSlot = $availability->getSlotById((int)$_GET['edit_id']);
	if ($editSlot != null && $editSlot['photographer_id'] != $pid) $editSlot = null;
}
// group the slots by date
$days = array();
foreach ($slotList as $s) { $days[$s['available_date']][] = $s; }

$pageTitle = 'Availability';
$pageSubtitle = 'The manager can only assign you on the slots you add here';
$active = 'availability';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-main">
	<div class="card">
		<div class="card-head"><h2>My free dates</h2><span class="muted"><?php echo count($slotList) ?> slot(s)</span></div>
		<div class="card-body flush mt-1">
			<?php foreach ($days as $date => $slots) { $past = $date < date('Y-m-d'); ?>
				<div class="slot-day" <?php echo $past ? 'style="opacity:.55"' : '' ?>>
					<div class="date-box"><b><?php echo e(date('d', strtotime($date))) ?></b><small><?php echo e(date('M', strtotime($date))) ?></small></div>
					<div style="min-width:110px"><b><?php echo e(date('l', strtotime($date))) ?></b><small><?php echo $past ? 'Past' : e(date('Y', strtotime($date))) ?></small></div>
					<div class="slot-pills">
						<?php foreach ($slots as $sl) { if ($sl['booked']) { ?>
							<span class="slot-pill booked" title="A shoot is assigned in this slot"><?php echo e($sl['time_slot']) ?> &middot; booked</span>
						<?php } else { ?>
							<span class="slot-pill"><?php echo e($sl['time_slot']) ?>
								<a href="availability.php?edit_id=<?php echo e($sl['id']) ?>" title="Edit"><?php echo icon('edit') ?></a>
								<form action="<?php echo e(app_url('controller/availability-handler.php')) ?>" method="post" data-confirm="This slot will be removed from your list." data-confirm-title="Remove slot?" data-confirm-yes="Remove">
									<?php echo csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo e($sl['id']) ?>">
									<button type="submit" title="Remove"><?php echo icon('x') ?></button>
								</form>
							</span>
						<?php } } ?>
					</div>
				</div>
			<?php } ?>
			<?php if (!count($days)) { echo empty_state('No free date yet', 'Add dates so you can be assigned to shoots.', 'calendar-check'); } ?>
		</div>
	</div>

	<div class="card">
		<div class="card-head"><h2><?php echo $editSlot ? 'Edit slot #' . e($editSlot['id']) : 'Add availability' ?></h2></div>
		<div class="card-body">
			<form action="<?php echo e(app_url('controller/availability-handler.php')) ?>" method="post">
				<?php echo csrf_field() ?>
				<?php if ($editSlot) { ?>
					<input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?php echo e($editSlot['id']) ?>">
					<div class="field"><label for="available_date">Date</label><input type="date" id="available_date" name="available_date" min="<?php echo date('Y-m-d') ?>" value="<?php echo e($editSlot['available_date']) ?>" required></div>
					<div class="field"><label for="time_slot">Time slot</label>
						<select id="time_slot" name="time_slot"><?php foreach (array('Morning', 'Afternoon', 'Evening') as $sl) { ?><option value="<?php echo $sl ?>" <?php echo $editSlot['time_slot'] == $sl ? 'selected' : '' ?>><?php echo $sl ?></option><?php } ?></select></div>
					<div class="form-actions"><button type="submit" class="btn btn-primary">Update slot</button><a class="btn btn-ghost" href="availability.php">Cancel</a></div>
				<?php } else { ?>
					<input type="hidden" name="action" value="create">
					<div class="field"><label for="available_date" class="req">From date</label><input type="date" id="available_date" name="available_date" min="<?php echo date('Y-m-d') ?>" required></div>
					<div class="field"><label for="end_date">To date (optional, for several days)</label><input type="date" id="end_date" name="end_date" min="<?php echo date('Y-m-d') ?>"></div>
					<div class="field"><div class="label">Time slots</div>
						<div class="flex flex-wrap gap-1">
							<?php foreach (array('Morning', 'Afternoon', 'Evening') as $sl) { ?><label class="check-pill"><input type="checkbox" name="slots[]" value="<?php echo $sl ?>" <?php echo $sl == 'Morning' ? 'checked' : '' ?>><span><?php echo $sl ?></span></label><?php } ?>
						</div></div>
					<div class="field"><label class="check-pill"><input type="checkbox" name="skip_friday" value="1"><span>Skip Fridays</span></label></div>
					<button type="submit" class="btn btn-primary"><?php echo icon('plus') ?>Add availability</button>
				<?php } ?>
			</form>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
