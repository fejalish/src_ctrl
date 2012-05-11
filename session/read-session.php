<?php
	echo "read session:<br />";
	session_start();
	echo session_id() ."<br />";
	print_r($_SESSION);
	session_write_close();
?>