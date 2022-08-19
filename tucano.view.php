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
 * tucano.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in Tucano_Tucano.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_tucano_tucano extends game_view
  {
    function getGameName() {
        return "tucano";
    }    
  	function build_page( $viewArgs )
  	{
        // Template name
        $template = self::getGameName().'_'.self::getGameName();

  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        // Get players in display order
        $players_in_order = $this->game->getPlayerInfoInViewOrder();

        $fruit_info = $this->game->getFruitInfo();

        // Translate text on page
        $this->tpl['COLUMN_A'] = self::_("column a");
        $this->tpl['COLUMN_B'] = self::_("column b");
        $this->tpl['COLUMN_C'] = self::_("column c");
        $this->tpl['FRUIT'] = self::_("fruit");
        $this->tpl['COLLECTION'] = self::_("collection");
        $this->tpl['SET_TOOLTIP_TEXT'] = self::_("Score the indicated points based on your amount of collected");
        $this->tpl['JOKER_TOOLTIP_TEXT'] = self::_("At the end of game assign the Joker card to a set of fruit. It will count as one of those fruits. You can not assign the Joker to a set in which you have collected every card.");

        // Inflate player tableau blocks
        $this->page->begin_block($template, 'playercard');
        $this->page->begin_block($template, 'playertableau');
        foreach ($players_in_order as $player_no => $player) {
            $this->page->reset_subblocks('playercard');

            for ($i = 0; $i <= 13; $i++ ) {
                $fruit = $fruit_info[$i];
                $this->page->insert_block(
                    'playercard',
                    array(
                        'PLAYER_ID' => $player['player_id'],
                        'FRUIT_ID' => $i,
                        'FRUIT_NAME' => $fruit['namecss']
                    )
                );
            }

            $this->page->insert_block(
                'playertableau',
                array(
                    'PLAYER_ID' => $player['player_id'],
                    'PLAYER_NAME' => $player['player_name'],
                    'PLAYER_COLOR' => $player['player_color']
                )
            );
        }

        // Inflate score table fruit columns
        $this->page->begin_block($template, 'scorefruit');
        for ($i = 0; $i <= 12; $i++) {
            $fruit = $fruit_info[$i];
            $this->page->insert_block(
                'scorefruit',
                array(
                    'FRUIT_ID' => $i,
                    'FRUIT_NAME' => $fruit['namecss']
                )
            );
        }

        // Inflate player score table blocks
        $this->page->begin_block($template, 'playerscorefruit');
        $this->page->begin_block($template, 'playerscore');
        foreach ($players as $player_id => $player) {
            $this->page->reset_subblocks('playerscorefruit');

            for ($i = 0; $i <= 12; $i++) {
                $fruit = $fruit_info[$i];
                $this->page->insert_block(
                    'playerscorefruit',
                    array(
                        'PLAYER_ID' => $player_id,
                        'FRUIT_ID' => $i,
                        'FRUIT_NAME' => $fruit['namecss']
                    )
                );
            }

            $this->page->insert_block(
                'playerscore',
                array(
                    'PLAYER_ID' => $player_id,
                    'PLAYER_NAME' => $player['player_name'],
                    'PLAYER_COLOR' => $player['player_color']
                )
            );
        }

        /*********** Do not change anything below this line  ************/
  	}
  }
  

