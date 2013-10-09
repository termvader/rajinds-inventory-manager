<html>
	<head>
		<title>Weight Entry</title>
	</head>
<?php
	require 'db_connect.php';
	global $connection;
?>
<style>
	html
	{
		font-family:Arial, Helvetica, sans-serif;
	}
</style>
<link rel="shortcut icon" href="favicon2.ico" />
<link rel="stylesheet" href="chosen/chosen.css" />
<script src="jquery-2.0.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="chosen/chosen.jquery.min.js"></script>
<script type="text/javascript">
	stocksel = "<?php
		$pattern = mysql_query("SELECT * FROM `pattern` WHERE `party` = 1 ORDER BY `name` ASC") or die(mysql_error());
		while($row = mysql_fetch_array($pattern))
		{
			echo "<option value=\\\"" . $row['id'] . "\\\">" . $row['name'] . "</option>";
		}
	?>"
	$(document).ready(function(e) {
		$("#patsel").append("<option value=\"\"></option>" + stocksel);
		$("#patsel").chosen();
		$("#partysel").chosen().change(function(event) {
			var partid = $(event.target).val();
			if(partid != "1")
			{
				$("#loadgif").show();
				$.post("get_patterns.php", {partyid:partid}, function(data, status) {
					var partysel = "";
					$.each(data, function(key, val) {
						partysel += "<option value=\"" + key + "\">" + val + "</option>";
					});
					
					$("#patsel").empty();
					$("#patsel").append("<option value=\"\"></option>" + partysel + stocksel);
					$("#patsel").trigger("liszt:updated");
					
					$("#loadgif").hide();
				}, "json");
			}
		});
	});
</script>

<body>
<?php
	function updateorder($qtydone2, $orderpatid2, $orderno2)
	{
		$updaquery2 = mysql_query("UPDATE `orderpattern` SET `qtydone` = '".$qtydone2."' WHERE `orderpattern`.`idp` = ".$orderpatid2) or die(mysql_error());
		echo "Order No. ".$orderno2." partially fulfilled.<br />";
	}
	
	function updateorderwithdispatch($qtydone3, $orderpatid3, $orderid3, $orderno3)
	{
		$updaquery3 = mysql_query("UPDATE `orderpattern` SET `qtydone` = '".$qtydone3."', `ready` = '1' WHERE `orderpattern`.`idp` = ".$orderpatid3) or die(mysql_error());
		
		$orderquery2 = mysql_query("SELECT * FROM `orderpattern` WHERE `orderpattern`.`orderid` = ".$orderid3." AND `orderpattern`.`ready` = 0;") or die(mysql_error());
		
		if(mysql_num_rows($orderquery2) == 0)
		{
			$updaquery4 = mysql_query("UPDATE `ordermain` SET `datecompleted` = CURRENT_TIMESTAMP, `complete` = '1' WHERE `ordermain`.`ido` = ".$orderid3) or die(mysql_error());
			echo "Order No. ".$orderno3." is ready for dispatch<br />";
		}
		else
		{
			echo "Order No. ".$orderno3." 's one component is ready for dispatch.<br />";
		}
	}
	
	function insertstock($stockqty, $patid)
	{
		$updaquery2 = mysql_query("UPDATE `pattern` SET `instock` = `instock` + ".$stockqty." WHERE `pattern`.`id` = ".$patid) or die(mysql_error());
		echo "Excess quantity made for ".patname($patid).". Added to Stock.<br />";
	}
	
	if(isset($_POST['party']) && ($_POST['party'] != NULL) && (isset($_POST['pattern'])) && ($_POST['pattern'] != NULL))
	{
		$orderquery = mysql_query("SELECT `ordermain`.`ido`, `orderpattern`.`idp`, `orderpattern`.`qty`, `orderpattern`.`qtydone`, `ordermain`.`orderno` FROM `ordermain`, `orderpattern` WHERE ((`ordermain`.`ido` = `orderpattern`.`orderid`) AND (`ordermain`.`partyid` = ".$_POST['party'].") AND (`orderpattern`.`patternid` = ".$_POST['pattern'].") AND (`orderpattern`.`qty` > `orderpattern`.`qtydone`) AND (`ordermain`.`complete` = 0) AND (`ordermain`.`archive` = 0)) ORDER BY `ordermain`.`dateadded` ASC");
		$numrows = mysql_num_rows($orderquery);
		if($numrows == 0)
		{
			//No such order exists. Need to add to stock.
			$insquery = mysql_query("INSERT INTO `weight` (`id`, `patternid`, `qty`, `wt`, `machin`, `date`) VALUES (NULL, '".$_POST['pattern']."', '".$_POST['qty']."', '".$_POST['wt']."', '".$_POST['machin']."', CURRENT_TIMESTAMP);") or die(mysql_error());
			$updaquery2 = mysql_query("UPDATE `pattern` SET `instock` = `instock` + ".$_POST['qty']." WHERE `pattern`.`id` = ".$_POST['pattern']) or die(mysql_error());
			echo "No such order from this company. It has been added to stock.";
		}
		else if($numrows == 1)
		{
			//Only one order subtract from it.
			$row = mysql_fetch_array($orderquery);
			$orderid = $row['idp'];
			$qtydone = $_POST['qty'] + $row['qtydone'];
			$insquery = mysql_query("INSERT INTO `weight` (`id`, `orderpatid`, `qty`, `wt`, `machin`, `date`) VALUES (NULL, '".$orderid."', '".$_POST['qty']."', '".$_POST['wt']."', '".$_POST['machin']."', CURRENT_TIMESTAMP);") or die(mysql_error());
			
			//Increase Qty Done
			if($qtydone >= $row['qty'])
			{
				updateorderwithdispatch($row['qty'], $row['idp'], $row['ido'], $row['orderno']);
				insertstock($qtydone - $row['qty'], $_POST['pattern']);
			}
			else
			{
				updateorder($qtydone, $row['idp'], $row['orderno']);
			}
			echo "Weight entry done.";
		}
		else
		{
			//Multiple orders
			$qtymade = $_POST['qty'];
			$fin = false;
			while($numrows > 1)
			{
				$row = mysql_fetch_array($orderquery);
				$numrows = $numrows - 1;
				if($row['qty'] - $row['qtydone'] > $qtymade)
				{
					$qtydone = $row['qtydone'] + $qtymade;
					updateorder($qtydone, $row['idp'], $row['orderno']);
					
					$orderid = $row['idp'];
					$fin = true;
					break;
				}
				else if($row['qty'] - $row['qtydone'] == $qtymade)
				{
					updateorderwithdispatch($qtymade, $row['idp'], $row['ido'], $row['orderno']);
					
					$orderid = $row['idp'];
					$fin = true;
					break;
				}
				else
				{
					$qtymade = $qtymade - ($row['qty'] - $row['qtydone']);
					updateorderwithdispatch($row['qty'], $row['idp'], $row['ido'], $row['orderno']);
				}
			}
			
			
			if($fin == false)
			{
				$row = mysql_fetch_array($orderquery);
				if($qtymade >= $row['qty'])
				{
					updateorderwithdispatch($row['qty'], $row['idp'], $row['ido'], $row['orderno']);
					insertstock($qtymade - $row['qty'], $_POST['pattern']);
				}
				else
				{
					updateorder($qtymade, $row['idp'], $row['orderno']);
				}
				$orderid = $row['idp'];
			}
			
			$insquery = mysql_query("INSERT INTO `weight` (`id`, `orderpatid`, `qty`, `wt`, `machin`, `date`) VALUES (NULL, '".$orderid."', '".$_POST['qty']."', '".$_POST['wt']."', '".$_POST['machin']."', CURRENT_TIMESTAMP);") or die(mysql_error());
			echo "Weight entry done.";
		}
	}
