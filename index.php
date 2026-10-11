<?php
require_once __DIR__ . '/includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
	redirect('view/home.php');
}
redirect(home_for_role($_SESSION['role']));
