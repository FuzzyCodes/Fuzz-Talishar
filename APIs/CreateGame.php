<?php

ob_start();
include "../HostFiles/Redirector.php";
include "../Libraries/HTTPLibraries.php";
include "../Libraries/SHMOPLibraries.php";
include_once "../Libraries/PlayerSettings.php";
include_once '../Assets/patreon-php-master/src/PatreonDictionary.php';
ob_end_clean();

$deck = TryPOST("deck");
$decklink = TryPOST("fabdb");
$deckTestMode = TryPOST("deckTestMode", "");
$format = TryPOST("format");
$visibility = TryPOST("visibility");
$decksToTry = TryPOST("decksToTry");
$favoriteDeck = TryPOST("favoriteDeck", "0");
$favoriteDeckLink = TryPOST("favoriteDecks", "0");
$gameDescription = htmlentities(TryPOST("gameDescription", "Game #"), ENT_QUOTES);

if($favoriteDeckLink != 0)
{
  $favDeckArr = explode("<fav>", $favoriteDeckLink);
  if(count($favDeckArr) == 1) $favoriteDeckLink = $favDeckArr[0];
  else {
    $favoriteDeckIndex = $favDeckArr[0];
    $favoriteDeckLink = $favDeckArr[1];
  }
}

session_start();

if (!isset($_SESSION["userid"])) {
  if (isset($_COOKIE["rememberMeToken"])) {
    include_once '../includes/functions.inc.php';
    include_once '../includes/dbh.inc.php';
    loginFromCookie();
  }
}

if(isset($_SESSION["userid"]))
{
  //Save game creation settings
  include_once 'includes/functions.inc.php';
  include_once 'includes/dbh.inc.php';
  if(isset($favoriteDeckIndex))
  {
    ChangeSetting("", $SET_FavoriteDeckIndex, $favoriteDeckIndex, $_SESSION["userid"]);
  }
  ChangeSetting("", $SET_Format, FormatCode($format), $_SESSION["userid"]);
  ChangeSetting("", $SET_GameVisibility, ($visibility == "public" ? 1 : 0), $_SESSION["userid"]);
  if($deckbuilderID != "")
  {
    if(str_contains($decklink, "fabrary")) storeFabraryId($_SESSION["userid"], $deckbuilderID);
    else if(str_contains($decklink, "fabdb")) storeFabDBId($_SESSION["userid"], $deckbuilderID);
  }
}

session_write_close();

$gameName = GetGameCounter("../");
$response = new stdClass();

if((!file_exists("../Games/$gameName")) && (mkdir("../Games/$gameName", 0700, true)) ){
} else {
  $response->error = "Encountered a problem creating a game. Please return to the main menu and try again";
  echo(json_encode($response));
  exit;
}

$p1Data = [1];
$p2Data = [2];
if ($deckTestMode != "") {
  $gameStatus = 4; //ReadyToStart
  $opponentDeck = "../Assets/Dummy.txt";
  copy($opponentDeck, "../Games/" . $gameName . "/p2Deck.txt");
} else {
  $gameStatus = 0; //Initial
}
$firstPlayerChooser = "";
$firstPlayer = 1;
$p1Key = hash("sha256", rand() . rand());
$p2Key = hash("sha256", rand() . rand() . rand());
$p1uid = "-";
$p2uid = "-";
$p1id = "-";
$p2id = "-";
$hostIP = $_SERVER['REMOTE_ADDR'];

$filename = "../Games/" . $gameName . "/GameFile.txt";
$gameFileHandler = fopen($filename, "w");
include "../MenuFiles/WriteGamefile.php";
WriteGameFile();

$filename = "../Games/" . $gameName . "/gamelog.txt";
$handler = fopen($filename, "w");
fclose($handler);

$currentTime = round(microtime(true) * 1000);
WriteCache($gameName, 1 . "!" . $currentTime . "!" . $currentTime . "!0!-1!" . $currentTime . "!!!0!0!"); //Initialize SHMOP cache for this game

$playerID = 1;

include './JoinGame.php';
?>