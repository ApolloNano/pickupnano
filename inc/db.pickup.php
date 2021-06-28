<?php
	function get_mysqli()
	{
		$mysqli = new mysqli(MYSQL_DB_HOST, MYSQL_DB_USER, MYSQL_DB_PASSWORD, MYSQL_DB_NAME);
		if ($mysqli->connect_errno)
		{
			printf("Connect failed: %s\n", $mysqli->connect_error);
			exit();
		}
		
		return $mysqli;
	}

	function db_get_pickups($auth)
	{
		$db = get_mysqli();
		$query = $db->query(sprintf("SELECT * FROM pickups WHERE password='%s' AND deleted=%d ORDER BY id DESC", mysqli_escape_string($db, $auth), 0));
		
		if(!$query)
			printf($db->error);
		
		$rows = FALSE;
		while($row = $query->fetch_array())
		{
			$rows[] = $row;
		}

		return $rows;
	}
	function db_insert_pickup_account($pickup_id, $session_hash, $wallet, $private_key, $public_key, $account)
	{
		$hash = rand_sha1(40);
		
		$sellItem = FALSE;
		$sellItem['hash'] = $hash;
		
		$db = get_mysqli();
		$result = $db->query(sprintf("INSERT INTO pickup_accounts
		(
		hash, pickup_id, session_hash, wallet, private_key, public_key, account, time_created
		) 
		VALUES (
		'%s', %d, '%s', '%s', '%s', '%s', '%s', %d
		)", 
		mysqli_escape_string($db, $hash), mysqli_escape_string($db, $pickup_id), mysqli_escape_string($db, $session_hash), mysqli_escape_string($db, $wallet), mysqli_escape_string($db, $private_key), mysqli_escape_string($db, $public_key), 
		mysqli_escape_string($db, $account), time()));
		
		if(!$result)
			printf($db->error);
		
		return $result ? $sellItem : FALSE;
	}
	function db_get_pickup_account_by_hash($hash)
	{
		$db = get_mysqli();
		$query = $db->query(sprintf("SELECT * FROM pickup_accounts WHERE hash='%s' ORDER BY id DESC LIMIT 1", mysqli_escape_string($db, $hash)));
		
		if(!$query)
			printf($db->error);
		
		return $query ? $query->fetch_array() : FALSE;
	}

	function db_delete_pickup($id)
	{
		$db = get_mysqli();
		$result = $db->query(sprintf("UPDATE pickups SET deleted=%d WHERE id=%d", 1, mysqli_escape_string($db, $id)));
		if(!$result)
		{
			//printf($db->error);
			return FALSE;
		}
		
		return TRUE;
	}

	function db_set_pickup_paid($account_id)
	{
		$db = get_mysqli();
		$result = $db->query(sprintf("UPDATE pickup_accounts SET paid=%d WHERE id=%d", 1, mysqli_escape_string($db, $account_id)));
		if(!$result)
		{
			printf($db->error);
			die();
		}
		
		return $result;
	}
	function db_all_expired_accounts($expiry_in_sec)
	{
		$db = get_mysqli();
		$query = $db->query(sprintf("SELECT * FROM pickup_accounts WHERE time_created<%d AND inactive=0 ORDER BY id DESC", time()-$expiry_in_sec));
		
		if(!$query)
			printf($db->error);
		
		$rows = FALSE;
		while($row = $query->fetch_array())
		{
			$rows[] = $row;
		}

		return $rows;
	}
	function db_set_pickup_inactive($account_id)
	{
		$db = get_mysqli();
		$result = $db->query(sprintf("UPDATE pickup_accounts SET inactive=%d WHERE id=%d", 1, mysqli_escape_string($db, $account_id)));
		if(!$result)
		{
			printf($db->error);
			die();
		}
		
		return $result;
	}
	function db_get_pickup_account($pickup_id, $auth = FALSE)
	{
		if($auth)
		{
			$db = get_mysqli();
			$query = $db->query(sprintf("SELECT * FROM pickup_accounts WHERE pickup_id=%d AND session_hash='%s' AND time_created>%d ORDER BY id DESC LIMIT 1", mysqli_escape_string($db, $pickup_id), mysqli_escape_string($db, $auth), time()-PICKUP_EXPIRY));
			
			if(!$query)
				printf($db->error);
		}
		else
		{
			$db = get_mysqli();
			$query = $db->query(sprintf("SELECT * FROM pickup_accounts WHERE pickup_id=%d ORDER BY id DESC LIMIT 1", mysqli_escape_string($db, $pickup_id)));
			
			if(!$query)
				printf($db->error);
		}
		
		return $query ? $query->fetch_array() : FALSE;
	}
	function db_insert_pickup($amount, $currency, $url, $pickup_account, $password)
	{
		$hash = rand_sha1(40);
		
		$sellItem = FALSE;
		$sellItem['hash'] = $hash;
		
		$db = get_mysqli();
		$result = $db->query(sprintf("INSERT INTO pickups
		(
		amount, currency, redirect_url, account, password, hash, time_created
		) 
		VALUES (
		'%s', '%s', '%s', '%s', '%s', '%s', %d
		)", 
		mysqli_escape_string($db, $amount), mysqli_escape_string($db, $currency), mysqli_escape_string($db, $url), mysqli_escape_string($db, $pickup_account), mysqli_escape_string($db, $password), mysqli_escape_string($db, $hash), 
		time()));
		
		if(!$result)
			printf($db->error);
		
		return $result ? mysqli_insert_id($db) : FALSE;
	}
	function db_get_pickup_by_id($id)
	{
		$db = get_mysqli();
		$query = $db->query(sprintf("SELECT * FROM pickups WHERE id='%s' ORDER BY id DESC LIMIT 1", mysqli_escape_string($db, $id)));
		
		if(!$query)
			printf($db->error);
		
		return $query ? $query->fetch_array() : FALSE;
	}
	function db_get_pickup_by_hash($hash)
	{
		$db = get_mysqli();
		$query = $db->query(sprintf("SELECT * FROM pickups WHERE hash='%s' ORDER BY id DESC LIMIT 1", mysqli_escape_string($db, $hash)));
		
		if(!$query)
			printf($db->error);
		
		return $query ? $query->fetch_array() : FALSE;
	}
	
	// CACHING
	function get_cached_item($item)
	{
		$db = get_mysqli();
		$query = $db->query(sprintf("SELECT * FROM pickup_cache WHERE item='%s' ORDER BY id DESC LIMIT 1", mysqli_escape_string($db, $item)));
		
		if(!$query)
			printf($db->error);
		
		return $query ? $query->fetch_array() : FALSE;
	}
	function set_cached_item($item, $value)
	{
		$db = get_mysqli();
		if(!get_cached_item($item))
			$query = $db->query(sprintf("INSERT INTO pickup_cache (item,data,last_change) VALUES ('%s','%s',%d)", mysqli_escape_string($db, $item), mysqli_escape_string($db, $value), time()));
		else
			$query = $db->query(sprintf("UPDATE pickup_cache SET data='%s', last_change=%d WHERE item='%s'", mysqli_escape_string($db, $value), time(), mysqli_escape_string($db, $item)));
		
		if(!$query)
			printf($db->error);
		
		return $query ? TRUE : FALSE;
	}
?>