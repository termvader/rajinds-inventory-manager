<html>
<?php
	
	require 'db_connect.php';
	global $connection;
?>
<head>
	<title>Weight Records</title>
	<link rel="shortcut icon" href="favicon2.ico" />
	<style>
		html
		{
			font-family:Arial, Helvetica, sans-serif;
		}
		#resulttab
		{
			border-collapse:collapse;
		}
		#resulttab tr
		{
			border-bottom:1px solid #ccc;
			color:#669;
		}
		#resulttab tbody tr:hover
		{
			color:#FFF;
			background-color:#A2C8F5;
		}
		/*#resulttab tr:last-child
		{
			border-bottom:none;
		}*/
		#resulttab td
		{
			border:1px solid #ccc;
		}

	</style>
</head>

<body>
	<h1 align="center">Weight Records</h1>
<form action="weight_record.php" method="post">
<table align="center" cellspacing="10">
	<tr>
		<td>Employee</td>
		<td><input type="radio" name="employee" value="0" checked="checked"/>All
		<?php
			$empquery = mysql_query("SELECT * FROM `machining`") or die(mysql_error());
			while($emprow = mysql_fetch_array($empquery))
			{
				echo "<br /><input type=\"radio\" name=\"employee\" value=\"".$emprow['id']."\" />".$emprow['name'];
			}
		?></td>
	</tr>
	<tr>
		<td>Begin Date</td>
		<td><input type="date" name="begdate" required/></td>
	</tr>
	<tr>
		<td>End Date</td>
		<td><input type="date" name="enddate" required/></td>
	</tr>
	<tr>
		<td></td>
		<td>Both dates inclusive</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" value="Go"/></td>
	</tr>
</table>
</form>
<?php
	if(isset($_POST['employee']))
	{
		echo "<table width=\"100%\" id=\"resulttab\" cellpadding=\"10\"><tr>";
		echo "<th width=\"5%\">Order No.</th>";
		echo "<th width=\"25%\">Party</th>";
		echo "<th width=\"30%\">Pattern</th>";
		echo "<th width=\"10%\">Qty.</th>";
		echo "<th width=\"10%\">Wt.</th>";
		echo "<th width=\"10%\">Machining By</th>";
		echo "<th width=\"10%\">Date</th></tr>";
		if($_POST['employee'] == '0')
		{
			$query = mysql_query("SELECT * FROM `weight` WHERE `date` >= '".$_POST['begdate']." 00:00:00' AND `date` <= '".$_POST['enddate']." 23:59:59';") or die(mysql_error());
		}
		else
		{
			$query = mysql_query("SELECT * FROM `weight` WHERE `date` >= '".$_POST['begdate']." 00:00:00' AND `date` <= '".$_POST['enddate']." 23:59:59' AND `machin` = ".$_POST['employee'].";") or die(mysql_error());
		}
		
		while($row = mysql_fetch_array($query))
		{
			if($row['orderpatid'] == 0)
			{
				echo "<tr>";
					echo "<td align=\"center\" valign=\"middle\">---</td>";
					echo "<td>---</td>";
					echo "<td>".patname($row['patternid'])."</td>";
					echo "<td>".$row['qty']."</td>";
					echo "<td>".$row['wt']." kg</td>";
					echo "<td>".empname($row['machin'])."</td>";
					echo "<td>".prdate($row['date'])."</td>";
				echo "</tr>";
			}
			else
			{
				$orderpatquerry = mysql_query("SELECT * FROM `orderpattern` WHERE `idp` = ".$row['orderpatid']) or die(mysql_error());
				if($orderpatrow = mysql_fetch_array($orderpatquerry))
				{
					$orderquery = mysql_query("SELECT * FROM `ordermain` WHERE `ido` = ".$orderpatrow['orderid']) or die(mysql_error());
					if($orderrow = mysql_fetch_array($orderquery))
					{				
						echo "<tr>";
							echo "<td align=\"center\" valign=\"middle\">".$orderrow['orderno']."</td>";
							echo "<td>".partyname($orderrow['partyid'])."</td>";
							echo "<td>".patname($orderpatrow['patternid'])."</td>";
							echo "<td>".$row['qty']."</td>";
							echo "<td>".$row['wt']." kg</td>";
							echo "<td>".empname($row['machin'])."</td>";
							echo "<td>".prdate($row['date'])."</td>";
						echo "</tr>";
					}
				}
			}
		}
		
		echo "</table>";
	}
?>
<br /><br />
<br />
</body>
</html>