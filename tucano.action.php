<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Tucano implementation : © Evan Pulgino <evan.pulgino@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * tucano.action.php
 *
 * Tucano main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/tucano/tucano/myAction.html", ...)
 *
 */
  
  
  class action_tucano extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "tucano_tucano";
            self::trace( "Complete reinitialization of board game" );
      }
  	}

    public function assignJoker()
    {
        self::setAjaxMode();
        $fruit = self::getArg("fruit", AT_posint, true);
        $this->game->assignJoker($fruit);
        self::ajaxResponse();
    }

    public function chooseGift()
    {
        self::setAjaxMode();
        $gift = self::getArg("gift", AT_posint, true);
        $this->game->chooseGift($gift);
        self::ajaxResponse();
    }

    public function resolveGift()
    {
        self::setAjaxMode();
        $player_to_gift = self::getArg("playerToGift", AT_posint, true);
        $this->game->resolveGift($player_to_gift);
        self::ajaxResponse();
    }

    public function resolveSteal()
    {
        self::setAjaxMode();
        $fruit = self::getArg("fruit", AT_posint, true);
        $target = self::getArg("target", AT_posint, true);
        $this->game->resolveSteal($fruit, $target);
        self::ajaxResponse();
    }

    public function selectFlip()
    {
        self::setAjaxMode();
        $this->game->selectFlip();
        self::ajaxResponse();
    }

    public function selectGift()
    {
        self::setAjaxMode();
        $this->game->selectGift();
        self::ajaxResponse();
    }
  	
  	public function selectColumn()
    {
        self::setAjaxMode();
        $column = self::getArg("column", AT_posint, true);
        $this->game->selectColumn($column);
        self::ajaxResponse();
    }

    public function selectSteal()
    {
        self::setAjaxMode();
        $this->game->selectSteal();
        self::ajaxResponse();
    }

  }
  

