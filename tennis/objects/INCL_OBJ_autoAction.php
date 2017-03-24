 <?php
/*
	=======================
	OBJECT: AUTO_AutoAction.
	=======================
	Include file that defines the class AUTO_AutoAction.

	PURPOSE: To provide an abstraction of the autoAction and autoParam 
	DBMS tables.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

			(b) I am still unsure how I want to deal with the issue of the display 
		in terms of web browser vs CRON vs Email - that is to say, do I want to 
		create a "Display" object at some point? Because of this, I would like 
		to adopt a policy whereby all output to the display is confined to one 
		private function within this object. This will permit a relatively 
		painless way to implement a Display object at some later point in time.

	12/10/2011: Initial creation.

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class autoAction
{

					//   Variables needed for getting data from the dbms.
	public $actRow = array();
	public $actRecsRead = 0;
	public $actParams = array();

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
	protected $queryOpen = FALSE;
	protected $dbmsRsrcAction;
	protected $dbmsRsrcParams;
			


	//---------------------------------------------------------------------------
	public function getNextAction()
	{
	/*	PURPOSE: Get next record from the dbms.

		ASSUMES:	Connection to DBMS is already open.
		
		TAKES --:
		
			1) 
				
		RETURNS --:
			
		   1) RTN_FAILURE if an error has occurred.
		   2) RTN_SUCCESS if an additional row has been read.
		   3) RTN_EOF if no add'l row - we are at EOF.
			   
		   4) If RTN_SUCCESS, Then the public array actRow will be
		   	populated with the data from the new row.
	
		NOTES --:

				1) Need to check to see if this is the first call to this 
			funcion. If so, then we need to actually open the dbms.
	
				2) As of 11/27/2011: Before creating the actual table in the dbms
			I'll simulate it. This will help me both focus on the logic and get 
			a firmer understanding of what data elements need to be in the 
			autoAction table. Also, for notices	I need to specify a variety of 
			parameters for each autoAction table line	entry. So I think I need 
			a 2nd table - the 'autoParms' table. Each record in this table will 
			hold one parameter for a given autoAction record. So it's a 
			one-to-many relationship.
				So, you know, somewhere in the container script which the CRON job
			would have fired off we'd open the autoAction table with a:
				$qryResult = Tennis_ViewGeneric($viewName, $where, $sort);
				Then somewhere in this area we would have read in the next 
			(or first) record in the autoAction table like:
				$row = mysql_fetch_array($qryResult);
				For the autoParms table - for right now I am not trying to simulate
			reading in those rows individually, rather I will directly write the
			parms into an array. Later this will need to be re-structured; including
			deciding where in the code to read the table and write to the params
			array.
	*/
	global $objError;
	$errLastErrorKey = 0;
	
	$returnValue = RTN_SUCCESS;
	$getParmsResult = FALSE;
	
	$debugText = "";
	$errorText = "";

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="ENTRY");


						//   Determine if the autoAction table is already open for
						//row-reading or not. IF not, then open it.
	if (!$this->queryOpen)
		{
		$this->queryOpen = $this->open();
		if (!$this->queryOpen)
			{
			if ($this->DEBUG) $this->writeDebug("Error Opening autoAction table.");
			$errLastErrorKey = $objError->RegisterErr(
				ERRSEV_ERROR, 
				ERRCLASS_DBCNNCT, 
				__FUNCTION__, 
				__LINE__, 
				"Unable to retreive Auto Action Records.", 
				False);
			$objError->ReportLastErr();
			if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
			return RTN_FAILURE;
			}
		}


						//   Determine (simulate) EOF.
	if ($this->actRecsRead >=1)
		{
		if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
		return RTN_EOF;
		}


						//   Determine run environment.
						//   My belief is that once we do away with this simulation
						//we no longer need this line because it is only used here
						//as a proxy to determine if we are running this script on
						//the local development box or on the live A2 production
						//server; and thus which clubID and SeriesIDs to use.
						//   If running in CRON it means we are running live on
						//the A2 hosted server. In that case we need to use
						//different ClubID and SeriesID.
	$RunningInCron = !isset($_SERVER['HTTP_HOST']);
					
	if ($RunningInCron)
		{
		$this->actRow['ID'] = 2;
		$this->actRow['ClubID'] = 2;
		$this->actRow['AutoActClassID'] = 63;
		$this->actRow['ActTitle'] = "TEST Sending RSVP Results";
		$this->actRow['TrggrObjType'] = 42;
		$this->actRow['TrggrObjID'] = 28;
		$this->actRow['Notes'] = "Testing sending of weekly RSVP Results email.";
		$this->actRow['Notes'] .= " For series Recreational Play (ID #28) in";
		$this->actRow['Notes'] .= " Demo and Test Club (ID #2 on A2 Host).";
		}
	else
		{
		$this->actRow['ID'] = 2;
		$this->actRow['ClubID'] = 2;
		$this->actRow['AutoActClassID'] = 63;
		$this->actRow['ActTitle'] = "TEST Sending RSVP Results";
		$this->actRow['TrggrObjType'] = 42;
		$this->actRow['TrggrObjID'] = 5;
		$this->actRow['Notes'] = "Simulating sending of weekly RSVP Results email";
		$this->actRow['Notes'] .= " on local. Series Recreational Play (ID #5)";
		$this->actRow['Notes'] .= " in Test Club (ID #2 on local).";
		}


	$getParmsResult = $this->getActionParams($this->actRow['ID']);
	switch ($getParmsResult)
		{
		case RTN_FAILURE:
			$returnValue = RTN_FAILURE;
			$errorText .= "ERROR: Unable to retreive Action Params.";
			$errorText .= " Action Aborted: Action ID {$this->actRow['ID']}";
			$errorText .= " || {$this->actRow['ActTitle']}";
			$errLastErrorKey = $objError->RegisterErr(
				ERRSEV_ERROR, 
				ERRCLASS_DBCNNCT, 
				__FUNCTION__, 
				__LINE__, 
				$errorText, 
				False);
			$objError->ReportLastErr();
			break;

		case RTN_WARNING:
			$returnValue = RTN_WARNING;
			$errorText= "Unidentified Warning Situation:";
			$errorText .= " Action ID {$this->actRow['ID']}";
			$errorText .= " || {$this->actRow['ActTitle']}";
			$errLastErrorKey = $objError->RegisterErr(
				ERRSEV_WARNING, 
				ERRCLASS_DBCNNCT, 
				__FUNCTION__, 
				__LINE__, 
				$errorText, 
				False);
			$objError->ReportLastErr();
			break;
		
		default:
		$this->actRecsRead ++;
		$returnValue = RTN_SUCCESS;
		}

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnValue;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function getActionParams($actID)
	{
	/*	PURPOSE: Get the set of parameters for a given autoAction record.
	
		ASSUMES --:
		
			1) Connection to DBMS is already open.
			2) autoAction table or view is already open.
		
		TAKES --:
		
			1) autoAction record #.
			
		RETURNS --:
		
		   1) RTN_FAILURE if an error has occurred.
		   2) RTN_SUCCESS if an additional row has been read.
		   3) RTN_EOF if no add'l row - we are at EOF.
		   
		   4) If RTN_SUCCESS, Then the public array actParms will be
		   	populated with the data from the parameter set.

		NOTES --:

			1) As of 12/04/2011: Before creating the actual table in the dbms
		I'll simulate it. This will help me both focus on the logic and get 
		a firmer understanding of what data elements need to be in the 
		autoAction table.
	*/
	global $objError;

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="ENTRY");

	$this->actParams['SendDayOfWeek'] = 4; //Sunday=0
	$this->actParams['SendHourOfDay'] = 9;
	$this->actParams['ToGroup'] = 30; // Send email to all members of the series.
	$this->actParams['EmailSubject'] = "Tennis RSVP Results";
	$this->actParams['EmailBodyTmplate'] = "This is the email body.";

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function open()
	{
	/*	PURPOSE: Open the dbms table or view for actionItems, ready to
		read in records.

		ASSUMES:	Connection to DBMS is already open.
		
		TAKES --: NA
		
		RETURNS --:
		
		   1) RTN_FAILURE if an error has occurred.
		   2) RTN_SUCCESS if table has been opened.
		   
		   3) If RTN_SUCCESS, Then the variables $dbmsRsrcAction and
		   	$dbmsRsrcParams will be set with the dbms object references
		   	(for use in fetching rows from the dbms).

		   4) If RTN_SUCCESS, Then the variable $queryOpen will be
		   	set to TRUE to indicate that the view is already open and
		   	ready to read records.
	*/
	global $objError;
				   	
	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="ENTRY");

					//   Since we are just simulating right now, this is just
					//stubbed out. 
	$this->dbmsRsrcAction = "";
	$this->dbmsRsrcParams = "";
	$this->queryOpen = TRUE;

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD


	//---------------------------------------------------------------------------
	private function writeDebug($debugMessage, $type="MISC")
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
			$TextToDisplay = $debugMessage;
		}

	$this->writeToDisplay($TextToDisplay);

	} // END METHOD


	//---------------------------------------------------------------------------
	public function SetDisplayType($type)
	{
	/* 	PURPOSE: Set the text formatting properties for browser vs 
		console/email output.
	*/

	if($type != "HTML")
		{
		$this->disLineFeed = LF;
		$this->disParaOpen = LF;
		$this->disParaClose = LF;
		$this->disNBspace = " ";
		}
	else
		{
		$this->disLineFeed = "<BR>";
		$this->disParaOpen = "<P>";
		$this->disParaClose = "</P>";
		$this->disNBspace = NBSP;
		}
	} // END FUNCTION



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

	$TextToDisplay = $this->disParaOpen . $displayMessage . $this->disParaClose;
	echo $TextToDisplay;

	} // END METHOD




} // END CLASS AUTO_AutoAction


?>
