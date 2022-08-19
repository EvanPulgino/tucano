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
 * material.inc.php
 *
 * Tucano game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

// Define Constants
if (!defined('FRUIT')) {
    // Card Types
    define('FRUIT', 0);
    define('TOUCAN', 1);

    // Fruit Types
    define('ACAI_BERRY', 0);
    define('AVOCADO', 1);
    define('BANANA', 2);
    define('CARAMBOLA', 3);
    define('COCONUT', 4);
    define('FIG', 5);
    define('LIME', 6);
    define('LYCHEE', 7);
    define('ORANGE', 8);
    define('PAPAYA', 9);
    define('PINEAPPLE', 10);
    define('POMEGRANATE', 11);
    define('RAMBUTAN', 12);
    define('JOKER', 13);

    // Toucan Types
    define('FLIP', 14);
    define('GIFT', 15);
    define('STEAL', 16);

    // Scoring Types
    define('RANK', 0);
    define('SET', 1);

    // Selection Columns
    define('A', 0);
    define('B', 1);
    define('C', 2);
}

// Export Constants
$this->constant = array(
    'A' => A,
    'ACAI_BERRY' => ACAI_BERRY,
    'AVOCADO' => AVOCADO,
    'B' => B,
    'BANANA' => BANANA,
    'C' => C,
    'CARAMBOLA' => CARAMBOLA,
    'COCONUT' => COCONUT,
    'FIG' => FIG,
    'FLIP' => FLIP,
    'FRUIT' => FRUIT,
    'GIFT' => GIFT,
    'LIME' => LIME,
    'LYCHEE' => LYCHEE,
    'ORANGE' => ORANGE,
    'PAPAYA' => PAPAYA,
    'PINEAPPLE' => PINEAPPLE,
    'POMEGRANATE' => POMEGRANATE,
    'RAMBUTAN' => RAMBUTAN,
    'RANK' => RANK,
    'SET' => SET,
    'STEAL' => STEAL,
    'TOUCAN' => TOUCAN,
    'JOKER' => JOKER
);

// Cards
$this->card = array(
   ACAI_BERRY => array(
     'cardtype' => FRUIT,
     'subtype' => ACAI_BERRY,
     'count' => 6
   ),
   AVOCADO => array(
    'cardtype' => FRUIT,
    'subtype' => AVOCADO,
    'count' => 5
  ),
  BANANA => array(
    'cardtype' => FRUIT,
    'subtype' => BANANA,
    'count' => 5
  ),
  CARAMBOLA => array(
    'cardtype' => FRUIT,
    'subtype' => CARAMBOLA,
    'count' => 5
  ),
  COCONUT => array(
    'cardtype' => FRUIT,
    'subtype' => COCONUT,
    'count' => 6
  ),
  FIG => array(
    'cardtype' => FRUIT,
    'subtype' => FIG,
    'count' => 4
  ),
  LIME => array(
    'cardtype' => FRUIT,
    'subtype' => LIME,
    'count' => 2
  ),
  LYCHEE => array(
    'cardtype' => FRUIT,
    'subtype' => LYCHEE,
    'count' => 2
  ),
  ORANGE => array(
    'cardtype' => FRUIT,
    'subtype' => ORANGE,
    'count' => 4
  ),
  PAPAYA => array(
    'cardtype' => FRUIT,
    'subtype' => PAPAYA,
    'count' => 4
  ),
  PINEAPPLE => array(
    'cardtype' => FRUIT,
    'subtype' => PINEAPPLE,
    'count' => 3
  ),
  POMEGRANATE => array(
    'cardtype' => FRUIT,
    'subtype' => POMEGRANATE,
    'count' => 6
  ),
  RAMBUTAN => array(
    'cardtype' => FRUIT,
    'subtype' => RAMBUTAN,
    'count' => 5
  ),
  JOKER => array(
    'cardtype' => FRUIT,
    'subtype' => JOKER,
    'count' => 1
  ),
  FLIP => array(
    'cardtype' => TOUCAN,
    'subtype' => FLIP,
    'count' => 4
  ),
  GIFT => array(
    'cardtype' => TOUCAN,
    'subtype' => GIFT,
    'count' => 4
  ),
  STEAL => array(
    'cardtype' => TOUCAN,
    'subtype' => STEAL,
    'count' => 4
  )
);

