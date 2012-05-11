<?php
	print_r($_SESSION);
	session_start();
	if(isset($_SESSION['clientWidth'])){
		unset($_SESSION['clientWidth']);
	}
	echo "clearing session...";
	session_write_close();
?>