<?php
if( is_file('./install.php') ) header('Location:./install.php');

include("./core/config.php");

function myUrlEncode($string) {
    $entities       = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements   = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($entities, $replacements, urlencode($string));
}

?>
<!DOCTYPE html>
<html>
<head>
	<!-- The MIT License (MIT)

	     Copyright (c) 2017 wr0ng.name

	     Original work from Azlux (2015). Remasatered and securised using prepared
	     queries and other means (PHP good practicies, OWASP Top10, Google ReCAPTACHA...).
	-->
    <title><?php echo $title; ?></title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="./static/common.css"/>
    <?php if($captcha) echo "<script src='https://www.google.com/recaptcha/api.js'></script>"; ?>
</head>
<body>
    <div id="content">
        <div id="form">
            <form name="url_form" action="index.php" method="post" id="short_form">
<?php

include("./core/bdd.php");

if( $captcha && isset($_REQUEST['g-recaptcha-response']) )
{
    $captcha_request = $_REQUEST['g-recaptcha-response'];
}

if ( !empty($_REQUEST['site']) )
{

        $site = $_REQUEST['site'];

        $req_site_exists = "SELECT count(*) FROM shortener WHERE short=:site;";

        $res_site_exists = $connexion->prepare($req_site_exists);
    	$res_site_exists->bindParam(':site', 	 	$site, 			PDO::PARAM_STR);
        $res_site_exists->setFetchMode(PDO::FETCH_OBJ);
    	$res_site_exists->execute();
        $res_site_exists = $res_site_exists->fetchColumn();

        if ($res_site_exists > 0) //if it exists
        {
            $get_site = $connexion->prepare('SELECT url, short, views FROM shortener WHERE short=:site');
            $get_site->bindParam(':site', 	 		$site, 			PDO::PARAM_STR);
    		$get_site->execute();
            $res_site = $get_site->fetch(PDO::FETCH_ASSOC);

            $views_plus_1 = $res_site['views'] + 1;

            $query_update = $connexion->prepare('UPDATE shortener SET views=:views WHERE short=:site');
            $query_update->bindParam(':site', 	 	$site, 			PDO::PARAM_STR);
            $query_update->bindParam(':views', 	 	$views_plus_1, 	PDO::PARAM_INT);
            $query_update->execute();

            header('Location: ' . $res_site['url']);
        } else
            header('Location: /');

}
else if ( !empty($_REQUEST['shorten']) )
{
    if( !$captcha || (isset($captcha_request) && $captcha_request !== false) )
    {
        if( $captcha ) $check = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$captcha_request);

        if( !$captcha || $check.success == true )
        {
            $shorten = myUrlEncode($_REQUEST['shorten']);
            if ( preg_match("_(^|[\s.:;?\-\]<\(])(https?://[-\w;/?:@&=+$\|\_.!~*\|'()\[\]%#,?]+[\w/#](\(\))?)(?=$|[\s',\|\(\).:;?\-\[\]>\)])_i", $shorten) )
            {
                $unic = 0;
                while ($unic == 0)
                {
                    $characters = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
                    $url_shortened = '';
                    for ($i = 0; $i < 5; $i++) { //This number is the number of letters after the adress
                        $url_shortened .= $characters[rand(0, strlen($characters) - 1)];
                    }

                    $req_verify_url = "SELECT count(*) FROM shortener WHERE short=:site;"; //select the post

                    $verify_url = $connexion->query($req_verify_url);
        			$verify_url->bindParam(':site', $url_shortened,		PDO::PARAM_STR);
                    $verify_url->setFetchMode(PDO::FETCH_OBJ);
        			$verify_url->execute();
                    $verify_url = $verify_url->fetchColumn();

                    if ($verify_url == 0) {
                        $unic = 1;
                    }
                }

                $userID = $_REQUEST['userID'];
                $z = 0;
                $comm = isset($_REQUEST['comment'])?htmlentities($_REQUEST['comment']):NULL;
        		
        		$req = $connexion->prepare('INSERT INTO shortener(short,url,id_user,comment,date,views) VALUES (:sh,:u,:id,:c,CURRENT_TIMESTAMP,:v);');
        		$req->bindParam(':sh', 	 			$url_shortened, 	PDO::PARAM_STR);
        		$req->bindParam(':u', 	 			$shorten, 			PDO::PARAM_STR);
                $req->bindParam(':id',              $userID,            PDO::PARAM_STR);
                $req->bindParam(':c',               $comm,              PDO::PARAM_STR);
        		$req->bindParam(':v', 	 			$z,					PDO::PARAM_INT);
        		$req->execute();
        			
                $req->closeCursor();

                echo '<div id="site">
        						<a href=".">&larr; Go Back to the shortner.</a>
        					</div>

        					<div id="shortened">
        						Your shortened URL: <br /><a id="newURL" href="./' . $url_shortened . '">' . $url_shortened . '</a>
        					</div>

        					<script>
        					    var short = document.getElementById("newURL").innerHTML;
        					    var long = window.location.href;
        					    var good_long = long.split("index.php");
                                var sep = "/";

                                if(good_long[0][good_long[0].length-1] == "/") sep = "";

        					    document.getElementById("newURL").innerHTML = good_long[0]+sep+short;
        					    window.prompt("Copy to clipboard: Ctrl+C, Enter",good_long[0]+short );
        					</script>';
            }
            else
                echo "<center>Error, invalid URL</center>";
        }
        else
            echo '<center>Error, not authorized.</center>';
    }
    else
    {
        echo '<script type="text/javascript">function sendForm(){ document.getElementById("short_form").submit(); }</script>
        		<input type="text" name="shorten" value="'.htmlentities($_REQUEST['shorten']).'" />
                <input type="hidden" name="userID" value="'.htmlentities($_REQUEST['userID']).'" id="userID2" />
                <input type="hidden" name="comment" value="'.htmlentities($_REQUEST['comment']).'" id="comment" /><br /><br />
                <center><button class="button g-recaptcha" data-sitekey="'.$site.'" data-callback="sendForm">Shorten</button></center>';

    }

}
else
{
	echo '<input type="text" name="shorten" placeholder="Link to shorten" /><input type="hidden" name="userID" value="" id="userID2" /><br /><br />';
	if( $captcha )
		echo '<script type="text/javascript">function sendForm(){ document.getElementById("short_form").submit(); }</script><button class="button g-recaptcha" data-sitekey="'.$site.'" data-callback="sendForm">Shorten</button>';
	else
		echo '<input type="submit" value="Shorten" class="button"/>';
                
    echo '<a id="bookmark" href="" onclick="event.preventDefault();" />Bookmarklet</a><div id="info_shortcut" onclick="document.getElementById(\'instructions\').style.display = \'block\';">i</div>'
         .'<a id="see_shorts" href="" >List of shortened links</a><br /><br />'
         .'<div id="instructions">Drag and Drop this button into your favorites. It allows you to quickly shorten an URL directly from the webpage, without having to use this specific page.</div>';

}

?>
            </form>
        </div>
    </div>
    <script src="./static/cookie.js" type="text/javascript"></script>
</body>
</html>
