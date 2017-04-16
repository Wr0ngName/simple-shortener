<?php
if( is_file('./install.php') ) header('Location:./install.php');

include("./core/config.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="./static/common.css"/>
    <link rel="stylesheet" href="./static/list.css"/>
    </head>
	<body>
<?php

error_reporting(-1);
error_reporting(E_ALL);
ini_set('display_errors',TRUE);
ini_set('display_startup_errors',TRUE);

	$root_url = $_SERVER['REQUEST_URI'];
    header("Cache-Control: no-cache, must-revalidate");

    if ( !empty($_GET['userID']) )
    {
	    include("./core/bdd.php");

	    if ( !empty($_GET['delete']) )
	    {
		    $req = $connexion->prepare('DELETE FROM shortener WHERE id_user= :id AND short = :sh');
		    $req->bindParam(':sh',              $_GET['delete'],         PDO::PARAM_STR);
		    $req->bindParam(':id',              $_GET['userID'],         PDO::PARAM_STR);
		    $req->execute();
		    $req->closeCursor();
	    }
	    elseif ( !empty($_GET['deleteRange']) )
	    {
		    $date = new DateTime("UTC");
		    $date->modify('-'.$_GET['deleteRange'].' day');
		    $date = $date->format('Y-m-d H:i:s');
		    echo $date;

		    if ( !empty($_GET['keepBM']) && $_GET['keepBM'] == "true" )
		    {
		        $req = $connexion->prepare('DELETE FROM shortener WHERE id_user= :id AND date < :d AND comment IS NULL');
		    }
		    else
		    {
		        $req = $connexion->prepare('DELETE FROM shortener WHERE id_user= :id AND date < :d');
		    }
		    
	        $req->bindParam(':d',               $date,               PDO::PARAM_STR);
	        $req->bindParam(':id',              $_GET['userID'],     PDO::PARAM_STR);
		    $req->execute();
		    $req->closeCursor();
	    }

	    echo '<table><tr><th>Short link</th><th class="center-div" style="width: 500px;">Original link</th><th>Total views</th></tr>';

	    $list = $connexion->prepare('SELECT * FROM shortener WHERE id_user= :id ORDER BY date DESC;');
	    $list->bindParam(':id',              $_GET['userID'],     PDO::PARAM_STR);
	    $list->execute();

	    while ( $row = $list->fetch(PDO::FETCH_ASSOC) )
	    {
		    echo "<tr><td><a href=\"./" . $row['short'] . "\" >" . $row['short'] . "</a></td>"
		    	."<td><div class=\"comment\">" . $row['comment'] . "</div><a href=\"./" . $row['short'] . "\" >" . $row['url'] . "</a></td>"
		    	."<td>" . $row['views'] . "<a href=./list.php?userID=" . htmlentities( $_GET['userID'] ) . "&amp;delete=" . $row['short'] . " class=\"delete\" ><img src=\"./static/delete-icon.png\" /></td></tr>";
	    }

	    $list->closeCursor();

	    echo '</table><div id="formDelete"><h3 style="text-align:center;">Bulk deletion:</h3><form action="list.php" method="get" style="display:inline;" ><input type="hidden" name="userID" value="'. htmlentities( $_GET['userID'] ) .'" />'
	    	.'<label>Remove links older than <input type="number" name="deleteRange" value="30" /> days.</label><br /><label>&rarr; Keep Bookmarks (w/ comment):<input type="checkbox" name="keepBM" value="true" /> </label><br /><br />'
	    	.'&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="submit" value="Delete" />&nbsp; &nbsp; &nbsp; or</form><form action="." method="get" style="display:inline;">&nbsp; &nbsp; &nbsp; <input type="submit" value="&larr; Go Back !"></form></div>';

    } else
        header("Location:../main/");
    ?>

    </body>
</html>