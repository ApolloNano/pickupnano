<?

	$pickup = false;
	if(isset($_GET['new']))
	{
		$new_account = nano_create_new_account();
		
		$pickup_id = db_insert_pickup(0, '', '', $new_account['account'], $auth);
		db_insert_pickup_account($pickup_id, $auth, $new_account['wallet'], $new_account['private_key'], $new_account['public_key'], $new_account['account']);
		
		$pickup_account = db_get_pickup_account($pickup_id, $auth);
		$pickup = db_get_pickup_by_id($pickup_id);
	}
	
?><? include('tpl_header.php'); ?>
						
		<? if(isset($_GET['new'])): ?>
			<? $url = 'https://pickupnano.com/?hash='.$pickup['hash']; ?>
			
			<? if($pickup && $pickup_account): ?>
				<div style="margin: auto; text-align: center; ">
					<h1>Pickup Link Created!</h1>
				
					<div class="text_padding">Send NANO to this address for pickup.</div>
					<div class="qr_image" id="qr_account"></div>
					<pre><?= $pickup_account['account'] ?></pre>
				</div>
				<div>Copy this link or redeem code and give it to whoever you want to receive the NANO from this pickup.</div>
				<div><pre class="text_box"><?= $url ?></pre></div>
				<div><pre class="text_box">Redeem Code: <?= $pickup['hash'] ?></pre></div>
				<div>Unpicked up NANO on this account will be sent back in 30 days</div>
				
				<script type="text/javascript">
					var uri = "nano:<?= $pickup['account'] ?>";
					new QRCode(document.getElementById("qr_account"), {text: uri, width:230, height:230, colorDark: '#222', colorLight: 'transparent'});
				</script>
				<h2>Personal Key</h2>
				Please save your personal key to manage and remove pickups as well as your private key.
				<pre class="text_box">Personal Key: <?= $auth ?></pre>
				<pre class="text_box">Private Key: <?= $new_account['private_key'] ?></pre>
				
				<h2>QR for Pickup</h2>
				<div class="text_padding">Give this QR code to anyone you want to receive the NANO from this pickup.</div>
				<div class="qr_image" id="qr_pickup"></div>
				<script type="text/javascript">
					var uri = "<?= $url ?>";
					new QRCode(document.getElementById("qr_pickup"), {text: uri, width:230, height:230, colorDark: '#222', colorLight: 'transparent'});
				</script>
			<? endif; ?>
		<? endif; ?>

		<? if($error): ?>
			<div style="font-weight: bold; color: red;"><?= $error ?></div>
		<? endif; ?>

		
		<div class="disclaimer">
			It's not recommended to store larger amounts of NANO on this service.<br />
			This service can undergo maintenance, downtimes and changes to it's service.
		</div>

						
<? include('tpl_footer.php'); ?>