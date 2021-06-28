<?
	// --------- INCLUDES

	// Node & DB
	require_once('inc/config.pickup.php');
	require_once('inc/db.pickup.php');
	require_once('inc/page.functions.php');
	require_once('inc/nano.pickup.php');
	require_once('inc/nano.pickup.helper.php');
	
	
	$auth = getAuth();
	
	$error = false;
	nano_receive_all_pending(NANO_MAIN_WALLET);
	cleanUpExpired();
	
	if(isset($_GET['new']))
		include('tpl/tpl_new.php');
	else if(isset($_GET['hash']))
		include('tpl/tpl_pickup.php');
	else if(isset($_GET['redeem']))
		include('tpl/tpl_redeem.php');
	else
		include('tpl/tpl_index.php');
?>