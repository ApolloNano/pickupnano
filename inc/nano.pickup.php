<?php

	function nano_curl ($post) {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, NODE_ADDRESS);
		curl_setopt($ch, CURLOPT_HEADER, 1); 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-length: '.strlen($post)));

		$output = curl_exec($ch);
		curl_close($ch);
		
		return $output;
	}
	function nano_add_wallet($wallet, $key)
	{
		$post = '{"action": "wallet_add", "wallet": "'.$wallet.'", "key": "'.$key.'"}';
		$result = nano_curl($post);
		return json_decode($result);
	}
	function nano_account_remove($wallet, $account)
	{
		$post = '{"action": "account_remove", "wallet": "'.$wallet.'", "account": "'.$account.'"}';
		$result = nano_curl($post);
		return json_decode($result);
	}
	function nano_create_new_account()
	{
		$WALLET = FALSE;
		
		// First generate a key
		$post = '{"action": "key_create"}';
		$key = json_decode(nano_curl($post));
		$WALLET['private_key'] = $key->private;
		$WALLET['public_key'] = $key->public;
		$WALLET['account'] = $key->account;
		$WALLET['wallet'] = NANO_MAIN_WALLET;

		// Add private key to wallet
		$post = '{"action": "wallet_add", "wallet": "'.NANO_MAIN_WALLET.'", "key": "'.$key->private.'"}';
		$account = json_decode(nano_curl($post));
		
		return $WALLET;
	}
	function nano_send_money($nano_address, $wallet, $source, $nano_amount, $uniq_id = '', $is_raw = false)
	{
		if($uniq_id != '')
			$uniq_id = md5($uniq_id);
		
		$work = false;
		if($is_raw)
			$raw_amount = $nano_amount;
		else
			$raw_amount = NanoHelper::den2raw($nano_amount, 'NANO');
		$post = '"action": "send", "wallet": "'.$wallet.'", "source": "'.$source.'", "destination": "'.$nano_address.'", "amount": "'.$raw_amount.'", "id": "'.$uniq_id.'"';
		if($work)
			$post .= ', "work":"'.$work.'"';
		$post = '{'.$post.'}';
		
		$result = json_decode(nano_curl($post));
		
		return $result;
	}
	function nano_get_balance($account)
	{
		$post = '{"action": "account_balance", "account":"'.$account.'"}';
		$balance = json_decode(nano_curl($post));
	
		return $balance;
	}
	function nano_receive_block($wallet, $account, $block)
	{
		$post = '{"action": "receive", "wallet":"'.$wallet.'", "account":"'.$account.'", "block":"'.$block.'"}';
		$result = json_decode(nano_curl($post));
		
		return $result;
	}
	function nano_wallet_contains($wallet, $account)
	{
		$post = '{"action": "wallet_contains", "wallet":"'.$wallet.'", "account":"'.$account.'"}';
		$result = json_decode(nano_curl($post));
		
		return $result;
	}
	function nano_receive_pending($wallet, $account)
	{
		$post = '{"action": "pending", "account": "'.$account.'", "count": "10"}';
		$result = json_decode(nano_curl($post));

		if(!isset($result->blocks) || empty($result->blocks))
			return;
		
		foreach($result->blocks as $block)
		{
			$post = '{"action": "receive", "wallet":"'.$wallet.'", "account":"'.$account.'", "block":"'.$block.'"}';
			$result = json_decode(nano_curl($post));
		}
	}
	function nano_receive_all_pending($wallet)
	{
		$post = '{"action": "wallet_pending", "wallet": "'.$wallet.'", "count": "10"}';
		$result = json_decode(nano_curl($post));

		if(!isset($result->blocks) || empty($result->blocks))
			return;
		
		foreach($result->blocks as $account => $blocks)
		{
			foreach($blocks as $block)
			{
				$post = '{"action": "receive", "wallet":"'.$wallet.'", "account":"'.$account.'", "block":"'.$block.'"}';
				$result = json_decode(nano_curl($post));
			}
		}
	}
	function nano_send_all_back($account, $wallet = FALSE)
	{
		if(!$wallet)
			$wallet = NANO_MAIN_WALLET;
		$success = TRUE;
		
		$post = '{"action": "account_history", "account": "'.$account.'", "count": "20"}';
		$result = json_decode(nano_curl($post));

		if(!isset($result->history) || empty($result->history))
			return;
		
		$send_back = array();
		foreach($result->history as $entry)
		{
			if($entry->type == 'receive')
				array_push($send_back, array('account' => $entry->account, 'amount' => $entry->amount, 'height' => $entry->height));
			/*if($entry['type'] == 'send')
				$send_back[$entry['account']] -= NanoHelper::raw2den($entry['amount'], 'NANO');*/
		}
		
		foreach($send_back as $send)
		{
			$result = nano_send_money($send['account'], $wallet, $account, $send['amount'], $account.'-expired-'.$send['height'], true);
			
			if(!isset($result->block))
				$success = FALSE;
		}
			
		return $success;
	}
?>