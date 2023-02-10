<?php

/*
Encounter variable
encounter[0] = Encounter ID (001-099 Special Encounters | 101-199 Combat Encounters | 201-299 Event Encounters)
encounter[1] = Encounter Subphase
encounter[2] = Position in adventure
encounter[3] = Hero ID
encounter[4] = Adventure ID
encounter[5] = A string made up of encounters that have already been visited, looks like "ID-subphase,ID-subphase,ID-subphase,etc."
encounter[6] = majesticCard% (1-100, the higher it is, the more likely a majestic card is chosen) (Whole code is based off of the Slay the Spire rare card chance)
encounter[7] = background chosen
encounter[8] = adventure difficulty (to be used later)
encounter[9] = current gold
encounter[10] = rerolls remaining //TODO: Add in a reroll system
*/

function GetOptions($amount, $upperBound, $lowerBound = 0, $step = 1) //amount needs to be less than both upperbound and the amount of options in the pool being chosen from
{
  $options = [];
  $randomNums = range($lowerBound, $upperBound, $step);
  shuffle($randomNums);
  for($i = 0; $i < $amount; ++$i)
  {
    array_push($options, $randomNums[$i]);
  }
  return $options;
}

function GetBackgrounds($character)
{
  switch($character)
  {
    case "Dorinthea": $backgroundChoices = array("The_Volcai_Sellsword", "The_Lowly_Solanian", "The_Fierce_Warrior", "Spiders_Deserter"); break;
    case "Bravo": $backgroundChoices = array("The_Everfest_Showman", "The_Reclusive_Blacksmith", "The_Slumbering_Giant"); break;
  }
  $options = getOptions(2, count($backgroundChoices)-1);
  return $backgroundChoices[$options[0]] . "," . $backgroundChoices[$options[1]];
}

/*function GetNextEncounter($player) //TODO overhaul this whole function and children
{
  $encounter = &GetZone(1, "Encounter");
  // WriteLog("hijacked GetNextEncounter");
  // WriteLog("Encounter[0]: " . $encounter[0]);
  // WriteLog("Encounter[1]: " . $encounter[1]);
  // WriteLog("Encounter[2]: " . $encounter[2]);
  ++$encounter[2];
  if($encounter[2] == 3 || $encounter[2] == 5) return GetCombat("Easy");
  else if($encounter[2] == 7 || $encounter[2] == 10) return GetCombat("Medium");
  else if($encounter[2] == 12 || $encounter[2] == 14) return GetCombat("Hard");
  else if($encounter[2] == 2) return "005-PickMode";
  else if($encounter[2] == 17) return "105-BeforeFight";
  else if($encounter[2] == 9 || $encounter[2] == 16) return "020-PickMode";
  //else return GetEvent();
  else return "20" . rand(1, 2) . "-PickMode";
}

function GetCombat($difficulty)
{
  $encounter = &GetZone(1, "Encounter");
  $alreadyPicked = explode(",", $encounter[5]);
  switch($difficulty)
  {
    case "Easy": $potentialEncounters = array("101-Fight", "102-BeforeFight", "103-BeforeFight", "104-BeforeFight", "106-BeforeFight", "107-BeforeFight", "113-BeforeFight"); break;
    case "Medium": $potentialEncounters = array("101-Fight", "102-BeforeFight", "103-BeforeFight", "104-BeforeFight", "106-BeforeFight", "107-BeforeFight"); break;
    case "Hard": $potentialEncounters = array("101-Fight", "102-BeforeFight", "103-BeforeFight", "104-BeforeFight", "106-BeforeFight", "107-BeforeFight"); break;
  }
  $generatedEncounters = [];
  for($i = 0; $i < count($potentialEncounters); ++$i)
  {
    $notFound = true;
    for($j = 0; $j < count($alreadyPicked) && $notFound; ++$j)
    {
      if($alreadyPicked[$j] == $potentialEncounters[$i]) $notFound = false;
    }
    if($notFound) array_push($generatedEncounters, $potentialEncounters[$i]);
  }
  //WriteLog("Amount of encounters to pick from: " . count($generatedEncounters));
  $randomEncounter = rand(0, count($generatedEncounters)-1);
  $encounter[5] = $encounter[5] . "," . $generatedEncounters[$randomEncounter];
  return $generatedEncounters[$randomEncounter];
}*/

