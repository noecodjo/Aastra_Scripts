<?php
########################################################################################################
# Aastra XML API Classes - AastraIPPhoneTextScreen
# Copyright Aastra Telecom 2005-2010
#
# AastraIPPhoneTextScreen object.
#
# Public methods
#
# Inherited from AastraIPPhone
#     setTitle(Title) to setup the title of an object (optional)
#          @title		string
#     setTitleWrap() to set the title to be wrapped on 2 lines (optional)
#     setCancelAction(uri) to set the cancel parameter with the URI to be called on Cancel (optional)
#          @uri		string
#     setDestroyOnExit() to set DestroyonExit parameter to 'yes', 'no' by default (optional)
#     setBeep() to enable a notification beep with the object (optional)
#     setLockIn() to set the Lock-in tag to 'yes' (optional)
#     setLockInCall() to set the Lock-in tag to 'call' (optional)
#     setAllowAnswer() to set the allowAnswer tag to 'yes' (optional only for non softkey phones)
#     setAllowDrop() to set the allowDrop tag to 'yes' (optional only for non softkey phones)
#     setAllowXfer() to set the allowXfer tag to 'yes' (optional only for non softkey phones)
#     setAllowConf() to set the allowConf tag to 'yes' (optional only for non softkey phones)
#     setTimeout(timeout) to define a specific timeout for the XML object (optional)
#          @timeout		integer (seconds)
#     addSoftkey(index,label,uri,icon_index) to add custom soktkeys to the object (optional)
#          @index		integer, softkey number
#          @label		string
#          @uri		string
#          @icon_index	integer, icon number
#     setRefresh(timeout,URL) to add Refresh parameters to the object (optional)
#          @timeout		integer (seconds)
#          @URL		string
#     setEncodingUTF8() to change encoding from default ISO-8859-1 to UTF-8 (optional)
#     addIcon(index,icon) to add custom icons to the object (optional)
#          @index		integer, icon index
#          @icon		string, icon name or definition
#     generate() to return the generated XML for the object
#     output(flush) to display the object
#          @flush		boolean optional, output buffer to be flushed out or not.
#
# Specific to the object
#     setText(text) to set the text to be displayed.
#          @text		string
#     setDoneAction(uri) to set the URI to be called when the user selects the default "Done" key (optional)
#          @uri		string
#     setAllowDTMF() to allow DTMF passthrough on the object
#     setScrollUp(uri) to set the URI to be called when the user presses the Up arrow (optional)
#          @uri		string
#     setScrollDown(uri) to set the URI to be called when the user presses the Down arrow (optional)
#          @uri		string
#     setScrollLeft(uri) to set the URI to be called when the user presses the Left arrow (optional)
#          @uri		string
#     setScrollRight(uri) to set the URI to be called when the user presses the Right arrow (optional)
#          @uri		string
#
# Example
#     require_once('AastraIPPhoneTextScreen.class.php');
#     $text = new AastraIPPhoneTextScreen();
#     $text->setTitle('Title');
#     $text->setText('Text to be displayed.');
#     $text->setDestroyOnExit();
#     $text->addSoftkey('1', 'Mail', 'http://myserver.com/script.php?action=1','1');
#     $text->addSoftkey('6', 'Exit', 'SoftKey:Exit');
#     $text->addIcon('1', 'Icon:Envelope');
#     $text->output();
#
########################################################################################################

require_once('AastraIPPhone.class.php');

class AastraIPPhoneTextScreen extends AastraIPPhone {
	var $_text;
   	var $_doneAction='';
	var $_allowDTMF='';
	var $_scrollUp='';
	var $_scrollDown='';
	var $_scrollLeft='';
	var $_scrollRight='';

	function setText($text)
	{
		$this->_text = $text;
	}

	function setDoneAction($uri)
	{
		$this->_doneAction = $uri;
	}

	function setScrollUp($uri)
	{
		$this->_scrollUp = $uri;
	}

	function setScrollDown($uri)
	{
		$this->_scrollDown = $uri;
	}

	function setScrollLeft($uri)
	{
		$this->_scrollLeft = $uri;
	}

	function setScrollRight($uri)
	{
		$this->_scrollRight = $uri;
	}

	function setAllowDTMF()
	{
		$this->_allowDTMF = 'yes';
	}

	function render()
	{
		# Beginning of root tag
		$out = "<AastraIPPhoneTextScreen";

		# DestroyOnExut
		if($this->_destroyOnExit == 'yes') $out .= " destroyOnExit=\"yes\"";

		# CancelAction
		if($this->_cancelAction != "")
			{ 
			$cancelAction = $this->escape($this->_cancelAction);
			$out .= " cancelAction=\"{$cancelAction}\"";
			}

		# DoneAction
		if($this->_doneAction != "")
			{ 
			$doneAction = $this->escape($this->_doneAction);
			$out .= " doneAction=\"{$doneAction}\"";
			}

		# Beep
		if ($this->_beep=='yes') $out .= " Beep=\"yes\"";

		# TimeOut
		if ($this->_timeout!=0) $out .= " Timeout=\"{$this->_timeout}\"";

		# Lockin
		if($this->_lockin!='') $out .= " LockIn=\"{$this->_lockin}\"";

		# AllowAnswer
		if ($this->_allowAnswer == 'yes') $out .= " allowAnswer=\"yes\"";

		# AllowDrop
		if ($this->_allowDrop == 'yes') $out .= " allowDrop=\"yes\"";

		# AllowXfer
		if ($this->_allowXfer == 'yes') $out .= " allowXfer=\"yes\"";

		# AllowConf
		if ($this->_allowConf == 'yes') $out .= " allowConf=\"yes\"";

		# AllowDTMF
		if ($this->_allowDTMF=='yes') $out .= " allowDTMF=\"yes\"";

		# Scrolls up/down/left/right
		if($this->_scrollUp!='') $out .= " scrollUp=\"".$this->escape($this->_scrollUp)."\"";
		if($this->_scrollDown!='') $out .= " scrollDown=\"".$this->escape($this->_scrollDown)."\"";
		if($this->_scrollLeft!='') $out .= " scrollLeft=\"".$this->escape($this->_scrollLeft)."\"";
		if($this->_scrollRight!='') $out .= " scrollRight=\"".$this->escape($this->_scrollRight)."\"";

		# End of root tag
		$out .= ">\n";

		# Title
		if ($this->_title!='')
			{
			$title = $this->escape($this->_title);
		 	$out .= "<Title";
		 	if ($this->_title_wrap=='yes') $out .= " wrap=\"yes\"";
			$out .= ">".$title."</Title>\n";
			}

		# Text
		$text = $this->escape($this->_text);
		$out .= "<Text>{$text}</Text>\n";

		# Softkeys
		if (isset($this->_softkeys) && is_array($this->_softkeys)) 
			{
		  	foreach ($this->_softkeys as $softkey) $out .= $softkey->render();
			}

		# Icons
		if (isset($this->_icons) && is_array($this->_icons)) 
			{
  			$IconList=False;
  			foreach ($this->_icons as $icon) 
  				{
	  			if(!$IconList) 
  					{
	  				$out .= "<IconList>\n";
	  				$IconList=True;
	  				}
	  			$out .= $icon->render();
  				}
  			if($IconList) $out .= "</IconList>\n";
			}

		# End tag
		$out .= "</AastraIPPhoneTextScreen>\n";

		# Return XML object
		return $out;
	}
}
?>
