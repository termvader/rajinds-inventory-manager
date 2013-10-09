<?php
	require 'db_connect.php';
	global $connection;
	echo "{\n";
	if(isset($_POST['partyid']))
	{
		$query = mysql_query("SELECT * FROM `pattern` WHERE `party` = ".$_POST['partyid']) or die(mysql_error());
		$b = false;
		while($row = mysql_fetch_array($query))
		{
			if($b == true)
			{
				echo ",\n";
			}
			$b = true;
			echo "\"".mysql_real_escape_string($row['id'])."\": \"".mysql_real_escape_string($row['name'])."\"";
		}
	}
	echo "\n}";
?>