<?php
	require_once('../config.php');
	session_start();
	echo "read session:<br />";
	echo session_id() ."<br />";
	print_r($_SESSION);
	session_write_close();
?>