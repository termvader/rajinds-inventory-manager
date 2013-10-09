<html>
<?php
	
	require 'db_connect.php';
	global $connection;
?>
<head>
	<title>Party-wise Orders</title>
	<style>
		html
		{
			font-family:Arial, Helvetica, sans-serif;
		}
		.tab
		{
			border-collapse:collapse;
		}
		.tab tr
		{
			border-bottom:1px solid #ccc;
			color:#669;
		}
		.tab tbody tr:hover
		{
			color:#FFF;
			background-color:#A2C8F5;
		}
		.tab tr:last-child
		{
			border-bottom:none;
		}
		.tab tr.alt td
		{
			border-top:1px solid #ccc;
		}
		.pendh1
		{
			color:#FF0000;
		}
		.readyh1
		{
			color:#00FF00;
		}
		.archh1
		{
			color:#0000FF;
		}
	</style>
	<link rel="shortcut icon" href="favicon2.ico" />
	<link rel="stylesheet" href="chosen/chosen.css" />
	<script src="jquery-2.0.2.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="chosen/chosen.jquery.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(e) {
			$(".chzn-select").chosen();
		});
	</script>
</head>
<body>
<h1 align="center">Party-wise Orders</h1>

<form action="party_orders.php" method="post">
	<table align="center">
		<tr>
			<td><select data-placeholder="Party Name" style="width: 300px;" name="party" class="chzn-select"><option value=""></option>
          	<?php
				$parties = mysql_query("SELECT * FROM `parties` WHERE `id` <> 1 ORDER BY `name` ASC");
				while($row = mysql_fetch_array($parties))
				{
					echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
				}
			?></select></td>
			<td><input type="submit" value="Go" /></td>
		</tr>
	</table>
</form>

