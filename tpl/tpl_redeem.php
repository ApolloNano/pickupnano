<? include('tpl_header.php'); ?>

	<? if(isset($_GET['redeem'])): ?>
		<?
			$balance = 0;
			$hash = $_GET['redeem'];
			$payout_account = $_GET['account'];
			$pickup = db_get_pickup_by_hash($hash);
			$result = FALSE;
			$error = false;
			
			if(!$payout_account)
			{
				$error = 'Please go back and specify a nano address';
			}
			
			if(!$error)
			{
				if($pickup && !$pickup['deleted'])
				{
					$pickup_account = db_get_pickup_account($pickup['id']);
					nano_receive_pending($pickup_account['wallet'], $pickup_account['account']);
					$account_balance = nano_get_balance($pickup_account['account']);
					
					if($account_balance->balance > 0 || $account_balance->pending > 0)
					{
						
						$result = nano_send_money($payout_account, $pickup_account['wallet'], $pickup_account['account'], $account_balance->balance, $hash.'-'.time(), true);
						if(isset($result->error) && stripos($result->error, 'Account not found in wallet') !== FALSE)
						{
							nano_add_wallet($pickup_account['wallet'], $pickup_account['private_key']);
							$result = nano_send_money($payout_account, $pickup_account['wallet'], $pickup_account['account'], $balance->balance, $hash.'-'.time(), true);
							
							if(isset($result->block))
								db_set_pickup_paid($pickup_account['id']);
						}
					}
				}
			}
		?>
		<div style="font-size: 15px; text-align: center;">
			<h1>NANO Pickup</h1><br />
			
			<? if($error): ?>
				<?= $error ?>
			<? else: ?>
				<? if(!$result): ?>
					No payment could be done
				<? elseif(isset($result->error)): ?>
					There was an error sending NANO: <?= $result->error ?>
				<? elseif(isset($result->block)): ?>
					Transaction was successful!<br />
					Your transaction ID is <?= $result->block ?>
				<? endif; ?>
			<? endif; ?>
		</div>

	<? endif; ?>
						
<? include('tpl_footer.php'); ?>