<?php
	require_once('config.php');
	session_start();
	parse_str($_SERVER['QUERY_STRING'], $q);
	if( isset($q['cw']) && is_numeric($q['cw']) ){
		$_SESSION['clientWidth'] = $q['cw'];
	}
	echo $q['cw']." ".$_SESSION['clientWidth'];
	session_write_close();
	header('Content-type: image/gif');
	readfile($local_host.'/screen.gif');
?>