?>
<h1 align="center">Weight Entry</h1>
<form method="post" action="weight_entry.php">
	<table align="center" cellpadding="10" cellspacing="10">
		<tr>
			<td>Party Name:</td>
			<td><select data-placeholder="Party Name" style="width: 300px;" name="party" id="partysel"><option value=""></option><option value ="1">Stock</option>
		  	<?php
				$parties = mysql_query("SELECT * FROM `parties` WHERE `id` <> 1 ORDER BY `name` ASC") or die(mysql_error());
				while($row = mysql_fetch_array($parties))
				{
					echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
				}
			?></select></td>
			<td><div id="loadgif" style="display:none"><img src="ajax-loader.gif" /></div></td>
		</tr>
		<tr>
			<td>Pattern Name</td>
			<td><select data-placeholder="Pattern Name" style="width: 300px;" name="pattern" id="patsel" ></select></td>
		</tr>
		<tr>
			<td>Quantity</td>
			<td><input type="number" name="qty" required/></td>
		</tr>
		<tr>
			<td>Weight</td>
			<td><input type="number" name="wt" step="any" required/></td>
		</tr>
		<tr>
			<td>Date</td>
			<td><input type="date" value="<?php
				if(isset($_POST['dat']))
				{
					echo $_POST['dat'];
				}
				else
				{
					echo date('Y-m-d');
				}
			?>" name="dat" /></td>
		</tr>
		<tr>
			<td>Machining By</td>
			<td>
			<input type="radio" name="machin" value="1" checked />None
			<?php
				$machin = mysql_query("SELECT * FROM `machining` WHERE `id` <> 1") or die(mysql_error());
				while($row = mysql_fetch_array($machin))
				{
					echo "<br /><br /><input type=\"radio\" name=\"machin\" value=\"".$row['id']."\" />".$row['name'];
				}
			?>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Submit" name="Submit" /></td>
		</tr>
	</table>
</form>
<div style="position:fixed; top:10px; right:10px;"><a href="weight_entry.php">Refresh</a></div>
</body>
</html>