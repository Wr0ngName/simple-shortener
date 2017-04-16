<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener - Installer</title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="./static/common.css"/>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>
    <body>
        <div id="content">
            <div id="form">
<?php

error_reporting(-1);
error_reporting(E_ALL);
ini_set('display_errors',TRUE);
ini_set('display_startup_errors',TRUE);

$title = 'My URL Shortener';
$hostname = 'localhost';

$captcha = false;

if ( isset($_GET['db_type']) && ($_GET['db_type'] == 'sqlite3' || $_GET['db_type'] == 'mysql') )
{
    if( !empty($_GET['title']) ) $title = urldecode( $_GET['title'] );

    if( !empty($_GET['site_id']) && !empty($_GET['site_secret']) ) 
    {
        $captcha    = true;
        $site       = urldecode( $_GET['site_id'] );
        $secret     = urldecode( $_GET['site_secret'] );
    }

    if ($_GET['db_type'] == 'sqlite3')
    {
        $connexion = new PDO('sqlite:./core/database.sqlite3');
        file_put_contents("./core/bdd.php", "<?php\r\ntry {\r\n    ".'$connexion'." = new PDO(\"sqlite:./core/database.sqlite3\");\r\n"
                            .'    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)'.";\r\n} catch (PDOException ".'$e'.") {\r\n    echo ".'$e->getMessage();'."\r\n}");

        file_put_contents("./core/config.php", "<?php\r\n".'    $title = \''.addslashes($title)."';\r\n"
                            .($captcha?'    $site = \''.$site."';\r\n".'    $secret = \''.$secret."';\r\n":'')
                            .'    $captcha = '.($captcha?'true':'false').";\r\n"
                        );
    }
    elseif ($_GET['db_type'] == 'mysql')
    {
        if( isset($_POST['hostname']) ) $hostname = urldecode( $_POST['hostname'] );

        if ( !empty($_POST['username']) && !empty($_POST['pwd']) && !empty($_POST['db_name']) )
        {
            $connexion = new PDO("mysql:host=$hostname;dbname=URLShortener", $_POST['username'], $_POST['pwd']);
            file_put_contents("./core/bdd.php", "<?php\r\n\r\ntry {\r\n"
                                . "    ".'$connexion'." = new PDO(\"mysql:host=localhost;dbname=" . stripslashes($_POST['db_name']) . ";charset=utf8\", '" . stripslashes($_POST['username']) . "', '" . stripslashes($_POST['pwd']) . "');\r\n"
                                . '    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)'.";\r\n} catch (PDOException ".'$e'.") {\r\n    echo ".'$e->getMessage()'.";\r\n}");
        }
        else
        {
            file_put_contents("./core/config.php", "<?php\r\n".'    $title = \''.addslashes($title)."';\r\n"
                            .($captcha?'    $site = \''.$site."';\r\n".'    $secret = \''.$secret."';\r\n":'')
                            .'    $captcha = '.($captcha?'true':'false').";\r\n"
                        );
            header('Location: install.php?form');
        }
    }

    if ($connexion->errorCode() == 0) 
    {
        /*  BDD schema
            TABLE : shortener
            short | url | comment | views | id_user | date
        */

        $connexion->query('CREATE TABLE shortener(
                                short   CHAR(5) PRIMARY KEY NOT NULL,
                                url     VARCHAR(700) NOT NULL,
                                comment CHAR(35),
                                views   INT,
                                id_user VARCHAR(35),
                                date    DATETIME NOT NULL
                            );
                            CREATE INDEX id_user ON shortener (id_user);'
                        );
        header('Location: install.php?done');
    } else {
        echo "SQL ERROR [" . $connexion->errorCode() . "]";
    }
}
elseif ( isset($_GET['done'] ) )
{
    echo "<h3>The installation is finish. You need to delete the file  \"install.php\"  now !</h3>";
}
elseif ( isset($_GET['form']) )
{
    echo '<form id="short_form" action="install.php?db_type=mysql" method="post">Hostname (or IP), Name of the MySQL database, Username and password to connect to it.<br /><br />'
            .'<input type="text" name="hostname" placeholder="localhost" />'
            .'<input type="text" name="db_name" placeholder="Database Name" />'
            .'<input type="text" name="username" placeholder="Username" />'
            .'<input type="password" name="pwd" placeholder="Password" /><br /><br />'
            .'<input type="submit" value="Install &rarr;" style="width: 98%;" /></form>';
}
else
    echo '<form method="get" id="short_form" action="install.php">Choose the type of database to use:<br /><p style="float:right; border-left:1px solid grey; width:50%; text-align:center;">'
        .'<input type="radio" name="db_type" checked="checked" value="sqlite3">SQLite3</p><p style="float:left; text-align:center; width:45%;"><input type="radio" name="db_type" value="mysql">MySQL</p><br /><br /><br />'
        .'Google ReCaptcha Site Key  :<input type="text" placeholder="Your Google ReCaptcha Site Key" name="site_id" /><br /><br />'
        .'Google ReCaptcha Secret Key  :<input type="text" placeholder="Your Google ReCaptcha Secret Key" name="site_secret" /><br /><br />'
        .'Choose the name of your instance  :<input type="text" placeholder="My URL Shortener" name="title" /><br /><br /><input type="submit" value="Install &rarr;" style="width: 98%;" />';

?>
                </form>
            </div>
        </div>
    </body>
</html>