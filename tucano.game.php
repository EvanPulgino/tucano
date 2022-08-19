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
  * tucano.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to define the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class Tucano extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
            "active_column" => 10,
            "gifted_fruit" => 11
        ) );

        // Use deck module, link to card table
        $this->cards = self::getNew('module.common.deck');
        $this->cards->init('card');    
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "tucano";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue( 'active_column', -1 );
        self::setGameStateInitialValue( 'gifted_fruit', -1);
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        self::initStat( 'player', 'turns_number', 0);
        self::initStat( 'player', 'fruit_cards_taken', 0);
        self::initStat( 'player', 'fruit_cards_per_turn', 0.0);
        self::initStat( 'player', 'acai_points', 0);
        self::initStat( 'player', 'avocado_points', 0);
        self::initStat( 'player', 'banana_points', 0);
        self::initStat( 'player', 'carambola_points', 0);
        self::initStat( 'player', 'coconut_points', 0);
        self::initStat( 'player', 'fig_points', 0);
        self::initStat( 'player', 'lime_points', 0);
        self::initStat( 'player', 'lychee_points', 0);
        self::initStat( 'player', 'orange_points', 0);
        self::initStat( 'player', 'papaya_points', 0);
        self::initStat( 'player', 'pineapple_points', 0);
        self::initStat( 'player', 'pomegranate_points', 0);
        self::initStat( 'player', 'rambutan_points', 0);
        self::initStat( 'player', 'flip_toucans_used', 0);
        self::initStat( 'player', 'gift_toucans_used', 0);
        self::initStat( 'player', 'steal_toucans_used', 0);
        self::initStat( 'player', 'total_toucans_used', 0);

        // Create cards
        $fruit_cards = array();
        $toucan_cards = array();
        foreach ( $this->card as $card_id => $card ) {
            if ( $this->card[$card_id]['cardtype'] == FRUIT ) {
                $fruit_cards[] = array('type' => $card_id, 'type_arg' => 0, 'nbr' => $this->card[$card_id]['count']);
            } else {
                $toucan_cards[] = array('type' => $card_id, 'type_arg' => 0, 'nbr' => $this->card[$card_id]['count']);
            }
        }

        // Create seperate piles of fruit and toucan cards
        $this->cards->createCards($fruit_cards, 'fruit');
        $this->cards->createCards($toucan_cards, 'toucan');

        // Shuffle fruit cards
        $this->cards->shuffle('fruit');

        // Place half of fruit cards and all toucans as bottom of deck
        $this->cards->pickCardsForLocation(29, 'fruit', 'deck');
        $this->cards->pickCardsForLocation(12, 'toucan', 'deck');

        // Shuffle bottom of deck
        $this->cards->shuffle('deck');

        // Move remaining fruit cards to top of deck
        foreach ( $this->cards->getCardsInLocation('fruit') as $card_id => $card ) {
            $this->cards->insertCardOnExtremePosition($card_id, 'deck', true);
        }

        // Deal initial cards to the display
        $this->cards->pickCardForLocation('deck', 'column', A);
        $this->cards->pickCardForLocation('deck', 'column', B);
        $this->cards->pickCardForLocation('deck', 'column', C);
        $this->cards->pickCardForLocation('deck', 'column', B);

        // Activate first player
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );

        // Get content from material
        $result['card'] = $this->card;
        $result['constant'] = $this->constant;
        $result['fruit'] = $this->fruit;
        $result['toucan'] = $this->toucan;
        $result['column_info'] = $this->column;

        // Get cards in current location
        $result['deck_count'] = $this->cards->countCardInLocation('deck');
        $result['column'] = $this->cards->getCardsInLocation('column');

        // Get cards in player tableaus
        foreach ( $this->card as $card_id => $card ) {
            $result[$card_id] = $this->cards->getCardsInLocation($card_id);
        }

        // Get facedown player cards
        $result['facedown'] = $this->cards->getCardsInLocation('99');

        foreach ( self::getAllPlayerIds() as $player_id => $player ) {
            for ( $x = ACAI_BERRY; $x <= RAMBUTAN; $x++ ) {
                $score_stat_name = $this->fruit[$x]['namecss'].'_points';
                $result[$player_id][$x] = self::getStat($score_stat_name, $player_id);
            }
        }

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $deck_count = $this->cards->countCardInLocation('deck');
        $cards_drawn = 66 - $deck_count;

        $empty_columns = 0;

        for ($i = 0; $i < 3; $i++) {
            if ($this->cards->countCardInLocation('column', $i) == 0) {
                $empty_columns++;
            }
        }

        return ($cards_drawn + $empty_columns * 3) / 72 * 100;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
