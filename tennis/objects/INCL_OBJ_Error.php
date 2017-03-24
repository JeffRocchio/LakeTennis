 <?php
/*
	=============
	OBJECT: ERROR.
	=============
	Include file for classes and functions specific to the Error object.

	PURPOSE --:
	 
		Provide a consistent way to handle errors in all scripts. 
		econdarily, to make it easier to handle errors.
	 
	 
	POLICIES --:
	 
			(a) All interaction with ERROR object is via functions. No direct 
		access to properties.
	 
			(b) To report out an error to the end-user, the appropriate function 
		must be called. The main script cannot do this on its own. 
		This is necessary in order to ensure that the internal count and 
		status variables are accurately maintained.
	 
			(c) I am still unsure how I want to deal with the issue 
		of the display in terms of web browser vs CRON vs Email - that is to 
		say, do I want to create a "Display" object at some point? Because 
		of this, I would like to adopt a policy whereby all output to the 
		display is confined to one private function within this object. 
		This will permit a relatively painless way to implement a Display 
		object at some later point in time.

	12/05/2011: Initial creation.

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class ErrorList {

					//   Properties for debugging.
	public $DEBUG = FALSE;
	public $DebugTxtAvail = FALSE;
	public $DebugTxt = "";

					//   Properties for text formatting.
   private $disParaOpen = "<P>";
   private $disParaClose = "</P>";
   private $disLineFeed = "<BR />";
   private $disNBspace = NBSP;
   
					//   Maps error severity levels to text sev titles.
   protected $errSevText = array (
   	   ERRSEV_NOTICE => "NOTICE", 
			ERRSEV_WARNING => "WARNING", 
			ERRSEV_ERROR => "ERROR", 
			ERRSEV_FATAL => "** FATAL ERROR **");

					//   2D Array to hold list of Errors.
   protected $errList = array();
					//   Total # of errors recorded in the array list.
   protected $errsInQueu = 0;
					//   Total # of errors which are as yet unreported out to user.
   protected $errsUnreported = 0;
 


	//---------------------------------------------------------------------------
	public function RegisterErr($sev, $class, $functn, $line, $detail, $rptStat=False)
	{
	/*	PURPOSE: Insert a new error record into the object.

		ASSUMES --:

		TAKES --:
				a) The values necessary to fully describe the error.

		RETURNS --:
				a) The index or key into the registered error
			(so that the calling program can later request that the error 
			be reported out).
	*/
	$errArrayCount = $this->errsInQueu;

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="ENTRY");


	if ($this->DEBUG) $this->writeDebug("DEBUG >> errArrayCount: $errArrayCount");
	$errArrayCount++;
	if ($this->DEBUG) $this->writeDebug("DEBUG >> errArrayCount: $errArrayCount");
	$this->errList[$errArrayCount]['Sev'] = $sev;
	$this->errList[$errArrayCount]['Class'] = $class;
	$this->errList[$errArrayCount]['Functn'] = $functn;
	$this->errList[$errArrayCount]['Line'] = $line;
	$this->errList[$errArrayCount]['Detail'] = $detail;
	$this->errList[$errArrayCount]['RptStat'] = $rptStat;

	$this->errsInQueu = $errArrayCount;
	if ($rptStat == FALSE) $this->errsUnreported++ ;

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
	return $errArrayCount;

	} // END METHOD


	//---------------------------------------------------------------------------
	public function GetByKey($key)
	{
	/*	PURPOSE: Return to caller an array which contains the information
		about a specific error. NOTE: This simply returns the data about the
		error, it does NOT report the error out to the user.

		ASSUMES --:

		TAKES --:
				a) The specific error's key value (index #).

		RETURNS --:
				a) An array which contains information about the error.
			Can be used by caller to determine if the main program can continue,
			can recover-and-continue, or should be terminated.
	*/
	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="ENTRY");

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
	return array (
          $this->errList[$key]['Sev'], 
          $this->errList[$key]['Class'], 
          $this->errList[$key]['Functn'], 
          $this->errList[$key]['Line'], 
          $this->errList[$key]['Detail'], 
          $this->errList[$key]['RptStat']);

  } // END METHOD


	//---------------------------------------------------------------------------
	public function GetLastErr()
	{
	/*	PURPOSE: Supplies caller with the data about the latest registered error.
		This function simply returns the data, it does not report it out to
		the user.

		ASSUMES --:

		TAKES --: NA

		RETURNS --:
				a) An array which contains information about the error.
			Can be used by caller to determine if the main program can continue,
			can recover and continue or should be terminated.
	*/

	$key = $this->errsInQueu;

	return $this->GetByKey($key);

  } // END METHOD


	//---------------------------------------------------------------------------
	public function ReportByKey($key)
	{
	/*	PURPOSE: Report a specific error out to user, using the key-index 
		into the error line item.

		ASSUMES --:

		TAKES --:
				a) The specific error's key value (index #).

		RETURNS --:
				a) An array which contains information about the error.
			Can be used by caller to determine if the main program can continue,
			can recover-and-continue, or should be terminated.
	*/
	$errDispText = "";

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="ENTRY");

	$errDispText = $this->formatErrDisplay($key);

	$this->writeToDisplay($errDispText);
	$this->errList[$key]['RptStat'] = TRUE;

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
	return array (
          $this->errList[$key]['Sev'], 
          $this->errList[$key]['Class'], 
          $this->errList[$key]['Functn'], 
          $this->errList[$key]['Line'], 
          $this->errList[$key]['Detail'], 
          $this->errList[$key]['RptStat']);

  } // END METHOD


	//---------------------------------------------------------------------------
	public function ReportLastErr()
	{
	/*	PURPOSE: Reports out to the user the latest registered error.

		ASSUMES --:

		TAKES --: NA

		RETURNS --:
				a) An array which contains information about the error.
			Can be used by caller to determine if the main program can continue,
			can recover and continue or should be terminated.
	*/


	$key = $this->errsInQueu;

	return $this->ReportByKey($key);

  } // END METHOD


	//---------------------------------------------------------------------------
	public function ReportAllErrs($sev, $rptReported=FALSE)
	{
	/*	PURPOSE: Reports out to the user all errors of the
		specified severity level and $errsUnreported flag.

		ASSUMES --:

		TAKES --:
				a) An integer that specifies a filter for which type of errors to
			report out. This integer must be either 0 to report out all errors or
			must be one of the ERRSEV_* constants.
				b) A flag to specify if the reported errors should also include 
			those which have already been reported out once. [TRUE | FALSE]

		RETURNS --:
				a) The number of errors reported out.
.
   */
	$errDispText = "";
	$errsReported = 0;
	$reportItOut = FALSE;

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="ENTRY");

					//   Loop through the error list and report out all those
					//that meet the filtering critera.
	foreach($this->errList as $key => $errRow)
		{
					//   Use a logical operation to do the 'Reported/Unreported'
					//filter. (a): Flip the item's RptStat value so that the
					//flipped boolean value will now mean "Needs to be Reported."
					//(b): Then do a logical OR of that
					//flipped value against the passed in param $rptReported. That
					//result will be = TRUE for all rows if $rptReported=TRUE.
					//And result will be = TRUE for only rows that 
					//'need reporting out' if $rptReported=FALSE.
		$reportItOut = ((boolean)$rptReported or (boolean)!$errRow['RptStat']);
		if ($this->DEBUG)
			{
			$boolTest0 = !$errRow['RptStat'];
			$boolTest1 = $errRow['Sev']>=$sev;
			$boolTest2 = ($errRow['Sev']>=$sev and $reportItOut);
			$debugTxt = "DEBUG >>";
			$debugTxt .= $this->disLineFeed;
			$debugTxt .= "errRow['RptStat']: {$errRow['RptStat']}";
			$debugTxt .= " | !errRow['RptStat']: {$boolTest0}";
			$debugTxt .= " | rptReported: {$rptReported}";
			$debugTxt .= " | reportItOut: {$reportItOut}";
			$debugTxt .= $this->disLineFeed;
			$debugTxt .= "DEBUG >> errRow['Sev']: {$errRow['Sev']}";
			$debugTxt .= $this->disLineFeed;
			$debugTxt .= $this->disLineFeed;

			$debugTxt .= "----Boolean Values--::";
			$debugTxt .= $this->disLineFeed;
			$debugTxt .= "errRow['Sev']>=sev: {$boolTest1}";
			$debugTxt .= " | sev AND reportItOut: {$boolTest2}";
			$this->writeDebug($debugTxt);
			}
		if (($errRow['Sev']>=$sev) and $reportItOut)
			{
			if ($this->DEBUG) $this->writeDebug("DEBUG >> INTO IF stmt for printing. key: {$key}");
			$errDispText = $this->formatErrDisplay($key);
			$this->writeToDisplay($errDispText);
			$this->errList[$key]['RptStat'] = TRUE;
			$errsReported++;
			}
		}

	if ($this->DEBUG) $this->writeDebug("DEBUG >> Errors Reported: {$errsReported}", $type="MISC");

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
	return $errsReported;

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
	private function formatErrDisplay($key)
	{
	/*	PURPOSE: Formats a given error entry suitable for display.

		ASSUMES --:
				a) Appropriate display type has been set with a call 
			to SetDisplayType(). Note that if no such call has been made the
			default is to assume Web Broswer - so we output in HTML.

		TAKES --:
				a) The key into the error list array for the entry to be formatted.

		RETURNS --:
				a) A string suitable for displaying the error to the user.

	*/

	$formattedErrorText = "";
	
	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="ENTRY");

	$formattedErrorText = "=================" . $this->disLineFeed;
	$formattedErrorText .= $this->errSevText[$this->errList[$key]['Sev']] . $this->disLineFeed;
	$formattedErrorText .= "----------------" . $this->disLineFeed;
	$formattedErrorText .= $this->errList[$key]['Class'] . $this->disLineFeed;
	$formattedErrorText .= "In Function: " . $this->errList[$key]['Functn'] . $this->disLineFeed;
	$formattedErrorText .= "At Line: " . $this->errList[$key]['Line'] . $this->disLineFeed . $this->disLineFeed;
	$formattedErrorText .= "--Details -->> " . $this->disLineFeed;
	$formattedErrorText .= $this->errList[$key]['Detail'] . $this->disLineFeed;
	$formattedErrorText .= "=================";

	if ($this->DEBUG) $this->writeDebug(__FUNCTION__, $type="EXIT");
	return $formattedErrorText;

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



} // END CLASS

?>
