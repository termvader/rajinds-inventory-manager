<html>
	<head>
    	<title>Pending Orders</title>
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
<h1 align="center">Pending Orders</h1>
    
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
                        <th width="18%">Qty. Left</td>
                    </tr>
                </table>
       	  	</th>
			<th width="20%">Comment</th>
        </tr>
        <?php
			$query = mysql_query("SELECT * FROM `ordermain` WHERE `ordermain`.`archive` = 0 AND `ordermain`.`complete` = 0 ORDER BY `dateadded` ASC, `ido` ASC") or die(mysql_error());
			while($row = mysql_fetch_array($query))
			{
				$orderid = $row['ido'];
				$dat = prdate($row['dateadded']);
				
				echo "<tr><td align=\"center\" valign=\"middle\">".$row['orderno']."</td><td>".partyname($row['partyid'])."</td><td>".$dat."</td><td><table width=\"100%\">";
				
				$patsquery = mysql_query("SELECT * FROM `orderpattern` WHERE `orderpattern`.`ready` = 0 AND `orderpattern`.`qty` > `orderpattern`.`qtydone` AND `orderpattern`.`orderid` = ".$orderid.";") or die(mysql_error());
				while($patsrow = mysql_fetch_array($patsquery))
				{
					echo "<tr><td width=\"55%\">".patname($patsrow['patternid'])."</td><td width=\"15%\">".$patsrow['qty']."</td><td width=\"15%\">".$patsrow['qtydone']."<td width=\"15%\">".($patsrow['qty']-$patsrow['qtydone'])."</td></tr>";
				}
				
				echo "</table></td><td>";
					echo str_replace("\n", "<br />", $row['comment']);
				echo "</td></tr>";
			}
		?>
    </table>
</body>
</html>