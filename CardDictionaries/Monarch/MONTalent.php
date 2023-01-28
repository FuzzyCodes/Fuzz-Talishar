<?php

  function MONTalentCardSubType($cardID)
  {
    switch($cardID)
    {
      case "MON000": return "Landmark";
      case "MON060": return "Chest";
      case "MON061": return "Head";
      case "MON187": return "Chest";
      case "MON188": return "Head";
      case "MON219": return "Ally";
      case "MON220": return "Ally";
      default: return "";
    }
  }

  function MONTalentPlayAbility($cardID, $from, $resourcesPaid, $target="-", $additionalCosts = "")
  {
    global $currentPlayer, $combatChainState, $CCS_GoesWhereAfterLinkResolves, $CS_NumAddedToSoul, $combatChain, $CS_PlayIndex;
    $otherPlayer = $currentPlayer == 1 ? 2 : 1;
    switch($cardID)
    {
      case "MON000":
        $rv = "";
        if($from == "PLAY")
        {
          DestroyLandmark(GetClassState($currentPlayer, $CS_PlayIndex));
          $rv = "The Great Library of Solana was destroyed.";
        }
        return $rv;
      case "MON061":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
        AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-");
        AddDecisionQueue("REMOVEMYHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDSOUL", $currentPlayer, "HAND", 1);
        AddDecisionQueue("ALLCARDTALENTORPASS", $currentPlayer, "LIGHT", 1);
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        return "";
      case "MON064":
        $hand = &GetHand($currentPlayer);
        for($i = 0; $i < count($hand); ++$i)
        {
          AddSoul($hand[$i], $currentPlayer, "HAND");
        }
        $hand = [];
        return "Put itself and all cards in your hand into your soul.";
      case "MON065":
        MyDrawCard();
        MyDrawCard();
        if(GetClassState($currentPlayer, $CS_NumAddedToSoul) > 0) MyDrawCard();
        return "";
      case "MON066": case "MON067": case "MON068":
        if(count(GetSoul($currentPlayer)) == 0)
        {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          $rv = "Goes into your soul after the chain link closes.";
        }
        return $rv;
      case "MON069": case "MON070": case "MON071":
        if($cardID == "MON069") $count = 4;
        else if($cardID == "MON070") $count = 3;
        else $count = 2;
        for($i=0; $i<$count; ++$i)
        {
          AddDecisionQueue("FINDINDICES", $currentPlayer, "WEAPON");
          AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("ADDATTACKCOUNTERS", $currentPlayer, "1", 1);
        }
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "MON081": case "MON082": case "MON083":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives your next attack action card +" . EffectAttackModifier($cardID) . " and go in your soul if it hits.";
      case "MON084": case "MON085": case "MON086":
        if($cardID == "MON084") $amount = 3;
        else if($cardID == "MON085") $amount = 2;
        else $amount = 1;
        if($target == "-")
        {
          WriteLog("Blinding Beam gives no bonus because it does not have a valid target.");
        }
        else $combatChain[intval($target)+5] -= $amount;
        return "";
      case "MON087":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives attacks against Shadow heroes +1 this turn.";
      case "MON188":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
        AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-");
        AddDecisionQueue("REMOVEMYHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIBANISH", $currentPlayer, "HAND,NA", 1);
        AddDecisionQueue("ALLCARDTALENTORPASS", $currentPlayer, "SHADOW", 1);
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        return "";
      case "MON189":
        PlayAlly("MON219", $currentPlayer);
        return "Creates a Blasmophet Ally.";
      case "MON190":
        PlayAlly("MON220", $currentPlayer);
        return "Creates an Ursur Ally.";
      case "MON192":
        if($from=="BANISH")
        {
          return "Returns to hand.";
        }
        return;
      case "MON193":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives your next action card +1, go again, and if it hits you may banish the top card of your deck.";
      case "MON194":
        MyDrawCard();
        return "Draws a card.";
      case "MON200": case "MON201": case "MON202":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Gives your next attack action this turn +" . EffectAttackModifier($cardID) . ".";
      case "MON212": case "MON213": case "MON214":
        if($cardID == "MON212") $maxCost = 2;
        else if($cardID == "MON213") $maxCost = 1;
        else $maxCost = 0;
        AddDecisionQueue("FINDINDICES", $currentPlayer, "MON212," . $maxCost);
        AddDecisionQueue("CHOOSEBANISH", $currentPlayer, "<-", 1);
        AddDecisionQueue("BANISHADDMODIFIER", $currentPlayer, "MON212", 1);
        return "Lets you play an attack action from your banish zone.";
      case "MON215": case "MON216": case "MON217":
        if($cardID == "MON215") $optAmt = 3;
        else if($cardID == "MON216") $optAmt = 2;
        else $optAmt = 1;
        Opt($cardID, $optAmt);
        AddDecisionQueue("FINDINDICES", $currentPlayer, "TOPDECK", 1);
        AddDecisionQueue("MULTIREMOVEDECK", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIBANISH", $currentPlayer, "DECK,NA", 1);
        AddDecisionQueue("SHOWBANISHEDCARD", $currentPlayer, "-", 1);
        return "Lets you opt $optAmt and banish the top card of your deck.";
      case "MON218":
        $theirCharacter = GetPlayerCharacter($otherPlayer);
        if(TalentContains($theirCharacter[0], "LIGHT", $otherPlayer))
        {
          if(GetHealth($currentPlayer) > GetHealth($otherPlayer))
          {
            AddDecisionQueue("FINDINDICES", $currentPlayer, "GYTYPE,AA");
            AddDecisionQueue("MAYCHOOSEDISCARD", $currentPlayer, "<-", 1);
            AddDecisionQueue("MULTIREMOVEDISCARD", $currentPlayer, "-", 1);
            AddDecisionQueue("MULTIBANISH", $currentPlayer, "GY,NA", 1);
          }
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
        return "";
      case "MON219":
        $otherPlayer = $currentPlayer == 2 ? 1 : 2;
        AddDecisionQueue("FINDINDICES", $currentPlayer, "HANDTALENT,SHADOW");
        AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIBANISH", $currentPlayer, "HAND,NA", 1);
        if (!IsAllyAttackTarget()) {
          AddDecisionQueue("PASSPARAMETER", $otherPlayer, "1", 1);
          AddDecisionQueue("MULTIREMOVEMYSOUL", $otherPlayer, "-", 1);
        }
        return "";
      default: return "";
    }
  }

  function MONTalentHitEffect($cardID)
  {
    global $combatChainState, $CCS_GoesWhereAfterLinkResolves, $defPlayer;
    switch($cardID)
    {
      case "MON072": case "MON073": case "MON074": $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL"; break;
      case "MON078": case "MON079": case "MON080": $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL"; break;
      case "MON198":
        if(IsHeroAttackTarget())
        {
          $numSoul = count(GetSoul($defPlayer));
          for($i=0; $i<$numSoul; ++$i) BanishFromSoul($defPlayer);
          LoseHealth($numSoul, $defPlayer);
        }
        break;
      case "MON206": case "MON207": case "MON208":
        if(IsHeroAttackTarget())
        {
          BanishFromSoul($defPlayer);
          $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "BANISH";
        }
        break;
      default: break;
    }
  }

  function ShadowPuppetryHitEffect()
  {
    global $mainPlayer;
    AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
    AddDecisionQueue("DECKCARDS", $mainPlayer, "0", 1);
    AddDecisionQueue("SETDQVAR", $mainPlayer, "1", 1);
    AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose if you want to banish <1> with Shadow Puppetry", 1);
    AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_banish_the_card", 1);
    AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
    AddDecisionQueue("PARAMDELIMTOARRAY", $mainPlayer, "0", 1);
    AddDecisionQueue("MULTIREMOVEDECK", $mainPlayer, "0", 1);
    AddDecisionQueue("MULTIBANISH", $mainPlayer, "DECK,-", 1);
    AddDecisionQueue("SHOWBANISHEDCARD", $mainPlayer, "-", 1);
  }

  function EndTurnBloodDebt()
  {
    global $mainPlayer;
    if(IsImmuneToBloodDebt($mainPlayer))
    {
      WriteLog("No blood debt damage was taken because you are immune.");
      return;
    }
    $numBD = SearchCount(SearchBanish($mainPlayer, "", "", -1, -1, "", "", true));
    if($numBD > 0)
    {
      LoseHealth($numBD, $mainPlayer);
      WriteLog("Player $mainPlayer lost $numBD health from Blood Debt at end of turn.", $mainPlayer);
    }
  }

  function IsImmuneToBloodDebt($player)
  {
    global $CS_Num6PowBan;
      $character = &GetPlayerCharacter($player);
      if($character[1] == 2 && ($character[0] == "MON119" || $character[0] == "MON120" || SearchCurrentTurnEffects("MON119-SHIYANA", $player) || SearchCurrentTurnEffects("MON120-SHIYANA", $player)) && GetClassState($player, $CS_Num6PowBan) > 0)
      {
        return true;
      }
      return false;
  }

?>
