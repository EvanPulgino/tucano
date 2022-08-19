<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Tucano implementation : © Evan Pulgino <evan.pulgino@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * Tucano game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

if ( !defined('STATE_END_GAME') ) {
    define('STATE_PLAYER_SELECT_COLUMN', 10);
    define('STATE_PLAYER_RESOLVE_TOUCANS', 11);
    define('STATE_PLAYER_CHOOSE_GIFT', 12);
    define('STATE_PLAYER_RESOLVE_GIFT', 13);
    define('STATE_PLAYER_RESOLVE_STEAL', 14);
    define('STATE_GAME_RESOLVE_TOUCANS', 15);
    define('STATE_GAME_NEXT_PLAYER', 20);
    define('STATE_PLAYER_ASSIGN_JOKER', 21);
    define('STATE_GAME_FINAL_SCORING', 22);
    define('STATE_END_GAME', 99);
}
 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => STATE_PLAYER_SELECT_COLUMN )
    ),
    
    STATE_PLAYER_SELECT_COLUMN => array(
        "name" => "playerSelectColumn",
        "description" => clienttranslate('${actplayer} must collect a column of cards'),
        "descriptionmyturn" => clienttranslate('${you} must collect a column of cards'),
        "type" => "activeplayer",
        "possibleactions" => array("selectColumn"),
        "transitions" => array(
            "resolveToucans" => STATE_PLAYER_RESOLVE_TOUCANS,
            "endTurn" => STATE_GAME_NEXT_PLAYER
        )
    ),

    STATE_PLAYER_RESOLVE_TOUCANS => array(
        "name" => "playerResolveToucans",
        "description" => clienttranslate('${actplayer} must select a toucan to resolve'),
        "descriptionmyturn" => clienttranslate('${you} must select a toucan to resolve'),
        "type" => "activeplayer",
        "args" => "argsResolveToucans",
        "possibleactions" => array("selectFlip", "selectGift", "selectSteal"),
        "transitions" => array(
            "chooseGift" => STATE_PLAYER_CHOOSE_GIFT,
            "resolveSteal" => STATE_PLAYER_RESOLVE_STEAL,
            "resolveToucans" => STATE_PLAYER_RESOLVE_TOUCANS,
            "endTurn" => STATE_GAME_NEXT_PLAYER
        )
    ),

    STATE_PLAYER_CHOOSE_GIFT => array(
        "name" => "playerChooseGift",
        "description" => clienttranslate('${actplayer} must choose a card to gift to another player'),
        "descriptionmyturn" => clienttranslate('${you} must choose a card to gift to another player'),
        "type" => "activeplayer",
        "possibleactions" => array("chooseGift"),
        "transitions" => array(
            "resolveGift" => STATE_PLAYER_RESOLVE_GIFT,
            "resolveToucans" => STATE_PLAYER_RESOLVE_TOUCANS,
            "endTurn" => STATE_GAME_NEXT_PLAYER
        )
    ),

    STATE_PLAYER_RESOLVE_GIFT => array(
        "name" => "playerResolveGift",
        "description" => clienttranslate('${actplayer} must select a player to gift ${card}'),
        "descriptionmyturn" => clienttranslate('${you} must select a player to gift ${card}'),
        "type" => "activeplayer",
        "args" => "argsResolveGift",
        "possibleactions" => array("resolveGift"),
        "transitions" => array(
            "resolveToucans" => STATE_PLAYER_RESOLVE_TOUCANS,
            "endTurn" => STATE_GAME_NEXT_PLAYER
        )
    ),

    STATE_PLAYER_RESOLVE_STEAL => array(
        "name" => "playerResolveSteal",
        "description" => clienttranslate('${actplayer} must select a card to steal from another player'),
        "descriptionmyturn" => clienttranslate('${you} must select a card to steal from another player'),
        "type" => "activeplayer",
        "args" => "argsResolveSteal",
        "possibleactions" => array("resolveSteal"),
        "transitions" => array(
            "resolveToucans" => STATE_PLAYER_RESOLVE_TOUCANS,
            "endTurn" => STATE_GAME_NEXT_PLAYER
        )
    ),

    STATE_GAME_NEXT_PLAYER => array(
        "name" => "gameNextPlayer",
        "type" => "game",
        "action" => "stGameNextPlayer",
        "updateGameProgression" => true,
        "transitions" => array(
            "nextPlayer" => STATE_PLAYER_SELECT_COLUMN,
            "assignJoker" => STATE_PLAYER_ASSIGN_JOKER,
            "endGame" => STATE_GAME_FINAL_SCORING
        )
    ),

    STATE_PLAYER_ASSIGN_JOKER => array(
        "name" => "playerAssignJoker",
        "description" => clienttranslate('${actplayer} must add Joker to a set of fruit'),
        "descriptionmyturn" => clienttranslate('${you} must add Joker to a set of fruit'),
        "type" => "activeplayer",
        "possibleactions" => array("assignJoker"),
        "transitions" => array("endGame" => STATE_GAME_FINAL_SCORING)
    ),

    STATE_GAME_FINAL_SCORING => array(
        "name" => "gameFinalScoring",
        "type" => "game",
        "action" => "stGameFinalScoring",
        "transitions" => array("endGame" => STATE_END_GAME)
    ),
   
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    STATE_END_GAME => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);

