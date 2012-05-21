<?php
	require_once('../config.php');
	session_start();
	print_r($_SESSION);
	if(isset($_SESSION['clientWidth'])){
		unset($_SESSION['clientWidth']);
	}
	echo "clearing session...";
	session_write_close();
?>