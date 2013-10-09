<?php

	require 'passwords.php';
	global $mysql_user;
	global $mysql_pass;
	$connection = mysql_connect("localhost", $mysql_user, $mysql_pass) or die ("Could not connect to localhost");
	$db_select = mysql_select_db("test", $connection) or die ("Could not select database");


	function partyname($partyid)
	{
		if(isset($partyid) && $partyid != "")
		{
			$partyquery = mysql_query("SELECT * FROM `parties` WHERE `id` = ".$partyid) or die(mysql_error());
			if($partyrow = mysql_fetch_array($partyquery))
			{
				return $partyrow['name'];
			}
		}
		$blank = "";
		return $blank;
	}
	
	function patname($patternid)
	{
		if(isset($patternid) && $patternid != "")
		{
			$patternquery = mysql_query("SELECT `name` FROM `pattern` WHERE `id` = ".$patternid) or die(mysql_error());
			if($patternrow = mysql_fetch_array($patternquery))
			{
				return $patternrow['name'];
			}
		}
		$blank = "";
		return $blank;
	}
	
	function empname($empid)
	{
		if(isset($empid) && $empid != "")
		{
			$empquery = mysql_query("SELECT `name` FROM `machining` WHERE `id` = ".$empid) or die(mysql_error());
			if($emprow = mysql_fetch_array($empquery))
			{
				return $emprow['name'];
			}
		}
		$blank = "";
		return $blank;
	}
	
	function prdate($dat)
	{
		if(isset($dat) && $dat != "")
		{
			return substr($dat, 8, 2)."-".substr($dat, 5, 2)."-".substr($dat, 0, 4);
		}
		$blank = "";
		return $blank;
	}
	
	function cleantex($tex)
	{
		if(isset($tex))
		{
			return preg_replace('/[^a-zA-Z0-9_-]/s', '', $tex);
		}
		$blank = "";
		return $blank;
	}
?>