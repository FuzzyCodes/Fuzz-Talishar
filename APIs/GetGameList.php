<?php

include "../Libraries/SHMOPLibraries.php";
include "../Libraries/HTTPLibraries.php";
include "../HostFiles/Redirector.php";
include "../CardDictionary.php";
include "../AccountFiles/AccountSessionAPI.php";
require_once '../Assets/patreon-php-master/src/PatreonLibraries.php';
include_once '../Assets/patreon-php-master/src/API.php';
include_once '../Assets/patreon-php-master/src/PatreonDictionary.php';
include_once "../AccountFiles/AccountDatabaseAPI.php";
include_once '../includes/functions.inc.php';
include_once '../includes/dbh.inc.php';

$path = "../Games";

session_start();
SetHeaders();

if(!IsUserLoggedIn()) {
  if(isset($_COOKIE["rememberMeToken"])) {
    loginFromCookie();
  }
}
$response = new stdClass();
$response->gamesInProgress = [];
$response->openGames = [];
$canSeeQueue = IsUserLoggedIn();
$response->canSeeQueue = $canSeeQueue;

$isShadowBanned = false;
if(isset($_SESSION["isBanned"])) $isShadowBanned = (intval($_SESSION["isBanned"]) == 1 ? true : false);
else if(IsUserLoggedIn()) $isShadowBanned = IsBanned(LoggedInUserName());

$gameInProgressCount = 0;
if ($handle = opendir($path)) {
  while (false !== ($folder = readdir($handle))) {
    if ('.' === $folder) continue;
    if ('..' === $folder) continue;
    $gameToken = $folder;
    $folder = $path . "/" . $folder . "/";
    $gs = $folder . "gamestate.txt";
    $currentTime = round(microtime(true) * 1000);
    if (file_exists($gs)) {
      $lastGamestateUpdate = intval(GetCachePiece($gameToken, 6));
      if ($currentTime - $lastGamestateUpdate < 30000) {
        $visibility = GetCachePiece($gameToken, 9);
        $gameInProgressCount += 1;
        if($visibility == "1")
        {
          $gameInProgress = new stdClass();
          $gameInProgress->p1Hero = GetCachePiece($gameToken, 7);
          $gameInProgress->p2Hero = GetCachePiece($gameToken, 8);
          $gameInProgress->secondsSinceLastUpdate = intval(($currentTime - $lastGamestateUpdate) / 1000);
          $gameInProgress->gameName = $gameToken;
          $gameInProgress->format = GetCachePiece($gameToken, 13);
          array_push($response->gamesInProgress, $gameInProgress);
        }
      }
      else if ($currentTime - $lastGamestateUpdate > 150000) //~10 minutes?
      {
        if ($autoDeleteGames) {
          deleteDirectory($folder);
          DeleteCache($gameToken);
        }
      }
      continue;
    }

    $gf = $folder . "GameFile.txt";
    $gameName = $gameToken;
    $lineCount = 0;
    $status = -1;
    if (file_exists($gf)) {
      $lastRefresh = intval(GetCachePiece($gameName, 2)); //Player 1 last connection time
      if ($lastRefresh != "" && $currentTime - $lastRefresh < 500) {
        include 'APIParseGamefile.php';
        $status = $gameStatus;
        UnlockGamefile();
      } else if ($lastRefresh == "" || $currentTime - $lastRefresh > 900000) //1 hour
      {
        deleteDirectory($folder);
        DeleteCache($gameToken);
      }
      if ($status == 0 && $visibility == "public" && intval(GetCachePiece($gameName, 11)) < 3) {
        $openGame = new stdClass();
        if($format != "compcc" && $format != "compblitz") $openGame->p1Hero = GetCachePiece($gameName, 7);
        $formatName = "";
        if ($format == "commoner") $formatName = "Commoner ";
        else if ($format == "livinglegendscc") $formatName = "Open Format";
        else if ($format == "clash") $formatName = "Clash";
        $description = ($gameDescription == "" ? "Game #" . $gameName : $gameDescription);
        $openGame->format = $format;
        $openGame->formatName = $formatName;
        $openGame->description = $description;
        $openGame->gameName = $gameToken;
        if($isShadowBanned) {
          if($format == "shadowblitz" || $format == "shadowcc") array_push($response->openGames, $openGame);
        } else {
          if($format != "shadowblitz" && $format != "shadowcc") array_push($response->openGames, $openGame);
        }
      }
    }
  }
  $response->gameInProgressCount = $gameInProgressCount;
  closedir($handle);
  echo json_encode($response);
}

function deleteDirectory($dir)
{
  if (!file_exists($dir)) {
    return true;
  }

  if (!is_dir($dir)) {
    $handler = fopen($dir, "w");
    fwrite($handler, "");
    fclose($handler);
    return unlink($dir);
  }

  foreach (scandir($dir) as $item) {
    if ($item == '.' || $item == '..') {
      continue;
    }
    if (!deleteDirectory($dir . "/" . $item)) {
      return false;
    }
  }
  return rmdir($dir);
}
?>
