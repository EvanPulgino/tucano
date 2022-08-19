/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Tucano implementation : © Evan Pulgino <evan.pulgino@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * tucano.js
 *
 * Tucano user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare", "dojo/query",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.tucano", ebg.core.gamegui, {
        constructor: function(){
            console.log('tucano constructor');

            // Content from material
            this.card = null;
            this.constant = null;
            this.fruit = null;
            this.toucan = null;
            this.column = null;

            // Current Active Column
            this.active_column = -1;

            this.cardWidth = 144;
            this.cardHeight = 271.33;
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );

            // Get content from material
            this.card = gamedatas.card;
            this.constant = gamedatas.constant;
            this.fruit = gamedatas.fruit;
            this.toucan = gamedatas.toucan;
            this.column = gamedatas.column_info;

            // Setup deck counter
            this.deckCounter = new ebg.counter();
            this.deckCounter.create('deck-counter');
            this.deckCounter.setValue(gamedatas.deck_count);

            // Setup stocks for the three selection columns
            this.selectionColumn = {};
            for (var i = this.constant.A; i <= this.constant.C; i++) 
            {
                this.selectionColumn[i] = new ebg.stock();
                this.selectionColumn[i].create(this, $('column_' + i), this.cardWidth, this.cardHeight);
                this.selectionColumn[i].image_items_per_row = 6;
                this.selectionColumn[i].extraClasses = 'tuc_custom_card';
                this.selectionColumn[i].centerItems = true;
                this.selectionColumn[i].use_vertical_overlap_as_offset = false;
                this.selectionColumn[i].onItemCreate = dojo.hitch( this, 'setupNewCard' );
                this.selectionColumn[i].vertical_overlap = 75;

                // Create card types
                for (var card_id in this.card) {
                    this.selectionColumn[i].addItemType(card_id, 0, g_gamethemeurl + 'img/cards.png', card_id);
                }

                dojo.connect(this.selectionColumn[i], 'onChangeSelection', this, 'onColumnCardSelectionChanged');
            }

            // Display current cards in selection columns
            for (var card_id in gamedatas.column) 
            {
                this.selectionColumn[gamedatas.column[card_id].location_arg].addToStockWithId(gamedatas.column[card_id].type, card_id);
            }

            // Set optimal column height based on number of cards
            this.setOptimalColumnHeight();
            
            // Setup player specific elements
            this.counters = {};
            this.scoreCounters = {};
            for (var player_id in gamedatas.players)
            {
                var player = gamedatas.players[player_id];

                this.counters[player_id] = {};

                // Create player counters for facedown tableau card
                this.counters[player_id][99] = new ebg.counter();
                this.counters[player_id][99].create('counter-99-' + player_id);

                // Create player counters and listeners for player tableau
                for (var i = this.constant.ACAI_BERRY; i <= this.constant.JOKER; i++) {
                    var tableauDivId = "player-" + i + "-" + player_id;
                    this.counters[player_id][i] = new ebg.counter();
                    this.counters[player_id][i].create('counter-' + i + '-' + player_id);
                    dojo.connect( dojo.byId( tableauDivId ), 'onclick', this, 'onPlayerCardSelection' );
                }

                this.scoreCounters[player_id] = {};

                // Create player counters for final scoring
                for (var i = this.constant.ACAI_BERRY; i <= this.constant.RAMBUTAN; i++) {
                    this.scoreCounters[player_id][i] = new ebg.counter();
                    this.scoreCounters[player_id][i].create('score-counter-' + i + '-' + player_id)
                }

                // Create player counter for total final score
                this.scoreCounters[player_id]['total'] = new ebg.counter();
                this.scoreCounters[player_id]['total'].create('score-counter-total-' + player_id);
            }

            // Increment counters for facedown cards
            for (var card_id in gamedatas.facedown) {
                var card = gamedatas.facedown[card_id];
                this.counters[card['location_arg']][99].incValue(1);
                dojo.removeClass('player-99-' + card['location_arg'], 'tuc_zero_cards');
            }

            // Increment counters for player owned fruit cards
            for (var i = this.constant.ACAI_BERRY; i <= this.constant.JOKER; i++) {
                for (var card_id in gamedatas[i]) {
                    var card = gamedatas[i][card_id];
                    this.counters[card['location_arg']][i].incValue(1);
                    dojo.removeClass('player-' + i + '-' + card['location_arg'], 'tuc_zero_cards');
                }
            }

            // Setup tooltips for player cards
            for (var i = this.constant.ACAI_BERRY; i <= this.constant.JOKER; i++) {
                for (var player_id in gamedatas.players) {
                    var fruitCssName = this.fruit[i]['namecss'];
                    var cardElement = document.getElementById("player-" + i + "-" + player_id);
                    this.setupNewCard(cardElement, i, i);
                }
            }

            // Set counter values for end game scoring
            // NOTE: This only matters if the game has already ended and the page is refreshed
            for (var player_id in gamedatas.players) {
                var totalPoints = 0;
                for (var i = this.constant.ACAI_BERRY; i <= this.constant.RAMBUTAN; i++) {
                    this.scoreCounters[player_id][i].setValue(gamedatas[player_id][i]);
                    totalPoints += parseInt(gamedatas[player_id][i]);
                }
                this.scoreCounters[player_id]['total'].setValue(totalPoints);
            }
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
                case 'gameEnd':
                    this.resetPlayerTableauCss();

                    // Hide main selection area
                    document.getElementById('main-table').classList.add('tuc_hidden');

                    // Show score sheet
                    document.getElementById('score-sheet').classList.remove('tuc_hidden');
                    break;
                case 'gameFinalScoring':
                    this.resetPlayerTableauCss();

                    // Hide main selection area
                    document.getElementById('main-table').classList.add('tuc_hidden');

                    // Show score sheet
                    document.getElementById('score-sheet').classList.remove('tuc_hidden');
                    break;
                case 'gameNextPlayer':
                    this.resetPlayerTableauCss();
                    this.setOptimalColumnHeight();
                    this.active_column = -1;
                    break;
                case 'playerAssignJoker':
                    this.resetPlayerTableauCss();

                    if ( this.isCurrentPlayerActive() ) {
                        var activePlayerId = this.getActivePlayerId();
                        
                        for ( var i = this.constant.ACAI_BERRY; i <= this.constant.RAMBUTAN; i++ ) {
                            var fruitCardDivId = 'player-' + i + '-' + activePlayerId;
                            if (this.counters[activePlayerId][i].getValue() > 0 &&
                                this.counters[activePlayerId][i].getValue() < this.card[i].count ) {
                                dojo.addClass( fruitCardDivId, 'tuc_clickable' );
                                dojo.addClass( fruitCardDivId, 'tuc_highlight' );
                                dojo.removeClass( fruitCardDivId, 'tuc_zero_cards' );
                            } else {
                                dojo.addClass( fruitCardDivId, 'tuc_transparent' );
                            }
                        }

                        dojo.addClass( 'player-13-' + activePlayerId, 'tuc_transparent' );
                        dojo.addClass( 'player-99-' + activePlayerId, 'tuc_hidden' );
                    }
                    break;
                case 'playerChooseGift':
                    if ( this.isCurrentPlayerActive() ) {
                        var activePlayerId = this.getActivePlayerId();

                        for ( var i = this.constant.ACAI_BERRY; i <= this.constant.JOKER; i++ )
                        {
                            var fruitCardDivId = 'player-' + i + '-' + activePlayerId;
                            if ( this.counters[activePlayerId][i].getValue() > 0 ) {
                                dojo.addClass( fruitCardDivId, 'tuc_clickable' );
                                dojo.addClass( fruitCardDivId, 'tuc_highlight' );
                            } else {
                                dojo.addClass( fruitCardDivId, 'tuc_hidden' );
                            }
                        }

                        dojo.addClass( 'player-99-' + activePlayerId, 'tuc_transparent' );
                    }
                    break;
                case 'playerResolveGift':
                    var activePlayerId = this.getActivePlayerId();
                    var giftedFruit = args.args.gifted_fruit;

                    this.resetPlayerTableauCss();

                    for ( var i = this.constant.ACAI_BERRY; i <= this.constant.JOKER; i++ )
                    {
                        var fruitCardDivId = 'player-' + i + '-' + activePlayerId;
                        if ( i != giftedFruit ) {
                            dojo.addClass( fruitCardDivId, 'tuc_transparent' );
                        }
                    }
                    dojo.addClass( 'player-99-' + activePlayerId, 'tuc_transparent' );
                    break;
                case 'playerResolveToucans':
                    this.resetPlayerTableauCss();
                    this.setOptimalColumnHeight();
                    this.active_column = args.args.active_column;
                    dojo.query( 'highlight' ).removeClass( 'tuc_highlight' );
                    if ( this.isCurrentPlayerActive() ) {
                        var activeColumn = parseInt( args.args.active_column );
                        this.highlightCards( 'column_' + activeColumn );
                    }
                    break;
                case 'playerResolveSteal':
                    if (this.isCurrentPlayerActive()) {
                        var nonActivePlayers = args.args.non_active_players;

                        for ( playerId in nonActivePlayers) {
                            for ( var i = this.constant.ACAI_BERRY; i <= this.constant.JOKER; i++ ) {
                                var fruitCardDivId = 'player-' + i + '-' + playerId;
                                if ( this.counters[playerId][i].getValue() > 0 ) {
                                    dojo.addClass( fruitCardDivId, 'tuc_clickable' );
                                    dojo.addClass( fruitCardDivId, 'tuc_highlight' );
                                } else {
                                    dojo.addClass( fruitCardDivId, 'tuc_hidden' );
                                }
                            }
                            dojo.addClass( 'player-99-' + playerId, 'tuc_transparent' );
                        }
                    }
                    break;
                case 'dummmy':
                    break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );

            switch ( stateName ) {
                case 'dummmy':
                    break;
            }
                      
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                    case 'playerSelectColumn':
                        for (var i = this.constant.A; i <= this.constant.C; i++) {
                            if (this.selectionColumn[i].count() > 0) {
                                var buttonId = 'column_' + i + '_button';
                                var listenerName = 'onCollectColumn' + this.column[i]['name'];
                                this.addActionButton(buttonId, _('Column ') + this.column[i]['namecss'], listenerName);
                            }
                        }
                        break;
                    case 'playerResolveGift':
                        for (let player_id in args.other_players)
                        {
                            var player = args.other_players[player_id];
                            this.addActionButton(player.player_id + '_button', player.player_name, 'onSelectPlayer');
                        }
                        break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods

        // Determine card type
        determineCardType: function( type_id )
        {
            if( type_id <= this.constant.RAMBUTAN )
            {
                return this.constant.FRUIT;
            }

            if( type_id == this.constant.JOKER )
            {
                return this.constant.JOKER;
            }

            if ( type_id >= this.constant.FLIP )
            {
                return this.constant.TOUCAN;
            }
        },

        // Get URL for a player action
        getActionUrl: function( action_name )
        {
            return '/' + this.game_name + '/' + this.game_name + '/' + action_name + '.html';
        },

        // Highlight cards
        highlightCards: function ( div_id )
        {
            var divElement = document.getElementById( div_id );
            var cardsInColumn = divElement.getElementsByTagName( '*' );

            for ( var i = 0; i < cardsInColumn.length; i++ )
            {
                if ((cardsInColumn[i].classList.contains('tuc_card') || cardsInColumn[i].classList.contains('tuc_custom_card'))
                    && !cardsInColumn[i].id.includes('player-table-99')) 
                {
                    cardsInColumn[i].classList.add('tuc_highlight');
                }
            }
        },

        // Remove extra css classes from player tableau cards
        resetPlayerTableauCss: function()
        {
            dojo.query( '.tuc_tableau_card' ).removeClass('tuc_clickable');
            dojo.query( '.tuc_tableau_card' ).removeClass('tuc_hidden');
            dojo.query( '.tuc_tableau_card' ).removeClass('tuc_highlight');
            dojo.query( '.tuc_tableau_card' ).removeClass('tuc_transparent');
        },

        setOptimalColumnHeight: function()
        {
            var maxCardsInColumn = 2;

            for (var i = 0; i < 3; i++)
            {
                if ( this.selectionColumn[i].count() > maxCardsInColumn )
                {
                    maxCardsInColumn = this.selectionColumn[i].count();
                }

                var colWrapDiv = 'column-' + this.column[i].namecss.toLowerCase() + '-wrap';
                dojo.style(colWrapDiv, 'height', (this.selectionColumn[i].count() - 2) * 75 + 382.5 + 'px');

            }

            var columnHeight = (maxCardsInColumn - 2) * 87.5 + 450;

            dojo.style('main-table', 'height', columnHeight + 'px');
        },

        // Setup new card: Add tooltips
        setupNewCard: function( card_div, card_type_id, card_id )
        {
            var cardType = this.determineCardType( card_type_id );

            if( cardType == this.constant.FRUIT )
            {
                var scoreType = this.fruit[card_type_id].scoretype;

                if (scoreType == this.constant.RANK) {
                    this.addTooltipHtml( card_div.id, this.format_block('jstpl_rank_fruit_tooltip', {
                        card_id: card_id,
                        name: this.fruit[card_type_id].name,
                        name_plural: this.fruit[card_type_id].pluralname,
                        name_css: this.fruit[card_type_id].namecss,
                        count: this.card[card_type_id].count,
                        text: this.fruit[card_type_id].tooltip
                    }));
                }

                if (scoreType == this.constant.SET) {
                    this.addTooltipHtml( card_div.id, this.format_block('jstpl_set_fruit_tooltip', {
                        card_id: card_id,
                        name: this.fruit[card_type_id].name,
                        name_plural: this.fruit[card_type_id].pluralname,
                        name_css: this.fruit[card_type_id].namecss,
                        count: this.card[card_type_id].count,
                        text: this.fruit[card_type_id].tooltip
                    }));
                }
            }

            if( cardType == this.constant.JOKER )
            {
                this.addTooltipHtml( card_div.id, this.format_block('jstpl_joker_tooltip', {
                    card_id: card_id,
                    type_id: card_type_id
                }));
            }

            if( cardType == this.constant.TOUCAN )
            {
                this.addTooltipHtml( card_div.id, this.format_block('jstpl_toucan_tooltip', {
                    card_id: card_id,
                    name: this.toucan[card_type_id].name,
                    name_css: this.toucan[card_type_id].namecss,
                    text: this.toucan[card_type_id].effect,
                    count: this.card[card_type_id].count
                }));
            }
        },

        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */

        onCollectColumn: function(column)
        {
            var action = 'selectColumn';
            if (this.checkAction(action, true))
            {
                this.ajaxcall(this.getActionUrl(action), {
                    column: column,
                    lock: true
                }, this, function(result) {
                }, function(is_error) {
                })
            }
        },
        
        onCollectColumnA: function(event)
        {
            this.onCollectColumn(0);
        },

        onCollectColumnB: function(event)
        {
            this.onCollectColumn(1);
        },

        onCollectColumnC: function(event)
        {
            this.onCollectColumn(2);
        },

        onPlayerCardSelection: function(event)
        {
            if ( this.isCurrentPlayerActive() )
            {
                var targetId = event.target.id.split("-")[2];
                var fruitId = event.target.id.split("-")[1];

                if ( this.checkAction( 'assignJoker', true ) )
                {
                    if (targetId == this.getActivePlayerId() && fruitId != 99 
                        && this.counters[targetId][fruitId].getValue() < this.card[fruitId]['count'])
                    {
                        this.ajaxcall(this.getActionUrl('assignJoker'), {
                            fruit: fruitId,
                            lock:true
                        }, this, function(result) {
                        }, function(is_error) {
                        });
                    }
                }

                if ( this.checkAction( 'chooseGift', true ) )
                {
                    if (targetId == this.getActivePlayerId() && fruitId != 99 
                        && this.counters[targetId][fruitId].getValue() > 0)
                    {
                        this.ajaxcall(this.getActionUrl('chooseGift'), {
                            gift: fruitId,
                            lock: true
                        }, this, function(result) {
                        }, function(is_error) {
                        });
                    }
                    
                }

                if ( this.checkAction( 'resolveSteal', true ) )
                {
                    if (targetId != this.getActivePlayerId() && fruitId != 99 
                        && this.counters[targetId][fruitId].getValue() > 0)
                    {
                        this.ajaxcall(this.getActionUrl('resolveSteal'), {
                            fruit: fruitId,
                            target: targetId,
                            lock: true
                        }, this, function(result) {
                        }, function(is_error) {
                        });
                    }
                }
            }
        },

        onColumnCardSelectionChanged: function(event)
        {
            var column = event.replace('column_', '');
            if ( this.isCurrentPlayerActive() )
            {
                var items = this.selectionColumn[column].getSelectedItems();
                if (items.length > 0) 
                {
                    if ( this.checkAction( 'selectColumn', true ) )
                    {
                        this.selectionColumn[column].unselectAll();
                        this.onCollectColumn(column);
                    }

                    if ( (this.checkAction( 'selectFlip', true ) || this.checkAction( 'selectGift', true ) || this.checkAction( 'selectSteal', true )) && this.active_column == column )
                    {
                        var toucan = items[0].type;
                        this.selectionColumn[column].unselectAll();

                        // Resolve Toucan
                        if (toucan == this.constant.FLIP)
                        {
                            this.onSelectFlip();
                        }

                        if (toucan == this.constant.GIFT)
                        {
                            this.onSelectGift();
                        }

                        if (toucan == this.constant.STEAL)
                        {
                            this.onSelectSteal();
                        }
                    }
                }
            }

            this.selectionColumn[column].unselectAll();
        },

        onSelectFlip: function()
        {
            // Resolve flip
            var action = 'selectFlip';
            if ( this.isCurrentPlayerActive() && this.checkAction( action, true ) )
            {
                this.ajaxcall(this.getActionUrl(action), {
                    lock: true
                }, this, function(result) {
                }, function(is_error) {
                })
            }
        },

        onSelectGift: function()
        {
            // Resolve gift
            var action = 'selectGift';
            if ( this.isCurrentPlayerActive() && this.checkAction( action, true ) )
            {
                this.ajaxcall(this.getActionUrl(action), {
                    lock: true
                }, this, function(result) {
                }, function(is_error) {
                })
            }
        },

        onSelectPlayer: function(event)
        {
            // Choose player for gift
            var action = 'resolveGift';
            if ( this.isCurrentPlayerActive() && this.checkAction ( action, true ) )
            {
                var selectedPlayer = event.target.id.replace('_button', '');

                this.ajaxcall(this.getActionUrl(action), {
                    playerToGift: selectedPlayer,
                    lock: true
                }, this, function(result) {
                }, function(is_error) {
                })
            }
        },

        onSelectSteal: function()
        {
            // Resolve steal
            var action = 'selectSteal';
            if ( this.isCurrentPlayerActive() && this.checkAction( action, true ) )
            {
                this.ajaxcall(this.getActionUrl(action), {
                    lock: true
                }, this, function(result) {
                }, function(is_error) {
                })
            }
        },
        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your tucano.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe('assignJoker', this, 'notif_assignJoker');
            dojo.subscribe('newCards', this, 'notif_newCards');
            dojo.subscribe('resolveGift', this, 'notif_resolveGift');
            dojo.subscribe('resolveSteal', this, 'notif_resolveSteal');
            dojo.subscribe('revealFacedownCards', this, 'notif_revealFacedownCards');
            dojo.subscribe('scorePoints', this, 'notif_scorePoints');
            dojo.subscribe('selectFlip', this, 'notif_selectFlip');
            dojo.subscribe('selectGift', this, 'notif_selectGift');
            dojo.subscribe('selectColumn', this, 'notif_selectColumn');
            dojo.subscribe('selectSteal', this, 'notif_selectSteal');

            this.notifqueue.setSynchronous( 'scorePoints', 750 );
        },  


        // Move joker to assigned set
        notif_assignJoker: function(notif)
        {
            var activePlayerId = this.getActivePlayerId();
            var fruitSet = notif.args.fruit;

            this.counters[activePlayerId][this.constant.JOKER].incValue(-1);
            dojo.addClass('player-' + this.constant.JOKER + '-' + activePlayerId, 'tuc_zero_cards');

            this.counters[activePlayerId][fruitSet].incValue(1);
            dojo.removeClass('player-' + fruitSet + '-' + activePlayerId, 'tuc_zero_cards');
        },
        
        // Deal new cards from deck
        notif_newCards: function(notif)
        {
            var column_a_card = notif.args.column_a_card;
            var column_b_card = notif.args.column_b_card;
            var column_c_card = notif.args.column_c_card;

            this.selectionColumn[this.constant.A].addToStockWithId(column_a_card.type, column_a_card.id, 'deck');
            this.selectionColumn[this.constant.B].addToStockWithId(column_b_card.type, column_b_card.id, 'deck');
            this.selectionColumn[this.constant.C].addToStockWithId(column_c_card.type, column_c_card.id, 'deck');

            this.deckCounter.incValue('-3');

            if ( this.deckCounter.getValue() == 0 ) {
                dojo.addClass('deck-back', 'tuc_zero_cards');
            }

            this.setOptimalColumnHeight();
        },

        // Move fruit from giver to receiver
        notif_resolveGift: function(notif)
        {
            var active_player_id = this.getActivePlayerId();
            var receiver_player_id = notif.args.receiver_player_id;
            var gift_fruit = notif.args.gift_fruit;

            // Remove fruit from active player
            this.counters[active_player_id][gift_fruit].incValue(-1);
            if (this.counters[active_player_id][gift_fruit].getValue() == 0) {
                dojo.addClass('player-' + gift_fruit + "-" + active_player_id, 'tuc_zero_cards');
            }

            // Give fruit to receiver
            this.counters[receiver_player_id][gift_fruit].incValue(1);
            dojo.removeClass('player-' + gift_fruit + '-' + receiver_player_id, 'tuc_zero_cards');
        },

        // Move fruit from target to active player
        notif_resolveSteal: function(notif)
        {
            var active_player_id = this.getActivePlayerId();
            var target_player_id = notif.args.target_player_id;
            var fruit = notif.args.fruit;

            // Remove fruit from target player
            this.counters[target_player_id][fruit].incValue(-1);
            if (this.counters[target_player_id][fruit].getValue() == 0) {
                dojo.addClass('player-' + fruit + "-" + target_player_id, 'tuc_zero_cards');
            }

            // Give fruit to active player
            this.counters[active_player_id][fruit].incValue(1);
            dojo.removeClass('player-' + fruit + '-' + active_player_id, 'tuc_zero_cards');
        },
 
        // Reveal facedown cards at end game
        notif_revealFacedownCards: function(notif)
        {
            var cardsCollected = notif.args.cardsCollected;

            for (card_id in cardsCollected) 
            {
                var card = cardsCollected[card_id];
                this.counters[card.location_arg][card.card_type].incValue(1);
                dojo.removeClass('player-' + card.card_type + '-' + card.location_arg, 'tuc_zero_cards');
                this.counters[card.location_arg][99].incValue(-1);
                dojo.addClass('player-99-' + card.location_arg, 'tuc_zero_cards');
            }
        },

        // Score points for a fruit type
        notif_scorePoints: function(notif)
        {
            var player_id = notif.args.player_id;
            var fruit_id = notif.args.fruit_id;
            var points = notif.args.player_points;

            this.scoreCounters[player_id][fruit_id].incValue(points);
            this.scoreCounters[player_id]['total'].incValue(points);
            this.scoreCtrl[player_id].incValue(points);
        },

        // Flip face up cards to face down pile
        notif_selectFlip: function(notif)
        {
            var player_id = this.getActivePlayerId();

            var active_column = notif.args.active_column;
            var toucan_to_discard = notif.args.toucan_to_discard;
            this.selectionColumn[active_column].removeFromStockById(toucan_to_discard);

            var cardsToFlip = 0;

            for (var i = this.constant.ACAI_BERRY; i <= this.constant.JOKER; i++)
            {
                var currentCount = this.counters[player_id][i].getValue();
                if (currentCount > 0)
                {
                    cardsToFlip += currentCount;
                    this.counters[player_id][i].setValue(0);
                    dojo.addClass('player-' + i + '-' + player_id, 'tuc_zero_cards');
                }
            }

            this.counters[player_id][99].incValue(cardsToFlip);
            dojo.removeClass('player-99-' + player_id, 'tuc_zero_cards')
        },

        // Remove gift toucan from active column
        notif_selectGift: function(notif)
        {
            var active_column = notif.args.active_column;
            var toucan_to_discard = notif.args.toucan_to_discard;
            this.selectionColumn[active_column].removeFromStockById(toucan_to_discard);
        },

        // Player selects cards from a column
        notif_selectColumn: function(notif)
        {
            var player_id = this.getActivePlayerId();
            var column = notif.args.column;
            var cards = notif.args.column_cards;

            for (var card_id in cards)
            {
                var card = cards[card_id];
                var card_stock_type = card.type;
                var card_type = this.card[card_stock_type].cardtype;
                var card_subtype = this.card[card_stock_type].subtype;

                // Only move fruit
                if (card_type == this.constant.FRUIT)
                {
                    dojo.removeClass('player-' + card_subtype + '-' + player_id, 'tuc_zero_cards');
                    this.selectionColumn[column].removeFromStockById(card_id, 'player-'+card_subtype+'-'+player_id);
                    this.counters[player_id][card_subtype].incValue(1);
                }
            }
        },

        // Remove steal toucan from active column
        notif_selectSteal: function(notif)
        {
            var active_column = notif.args.active_column;
            var toucan_to_discard = notif.args.toucan_to_discard;
            this.selectionColumn[active_column].removeFromStockById(toucan_to_discard);
        }
   });             
});
