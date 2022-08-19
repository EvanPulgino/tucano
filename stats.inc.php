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
 * stats.inc.php
 *
 * Tucano game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),
    ),
    
    // Statistics existing for each player
    "player" => array(

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),

        "fruit_cards_taken" => array("id" => 27,
                    "name" => totranslate("Number of fruit cards taken"),
                    "type" => "int"),

        "fruit_cards_per_turn" => array("id" => 28,
                    "name" => totranslate("Number of fruit cards taken per turn"),
                    "type" => "float"),

        "acai_points" => array("id"=> 11,
                    "name" => totranslate("Points from Acai Berries"),
                    "type" => "int" ),

        "avocado_points" => array("id"=> 12,
                    "name" => totranslate("Points from Avocados"),
                    "type" => "int" ), 

        "banana_points" => array("id"=> 13,
                    "name" => totranslate("Points from Bananas"),
                    "type" => "int" ), 

        "carambola_points" => array("id"=> 14,
                    "name" => totranslate("Points from Carambolas"),
                    "type" => "int" ), 

        "coconut_points" => array("id"=> 15,
                    "name" => totranslate("Points from Coconuts"),
                    "type" => "int" ), 

        "fig_points" => array("id"=> 16,
                    "name" => totranslate("Points from Figs"),
                    "type" => "int" ), 

        "lime_points" => array("id"=> 17,
                    "name" => totranslate("Points from Limes"),
                    "type" => "int" ), 

        "lychee_points" => array("id"=> 18,
                    "name" => totranslate("Points from Lychees"),
                    "type" => "int" ), 

        "orange_points" => array("id"=> 19,
                    "name" => totranslate("Points from Oranges"),
                    "type" => "int" ), 

        "papaya_points" => array("id"=> 20,
                    "name" => totranslate("Points from Papayas"),
                    "type" => "int" ), 

        "pineapple_points" => array("id"=> 21,
                    "name" => totranslate("Points from Pineapples"),
                    "type" => "int" ), 

        "pomegranate_points" => array("id"=> 22,
                    "name" => totranslate("Points from Pomegranates"),
                    "type" => "int" ), 

        "rambutan_points" => array("id"=> 23,
                    "name" => totranslate("Points from Rambutans"),
                    "type" => "int" ), 

        "flip_toucans_used" => array("id"=> 24,
                    "name" => totranslate("Flip Toucans Used"),
                    "type" => "int" ), 

        "gift_toucans_used" => array("id"=> 25,
                    "name" => totranslate("Gift Toucans Used"),
                    "type" => "int" ), 

        "steal_toucans_used" => array("id"=> 26,
                    "name" => totranslate("Steal Toucans Used"),
                    "type" => "int" ), 

        "total_toucans_used" => array("id"=> 29,
                    "name" => totranslate("Total Toucans Used"),
                    "type" => "int" )
    )

);