<?php
	if(isset($_POST['party']))
	{
		echo "Orders from ".partyname($_POST['party'])."<hr>";
		$pendq = mysql_query("SELECT * FROM `ordermain` WHERE `ordermain`.`archive` = 0 AND `ordermain`.`complete` = 0 AND `partyid` = ".$_POST['party']." ORDER BY `dateadded` ASC, `ido` ASC") or die(mysql_error());
		$first = true;
		if($pendrow = mysql_fetch_array($pendq))
		{
			$orderid = $pendrow['ido'];
			$patsquery = mysql_query("SELECT * FROM `orderpattern` WHERE `orderpattern`.`ready` = 0 AND `orderpattern`.`archive` = 0 AND `orderpattern`.`qty` > `orderpattern`.`qtydone` AND `orderpattern`.`orderid` = ".$orderid.";") or die(mysql_error());
			if($patsrow = mysql_fetch_array($patsquery))
			{
				if($first)
				{
					echo "<h1 align=\"center\" class=\"pendh1\">Pending Orders</h1>";
					echo "<table width=\"100%\" align=\"center\" class=\"tab\" cellpadding=\"10\" >";
					echo "<tr>";
						echo "<th width=\"5%\">Order No.</th>";
						echo "<th width=\"10%\">Date</th>";
						echo "<th width=\"60%\"><table width=\"100%\">";
							echo "<th width=\"52%\">Pattern Name</th>";
							echo "<th width=\"16%\">Qty.</th>";
							echo "<th width=\"16%\">Qty. Done</th>";
							echo "<th width=\"16%\">Qty. Dispatched</th>";
						echo "</table></th>";
						echo "<th width=\"25%\">Comment</th>";
					echo "</tr>";
				}
				echo "<tr>";
					echo "<td>".$pendrow['orderno']."</td>";
					echo "<td>".prdate($pendrow['dateadded'])."</td>";
					echo "<td><table width=\"100%\"><tr>";
						echo "<td width=\"52%\">".patname($patsrow['patternid'])."</td>";
						echo "<td width=\"16%\">".$patsrow['qty']."</td>";
						echo "<td width=\"16%\">".$patsrow['qtydone']."</td>";
						echo "<td width=\"16%\">".$patsrow['qtydispatch']."</td></tr>";
						
						while($patsrow = mysql_fetch_array($patsquery))
						{
							echo "<tr>";
								echo "<td>".patname($patsrow['patternid'])."</td>";
								echo "<td>".$patsrow['qty']."</td>";
								echo "<td>".$patsrow['qtydone']."</td>";
								echo "<td>".$patsrow['qtydispatch']."</td></tr>";
							echo "</tr>";
						}
						
					echo "</table></td>";
					echo "<td>".str_replace("\n", "<br />", $pendrow['comment'])."</td>";
				echo "</tr>";
				if($first)
				{
					echo "<table>";
				}
				$first = false;
			}
		}
		
		$pendq = mysql_query("SELECT * FROM `ordermain` WHERE `ordermain`.`archive` = 0 AND `partyid` = ".$_POST['party']." ORDER BY `dateadded` ASC, `ido` ASC") or die(mysql_error());
		$first = true;
		if($pendrow = mysql_fetch_array($pendq))
		{
			$orderid = $pendrow['ido'];
			$patsquery = mysql_query("SELECT * FROM `orderpattern` WHERE `orderpattern`.`ready` = 1 AND `orderpattern`.`archive` = 0 AND `orderpattern`.`orderid` = ".$orderid.";") or die(mysql_error());
			if($patsrow = mysql_fetch_array($patsquery))
			{
				if($first)
				{
					echo "<h1 align=\"center\" class=\"readyh1\">Ready Orders</h1>";
					echo "<table width=\"100%\" align=\"center\" class=\"tab\" cellpadding=\"10\" >";
					echo "<tr>";
						echo "<th width=\"5%\">Order No.</th>";
						echo "<th width=\"10%\">Date</th>";
						echo "<th width=\"60%\"><table width=\"100%\">";
							echo "<th width=\"52%\">Pattern Name</th>";
							echo "<th width=\"16%\">Qty.</th>";
							echo "<th width=\"16%\">Qty. Done</th>";
							echo "<th width=\"16%\">Qty. Dispatched</th>";
						echo "</table></th>";
						echo "<th width=\"25%\">Comment</th>";
					echo "</tr>";
				}
				echo "<tr>";
					echo "<td>".$pendrow['orderno']."</td>";
					echo "<td>".prdate($pendrow['dateadded'])."</td>";
					echo "<td><table width=\"100%\"><tr>";
						echo "<td width=\"52%\">".patname($patsrow['patternid'])."</td>";
						echo "<td width=\"16%\">".$patsrow['qty']."</td>";
						echo "<td width=\"16%\">".$patsrow['qtydone']."</td>";
						echo "<td width=\"16%\">".$patsrow['qtydispatch']."</td></tr>";
						
						while($patsrow = mysql_fetch_array($patsquery))
						{
							echo "<tr>";
								echo "<td>".patname($patsrow['patternid'])."</td>";
								echo "<td>".$patsrow['qty']."</td>";
								echo "<td>".$patsrow['qtydone']."</td>";
								echo "<td>".$patsrow['qtydispatch']."</td></tr>";
							echo "</tr>";
						}
						
					echo "</table></td>";
					echo "<td>".str_replace("\n", "<br />", $pendrow['comment'])."</td>";
				echo "</tr>";
				if($first)
				{
					echo "<table>";
				}
				$first = false;
			}
		}
		
		$pendq = mysql_query("SELECT * FROM `ordermain` WHERE `partyid` = ".$_POST['party']." ORDER BY `dateadded` ASC, `ido` ASC") or die(mysql_error());
		$first = true;
		if($pendrow = mysql_fetch_array($pendq))
		{
			$orderid = $pendrow['ido'];
			$patsquery = mysql_query("SELECT * FROM `orderpattern` WHERE `orderpattern`.`archive` = 1 AND `orderpattern`.`orderid` = ".$orderid.";") or die(mysql_error());
			if($patsrow = mysql_fetch_array($patsquery))
			{
				if($first)
				{
					echo "<h1 align=\"center\" class=\"archh1\">Archived Orders</h1>";
					echo "<table width=\"100%\" align=\"center\" class=\"tab\" cellpadding=\"10\" >";
					echo "<tr>";
						echo "<th width=\"5%\">Order No.</th>";
						echo "<th width=\"10%\">Date</th>";
						echo "<th width=\"60%\"><table width=\"100%\">";
							echo "<th width=\"52%\">Pattern Name</th>";
							echo "<th width=\"16%\">Qty.</th>";
							echo "<th width=\"16%\">Qty. Done</th>";
							echo "<th width=\"16%\">Qty. Dispatched</th>";
						echo "</table></th>";
						echo "<th width=\"25%\">Comment</th>";
					echo "</tr>";
				}
				echo "<tr>";
					echo "<td>".$pendrow['orderno']."</td>";
					echo "<td>".prdate($pendrow['dateadded'])."</td>";
					echo "<td><table width=\"100%\"><tr>";
						echo "<td width=\"52%\">".patname($patsrow['patternid'])."</td>";
						echo "<td width=\"16%\">".$patsrow['qty']."</td>";
						echo "<td width=\"16%\">".$patsrow['qtydone']."</td>";
						echo "<td width=\"16%\">".$patsrow['qtydispatch']."</td></tr>";
						
						while($patsrow = mysql_fetch_array($patsquery))
						{
							echo "<tr>";
								echo "<td>".patname($patsrow['patternid'])."</td>";
								echo "<td>".$patsrow['qty']."</td>";
								echo "<td>".$patsrow['qtydone']."</td>";
								echo "<td>".$patsrow['qtydispatch']."</td></tr>";
							echo "</tr>";
						}
						
					echo "</table></td>";
					echo "<td>".str_replace("\n", "<br />", $pendrow['comment'])."</td>";
				echo "</tr>";
				if($first)
				{
					echo "<table>";
				}
				$first = false;
			}
		}
	}
?>
</body>
</html>