/*function GetEvent()
{
  $encounter = &GetZone(1, "Encounter");
  $alreadyPicked = explode(",", $encounter[5]);
  $generateRand = rand(1, 100);
  if($generateRand >= 90) $rarity = "Rare";
  else if($generateRand >= 60) $rarity = "Uncommon";
  else $rarity = "Common";
  switch($rarity)
  {
    case "Common": $potentialEncounters = array("205-PickMode", "204-PickMode"); break;
    case "Uncommon": $potentialEncounters = array("205-PickMode"); break;
    case "Rare": $potentialEncounters = array("201-PickMode", "202-PickMode"); break;
  }
  $generatedEncounters = [];
  for($i = 0; $i < count($potentialEncounters); ++$i)
  {
    $notFound = true;
    for($j = 0; $j < count($alreadyPicked) && $notFound; ++$j)
    {
      if($alreadyPicked[$j] == $potentialEncounters[$i]) $notFound = false;
    }
    if($notFound) array_push($generatedEncounters, $potentialEncounters[$i]);
  }
  //WriteLog("Amount of encounters to pick from: " . count($generatedEncounters));
  $randomEncounter = rand(0, count($generatedEncounters)-1);
  $encounter[5] = $encounter[5] . "," . $generatedEncounters[$randomEncounter];
  return $generatedEncounters[$randomEncounter];
}*/

function GetPowers($amount = 3, $special = "-")
{
  $common = array("ROGUE507", "ROGUE508", "ROGUE509", "ROGUE510", "ROGUE511", "ROGUE512", "ROGUE513", "ROGUE516", "ROGUE517");
  $rare = array("ROGUE501", "ROGUE504", "ROGUE518", "ROGUE519", "ROGUE521", "ROGUE522", "ROGUE523", "ROGUE524", "ROGUE525");
  $majestic = array("ROGUE502", "ROGUE503", "ROGUE505", "ROGUE506", "ROGUE526", "ROGUE527", "ROGUE528");
  if($special = "-")
  {
    $options = [];
    $rarityCount = array(0, 0, 0);
    for($i = 0; $i < $amount; ++$i)
    {
      $random = rand(1,100);
      if($random >= 90) ++$rarityCount[2];
      else if($random >= 60) ++$rarityCount[1];
      else ++$rarityCount[0];
    }
    if($rarityCount[0] > 0)
    {
      $randomNums = getOptions($rarityCount[0], count($common)-1);
      for($i = 0; $i < $rarityCount[0]; ++$i)
      {
        array_push($options, $common[$randomNums[$i]]);
      }
    }
    if($rarityCount[1] > 0)
    {
      $randomNums = getOptions($rarityCount[1], count($rare)-1);
      for($i = 0; $i < $rarityCount[1]; ++$i)
      {
        array_push($options, $rare[$randomNums[$i]]);
      }
    }
    if($rarityCount[2] > 0)
    {
      $randomNums = getOptions($rarityCount[2], count($majestic)-1);
      for($i = 0; $i < $rarityCount[2]; ++$i)
      {
        array_push($options, $majestic[$randomNums[$i]]);
      }
    }
    $result = "";
    for($i = 0; $i < count($options); ++$i)
    {
      if($i != 0) $result.=",";
      $result.=$options[$i];
    }
    return $result;
  }
}

function GetRandomCards($number = 4, $special = "-", $specialType = "-")
{
  if($special == "ForcedRarity")
  {
    $result = [];
    $pool = GeneratePool($result, "Class", $specialType);
    array_push($result, $pool[rand(0, count($pool)-1)]);
    $pool = GeneratePool($result, "Class", $specialType);
    array_push($result, $pool[rand(0, count($pool)-1)]);
    $pool = GeneratePool($result, "Talent", $specialType);
    array_push($result, $pool[rand(0, count($pool)-1)]);
    $pool = GeneratePool($result, "Generic", $specialType);
    array_push($result, $pool[rand(0, count($pool)-1)]);
    $resultStr = "";
    for($i = 0; $i < count($result); ++$i)
    {
      if($i != 0) $resultStr.=",";
      $resultStr.=$result[$i];
    }
    return $resultStr;
  }
  else //default. This is used in the combat encounter rewards. Literally everything passed into the function is ignored.
  {
    $result = [];
    $pool = GeneratePool($result, "Class");
    array_push($result, $pool[rand(0, count($pool)-1)]);
    $pool = GeneratePool($result, "Class");
    array_push($result, $pool[rand(0, count($pool)-1)]);
    $pool = GeneratePool($result, "Talent");
    array_push($result, $pool[rand(0, count($pool)-1)]);
    $pool = GeneratePool($result, "Generic");
    array_push($result, $pool[rand(0, count($pool)-1)]);
    $resultStr = "";
    for($i = 0; $i < count($result); ++$i)
    {
      if($i != 0) $resultStr.=",";
      $resultStr.=$result[$i];
    }
    return $resultStr;
  }
}

function GeneratePool($selected, $type, $rarity = "-")
{
  $encounter = &GetZone(1, "Encounter");
  if($rarity == "-")
  {
    $randRarity = rand(1,100);
    if($randRarity <= $encounter[6])
    {
      $encounter[6] = 1;
      $rarity = "Majestic";
    }
    else if($randRarity >= 75)
    {
      $encounter[6] += 3;
      $rarity = "Rare";
    }
    else
    {
      $encounter[6] +=1;
      $rarity = "Common";
    }
  }
  $pool = GetPool($type, $encounter[3], $rarity, $encounter[7]);
  $generatedPool = [];
  for($i = 0; $i < count($pool); ++$i)
  {
    $found = false;
    for($j = 0; $j < count($selected); ++$j)
    {
      if($selected[$j] == $pool[$i]) $found = true;
    }
    if(!$found) array_push($generatedPool, $pool[$i]);
  }
  return $generatedPool;
}

