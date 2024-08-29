<?php
declare(strict_types=1);

require_once "../includes/config.php";

$size = $_GET["size"] ?? "normal";

$dimensions = match ($size) {
    "tiny" => ["width" => 475, "height" => 350, "font" => 8],
    "large" => ["width" => 1150, "height" => 840, "font" => 14],
    "huge" => ["width" => 1750, "height" => 1280, "font" => 19],
    default => ["width" => 750, "height" => 550, "font" => 10],
};

$query = $MySQLi->query("SELECT * FROM df_settings LIMIT 1");
$settings = $query->fetch_assoc();

$loaderSWF = $settings['loaderSWF'];
$CoreSWF = $settings['gameSWF'];
$sitename = $settings['DFSitename'];

$MySQLi->close();

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($sitename) ?> | Play</title>
    <link rel="stylesheet" href="../includes/css/style.css">
    <link rel="shortcut icon" href="../includes/favicon.ico">
    <script src="../includes/scripts/AC_RunActiveContent.js"></script>
    <script src="../includes/scripts/extra.js"></script>
    <!--[if lt IE 9]>
    <script src="https://raw.githubusercontent.com/aFarkas/html5shiv/master/src/html5shiv.js"></script>
    <![endif]-->
</head>
<body onload="pageLoaded()">
    <section id="window">
        <section id="outsideWindow">
            <section id="gameWindow" style="width:<?= $dimensions['width'] ?>px; height:<?= $dimensions['height'] ?>px;">
                <object type="application/x-shockwave-flash" data="gamefiles/<?= htmlspecialchars($loaderSWF) ?>" 
                        width="<?= $dimensions['width'] ?>" height="<?= $dimensions['height'] ?>" id="FFable">
                    <param name="movie" value="gamefiles/<?= htmlspecialchars($loaderSWF) ?>">
                    <param name="allowScriptAccess" value="sameDomain">
                    <param name="menu" value="false">
                    <param name="allowFullScreen" value="true">
                    <param name="flashvars" value="strFileName=<?= htmlspecialchars($CoreSWF) ?>">
                    <param name="bgcolor" value="#530000">
                    <embed src="gamefiles/<?= htmlspecialchars($loaderSWF) ?>" 
                           flashvars="strFileName=<?= htmlspecialchars($CoreSWF) ?>"
                           name="FFable" bgcolor="#530000" menu="false" allowFullScreen="true" 
                           width="<?= $dimensions['width'] ?>" height="<?= $dimensions['height'] ?>" 
                           align="middle" allowScriptAccess="sameDomain" 
                           type="application/x-shockwave-flash" 
                           pluginspage="http://www.macromedia.com/go/getflashplayer" 
                           swLiveConnect="true">
                </object>
                <section id="linkWindow">
                    <span>
                        <a href="index.php">Play</a> | 
                        <a href="../df-signup.php">Register</a> | 
                        <a href="../mb-charTransfer.php">Transfer</a> | 
                        <a href="../top100.php">Top100</a> | 
                        <a href="../mb-bugTrack.php">Submit Bug</a> | 
                        <a href="../df-upgrade.php">Upgrade</a> | 
                        <a href="../account/">Account</a> |
                        <a href="../df-lostpassword.php">Lost Password</a>
                    </span>
                </section>
            </section>
        </section>
    </section>
</body>
</html>
