 <?php
/*
	=======================
	CLASS: debug.
	=======================
	Include file that defines the class "debug".

	PURPOSE: To provide a consistent and easy way to output debugging messages.

	POLICIES --:

			(b) I am still unsure how I want to deal with the issue of the display 
		in terms of web browser vs CRON vs Email - that is to say, do I want to 
		create a "Display" object at some point? Because of this, I would like 
		to adopt a policy whereby all output to the display is confined to one 
		private function within this object. This will permit a relatively 
		painless way to implement a Display object at some later point in time.

	01/15/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class debug
{

					// Properties for debugging.
	public $DEBUG = FALSE;
	public $DebugTxtAvail = FALSE;
	public $DebugTxt = "";

					// Properties for text formatting.
	protected $LineFeed = "<BR>";
	protected $OpenPara = "<P>";
	protected $ClosePara = "</P>";
	protected $nbSpace = NBSP;
	
					// Properties for control and admin stuff.
			


	//---------------------------------------------------------------------------
	public function SetDisplayType($type)
	{
	/* 	PURPOSE: Set the text formatting properties for browser vs 
		console/email output.
	*/

	if($type != "HTML")
		{
		$this->LineFeed = LF;
		$this->OpenPara = LF;
		$this->ClosePara = LF;
		$this->nbSpace = " ";
		}
	else
		{
		$this->LineFeed = "<BR>";
		$this->OpenPara = "<P>";
		$this->ClosePara = "</P>";
		$this->nbSpace = NBSP;
		}
	} // END METHOD



	//---------------------------------------------------------------------------
	public function writeDebug($debugMessage, $type="MISC")
	{
	/*	PURPOSE: Outputs a debug message.

		ASSUMES --:
				a) Appropriate display type has been set with a call 
			to SetDisplayType(). Note that if no such call has been made the
			default is to assume Web Broswer - so we output in HTML.
				b) A page is 'open' to write to. E.g., if HTML the HTML page
			tags have already been issued (<html><head><body>).

		TAKES --:
				a) A string value that is the message to display to the user.
				b) A code to indicate if the message is an function entry/exit
			message or other general purpose information. These codes are:
					"MISC" = General info.
					"ENTRY" = Function Entry ($debugMessage = function name).
					"EXIT" = Function Exit ($debugMessage = function name).

		RETURNS --:
				a) Nothing.

	*/

	$TextToDisplay = "";
	
	switch ($type)
		{
		case "ENTRY":
			$TextToDisplay = "DEBUG >> Entering Function {$debugMessage}()";
			break;
			
		case "EXIT":
			$TextToDisplay = "DEBUG >> Exiting Function {$debugMessage}()";
			break;

		default:
			$TextToDisplay = "DEBUG >> " . $debugMessage;
		}

	$this->writeToDisplay($TextToDisplay);

	} // END FUNCTION


	//---------------------------------------------------------------------------
	public function displayDBRecord(&$recArray, $display=TRUE)
	{
	/*	PURPOSE: Create a formatted string suitable for displaying one record
		from the database.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
		
			1) Pointer to array where the dbms record is.
				
		RETURNS --:
			
		   1)	A string suitable for displaying all the fields
		   	of the record. AND IF $display=TRUE then this string will
		   	also be output to the current window.
	
		NOTES --:

				1) .
	
	*/
	$HeaderText = "DEBUG >> Listing Field Values in DB Record --";
	$HeaderText .= $this->LineFeed;
	$TextToDisplay = "";

	foreach($recArray as $key => $value)
		{
		$TextToDisplay .= "   Field[<i>{$key}</i>] = {$value}{$this->LineFeed}";
		}
	if($display)
		{
		$this->writeToDisplay($HeaderText);
		$this->writeToDisplay($TextToDisplay);
		}
	
	return $TextToDisplay;

	} // END METHOD



	//---------------------------------------------------------------------------
	private function writeToDisplay($displayMessage)
	{
	/*	PURPOSE: Writes text to the output device (Web Browser or CRON console).
		To confine all display output to a single function.
		This will permit a redesign of how we handle the display later
		down the line, with minimal impact.

		ASSUMES --:
				a) Appropriate display type has been set with a call 
			to SetDisplayType(). Note that if no such call has been made the
			default is to assume Web Broswer - so we output in HTML.

		TAKES --:
				a) A string value that is the message to display to the user.

		RETURNS --:
				a) Outputs the string to the display. Each call to this function
			outputs the string in a new paragraph.
				b) The function itself does not return any value at all.

	*/

	$TextToDisplay = "";

	$TextToDisplay = $this->OpenPara . $displayMessage . $this->ClosePara;
	echo $TextToDisplay;

	} // END FUNCTION


} // END CLASS debug


?>
