<html>
<head>
    <title>Add Pattern</title>
	<style>
		html
		{
			font-family:Arial, Helvetica, sans-serif;
		}
	</style>
	<link rel="stylesheet" href="chosen/chosen.css" />
	<link rel="shortcut icon" href="favicon2.ico" />
	<script src="jquery-2.0.2.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="chosen/chosen.jquery.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(e) {
			$(".chzn-select").chosen({width:"300px"});
			$("#image").change(function(e) {
				$("#noimage").prop("checked", false);
			});
        });
	</script>	
</head>
<body>    
<?php
	//session_start();
	require 'db_connect.php';
	global $connection;
	
	if(isset($_POST['patternname']))
	{
		//Save the party
		$cleanname = cleantex($_POST['patternname']);
		$found = FALSE;
		if($_POST['noimage'] == "true")
		{
			$filename = "null";
			$found = TRUE;
		}
		else
		{
			if ($_FILES["patternimg"]["error"] > 0)
			{
				echo "Image Could not be found or uploaded";
			}
			else
			{
				$allowedExts = array("gif", "jpeg", "jpg", "png", "JPG", "JPEG", "PNG", "GIF");
				$exploend = explode(".", $_FILES["patternimg"]["name"]);
				$extension = end($exploend);
				if ((($_FILES["patternimg"]["type"] == "image/gif")
				|| ($_FILES["patternimg"]["type"] == "image/jpeg")
				|| ($_FILES["patternimg"]["type"] == "image/jpg")
				|| ($_FILES["patternimg"]["type"] == "image/pjpeg")
				|| ($_FILES["patternimg"]["type"] == "image/x-png")
				|| ($_FILES["patternimg"]["type"] == "image/png"))
				&& ($_FILES["patternimg"]["size"] < 2000000)
				&& in_array($extension, $allowedExts))
				{
				
					if (file_exists("pattern_imgs/pat_" . $cleanname . "." . $extension))
					{
						$count = 1;
						while (file_exists("pattern_imgs/pat_" . $cleanname . "_" . $count . "." . $extension))
						{
							$count = $count + 1;
						}
						move_uploaded_file($_FILES["patternimg"]["tmp_name"], "pattern_imgs/pat_" . $cleanname . "_" . $count . "." .$extension);
						$filename = "pattern_imgs/pat_" . $cleanname . "_" . $count . "." .$extension;
					}
					else
					{
						move_uploaded_file($_FILES["patternimg"]["tmp_name"], "pattern_imgs/pat_" . $cleanname . "." . $extension);
						$filename = "pattern_imgs/pat_" . $cleanname . "." . $extension;
					}
					$found = TRUE;
				}
				else
				{
					echo "Please upload appropriate file.";
				}
			}
		}
		if($found == TRUE)
		{
			mysql_query("INSERT INTO pattern ( id, name, party, img  ) VALUES ( NULL, '".mysql_real_escape_string($_POST['patternname'])."', '".$_POST['party']."', '".$filename."' )") or die(mysql_error());
			echo $_POST['patternname'] ." succesfully added to ".partyname($_POST['party']).".";
		}
	}
?>
	<h1 align="center">Pattern Entry</h1>
	<form action="add_pattern.php" method="post" enctype="multipart/form-data">
        <table align="center" cellpadding="10" cellspacing="10">
            <tr>
                <td>Pattern Name</td>
                <td><input name="patternname" type="text" size="80"/></td>
            </tr>
			<tr>
				<td>Party</td>
				<td><select data-placeholder="Party Name" style="width: 300px;" name="party" class="chzn-select"><option value="1">Stock</option>
				<?php
					$parties = mysql_query("SELECT * FROM `parties` WHERE `id` <> 1 ORDER BY `name` ASC");
					while($row = mysql_fetch_array($parties))
					{
						echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>";
					}
				?></select></td>
			</tr>
            <tr>
                <td>Image File</td>
                <td><input type="checkbox" name="noimage" value="true" checked id="noimage"/>No Image<br />
					<input type="file" name="patternimg" id="image"/></td>
            </tr>
            <tr>
            	<td></td>
                <td><input type="submit" value="Submit" /></td>
            </tr>
        </table>
	</form>
<div style="position:fixed; top:10px; right:10px;"><a href="add_pattern.php">Refresh</a></div>
</body>
</html>