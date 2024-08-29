<?php
declare(strict_types=1);

require_once "includes/config.php";

$settings = fetchSettings($MySQLi);
$signupSWF = $settings['signupSWF'];
$sitename = $settings['DFSitename'];

function fetchSettings(mysqli $MySQLi): array {
    $query = $MySQLi->query("SELECT * FROM df_settings LIMIT 1");
    $settings = $query->fetch_assoc();
    $MySQLi->close();
    return $settings;
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($sitename) ?> | Register</title>
    <base href="">
    <link rel="stylesheet" href="includes/css/style.css">
    <link rel="shortcut icon" href="includes/favicon.ico">
    <!--[if lt IE 9]>
    <script src="https://raw.githubusercontent.com/aFarkas/html5shiv/master/src/html5shiv.js"></script>
    <![endif]-->
</head>
<body>
    <section id="window">
        <section id="outsideWindow">
            <section id="gameWindow">
                <embed 
                    src="<?= htmlspecialchars($signupSWF) ?>"
                    bgcolor="#3B0100"
                    wmode="transparent"
                    style="border-radius:5px"
                    scale="noborder"
                    quality="autohigh"
                    width="700"
                    height="550"
                    type="application/x-shockwave-flash"
                    pluginspage="http://www.macromedia.com/go/getflashplayer"
                    swLiveConnect="true"
                >
                <section id="linkWindow">
                    <span>
                        <a href="game/index.php">Play</a> | 
                        <a href="df-signup.php">Register</a> | 
                        <a href="mb-charTransfer.php">Transfer</a> | 
                        <a href="top100.php">Top100</a> | 
                        <a href="mb-bugTrack.php">Submit Bug</a> | 
                        <a href="df-upgrade.php">Upgrade</a> | 
                        <a href="account/">Account</a> |
                        <a href="df-lostpassword.php">Lost Password</a>
                    </span>
                </section>
            </section>
        </section>
    </section>
</body>
</html>
