<html>
<head>
	<title>Edit Parties</title>
	<style>
		html
		{
			font-family:Arial, Helvetica, sans-serif;
		}
		#editprev
		{
			border-collapse:collapse;
		}
	</style>
	<link rel="shortcut icon" href="favicon2.ico" />
</head>
<body>

<?php
	//session_start();
	require 'db_connect.php';
	global $connection;
	if(isset($_POST['partyname']))
	{
		//Save the party
		$insquery = mysql_query("INSERT INTO `parties` (`id`, `name`) VALUES (NULL, '".mysql_real_escape_string($_POST['partyname'])."');") or die(mysql_error());
	}
	else if(isset($_POST['editparty']))
	{
		$editquery = mysql_query("UPDATE `parties` SET  `name`='".mysql_real_escape_string($_POST['editname'])."' WHERE `parties`.`id` = ".$_POST['editparty']." LIMIT 1") or die(mysql_error());
	}
	else if(isset($_POST['delparty']))
	{
		$orderquery = mysql_query("SELECT * FROM `ordermain` WHERE `partyid` = ".$_POST['delparty'].";") or die(mysql_error());
		if($row = mysql_fetch_array($orderquery))
		{
			echo "Delete the associsted order/s with this party first to delete this party.";
			//$wtquery = mysql_query("DELETE FROM `weight` WHERE `orderid` = ".$row['id'].";") or die(mysql_error());
		}
		else
		{
			$patquery = mysql_query("DELETE FROM `pattern` WHERE `pattern`.`party` = ".$_POST['delparty'].";") or die(mysql_error());
			$delquery = mysql_query("DELETE FROM `parties` WHERE `parties`.`id` = ".$_POST['delparty'].";") or die(mysql_error());
		}
	}
?>
	
	<h1 align="center">Party Entry</h1>
	<form action="edit_parties.php" method="post">
        <table align="center" cellpadding="10">
            <tr>
                <td valign="middle">Party Name</td>
                <td><input name="partyname" type="text" size="80"/></td>
                <td><input type="submit" value="Add" /></td>
            </tr>
        </table>
	</form>

<br />
<br />

<table align="center" cellpadding="10" id="editprev" >
<tr>
	<th>Party Name</th>
	<th></th>
	<th></th>
</tr>
<?php
	$query = mysql_query("SELECT * FROM `parties` WHERE `id` <> 1 ORDER by `name` ASC;") or die(mysql_error());
	while($row = mysql_fetch_array($query))
	{
		echo "<tr><form action=\"edit_parties.php\" method=\"post\">";
			echo "<td><input type=\"text\" name=\"editname\" size=\"50\" value=\"".$row['name']."\" /></td>";
			echo "<td><input type=\"hidden\" name=\"editparty\" value=\"".$row['id']."\" />";
			echo "<input type=\"submit\" value=\"Save\" /></td>";
		echo "</form>";
		
		echo "<td><form action=\"edit_parties.php\" method=\"post\" style=\"height: 13px;\">";
			echo "<input type=\"hidden\" name=\"delparty\" value=\"".$row['id']."\" />";
			echo "<input type=\"submit\" value=\"Delete Party, its patterns, its orders\" />";
		echo "</form></td></tr>";
	}
?>
</table>
<div style="position:fixed; top:10px; right:10px;"><a href="edit_parties.php">Refresh</a></div>
</body>
</html>