{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- Tucano implementation : © Evan Pulgino <evan.pulgino@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    tucano_tucano.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<!-- Score Sheet -->
<div id="score-sheet" class="tuc_hidden">
    <table id="score-table" class="tuc_score_table">
        <tr class="tuc_score_table_row tuc_score_header">
            <th id="score-table-empty" class="tuc_score_table_empty">
            </th>
            <!-- BEGIN scorefruit -->
            <th class="tuc_score_table_header">
                <div id="score-table-header-{FRUIT_ID}" class="tuc_icon tuc_icon_{FRUIT_NAME}"></div>
            </th>
            <!-- END scorefruit -->
            <th class="tuc_score_table_header">
                <div id="score-table-header-total" class="tuc_icon_total"></div>
            </th>
        </tr>
        <!-- BEGIN playerscore -->
        <tr class="tuc_score_table_row">
            <td id="player-{PLAYER_ID}-score-name" class="tuc_score_player_name tuc_score_player_cell">
                <div id="player-{PLAYER_ID}-name-text" style="color:#{PLAYER_COLOR}" class="tuc_score_player_name_text">
                    {PLAYER_NAME}
                </div>
            </td>
            <!-- BEGIN playerscorefruit -->
            <td id="player-{PLAYER_ID}-{FRUIT_ID}-score" class="tuc_score_player_cell tuc_{FRUIT_NAME}_color">
                <span id="score-counter-{FRUIT_ID}-{PLAYER_ID}">0</span>
            </td>
            <!-- END playerscorefruit -->
            <td id="player-{PLAYER_ID}-total-score" class="tuc_score_player_cell tuc_total_color">
                <span id="score-counter-total-{PLAYER_ID}">0</span>
            </td>
        </tr>
        <!-- END playerscore -->
    </table>
</div>


<!-- Card Deck + Selection Columns -->
<div id="tuc-table">
    <div id="main-table">
        <div id="deck">
            <div id="deck-back" class="tuc_card tuc_card_back">
                <span id="deck-counter" class="tuc_counter">0</span>
            </div>
        </div>
        <div id="column-a-wrap" class="tuc_linenblock tuc_column">
            <div id="column-a-header" class="tuc_header_container">
                <h3 class="tuc_header tuc_header_a">{COLUMN_A}</h3>
            </div>
            <div id="column_0"></div>
        </div>
        <div id="column-b-wrap" class="tuc_linenblock tuc_column">
            <div id="column-b-header" class="tuc_header_container">
                <h3 class="tuc_header tuc_header_b">{COLUMN_B}</h3>
            </div>
            <div id="column_1"></div>
        </div>
        <div id="column-c-wrap" class="tuc_linenblock tuc_column">
            <div id="column-c-header" class="tuc_header_container">
                <h3 class="tuc_header tuc_header_c">{COLUMN_C}</h3>
            </div>
            <div id="column_2"></div>
        </div>
    </div>
    <div id="tuc-player-tableaus">
        <!-- BEGIN playertableau -->
        <div id="player-{PLAYER_ID}-tableau" class="tuc_linenblock">
            <div id="player-{PLAYER_ID}-header" class="tuc_header_container">
                <h3 class="tuc_header" style="background-color:#{PLAYER_COLOR}90">
                    {PLAYER_NAME} {FRUIT} {COLLECTION}
                </h3>
                <div id="player-{PLAYER_ID}-collection" class="tuc_collection">
                    <div id="player-99-{PLAYER_ID}" class="tuc_card_small tuc_tableau_card tuc_card_back tuc_zero_cards">
                        <span id="counter-99-{PLAYER_ID}" class="tuc_counter">0</span>
                    </div>
                    <!-- BEGIN playercard -->
                    <div id="player-{FRUIT_ID}-{PLAYER_ID}" class="tuc_card_small tuc_tableau_card tuc_card_{FRUIT_NAME} tuc_zero_cards">
                        <span id="counter-{FRUIT_ID}-{PLAYER_ID}" class="tuc_counter">0</span>
                    </div>
                    <!-- END playercard -->
                </div>
            </div>
        </div>
        <!-- END playertableau -->
    </div>
</div>


<script type="text/javascript">

    var jstpl_rank_fruit_tooltip = '<div id="fruit_${card_id}_tooltip" class="tuc_tooltip">\
        <div id="fruit_${card_id}_tooltip_image" class="tuc_card tuc_custom_card tuc_card_${name_css}"><span class="tuc_counter">x${count}</span></div>\
        <div id="fruit_${card_id}_tooltip_info" class="tuc_tooltip_info">\
            <div id="fruit_${card_id}_tooltip_text">\
                <h3 class="tuc_capitalize tuc_center">${name}</h3><br/><br/>\
                <div id="fruit_${card_id}_scoring_info">\
                    ${text}\
                </div>\
            <div>\
        </div>\
    </div>';

    var jstpl_set_fruit_tooltip = '<div id="fruit_${card_id}_tooltip" class="tuc_tooltip">\
        <div id="fruit_${card_id}_tooltip_image" class="tuc_card tuc_custom_card tuc_card_${name_css}"><span class="tuc_counter">x${count}</span></div>\
        <div id="fruit_${card_id}_tooltip_info" class="tuc_tooltip_info">\
            <div id="fruit_${card_id}_tooltip_text">\
                <h3 class="tuc_capitalize tuc_center">${name}</h3><br/><br/>\
                <div id="fruit_${card_id}_set_text">\
                    <span>{SET_TOOLTIP_TEXT} ${name_plural}:</span><br/><br/>\
                    <div id="fruit_${card_id}_set_points">\
                        ${text}\
                    <div>\
                </div>\
            </div>\
        <div>\
    </div>';

    var jstpl_joker_tooltip = '<div id="joker_${card_id}_tooltip" class="tuc_tooltip">\
        <div id="joker_${card_id}_tooltip_image" class="tuc_card tuc_custom_card tuc_card_joker"><span class="tuc_counter">x1</span></div>\
        <div id="joker_${card_id}_tooltip_info" class="tuc_tooltip_info">\
            <div id="joker_${card_id}_tooltip_text">\
                <h3 class="tuc_capitalize tuc_center">Joker</h3><br/><br/>\
                {JOKER_TOOLTIP_TEXT}\
            <div>\
        <div>\
    </div>';

    var jstpl_toucan_tooltip = '<div id="toucan_${card_id}_tooltip" class="tuc_tooltip">\
        <div id="toucan_${card_id}_tooltip_image" class="tuc_card tuc_custom_card tuc_card_${name_css}"><span class="tuc_counter">x${count}</span></div>\
        <div id="toucan_${card_id}_tooltip_info" class="tuc_tooltip_info">\
            <div id="toucan_${card_id}_tooltip_text">\
                <h3 class="tuc_capitalize tuc_center">${name}</h3><br/><br/>\
                <div id="toucan_${card_id}_effect_info">\
                    ${text}\
                </div>\
            </div>\
        <div>\
    </div>';


    var jstpl_player_panel_fruit = '\<div class="cp_board">\
        <div id="icon-0-${id}" class="tuc_icon tuc_icon_acai"><span id="counter-0-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-1-${id}" class="tuc_icon tuc_icon_avocado"><span id="counter-1-${id}" class="tuc_icon_counter">0</span>\</div>\
        <div id="icon-2-${id}" class="tuc_icon tuc_icon_banana"><span id="counter-2-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-3-${id}" class="tuc_icon tuc_icon_carambola"><span id="counter-3-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-4-${id}" class="tuc_icon tuc_icon_coconut"><span id="counter-4-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-5-${id}" class="tuc_icon tuc_icon_fig"><span id="counter-5-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-6-${id}" class="tuc_icon tuc_icon_lime"><span id="counter-6-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-7-${id}" class="tuc_icon tuc_icon_lychee"><span id="counter-7-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-8-${id}" class="tuc_icon tuc_icon_orange"><span id="counter-8-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-9-${id}" class="tuc_icon tuc_icon_papaya"><span id="counter-9-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-10-${id}" class="tuc_icon tuc_icon_pineapple"><span id="counter-10-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-11-${id}" class="tuc_icon tuc_icon_pomegranate"><span id="counter-11-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-12-${id}" class="tuc_icon tuc_icon_rambutan"><span id="counter-12-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-13-${id}" class="tuc_icon tuc_icon_joker"><span id="counter-13-${id}" class="tuc_icon_counter">0</span></div>\
        <div id="icon-99-${id}" class="tuc_icon tuc_icon_back"><span id="counter-99-${id}" class="tuc_icon_counter_facedown">0</span></div>\
    </div>'

</script>  

{OVERALL_GAME_FOOTER}
