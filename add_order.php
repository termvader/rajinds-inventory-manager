<html>
	<head>
    	<title>Add Order</title>
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
		var rows = 1;
		var stocksel = "<?php
						$patterns = mysql_query("SELECT * FROM `pattern` WHERE `party` = 1 ORDER BY `name` ASC");
						while($row = mysql_fetch_array($patterns))
						{
							echo "<option value=\\\"" . $row['id'] . "\\\">" . $row['name'] . "</option>";
						}
					?>";
		var partysel = "";
		function addRow()
		{
			tex = "<tr><td><select name=\"pat_" + rows + "\"  style=\"width: 300px;\" data-placeholder=\"Pattern Name\" class=\"chzn-select\"><option value=\"\"></option>" + partysel + stocksel + "</select></td><td><input name=\"patqty_" + rows + "\" type=\"number\"  required /></td></tr>";
			$tex = $(tex);
			$tex.find(".chzn-select").chosen({width:"300px"});
			$tex.appendTo("#tab");
			rows = rows + 1;
		}
		$(document).ready(function(e) {
			$("#partysel").chosen().change(function(event) {
				//console.log($(event.target).val());
				var partid = $(event.target).val();
				$("#loadgif").show();
				$.post("get_patterns.php", {partyid:partid}, function(data, status) {
					partysel = "";
					$.each(data, function(key, val) {
						partysel += "<option value=\"" + key + "\">" + val + "</option>";
					});
					
					$(".chzn-select").each(function(index, element) {
						element.innerHTML = "<option value=\"\"></option>" + partysel + stocksel;
						//element.append($(stocksel + partysel));
						$(element).trigger("liszt:updated");
					});
					
					$("#loadgif").hide();
				}, "json");
			});
			addRow();
        });
	</script>
	
	
<body>
<?php
	if(isset($_POST['party']) && $_POST['party'] != NULL && isset($_POST['pat_1']) && $_POST['pat_1'] != NULL)
	{
		$orderquery = mysql_query("SELECT MAX(`orderno`) AS `orderno` FROM `ordermain`") or die(mysql_error());
		if($orderrow = mysql_fetch_array($orderquery))
		{
			$orderno = 1 + $orderrow['orderno'];
		}
		else
		{
			$orderno = 1;
		}
		
		$insquery = mysql_query("INSERT INTO `ordermain` (`ido`, `orderno`, `partyid`, `comment`, `dateadded`, `datecompleted`, `complete`, `archive`) VALUES (NULL, '".$orderno."', '".$_POST['party']."', '".mysql_real_escape_string($_POST['comment'])."', CURRENT_TIMESTAMP, '0000-00-00 00:00:00', '0', '0');") or die(mysql_error());
		$orderid = mysql_insert_id();
		
		
		$count = 1;
		$comp = 0;
		while(isset($_POST['pat_'.$count]) && $_POST['pat_'.$count] != NULL)
		{
			$stockquery = mysql_query("SELECT * FROM `pattern` WHERE `id` = ".$_POST['pat_'.$count]) or die(mysql_error());
			$qtydoneds = 0;
			$readyds = 0;
			if($stockrow = mysql_fetch_array($stockquery))
			{
				if($stockrow['instock'] < intval($_POST['patqty_'.$count]) )
				{
					$qtydoneds = $stockrow['instock'];
					$stockquery2 = mysql_query("UPDATE `pattern` SET `instock` = 0 WHERE `pattern`.`id` = ".$_POST['pat_'.$count]) or die(mysql_error());
					$readyds = 0;
				}
				else
				{
					$qtydoneds = $_POST['patqty_'.$count];
					$stockquery2 = mysql_query("UPDATE `pattern` SET `instock` = `instock` - ".$_POST['patqty_'.$count]." WHERE `pattern`.`id` = ".$_POST['pat_'.$count]) or die(mysql_error());
					$readyds = 1;
				}
			}
			$insquery = mysql_query("INSERT INTO `orderpattern` (`idp`, `orderid`, `patternid`, `qty`, `qtydone`, `ready`, `qtydispatch`, `archive`, `lastupdate`) VALUES (NULL, '".$orderid."', '".$_POST['pat_'.$count]."', '".$_POST['patqty_'.$count]."', '".$qtydoneds."', '".$readyds."', '0', '0', CURRENT_TIMESTAMP);") or die(mysql_error());
			$comp = $comp + $readyds;
			$count = $count + 1;
		}
		if($comp == $count - 1)
		{
			$compquery = mysql_query("UPDATE `ordermain` SET `complete` = 1 WHERE `ido` = ".$orderid) or die(mysql_error());
		}
		echo "Order No. ".$orderno." added for ".partyname($_POST['party']);
		
	}
?>

<h1 align="center">Order Form</h1>
<form method="post" action="add_order.php">
	<table align="center" cellpadding="10" cellspacing="10" id="tab">
    	<tr>
        	<td><select data-placeholder="Party Name" style="width: 300px;" name="party" id="partysel"><option value=""></option>
          	<?php
				$parties = mysql_query("SELECT * FROM `parties` WHERE `id` <> 1 ORDER BY `name` ASC");
				while($row = mysql_fetch_array($parties))
				{
					echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
				}
			?></select></td>
            <td><div id="loadgif" style="display:none"><img src="ajax-loader.gif" /></div></td>
        </tr>
    </table>
    <center>
    	<img src="plus_orange.png" onClick="addRow()"  />
        <br />
		<textarea name="comment" rows="4" cols="50"></textarea><br /><br />
        <input type="submit" value="Submit"/>
    </center>
</form>
<div style="position:fixed; top:10px; right:10px;"><a href="add_order.php">Refresh</a></div>
</body>
</html>