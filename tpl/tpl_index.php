<? include('tpl_header.php'); ?>


	<? if($error): ?>
		<div style="font-weight: bold; color: red;"><?= $error ?></div>
	<? endif; ?>
	
	<div id="create_box">
		<h1>Create a NANO Pickup</h1>
		<div>Send or give someone NANO without having their NANO address.</div>
		<div>Create a link or QR code that allows others to pick up NANO instead.</div><br />
		<form action="?new" method="post" enctype="multipart/form-data">
			<input type="submit" class="button-fancy-large cta create-pickup" value="Create Pickup" />
		</form>
	</div>
	
	<? if(isset($_GET['delete'])): ?>
	
		<? $pickup = db_get_pickup_by_hash($_GET['delete']); ?>
		<? if($pickup)
			{
				$result = db_delete_pickup($pickup['id']);
				if($result)
				{
					echo '<h1 style="color: green;">Pickup Deleted!</h1>';
					$pickup_account = db_get_pickup_account($pickup['id']);
					nano_send_all_back($pickup_account['account'], $pickup_account['wallet']);
				}
			}
		?>
	<? endif; ?>
	
	<div class="box">
		<h1>Withdraw from Pickup</h1>
		<form action="?" method="get" enctype="multipart/form-data">
			<div class="form_input"><div class="input_label">Code</div><div class="input_field"><input type="text" name="hash" value="" placeholder="" /></div><div class="input_desc"><input type="submit" value="Redeem" /></div><div class="clr"></div></div>
			<div class="clr"></div>
		</form>
	</div>
	
	<?
		$pickups = db_get_pickups($auth);
		if($pickups)
		{?>
			<h1>Pickup Links</h1>
			<? foreach($pickups as $pickup){ ?>
				<div style="margin-bottom: 15px; border: 1px solid #ddd; background: #fff; padding: 15px; ">
					<div>Code: <?= $pickup['hash'] ?></div>
					<div>Account: <?= $pickup['account'] ?></div>
					<div>URL: https://pickupnano.com/?hash=<?= $pickup['hash'] ?></div>
					<div>Created: <?= date('Y-m-d H:i:s', $pickup['time_created']) ?></div>
					<div><a href="?delete=<?= $pickup['hash'] ?>">Delete</a></div>
				</div>
				
			<? } ?>
		<?}
	?>

	<div class="box">
		<h1>Authenticate</h1>
		
		<? if(isset($_GET['login']) && isset($_POST['auth'])): ?>
			<? if(strlen($_POST['auth']) != 40): ?>
				<h2 style="color: red;">Invalid Key!</h2>
			<? else: ?>
				<h2 style="color: green;">Personal Key Set!</h2>
			<? endif; ?>
		<? endif; ?>
		<form action="?login" method="post" enctype="multipart/form-data">
			<div class="form_input"><div class="input_label">Personal Key</div><div class="input_field"><input type="text" name="auth" value="" placeholder="" /></div><div class="input_desc"><input type="submit" value="Login" /></div><div class="clr"></div></div>
			<div class="clr"></div>
		</form>
	</div>
	
	<div style="margin: 50px 0; text-align: center;">
		Donations to run this service are appreciated: <?= DONATION_ACCOUNT ?><br />
		admin@pickupnano.com
	</div>
						
<? include('tpl_footer.php'); ?>