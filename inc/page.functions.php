<?php

	function receivePending()
	{
		$accounts = db_all_active_accounts();
		
		if($expired_accounts)
		{
			foreach($expired_accounts as $account)
			{
				nano_receive_pending($account['wallet'], $account['account']);
			}
		}
	}
	function cleanUpExpired()
	{
		$expired_accounts = db_all_expired_accounts(PICKUP_EXPIRY);
		
		if($expired_accounts)
		{
			foreach($expired_accounts as $account)
			{
				if(empty($account['account']))
					continue;
				
				$success = TRUE;
				$balance = nano_get_balance($account['account']);
				
				if($balance->balance > 0 || $balance->pending > 0)
				{
					$account_added = nano_wallet_contains($account['wallet'], $account['account']);;
					if(isset($account_added->exists) && !$account_added->exists)
						nano_add_wallet($account['wallet'], $account['private_key']);
					nano_receive_pending($account['wallet'], $account['account']);
					$success = nano_send_all_back($account['account'], $account['wallet']);
				}
								
				if($success)
				{
					nano_account_remove($account['wallet'], $account['account']);
					db_set_pickup_inactive($account['id']);
				}
			}
		}
	}
	function getNanoUSD()
	{
		$cache = get_cached_item('NANO_USD_VALUE');
		if($cache && $cache['last_change'] > (time() - 60*10))
			return json_decode($cache['data'], TRUE)['nano']['usd'];
		
		$data = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=nano&vs_currencies=usd');
		set_cached_item('NANO_USD_VALUE', $data);
		
		return json_decode($data, TRUE)['nano']['usd'];
	}
	function getAuth()
	{
		$auth = FALSE;
		if(isset($_GET['login']) && isset($_POST['auth']) && strlen($_POST['auth']) == 40)
		{
			$auth = htmlspecialchars($_POST['auth']);
			setcookie('auth', $auth, time() + (10 * 365 * 24 * 60 * 60), '/');
		}
		else
		{
			if(!isset($_COOKIE['auth']))
			{
				$auth = rand_sha1(40);
				setcookie('auth', $auth, time() + (10 * 365 * 24 * 60 * 60), '/');
			}
			else
				$auth = $_COOKIE['auth'];
		}
		
		return $auth;
	}
	
	function getCurrencyRates()
	{
		$json_data = FALSE;
		$cached_item = get_cached_item('CURRENCIES');
		if($cached_item == FALSE || (time() > ($cached_item['last_change'] + 60*60)))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://openexchangerates.org/api/latest.json?base=USD&app_id=49549cb131e64245af9b8915ff5faf64");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$json_data = curl_exec($ch);
			curl_close($ch);
			
			set_cached_item('CURRENCIES', $json_data);
		}
		else
			$json_data = $cached_item['data'];
		
		return json_decode($json_data,  TRUE)['rates'];
	}
	function rand_sha1($length)
	{
		$max = ceil($length / 40);
		$random = '';
		for ($i = 0; $i < $max; $i ++) {
			$random .= sha1(microtime(true).mt_rand(10000,90000));
		}
		return substr($random, 0, $length);
	}
?>