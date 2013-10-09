<html>
	<head>
		<title>Dispatch Entry</title>
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
		});
	});
</script>

<body>
<?php
	function updateorder($qtydone2, $orderpatid2, $orderno2)
	{
		$updaquery2 = mysql_query("UPDATE `orderpattern` SET `qtydispatch` = '".$qtydone2."' WHERE `orderpattern`.`idp` = ".$orderpatid2) or die(mysql_error());
		echo "Order No. ".$orderno2." partially dispatched.<br />";
	}
	
	function updateorderdisarchive($qty, $qtydone3, $orderpatid3, $orderid3, $orderno3)
	{
		if($qtydone3 >= $qty)
		{
			$updaquery3 = mysql_query("UPDATE `orderpattern` SET `qtydispatch` = '".$qtydone3."', `archive` = '1' WHERE `orderpattern`.`idp` = ".$orderpatid3) or die(mysql_error());
			
			$orderquery2 = mysql_query("SELECT * FROM `orderpattern` WHERE `orderpattern`.`orderid` = ".$orderid3." AND `orderpattern`.`archive` = 0;") or die(mysql_error());
			
			if(mysql_num_rows($orderquery2) == 0)
			{
				$updaquery4 = mysql_query("UPDATE `ordermain` SET `datedispatched` = CURRENT_TIMESTAMP, `archive` = '1' WHERE `ordermain`.`ido` = ".$orderid3) or die(mysql_error());
				echo "Order No. ".$orderno3." is completed and has been archived.<br />";
			}
			else
			{
				echo "Order No. ".$orderno3." 's one component dispatch done.<br />";
			}
		}
		else
		{
			$updaquery3 = mysql_query("UPDATE `orderpattern` SET `qtydispatch` = '".$qtydone3."', WHERE `orderpattern`.`idp` = ".$orderpatid3) or die(mysql_error());
			echo "Order No. ".$orderid3." is partially dispatched.<br />";
		}
	}
	
	function updateorderfromstock($patternid, $qty, $qtydone, $orderpatid, $orderid, $orderno)
	{
		if($qtydone < $qty)
			echo "Not enough in stock";
		else
		{
			$stockquery = mysql_query("SELECT * FROM `pattern` WHERE `id` = ".$patternid) or die(mysql_error());
			if($stockrow = mysql_fetch_array($stockquery))
			{
				if($qtydone - $qty <= $stockrow['instock'])
				{
					$updaquery2 = mysql_query("UPDATE `pattern` SET `instock` = `instock` - ".($qtydone - $qty)." WHERE `pattern`.`id` = ".$patternid) or die(mysql_error());
					updateorderdisarchive($qty, $qtydone, $orderpatid, $orderid, $orderno);
				}
				else
					echo "Not enough in stock.";
			}
			else
				echo "WTF Pattern not found!!!";
		}
	}
	
	if(isset($_POST['party']) && ($_POST['party'] != NULL) && (isset($_POST['pattern'])) && ($_POST['pattern'] != NULL))
	{
		$orderquery = mysql_query("SELECT `ordermain`.`ido`, `orderpattern`.`idp`, `orderpattern`.`qty`, `orderpattern`.`qtydone`, `orderpattern`.`qtydispatch`, `ordermain`.`orderno` FROM `ordermain`, `orderpattern` WHERE ((`ordermain`.`ido` = `orderpattern`.`orderid`) AND (`ordermain`.`partyid` = ".$_POST['party'].") AND (`orderpattern`.`patternid` = ".$_POST['pattern'].") AND (`orderpattern`.`qtydone` > `orderpattern`.`qtydispatch`) AND (`orderpattern`.`archive` = 0) AND (`ordermain`.`archive` = 0)) ORDER BY `ordermain`.`dateadded` ASC");
		$numrows = mysql_num_rows($orderquery);
		if($numrows == 0)
		{
			echo "No such order from this company. ";
			/*$stockquery = mysql_query("SELECT * FROM `pattern` WHERE `id` = ".$_POST['pattern']) or die(mysql_errno());
			if($stockrow = mysql_fetch_array($stockquery))
			{
				if($stockrow['instock'] < $_POST['qty'])
				{
					echo "Stock is less than quantity needed to be dispatched";
				}
				else
				{
					$updaquery2 = mysql_query("UPDATE `pattern` SET `instock` = `instock` - ".$_POST['qty']." WHERE `pattern`.`id` = ".$_POST['pattern']) or die(mysql_error());
					$insquery = mysql_query("INSERT INTO `dispatch` (`id`, `patternid`, `qty`, `comment`, `date`) VALUES (NULL, '".$_POST['pattern']."', '".$_POST['qty']."', '".$_POST['comment']."', CURRENT_TIMESTAMP);") or die(mysql_error());
					echo "Pattern found in stock and has been subtracted from there.";
				}
			}
			else
			{
				echo "No such pattern found.";
			}*/
		}
		else if($numrows == 1)
		{
			//Only one order subtract from it.
			$row = mysql_fetch_array($orderquery);
			$orderid = $row['idp'];
			$qtydone = $_POST['qty'] + $row['qtydispatch'];
			$insquery = mysql_query("INSERT INTO `dispatch` (`id`, `orderpatid`, `qty`, `comment`, `date`) VALUES (NULL, '".$orderid."', '".$_POST['qty']."', '".$_POST['comment']."', CURRENT_TIMESTAMP);") or die(mysql_error());
			
			//Increase Qty Done
			if($qtydone >= $row['qtydone'])
			{
				if($qtydone == $row['qtydone'])
				{
					updateorderdisarchive($row['qty'], $qtydone, $row['idp'], $row['ido'], $row['orderno']);
				}
				else
				{
					updateorderfromstock($_POST['pattern'], $row['qty'], $qtydone, $row['idp'], $row['ido'], $row['orderno']);
				}
			}
			else
			{
				updateorderdis($qtydone, $row['idp'], $row['orderno']);
			}
			echo "Dispatch entry done.";
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
				if($row['qtydone'] - $row['qtydispatch'] > $qtymade)
				{
					$qtydone = $row['qtydispatch'] + $qtymade;
					updateorder($qtydone, $row['idp'], $row['orderno']);
					
					$orderid = $row['idp'];
					$fin = true;
					break;
				}
				else if($row['qtydone'] - $row['qtydispatch'] == $qtymade)
				{
					updateorderdisarchive($row['qty'], $qtymade, $row['idp'], $row['ido'], $row['orderno']);
					
					$orderid = $row['idp'];
					$fin = true;
					break;
				}
				else
				{
					$qtymade = $qtymade - ($row['qtydone'] - $row['qtydispatch']);
					updateorderdisarchive($row['qty'], $row['qtydone'], $row['idp'], $row['ido'], $row['orderno']);
				}
			}
			
			
			if($fin == false)
			{
				$row = mysql_fetch_array($orderquery);
				if($qtymade >= $row['qtydone'])
				{
					if($qtymade == $row['qtydone'])
					{
						updateorderdisarchive($row['qty'], $qtymade, $row['idp'], $row['ido'], $row['orderno']);
					}
					else
					{
						updateorderfromstock($_POST['pattern'], $row['qty'], $qtymade, $row['idp'], $row['ido'], $row['orderno']);					}
				}
				else
				{
					updateorder($qtymade, $row['idp'], $row['orderno']);
				}
				$orderid = $row['idp'];
			}
			
			$insquery = mysql_query("INSERT INTO `dispatch` (`id`, `orderpatid`, `qty`, `comment`, `date`) VALUES (NULL, '".$orderid."', '".$_POST['qty']."', '".$_POST['comment']."', CURRENT_TIMESTAMP);") or die(mysql_error());
			echo "Dispatch entry done.";
		}
	}
?>
<h1 align="center">Dispatch Entry</h1>
<form method="post" action="dispatch_entry.php">
	<table align="center" cellpadding="10" cellspacing="10">
		<tr>
			<td>Party Name:</td>
			<td><select data-placeholder="Party Name" style="width: 300px;" name="party" id="partysel"><option value=""></option>
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
			<td>Comment</td>
			<td><textarea name="comment" rows="4" cols="50"></textarea></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Submit" name="Submit" /></td>
		</tr>
	</table>
</form>
<div style="position:fixed; top:10px; right:10px;"><a href="dispatch_entry.php">Refresh</a></div>
</body>
</html>