// Fruit Detail
$this->fruit = array(
  ACAI_BERRY => array(
    'name' => clienttranslate('acai berry'),
    'nametr' => self::_('acai berry'),
    'namecss' => 'acai',
    'pluralname' => clienttranslate('acai berries'),
    'pluralnametr' => self::_('acai berries'),
    'scoretype' => SET,
    'points' => array(
      1 => 1,
      2 => 2,
      3 => 3,
      4 => 5,
      5 => 8,
      6 => 13
    ),
    'tooltip' => clienttranslate('<div id="fruit_acai_points_1">1 card = 1 point</div><div id="fruit_acai_points_2">2 cards = 2 points</div><div id="fruit_acai_points_3">3 cards = 3 points</div><div id="fruit_acai_points_4">4 cards = 5 points</div><div id="fruit_acai_points_5">5 cards = 8 points</div><div id="fruit_acai_points_6">6 cards = 13 points</div>')
  ),
  AVOCADO => array(
    'name' => clienttranslate('avocado'),
    'nametr' => self::_('avocado'),
    'namecss' => 'avocado',
    'pluralname' => clienttranslate('avocados'),
    'pluralnametr' => self::_('avacados'),
    'scoretype' => RANK,
    'points' => array(
      0 => 1,
      1 => 3,
    ),
    'tooltip' => clienttranslate('If you have more avocados than any other player you score 3 points per collected avocado. If not you score 1 point per avocado. If there is a tie for the most all players score 1 point per avocado.')
  ),
  BANANA => array(
    'name' => clienttranslate('banana'),
    'nametr' => self::_('banana'),
    'namecss' => 'banana',
    'pluralname' => clienttranslate('bananas'),
    'pluralnametr' => self::_('bananas'),
    'scoretype' => RANK,
    'points' => array(
      0 => 0,
      1 => 2,
    ),
    'tooltip' => clienttranslate('If you have more bananas than any other player you score 2 points per collected banana. If not you score 0 points per banana. If there is a tie for the most all players score 0 points per banana.')
  ),
  CARAMBOLA => array(
    'name' => clienttranslate('carambola'),
    'nametr' => self::_('carambola'),
    'namecss' => 'carambola',
    'pluralname' => clienttranslate('carambolas'),
    'pluralnametr' => self::_('carambolas'),
    'scoretype' => SET,
    'points' => array(
      1 => 1,
      2 => 3,
      3 => 6,
      4 => 10,
      5 => 15
    ),
    'tooltip' => clienttranslate('<div id="fruit_carambola_points_1">1 card = 1 point</div><div id="fruit_carambola_points_2">2 cards = 3 points</div><div id="fruit_carambola_points_3">3 cards = 6 points</div><div id="fruit_carambola_points_4">4 cards = 10 points</div><div id="fruit_carambola_points_5">5 cards = 15 points</div>')
  ),
  COCONUT => array(
    'name' => clienttranslate('coconut'),
    'nametr' => self::_('coconut'),
    'namecss' => 'coconut',
    'pluralname' => clienttranslate('coconuts'),
    'pluralnametr' => self::_('coconuts'),
    'scoretype' => SET,
    'points' => array(
      1 => 8,
      2 => 6,
      3 => 4,
      4 => 2,
      5 => 0,
      6 => -2
    ),
    'tooltip' => clienttranslate('<div id="fruit_coconut_points_1">1 card = 8 points</div><div id="fruit_coconut_points_2">2 cards = 6 points</div><div id="fruit_coconut_points_3">3 cards = 4 points</div><div id="fruit_coconut_points_4">4 cards = 2 points</div><div id="fruit_coconut_points_5">5 cards = 0 points</div><div id="fruit_coconut_points_6">6 cards = -2 points</div>')
  ),
  FIG => array(
    'name' => clienttranslate('fig'),
    'nametr' => self::_('fig'),
    'namecss' => 'fig',
    'pluralname' => clienttranslate('figs'),
    'pluralnametr' => self::_('figs'),
    'scoretype' => SET,
    'points' => array(
      1 => -2,
      2 => 0,
      3 => 9,
      4 => 16
    ),
    'tooltip' => clienttranslate('<div id="fruit_fig_points_1">1 card = -2 points</div><div id="fruit_fig_points_2">2 cards = 0 points</div><div id="fruit_fig_points_3">3 cards = 9 points</div><div id="fruit_fig_points_4">4 cards = 16 points</div>')
  ),
  LIME => array(
    'name' => clienttranslate('lime'),
    'nametr' => self::_('lime'),
    'namecss' => 'lime',
    'pluralname' => clienttranslate('limes'),
    'pluralnametr' => self::_('limes'),
    'scoretype' => SET,
    'points' => array(
      1 => -2,
      2 => -8
    ),
    'tooltip' => clienttranslate('<div id="fruit_lime_points_1">1 card = -2 points</div><div id="fruit_lime_points_2">2 cards = -8 points</div>')
  ),
  LYCHEE => array(
    'name' => clienttranslate('lychee'),
    'nametr' => self::_('lychee'),
    'namecss' => 'lychee',
    'pluralname' => clienttranslate('lychees'),
    'pluralnametr' => self::_('lychees'),
    'scoretype' => SET,
    'points' => array(
      1 => 5,
      2 => 12
    ),
    'tooltip' => clienttranslate('<div id="fruit_lychee_points_1">1 card = 5 points</div><div id="fruit_lychee_points_2">2 cards = 12 points</div>')
  ),
  ORANGE => array(
    'name' => clienttranslate('orange'),
    'nametr' => self::_('orange'),
    'namecss' => 'orange',
    'pluralname' => clienttranslate('oranges'),
    'pluralnametr' => self::_('oranges'),
    'scoretype' => SET,
    'points' => array(
      1 => 4,
      2 => 8,
      3 => 12,
      4 => 0
    ),
    'tooltip' => clienttranslate('<div id="fruit_orange_points_1">1 card = 4 points</div><div id="fruit_orange_points_2">2 cards = 8 points</div><div id="fruit_orange_points_3">3 cards = 12 points</div><div id="fruit_orange_points_4">4 cards = 0 points</div>')
  ),
  PAPAYA => array(
    'name' => clienttranslate('papaya'),
    'nametr' => self::_('papaya'),
    'namecss' => 'papaya',
    'pluralname' => clienttranslate('papayas'),
    'pluralnametr' => self::_('papayas'),
    'scoretype' => SET,
    'points' => array(
      1 => 1,
      2 => 1,
      3 => 9,
      4 => 20
    ),
    'tooltip' => clienttranslate('<div id="fruit_papaya_points_1">1 card = 1 point</div><div id="fruit_papaya_points_2">2 cards = 1 point</div><div id="fruit_papaya_points_3">3 cards = 9 points</div><div id="fruit_papaya_points_4">4 cards = 20 points</div>')
  ),
  PINEAPPLE => array(
    'name' => clienttranslate('pineapple'),
    'nametr' => self::_('pineapple'),
    'namecss' => 'pineapple',
    'pluralname' => clienttranslate('pineapples'),
    'pluralnametr' => self::_('pineapples'),
    'scoretype' => SET,
    'points' => array(
      1 => 0,
      2 => -2,
      3 => -4
    ),
    'tooltip' => clienttranslate('<div id="fruit_pineapple_points_1">1 card = 0 points</div><div id="fruit_pineapple_points_2">2 cards = -2 points</div><div id="fruit_pineapple_points_3">3 cards = -4 points</div>')
  ),
  POMEGRANATE => array(
    'name' => clienttranslate('pomegranate'),
    'nametr' => self::_('pomegranate'),
    'namecss' => 'pomegranate',
    'pluralname' => clienttranslate('pomegranates'),
    'pluralnametr' => self::_('pomegranates'),
    'scoretype' => RANK,
    'points' => array(
      0 => -1,
      1 => 1,
    ),
    'tooltip' => clienttranslate('If you have more pomegranates than any other player you score 1 point per collected pomegranate. If not you score -1 point per pomegranate. If there is a tie for the most all players score -1 point per pomegranate.')
  ),
  RAMBUTAN => array(
    'name' => clienttranslate('rambutan'),
    'nametr' => self::_('rambutan'),
    'namecss' => 'rambutan',
    'pluralname' => clienttranslate('rambutans'),
    'pluralnametr' => self::_('rambutans'),
    'scoretype' => SET,
    'points' => array(
      1 => 3,
      2 => 6,
      3 => 9,
      4 => 12,
      5 => 15
    ),
    'tooltip' => clienttranslate('<div id="fruit_rambutan_points_1">1 card = 3 points</div><div id="fruit_rambutan_points_2">2 cards = 6 points</div><div id="fruit_rambutan_points_3">3 cards = 9 points</div><div id="fruit_rambutan_points_4">4 cards = 12 points</div><div id="fruit_rambutan_points_5">5 cards = 15 points</div>')
  ),
  JOKER => array(
    'name' => clienttranslate('joker'),
    'nametr' => self::_('joker'),
    'namecss' => 'joker'
  )
);