function GetRandomArmor($type) //TODO combine this with GetRandomCards()
{
  $encounter = &GetZone(1, "Encounter");
  switch($encounter[3])
  {
    case "Dorinthea":
    {
      switch($type)
      {
        case "Head": $pool = array("UPR183", "WTR151", "MON241", "WTR155", "ARC155", "ELE233", "DYN236", "ARC151", "EVR053"); break;
        case "Chest": $pool = array("MON238", "DVR004", "ELE234", "WTR152", "MON242", "WTR156", "ARC156", "UPR184", "DYN237", "ARC152", "CRU081"); break;
        case "Arms": $pool = array("ARC153", "ELE235", "CRU179", "WTR153", "MON243", "WTR157", "ARC157", "UPR185", "DYN238", "MON239", "MON108"); break;
        case "Legs": $pool = array("MON244", "WTR158", "ARC154", "ARC158", "UPR186", "ELE236", "WTR154", "DYN239", "MON240", "WTR117"); break;
      }
      break;
    }
    case "Bravo":
    {
      switch($type)
      {
        case "Head": $pool = array("UPR183", "WTR151", "MON241", "WTR155", "ARC155", "ELE233", "DYN236", "ARC151", "WTR042"); break;
        case "Chest": $pool = array("MON238", "DVR004", "ELE234", "WTR152", "MON242", "WTR156", "ARC156", "UPR184", "DYN237", "ARC152", "EVR020"); break;
        case "Arms": $pool = array("ARC153", "ELE235", "CRU179", "WTR153", "MON243", "WTR157", "ARC157", "UPR185", "DYN238", "MON239", "CRU025"); break;
        case "Legs": $pool = array("MON244", "WTR158", "ARC154", "ARC158", "UPR186", "ELE236", "WTR154", "DYN239", "MON240"); break;
      }
      break;
    }
  }
  return $pool[rand(0, count($pool)-1)];
}

function GetRandomDeckCard($player, $special = "") //TODO add in a seperate special call to remove random cards instead of any card and a special call to remove powers.
{
  
  $deck = &GetZone($player, "Deck");
  $fullList = "";
  for($i = 0; $i < count($deck); ++$i)
  {
    if(CardSubtype($deck[$i]) != "Power")
    {
      //WriteLog(CardSubtype($deck[$i]));
      if($i != 0) $fullList .= ",";
      $fullList .= $deck[$i];
    }
  }

  if ($special == "") {
    $special = "ALL"; //This is the default mode
  }
  if ($special == "ALL") return $fullList; // By default, this is all we need
  elseif($special == 4) {
    $deckNoPowers = explode(",", $fullList);
    
    $deckIndexes = array("");
    for ($i = 0; $i < count($deckNoPowers); $i++){
      $deckIndexes[$i] = $i; //Fill an array with indexes so we can keep track of which ones have been picked
    }
    $picks = array("");
    for($i = 0; $i < $special; $i++){
      $pickedNumber = rand(0, count($deckIndexes));
      $picks[$i] = $deckIndexes[$pickedNumber];
      array_splice($deckIndexes, $pickedNumber, 1); //Remove from possible lists
    }
    WriteLog($picks);
    $output = "";
    for($i=0; $i< count($picks); $i++){
      if($i != 0) $output .= ",";
      $output .= $deckNoPowers[$picks[$i]];
      WriteLog($output); 
    }
    return $output;
  }
  WriteLog("Function GetRandomDeckCard failed to find a case");
  return "This should never happen";
}

function GetShop()
{
  $result = [];
  $pool = GeneratePool($result, "Class");
  array_push($result, $pool[rand(0, count($pool)-1)]);
  $pool = GeneratePool($result, "Class");
  array_push($result, $pool[rand(0, count($pool)-1)]);
  $pool = GeneratePool($result, "Talent");
  array_push($result, $pool[rand(0, count($pool)-1)]);
  $pool = GeneratePool($result, "Generic");
  array_push($result, $pool[rand(0, count($pool)-1)]);
  $pool = GeneratePool($result, "Generic"); //change to weapon once that is set up
  array_push($result, $pool[rand(0, count($pool)-1)]);
  //$pool = GeneratePool($result, "Power");
  array_push($result, GetPowers(1));
  $resultStr = "";
  for($i = 0; $i < count($result); ++$i)
  {
    if($i != 0) $resultStr.=",";
    $resultStr.=$result[$i];
  }
  return $resultStr;
}

function GetShopCost($cardID)
{
  if($cardID == "CardBack") return 0;
  if(CardSubtype($cardID) == "Power") return 3;
  return 1;
}
?>