//////////// 

    function getAllPlayerIds()
    {
        $sql = "SELECT player_id id FROM player";
        return self::getCollectionFromDb( $sql );
    }

    function getAllPlayerInfo()
    {
        return self::getCollectionFromDb("SELECT player_no, player_id, player_name, player_color FROM player");
    }

    function getAllPlayerIdsAsCommaDelimitedString()
    {
        $playerIdString = "";
        $players = self::getAllPlayerIds();

        foreach ($players as $player_id => $player) {
            if (strlen($playerIdString > 0)) {
                $playerIdString .= ",";
            }

            $playerIdString .= $player_id;
        }

        return $playerIdString;
    }

    function getFruitInfo() {
        return $this->fruit;
    }

    function getNonActivePlayers()
    {
        $sql = "SELECT player_id, player_name, player_color FROM player WHERE player_id != ".self::getActivePlayerId();
        return self::getCollectionFromDb( $sql );
    }

    function getNonActivePlayerIds()
    {
        $sql = "SELECT player_id id FROM player WHERE player_id != ".self::getActivePlayerId();
        return self::getCollectionFromDb( $sql );
    }

    function getNonActivePlayerIdsAsCommaDelimitedString()
    {
        $playerIdString = "";
        $players = self::getNonActivePlayers();

        foreach ($players as $player_id => $player) {
            if (strlen($playerIdString > 0)) {
                $playerIdString .= ",";
            }

            $playerIdString .= $player_id;
        }

        return $playerIdString;
    }

    function getPlayerIdOfJokerHolder()
    {
        $players = self::getAllPlayerIdsAsCommaDelimitedString();
        $sql = "SELECT card_location_arg FROM card WHERE card_type = 13 AND card_location_arg IN (".$players.")";
        $result = current(self::getCollectionFromDb($sql));

        if ($result != null) {
            return $result['card_location_arg'];
        }

        return null;
    }

    function getPlayerInfoInViewOrder()
    {
        $players = self::getAllPlayerInfo();
        $player_count = count( $players );
        $viewing_player_id = self::getCurrentPlayerId();

        if (!in_array($viewing_player_id, array_keys(self::getAllPlayerIds()))) {
            return $players;
        } else {
            $sorted_player_info = array();
            $players_added = 0;
            $last_added_player_no = 0;

            while ($players_added < $player_count) {
                
                foreach ($players as $player_no => $player) {
                    if ($viewing_player_id == $player['player_id'] && $players_added == 0) {
                        $sorted_player_info[] = $player;
                        $last_added_player_no = $player_no;
                        $players_added++;
                    } elseif ($player['player_no'] > $last_added_player_no 
                            && $players_added > 0 
                            && $players_added < $player_count
                            && $viewing_player_id != $player['player_id']
                        ) {
                        $sorted_player_info[] = $player;
                        $last_added_player_no = $player_no;
                        $players_added++;
                    }

                    if ($last_added_player_no == $player_count && $players_added != 0) {
                        $last_added_player_no = 0;
                    }
                }
            }

            return $sorted_player_info;
        }
    }

    function moveFruitToPlayerCollection( $player_id, $card_id, $fruit_type ) 
    {
        $this->cards->moveCard($card_id, $fruit_type, $player_id);
    }

    function playerPointsUpdate ( $player_id, $points )
    {
        self::DbQuery( "UPDATE player SET player_score=player_score+".$points." WHERE player_id='".$player_id."'" );
    }

    function revealAllFacedownCards()
    {
        $players = self::getAllPlayerIds();

        foreach ($players as $player_id => $player) {

            for ($x = ACAI_BERRY; $x <= JOKER; $x++) {
                $facedownCards = $this->cards->getCardsOfTypeInLocation($x, 0, '99', $player_id);

                foreach($facedownCards as $card_id => $card) {
                    $this->cards->moveCard($card_id, $x, $player_id);
                }
            }
        }
    }

    function scoreFruit($fruit_id)
    {
        $fruit_info = $this->fruit[$fruit_id];
        $score_type = $fruit_info['scoretype'];

        if ($score_type == RANK) {
            self::scoreRankFruit($fruit_id);
        }

        if ($score_type == SET) {
            self::scoreSetFruit($fruit_id);
        }
    }

    function scoreRankFruit($fruit_id)
    {
        $fruit_info = $this->fruit[$fruit_id];
        $fruit_name = $fruit_info['pluralname'];
        $fruit_points = $fruit_info['points'];
        $players = self::getAllPlayerIds();

        $most_fruit = 0;
        $player_with_most_fruit = 0;

        foreach ($players as $player_id => $player) {
            $fruit_count = $this->cards->countCardInLocation($fruit_id, $player_id);

            if ($fruit_count > $most_fruit) {
                $most_fruit = $fruit_count;
                $player_with_most_fruit = $player_id;
            } else if ($fruit_count == $most_fruit) {
                $player_with_most_fruit = 0;
            }
        }

        foreach ($players as $player_id => $player) {
            $points_scored = 0;
            $fruit_count = $this->cards->countCardInLocation($fruit_id, $player_id);

            if ($player_id == $player_with_most_fruit) {
                $points_scored = $fruit_points[1] * $fruit_count;
            } else {
                $points_scored = $fruit_points[0] * $fruit_count;
            }

            if ($fruit_count == 1) {
                $fruit_name = $fruit_info['name'];
            } 

            if ($fruit_count > 0) {
                self::playerPointsUpdate($player_id, $points_scored);

                self::setStat( $points_scored, $fruit_info['namecss'].'_points', $player_id );

                self::notifyAllPlayers(
                    'scorePoints',
                    clienttranslate('${player_name} scores ${player_points} points from ${player_fruit} ${fruit_name}'),
                    array(
                        'player_name' => self::getPlayerNameById($player_id),
                        'player_points' => $points_scored,
                        'player_fruit' => $fruit_count,
                        'fruit_name' => ucwords($fruit_name),
                        'player_id' => $player_id,
                        'fruit_id' => $fruit_id
                    )
                );
            }
        }
    }

    function scoreSetFruit($fruit_id)
    {
        $fruit_info = $this->fruit[$fruit_id];
        $fruit_name = $fruit_info['pluralname'];
        $fruit_points = $fruit_info['points'];
        $players = self::getAllPlayerIds();

        foreach ($players as $player_id => $player) {
            $fruit_count = $this->cards->countCardInLocation($fruit_id, $player_id);

            if ($fruit_count > 0) {
                $points_scored = $fruit_points[$fruit_count];

                if ($fruit_count == 1) {
                    $fruit_name = $fruit_info['name'];
                }

                self::playerPointsUpdate($player_id, $points_scored);

                self::setStat( $points_scored, $fruit_info['namecss'].'_points', $player_id );

                self::notifyAllPlayers(
                    'scorePoints',
                    clienttranslate('${player_name} scores ${player_points} points from ${player_fruit} ${fruit_name}'),
                    array(
                        'player_name' => self::getPlayerNameById($player_id),
                        'player_points' => $points_scored,
                        'player_fruit' => $fruit_count,
                        'fruit_name' => ucwords($fruit_name),
                        'player_id' => $player_id,
                        'fruit_id' => $fruit_id
                    )
                );
            }
        }
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in tucano.action.php)
    */

    function assignJoker( $fruit )
    {
        self::checkAction("assignJoker");

        $fruit_info = $this->fruit[$fruit];

        // Move joker to set of selected fruit
        $jokerCard = current($this->cards->getCardsOfType(JOKER));
        $this->cards->moveCard($jokerCard['id'], $fruit, self::getActivePlayerId());

        self::notifyAllPlayers(
            'assignJoker',
            clienttranslate('${player_name} adds Joker to ${fruit_name} set' ),
            array(
                'player_name' => self::getActivePlayerName(),
                'fruit' => $fruit,
                'fruit_name' => ucwords($fruit_info['name'])
            )
        );

        $this->gamestate->nextState('endGame');

    }

    function chooseGift( $gift )
    {
        self::checkAction("chooseGift");

        self::setGameStateValue('gifted_fruit', $gift);

        $gift_info = $this->fruit[$gift];

        self::notifyAllPlayers(
            'chooseGift',
            clienttranslate('${player_name} chooses to gift ${fruit_name}'),
            array(
                'player_name' => self::getActivePlayerName(),
                'fruit_type' => $gift,
                'fruit_name' => ucwords($gift_info['name']),
            )
        );

        // If only 2 players automatically give gift to other player
        $player_ids = self::getAllPlayerIds();
        if (count($player_ids) == 2) {
            $other_player_id = '';
            foreach ($player_ids as $player_id => $player) {
                if ($player_id != self::getActivePlayerId()) {
                    $other_player_id = $player_id;
                }
            }

            $fruitToMove = current($this->cards->getCardsOfTypeInLocation($gift, 0, $gift, self::getActivePlayerId()));
            self::moveFruitToPlayerCollection($other_player_id, $fruitToMove['id'], $fruitToMove['type']);

            self::notifyAllPlayers(
                'resolveGift',
                clienttranslate('${giver_name} gifts ${fruit_name} to ${receiver_name}'),
                array(
                    'giver_name' => self::getActivePlayerName(),
                    'fruit_name' => ucwords($this->fruit[$gift]['name']),
                    'receiver_name' => self::getPlayerNameById($other_player_id),
                    'receiver_player_id' => $other_player_id,
                    'gift_fruit' => $gift
                )
            );

            // Reset gifted fruit variable
            self::setGameStateValue('gifted_fruit', -1);

            // Check column for remaining toucans
            $active_column = self::getGameStateValue('active_column');
            $cards_in_active_column = $this->cards->countCardInLocation('column', $active_column);

            // If toucans remainging resolve them. Else end turn.
            if ($cards_in_active_column > 0) {
                $this->gamestate->nextState('resolveToucans');
            } else {
                $this->gamestate->nextState('endTurn');
            }
        } else {
            $this->gamestate->nextState('resolveGift');
        }
    }

    function resolveGift( $player_to_gift )
    {
        self::checkAction("resolveGift");
        $active_player = self::getActivePlayerId();
        $gift_fruit = self::getGameStateValue('gifted_fruit');

        // Get first found fruit of gift type in active players collection
        $fruitToMove = current($this->cards->getCardsOfTypeInLocation($gift_fruit, 0, $gift_fruit, $active_player));
        // Move that fruit to chose player
        self::moveFruitToPlayerCollection($player_to_gift, $fruitToMove['id'], $fruitToMove['type']);

        self::notifyAllPlayers(
            'resolveGift',
            clienttranslate('${giver_name} gifts ${fruit_name} to ${receiver_name}'),
            array(
                'giver_name' => self::getActivePlayerName(),
                'fruit_name' => ucwords($this->fruit[$gift_fruit]['name']),
                'receiver_name' => self::getPlayerNameById($player_to_gift),
                'receiver_player_id' => $player_to_gift,
                'gift_fruit' => $gift_fruit
            )
        );

        // Reset gifted fruit variable
        self::setGameStateValue('gifted_fruit', -1);

        // Check column for remaining toucans
        $active_column = self::getGameStateValue('active_column');
        $cards_in_active_column = $this->cards->countCardInLocation('column', $active_column);

        // If toucans remainging resolve them. Else end turn.
        if ($cards_in_active_column > 0) {
            $this->gamestate->nextState('resolveToucans');
        } else {
            $this->gamestate->nextState('endTurn');
        }
    }

    function resolveSteal( $fruit, $target )
    {
        self::checkAction("resolveSteal");
        $active_player_id = self::getActivePlayerId();

        // Get first found fruit of gift type in target players collection
        $fruitToMove = current($this->cards->getCardsOfTypeInLocation($fruit, 0, $fruit, $target));
        self::moveFruitToPlayerCollection($active_player_id, $fruitToMove['id'], $fruitToMove['type']); 

        self::notifyAllPlayers(
            'resolveSteal',
            clienttranslate('${active_player} steals ${fruit_name} from ${target_name}'),
            array(
                'active_player' => self::getActivePlayerName(),
                'fruit_name' => ucwords($this->fruit[$fruit]['name']),
                'target_name' => self::getPlayerNameById($target),
                'target_player_id' => $target,
                'fruit' => $fruit
            )
        );

        // Check column for remaining toucans
        $active_column = self::getGameStateValue('active_column');
        $cards_in_active_column = $this->cards->countCardInLocation('column', $active_column);

        // If toucans remainging resolve them. Else end turn.
        if ($cards_in_active_column > 0) {
            $this->gamestate->nextState('resolveToucans');
        } else {
            $this->gamestate->nextState('endTurn');
        }
    }

    function selectFlip()
    {
        self::checkAction("selectFlip");

        self::incStat( 1, 'flip_toucans_used', self::getActivePlayerId() );
        self::incStat( 1, 'total_toucans_used', self::getActivePlayerId() );

        // Discard a flip toucan from active column
        $active_column = self::getGameStateValue('active_column');
        $flip_toucan = current($this->cards->getCardsOfTypeInLocation(FLIP, 0, 'column', $active_column));
        $this->cards->playCard($flip_toucan['id']);

        $active_player = self::getActivePlayerId();

        // Move all face up cards to face down pile
        for ($x = ACAI_BERRY; $x <= JOKER; $x++) {
            $this->cards->moveAllCardsInLocation($x, 99, $active_player, $active_player);
        }

        self::notifyAllPlayers(
            'selectFlip',
            clienttranslate('${player_name} chooses to resolve a Flip Toucan'),
            array(
                'player_name' => self::getActivePlayerName(),
                'active_column' => $active_column,
                'toucan_to_discard' => $flip_toucan['id']
            )
        );

        // Check column for remaining toucans
        $active_column = self::getGameStateValue('active_column');
        $cards_in_active_column = $this->cards->countCardInLocation('column', $active_column);

        if ($cards_in_active_column > 0) {
            $this->gamestate->nextState('resolveToucans');
        } else {
            $this->gamestate->nextState('endTurn');
        }

    }

    function selectGift()
    {
        self::checkAction("selectGift");

        self::incStat( 1, 'gift_toucans_used', self::getActivePlayerId() );
        self::incStat( 1, 'total_toucans_used', self::getActivePlayerId() );

        // Discard a gift toucan from active column
        $active_column = self::getGameStateValue('active_column');
        $gift_toucan = current($this->cards->getCardsOfTypeInLocation(GIFT, 0, 'column', $active_column));
        $this->cards->playCard($gift_toucan['id']);

        // Get count of cards that can be gifted
        $sql = "SELECT count(card_id) cardcount
            FROM card 
            WHERE card_location != 99 
                AND card_location_arg = ".self::getActivePlayerId();
        $eligible_cards = current(self::getCollectionFromDb( $sql ))['cardcount'];

        // If there are no cards to gift do nothing and move on
        if ($eligible_cards == 0) {
            self::notifyAllPlayers(
                'selectGift',
                clienttranslate('No eligible cards to gift.'),
                array(
                    'active_column' => $active_column,
                    'toucan_to_discard' => $gift_toucan['id']
                )
            );

            $cards_in_active_column = $this->cards->countCardInLocation('column', $active_column);

            if ($cards_in_active_column > 0) {
                $this->gamestate->nextState('resolveToucans');
            } else {
                $this->gamestate->nextState('endTurn');
            }
        } else {
            self::notifyAllPlayers(
                'selectGift',
                clienttranslate('${player_name} chooses to resolve a Gift Toucan'),
                array(
                    'player_name' => self::getActivePlayerName(),
                    'active_column' => $active_column,
                    'toucan_to_discard' => $gift_toucan['id']
                )
            );

            $this->gamestate->nextState('chooseGift');
        }
    }

    function selectColumn( $selected_column ) 
    {
        self::checkAction("selectColumn");

        // Set active column
        self::setGameStateValue('active_column', $selected_column);

        // Set toucan count to 0
        $toucan_count = 0;

        // Get player and selected column info
        $player_id = self::getActivePlayerId();
        $column_cards = $this->cards->getCardsInLocation('column', $selected_column);

        // Move all fruit cards in column to player tableau
        foreach( $column_cards as $card_id => $card ) {
            $card_type = $column_cards[$card_id]['type'];
            $card_info = $this->card[$card_type];

            if ( $card_info['cardtype'] == TOUCAN ) {
                $toucan_count++;
            } else {
                self::moveFruitToPlayerCollection($player_id, $card_id, $card_info['subtype']);
                self::incStat( 1, 'fruit_cards_taken', self::getActivePlayerId() );
            }
        }

        // Notify players
        self::notifyAllPlayers(
            'selectColumn',
            clienttranslate('${player_name} selects the cards from Column ${column_name}'),
            array(
                'i18n' => array('column_name'),
                'player_name' => self::getActivePlayerName(),
                'column' => $selected_column,
                'column_cards' => $column_cards,
                'column_name' => $this->column[$selected_column]['name']
            )
        );

        // Move to next state
        if( $toucan_count > 0 ) {
            $this->gamestate->nextState('resolveToucans');
        } else {
            $this->gamestate->nextState('endTurn');
        }
    }

    function selectSteal()
    {
        self::checkAction("selectSteal");

        self::incStat( 1, 'steal_toucans_used', self::getActivePlayerId() );
        self::incStat( 1, 'total_toucans_used', self::getActivePlayerId() );

        // Discard a steal toucan from active column
        $active_column = self::getGameStateValue('active_column');
        $steal_toucan = current($this->cards->getCardsOfTypeInLocation(STEAL, 0, 'column', $active_column));
        $this->cards->playCard($steal_toucan['id']);

        // Get count of cards that can be stolen
        $sql = "SELECT count(card_id) cardcount
            FROM card 
            WHERE card_location != 99 
                AND card_location_arg IN (".self::getNonActivePlayerIdsAsCommaDelimitedString().")";
        $eligible_cards = current(self::getCollectionFromDb( $sql ))['cardcount'];

        // If there are no cards to steal, do nothing and move on
        if ($eligible_cards == 0) {
            self::notifyAllPlayers(
                'selectSteal',
                clienttranslate('No eligible cards to steal.'),
                array(
                    'active_column' => $active_column,
                    'toucan_to_discard' => $steal_toucan['id']
                )
            );

            $cards_in_active_column = $this->cards->countCardInLocation('column', $active_column);

            if ($cards_in_active_column > 0) {
                $this->gamestate->nextState('resolveToucans');
            } else {
                $this->gamestate->nextState('endTurn');
            }
        } else {
            self::notifyAllPlayers(
                'selectSteal',
                clienttranslate('${player_name} chooses to resolve a Steal Toucan'),
                array(
                    'player_name' => self::getActivePlayerName(),
                    'active_column' => $active_column,
                    'toucan_to_discard' => $steal_toucan['id']
                )
            );

            $this->gamestate->nextState('resolveSteal');
        }
    }
    
    

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argsResolveGift()
    {
        $fruit_name = ucwords($this->fruit[self::getGameStateValue('gifted_fruit')]['name']);
        return array (
            'gifted_fruit' => self::getGameStateValue( 'gifted_fruit' ),
            'card' => $fruit_name,
            'other_players' => self::getNonActivePlayers()
        );
    }

    function argsResolveSteal()
    {
        return array (
            'non_active_players' => self::getNonActivePlayerIds()
        );
    }

    function argsResolveToucans()
    {
        return array ( 'active_column' => self::getGameStateValue( 'active_column' ) );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    // Perform final scoring
    function stGameFinalScoring() {

        $players = self::getAllPlayerIds();

        foreach ($players as $player_id => $player) {
            $turnsTaken = self::getStat('turns_number', $player_id);
            $fruitTaken = self::getStat('fruit_cards_taken', $player_id);
            self::setStat( $fruitTaken / $turnsTaken, 'fruit_cards_per_turn', $player_id );
        }

        for ($x = ACAI_BERRY; $x <= RAMBUTAN; $x++) {
            self::scoreFruit($x);
        }

        $this->gamestate->nextState("endGame");
    }
    
    // Setup turn for next player
    function stGameNextPlayer() {

        self::incStat( 1, 'turns_number', self::getActivePlayerId() );

        self::setGameStateValue('active_column', -1);

        // If there are cards in the deck then deal new cards to display
        if ($this->cards->countCardInLocation('deck') > 0) {
            $column_a_card = $this->cards->pickCardForLocation('deck', 'column', A);
            $column_b_card = $this->cards->pickCardForLocation('deck', 'column', B);
            $column_c_card = $this->cards->pickCardForLocation('deck', 'column', C);

            self::notifyAllPlayers(
                'newCards',
                clienttranslate('One new card added to each column'),
                array(
                    'column_a_card' => $column_a_card,
                    'column_b_card' => $column_b_card,
                    'column_c_card' => $column_c_card
                )
            );
        }

        // Determine how many columns are empty
        $empty_columns = 0;

        for ($i = A; $i <= C; $i++) {
            if ($this->cards->countCardInLocation('column', $i) == 0) {
                $empty_columns++;
            }
        }

        // If two columns are empty proceed to final scoring, else activate next player
        if ($empty_columns == 2) {
            
            $players = self::getAllPlayerIdsAsCommaDelimitedString();

            $sql = "SELECT card_id id, card_type, card_location_arg location_arg 
                FROM card 
                WHERE card_location = '99' AND card_location_arg IN (".$players.")";
            $cardsCollected = self::getCollectionFromDb($sql);

            self::revealAllFacedownCards();

            self::notifyAllPlayers(
                'revealFacedownCards',
                clienttranslate('End of game reached. All face down cards revealed'),
                array(
                    'cardsCollected' => $cardsCollected
                )
            );
            
            // Find player with Joker
            $playerWithJoker = self::getPlayerIdOfJokerHolder();

            if ($playerWithJoker == null) {
                $this->gamestate->nextState('endGame');
            } else {
                // Make player with joker active and move to assignment state
                $this->gamestate->changeActivePlayer($playerWithJoker);
                self::giveExtraTime($playerWithJoker);
                $this->gamestate->nextState('assignJoker');
            }
        } else {
            $player_id = self::activeNextPlayer();
            self::giveExtraTime($player_id);
            $this->gamestate->nextState('nextPlayer');
        }
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