// Toucan Detail
$this->toucan = array(
  FLIP => array(
    'name' => clienttranslate('flip'),
    'nametr' => self::_('flip'),
    'namecss' => 'flip',
    'pluralname' => clienttranslate('flips'),
    'pluralnametr' => self::_('flips'),
    'effect' => clienttranslate('You must flip all of the fruit cards in your collection into a single face down pile. Once cards have been flipped face down no player can look at them (including yourself) and they cannot be gifted to or stolen by another player.')
  ),
  GIFT => array(
    'name' => clienttranslate('gift'),
    'nametr' => self::_('gift'),
    'namecss' => 'gift',
    'pluralname' => clienttranslate('gifts'),
    'pluralnametr' => self::_('gifts'),
    'effect' => clienttranslate('You must give 1 of your face-up cards to another player who cannot refuse it. If you have no face-up cards this has no effect.')
  ),
  STEAL => array(
    'name' => clienttranslate('steal'),
    'nametr' => self::_('steal'),
    'namecss' => 'steal',
    'pluralname' => clienttranslate('steals'),
    'pluralnametr' => self::_('steals'),
    'effect' => clienttranslate('You must take a face-up card of your choice from another player who cannot refuse to give it to you. If there are no cards to steal this has no effect.')
  )
);

// Column Detail
$this->column = array(
  A => array(
    'name' => clienttranslate('A'),
    'nametr' => self::_('A'),
    'namecss' => 'A'
  ),
  B => array(
    'name' => clienttranslate('B'),
    'nametr' => self::_('B'),
    'namecss' => 'B'
  ),
  C => array(
    'name' => clienttranslate('C'),
    'nametr' => self::_('C'),
    'namecss' => 'C'
  )
);