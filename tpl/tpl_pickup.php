<? include('tpl_header.php'); ?>

	<? if(isset($_GET['hash'])): ?>
		<?
			$balance = 0;
			$hash = $_GET['hash'];
			$pickup = db_get_pickup_by_hash($hash);
			$USD = FALSE;
			if($pickup)
			{
				$pickup_account = db_get_pickup_account($pickup['id']);
				$account_balance = nano_get_balance($pickup_account['account']);
				
				if($pickup['deleted'])
					$balance = 0;
				else
				{
					nano_receive_pending($pickup_account['wallet'], $pickup_account['account']);
					$balance = NanoHelper::raw2den($account_balance->balance, 'NANO') + NanoHelper::raw2den($account_balance->pending, 'NANO');
					
					$USD = getNanoUSD() * $balance;
				}
			}
			
			if($USD)
			{
				$zero_count = strspn(number_format($USD, 10), "0", strpos(number_format($USD, 10), ".")+1);
				$USD = number_format($USD, $zero_count + 2);
				$USD = ' <span style="font-size:90%;">($'.$USD.' USD)</span>';
			}
		?>
		<div style="font-size: 15px; text-align: center;">
			<h1>NANO Pickup</h1><br />
			
			<div>There's <?= $balance ?> NANO<?= $USD ?> available for withdraw</div><br />
			
			<? if($pickup && !$pickup['deleted'] && $balance != 0): ?>
				<form action="?" method="get" enctype="multipart/form-data">
					<div class="form_input"><div class="input_label">Withdraw address:</div><div class="input_field"><input type="hidden" name="redeem" value="<?= $hash ?>" /><input type="text" name="account" value="" placeholder="nano_" /></div><div class="input_desc"><input type="submit" value="Withdraw" /></div><div class="clr"></div></div>
					<div class="clr"></div>
				</form>
				
				<div style="margin-top: 50px;"><a href="https://natrium.io" target="_blank">Natrium</a> and <a href="https://nault.cc" target="_blank">Nault</a> are both good NANO wallets if you don't have one yet.</div>
			<? endif; ?>
			
		</div>

	<? endif; ?>
						
<? include('tpl_footer.php'); ?>