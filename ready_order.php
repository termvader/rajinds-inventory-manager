<html>
	<head>
    	<title>Ready Orders</title>
    </head>
<?php
	
	require 'db_connect.php';
	global $connection;
?>
	<link rel="shortcut icon" href="favicon2.ico" />
	<style>
		html
		{
			font-family:Arial, Helvetica, sans-serif;
		}
		
		#hor-minimalist-b
		{
			border-collapse:collapse;
		}
		#hor-minimalist-b tr
		{
			border-bottom:1px solid #ccc;
			color:#669;
		}
		#hor-minimalist-b tbody tr:hover
		{
			color:#FFF;
			background-color:#A2C8F5;
		}
		#hor-minimalist-b tr:last-child
		{
			border-bottom:none;
		}
		#hor-minimalist-b tr.alt td
		{
			border-top:1px solid #ccc;
		}
		/*#hor-minimalist-b table.alte
		{
			border:1px solid #ccc;
		}
		#hor-minimalist-b table.alte td
		{
			border-left:1px solid #ccc;
		}*/
		
	</style>
	
<body>
<h1 align="center">Orders ready for Dispatch</h1>
    
<table width="100%" cellpadding="10" id="hor-minimalist-b">
	<tr>
		<th width="5%">Order No.</td>
		<th width="21%">Company</td>
		<th width="10%">Date Added</th>
		<th width="44%">
		  <table width="100%">
				<tr>
					<th width="46%">Pattern Name</td>
					<th width="18%">Qty.</td>
					<th width="18%">Qty. Done</td>
					<th width="18%">Qty. Dispatched</td>
				</tr>
			</table>
		</th>
		<th width="20%">Comment</th>
	</tr>
	<?php
		$query = mysql_query("SELECT * FROM `ordermain` WHERE `ordermain`.`archive` = 0 ORDER BY `dateadded` ASC, `ido` ASC") or die(mysql_error());
		
		while($row = mysql_fetch_array($query))
		{
			$orderid = $row['ido'];
			
			$patsquery = mysql_query("SELECT * FROM `orderpattern` WHERE `orderpattern`.`ready` = 1 AND `orderpattern`.`archive` = 0 AND `orderpattern`.`qtydispatch` < `orderpattern`.`qtydone` AND `orderpattern`.`orderid` = ".$orderid.";") or die(mysql_error());
			if(mysql_num_rows($patsquery) != 0)
			{
				$dat = prdate($row['dateadded']);
				echo "<tr><td align=\"center\" valign=\"middle\">".$row['orderno']."</td><td>".partyname($row['partyid'])."</td><td>".$dat."</td><td><table width=\"100%\" class=\"alte\">";
				while($patsrow = mysql_fetch_array($patsquery))
				{
					echo "<tr><td width=\"46%\">".patname($patsrow['patternid'])."</td><td width=\"18%\">".$patsrow['qty']."</td><td width=\"18%\">".$patsrow['qtydone']."<td width=\"18%\">".$patsrow['qtydispatch']."</td></tr>";
				}
				echo "</table></td><td>";
				echo str_replace("\n", "<br />", $row['comment']);
				echo "</td></tr>";
			}
		}
	?>
</table>
<br />
<hr />
<br />
<h1 align="center">Partially Ready Orders</h1>
<table width="100%" cellpadding="10" id="hor-minimalist-b">
	<tr>
		<th width="5%">Order No.</td>
		<th width="21%">Company</td>
		<th width="10%">Date Added</th>
		<th width="44%">
		  <table width="100%">
				<tr>
					<th width="46%">Pattern Name</td>
					<th width="18%">Qty.</td>
					<th width="18%">Qty. Done</td>
					<th width="18%">Qty. Dispatched</td>
				</tr>
			</table>
		</th>
		<th width="20%">Comment</th>
	</tr>
	<?php
		$query = mysql_query("SELECT * FROM `ordermain` WHERE `ordermain`.`archive` = 0 ORDER BY `dateadded` ASC, `ido` ASC") or die(mysql_error());
		
		while($row = mysql_fetch_array($query))
		{
			$orderid = $row['ido'];
			
			$patsquery = mysql_query("SELECT * FROM `orderpattern` WHERE `orderpattern`.`ready` = 0 AND `orderpattern`.`archive` = 0 AND `orderpattern`.`qtydone` > 0 AND `orderpattern`.`orderid` = ".$orderid.";") or die(mysql_error());
			if(mysql_num_rows($patsquery) != 0)
			{
				$dat = prdate($row['dateadded']);
				echo "<tr><td align=\"center\" valign=\"middle\">".$row['orderno']."</td><td>".partyname($row['partyid'])."</td><td>".$dat."</td><td><table width=\"100%\" class=\"alte\">";
				while($patsrow = mysql_fetch_array($patsquery))
				{
					echo "<tr><td width=\"46%\">".patname($patsrow['patternid'])."</td><td width=\"18%\">".$patsrow['qty']."</td><td width=\"18%\">".$patsrow['qtydone']."<td width=\"18%\">".$patsrow['qtydispatch']."</td></tr>";
				}
				echo "</table></td><td>";
				echo str_replace("\n", "<br />", $row['comment']);
				echo "</td></tr>";
			}
		}
	?>
</table>
<hr />
<h1 align="center">Instock Patterns</h1>
<table align="center" cellpadding="10" cellspacing="10" id="hor-minimalist-b">
	<tr>
		<th>Company</th>
		<th>Pattern Name</th>
		<th>Stock</th>
	</tr>
	<?php
		$instockq = mysql_query("SELECT * FROM `pattern` WHERE `instock` > 0") or die(mysql_errno());
		while($instockrow = mysql_fetch_array($instockq))
		{
			echo "<tr>";
				echo "<td>".partyname($instockrow['party'])."</td>";
				echo "<td>".$instockrow['name']."</td>";
				echo "<td>".$instockrow['instock']."</td>";
			echo "</tr>";
		}
	?>
</table>
<br /><br /><br /><br /><br /><br /><br /><br />
</body>
</html>