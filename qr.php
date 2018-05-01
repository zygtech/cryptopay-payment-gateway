<?php
	require_once('phpqrcode/qrlib.php');
	QRcode::png( 'cryptocoin:' . $_GET['addr'] );
?>
