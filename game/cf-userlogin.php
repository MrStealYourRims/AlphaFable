<?php

declare(strict_types=1);

/*
 * AlphaFable (DragonFable Private Server)
 * Made by MentalBlank
 * File: cf-userlogin.php - v0.1.2 (Updated to PHP 8.3)
 */

require_once "../includes/classes/Core.class.php";
require_once "../includes/classes/Security.class.php";
require_once "../includes/classes/Ninja.class.php";
require_once '../includes/config.php';

$Core->makeXML();

try {
    $rawPostData = file_get_contents('php://input');
    if (!empty($rawPostData)) {
        $xml = new SimpleXMLElement($rawPostData);

        if (isset($xml->strUsername, $xml->strPassword)) {
            $username = (string)$xml->strUsername;
            $password = $Security->encode((string)$xml->strPassword);

            $userQuery = $MySQLi->query("SELECT * FROM df_users WHERE name = ? AND pass = ? LIMIT 1");
            $userQuery->bind_param("ss", $username, $password);
            $userQuery->execute();
            $userResult = $userQuery->get_result()->fetch_assoc();

            if ($userResult) {
                $settingsQuery = $MySQLi->query("SELECT * FROM df_settings LIMIT 1");
                $settingsResult = $settingsQuery->fetch_assoc();

                $canPlay = $Security->checkAccessLevel($userResult['access'], $settingsResult['minAccess']);
                match ($canPlay) {
                    "Banned" => $Core->returnXMLError('Banned!', 'You have been <b>banned</b> from <b>AlphaFable</b>. If you believe this is a mistake, please contact the <b>AlphaFable</b> Staff.'),
                    "Invalid" => $Core->returnXMLError('Invalid Rank!', 'Sorry, The server is currently unavailable for your account, this may be due to server testing or upgrades. If you believe this is a mistake, please contact the <b>AlphaFable</b> Staff.'),
                    default => null,
                };

                $news = $settingsResult['news'];

                $dob = explode('T', $userResult['dob']);
                $dobnew = explode('-', $dob[0]);
                if ($dobnew[0] . "-" . $dobnew[1] === date('m-j')) {
                    $news .= "<br /><br /><b>Happy Birthday!</b>";
                }

                $MySQLi->query("UPDATE `df_users` SET `lastLogin` = ? WHERE `id` = ?");
                $MySQLi->bind_param("si", $dateToday, $userResult['id']);
                $MySQLi->execute();

                $token = strtoupper(md5($Security->encode($Ninja->encryptNinja(md5(md5(strlen((string)$token)) . md5($token . random_int(1, 100000)))))));
                $MySQLi->query("UPDATE `df_users` SET `LoginToken` = ? WHERE `id` = ?");
                $MySQLi->bind_param("si", $token, $userResult['id']);
                $MySQLi->execute();

                if ($MySQLi->affected_rows > 0) {
                    $dom = new DOMDocument();
                    $XML = $dom->appendChild($dom->createElement('characters'));

                    $user = $XML->appendChild($dom->createElement('user'));
                    $user->setAttribute('UserID', (string)$userResult['id']);
                    $user->setAttribute('intCharsAllowed', (string)$userResult['chars_allowed']);
                    $user->setAttribute('intAccessLevel', (string)$userResult['access']);
                    $user->setAttribute('intUpgrade', (string)$userResult['upgrade']);
                    $user->setAttribute('intActivationFlag', (string)$userResult['activation']);
                    $user->setAttribute('strUsername', $userResult['name']);
                    $user->setAttribute('strPassword', $password);
                    $user->setAttribute('strToken', $token);
                    $user->setAttribute('strNews', $news);
                    $user->setAttribute('strServerBuild', 'v0.0.1');
                    $user->setAttribute('bitAdFlag', (string)$userResult['ad_flag']);
                    $user->setAttribute('strServer', 'Private Server');
                    $user->setAttribute('dateToday', $dateToday);
                    $user->setAttribute('strDOB', $userResult['dob']);

                    $charactersQuery = $MySQLi->query("SELECT * FROM df_characters WHERE userid = ?");
                    $charactersQuery->bind_param("i", $userResult['id']);
                    $charactersQuery->execute();
                    $charactersResult = $charactersQuery->get_result();

                    while ($characterData = $charactersResult->fetch_assoc()) {
                        $characters = $user->appendChild($dom->createElement('characters'));
                        $characters->setAttribute('CharID', (string)$characterData['id']);
                        $characters->setAttribute('strCharacterName', $characterData['name']);
                        $characters->setAttribute('intLevel', (string)$characterData['level']);
                        $characters->setAttribute('intAccessLevel', (string)$characterData['access']);
                        $characters->setAttribute('intDragonAmulet', ($userResult['upgrade'] == 1 || $characterData['dragon_amulet'] == 1) ? '1' : '0');
                        $characters->setAttribute('strRaceName', "Human");
                        $characters->setAttribute('orgClassID', (string)$characterData['classid']);

                        $classQuery = $MySQLi->query("SELECT ClassName FROM df_class WHERE ClassID = ?");
                        $classQuery->bind_param("i", $characterData['classid']);
                        $classQuery->execute();
                        $classResult = $classQuery->get_result()->fetch_assoc();
                        $characters->setAttribute('strClassName', $classResult['ClassName']);
                    }
                } else {
                    $Core->returnXMLError('Error!', 'There was an issue updating your account information.');
                }
            } else {
                $Core->returnXMLError('User Not Found', 'Your username or / and password were incorrect, Please check your spelling and try again.');
            }
        } else {
            $MySQLi->query("INSERT INTO `df_error_logs` (`File`, `ErrorReason`, `PlayerID`, `UserID`) VALUES ('cf-userlogin', 'Database Communication Error', '0', '0')");
            $Core->returnXMLError('Error!', 'There was an error communicating with the database.');
        }
    } else {
        $Core->returnXMLError('Invalid Data!', 'Message');
    }
} catch (Exception $e) {
    $error = $MySQLi->real_escape_string($e->getMessage());
    $MySQLi->query("INSERT INTO `df_error_logs` (`File`, `ErrorReason`, `PlayerID`, `UserID`) VALUES ('cf-userlogin', ?, '0', '0')");
    $MySQLi->bind_param("s", $error);
    $MySQLi->execute();
    $Core->returnXMLError('Error!', 'There was an error communicating with the database.');
}

echo $dom->saveXML();
$MySQLi->close();
