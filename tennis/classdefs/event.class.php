 <?php
/*
	=======================
	CLASS: event.
	=======================
	Include file that defines the class event.

	PURPOSE: To provide an abstraction of the event DBMS table.

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

	NOTES --:

			1) For simplicity the current design does not support more than one
		dbms query into the events table to be open at once. Should it
		be required at some future point to be able to do that, then the
		proper design will be to define a class that is specific to an
		open view and return an instance of that class for each call into
		$Obj__xx->openView($seriesID, $subset).
				


	01/15/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class event
{

					//   The name of the table this class abstracts, 
					//as known by the underlying dbms.
	protected $table = "Event";
	
					//		Number of records returned, updated, inserted or 
					//		deleted by the last query run.
	public $rowsAffected = 0;

					//   Number of records read from the currently open view to-date.
	public $RecsRead = 0;

					//   Contains the MySQL resource pointer to the currently open view.
	public $viewRsc = FALSE;
			


	//---------------------------------------------------------------------------
	public function getRSVPstatString($eventID, &$returnString)
	{
	/*	PURPOSE: Generate and return a displayable list of the RSVP status's
		for a given event in a series.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Event ID.
				
		RETURNS --:
			
		   1) A String that contains a list of RSVP's for the event.
		   2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	global $CRLF;

	//---DECLARE LOCAL VARIABLES ------------------------------------------------
						//   Which name column in the query shall we use?.
	$keyPrsnName = 'prsnFullName';
						//   Create rsvp object for functions we'll need.
	$objRSVP = new rsvp();

	$rsvpRow = array();
	$errLastErrorKey = 0;
	$numResponses = 0;
	$funcResult = false;
	$returnValue = RTN_SUCCESS;
	$EventTitle = "";
	//---END LOCAL VARIABLES ----------------------------------------------------

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

						//   Do some prep stuff.
	$numResponses = 0;
	$keyPrsnName = 'prsnFullName';

						//   Get the series title, date and time to use has a
						//heading for the RSVP list.
	$EventTitle = $this->getEventTitle($eventID);
	$returnString = $EventTitle . ":<BR />";

						//   Now make the RSVP list itself....
						//   1st - Make list for "Playing" status.
	$funcResult = $objRSVP->openView4Event($eventID, "PLAYING");
	$funcResult = $objRSVP->getNextRecord($rsvpRow);
	if (strlen($rsvpRow['prsnPName']) > 0)
		{
		do
			{
			$returnString .= "&nbsp;&nbsp;&nbsp;*&nbsp;{$rsvpRow[$keyPrsnName]}<BR>{$CRLF}";
			$numResponses ++;
			}
		while (($objRSVP->getNextRecord($rsvpRow))<>RTN_EOF);
		}
	$objRSVP->closeView();
	
						//   2nd - Make RSVP list for "Late" status.
	$funcResult = $objRSVP->openView4Event($eventID, "LATE");
	$funcResult = $objRSVP->getNextRecord($rsvpRow);
	if (strlen($rsvpRow['prsnPName']) > 0)
		{
		do
			{
			$returnString .= "&nbsp;&nbsp;&nbsp;*&nbsp;will be late> {$rsvpRow[$keyPrsnName]}<BR>{$CRLF}";
			$numResponses ++;
			}
		while (($objRSVP->getNextRecord($rsvpRow))<>RTN_EOF);
		}
	$objRSVP->closeView();
	
						//   Make RSVP list for "Tentative" status.
	$funcResult = $objRSVP->openView4Event($eventID, "TENT");
	$funcResult = $objRSVP->getNextRecord($rsvpRow);
	if (strlen($rsvpRow['prsnPName']) > 0)
		{
		do
			{
			$returnString .= "&nbsp;&nbsp;&nbsp;*&nbsp;tentative> {$rsvpRow[$keyPrsnName]}<BR>{$CRLF}";
			$numResponses ++;
			}
		while (($objRSVP->getNextRecord($rsvpRow))<>RTN_EOF);
		}
	$objRSVP->closeView();


	if ($numResponses == 0)
		{
		$returnString .= "&nbsp;&nbsp;&nbsp;*** NO RSVP RESPONSES ***{$CRLF}";
		}
	
	$returnString .= "</P>{$CRLF}{$CRLF}";


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD


	//---------------------------------------------------------------------------
	public function openView($seriesID, $subset="")
	{
	/*	PURPOSE: Open a MySQL view into the Event table for a given series.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
		
			1) Event ID.
			2) $subset: String that defines what sub-set of records to include in
				the view.
				
		RETURNS --:
			
		   1) A MySQL Resource. However, it is strongly recommended to NOT use
		   	this resource, but instead to strictly use the function
		   	$obj_xx->nextRSVPrecord(&$recArray) to retreive records from
		   	the open view.
		   2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	$errLastErrorKey = 0;
	$qryResult = FALSE;
	$where = "";
	$orderby = "ORDER BY evtStart";
	$queryName = "qrySeriesEvts";
	$debugTxt = "";


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	switch ($subset)
		{
		case 'UPCOMING':
				$where = "WHERE (";
				$where .= "{$queryName}.ID={$seriesID} AND ";
				$where .= "(evtResultCode=34 OR evtResultCode=35)";
				$where .= " AND ";
				$where .= "(evtStart>NOW() AND ";
				$where .= "evtStart<=date_add(NOW(), INTERVAL 7 DAY))";
				$where .= ")";
			break;
		
			case 'DON':
				$where = "WHERE ({$queryName}.ID={$seriesID} AND (evtResultCode=36 OR evtResultCode=37 OR evtResultCode=38))";
				break;
		
			case 'FUT':
				$where = "WHERE ({$queryName}.ID={$seriesID} AND (evtResultCode=34 OR evtResultCode=35))";
				break;
		
			case 'PAST':
				$where = "WHERE ({$queryName}.ID={$seriesID} AND (evtResultCode=34 OR evtResultCode=35) AND {$queryName}.evtStart<NOW())";
				break;
		
			default:
				$where = "WHERE ({$queryName}.ID={$seriesID})";
		}
	

	if ($objDebug->DEBUG)
		{
		$debugTxt = "Query Where Clause Built:<BR />";
		$debugTxt .= $where;
		$objDebug->writeDebug($debugTxt);
		}

	if(!$qryResult = Tennis_OpenViewGenericAuth($queryName, $where, $orderby, 43))
		{
		$temp = "Unable to Open requested Event sub-set. MySQL Error:<BR />";
		$temp .= mysql_error();
		$errLastErrorKey = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			$temp, 
			False);
		$qryResult = RTN_FAILURE;
		}

	$this->viewRsc = $qryResult;
	$this->rowsAffected = mysql_num_rows($qryResult);
	if ($objDebug->DEBUG) $objDebug->writeDebug("Rows Returned from Query: {$this->rowsAffected}");

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $qryResult;

	} // END METHOD





	//---------------------------------------------------------------------------
	public function getNextRecord(&$recArray)
	{
	/*	PURPOSE: Fetch the next record from the currently open view.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
			D) An open view exists by virtual of prior call to 
				openView().
		
		TAKES --:
		
			1) Pointer to array where the fetched record will be placed.
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error, and RTN_EOF if past end of
		   	recordset.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	
	$errLastErrorKey = 0;

	$returnResult = FALSE;


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	if (!$this->viewRsc)
		{
		$errLastErrorKey = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_OBJDATA, 
			__FUNCTION__, 
			__LINE__, 
			"Trying to read next Event record, but no Event view is open.", 
			False);
		$returnResult = RTN_FAILURE;
		}

	$recArray = mysql_fetch_array($this->viewRsc);
	if(!$recArray)
		{
		$returnResult = RTN_EOF;
		}
	else
		{
		$returnResult = RTN_SUCCESS;
		$this->RecsRead++;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function closeView()
	{
	/*	PURPOSE: Close the currently open view.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
		
			1) .
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	
	$errLastErrorKey = 0;

	$returnResult = FALSE;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	$this->RecsRead = 0;
	$this->viewRsc = FALSE;
	$returnResult = RTN_SUCCESS;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD




	//---------------------------------------------------------------------------
	public function getEventTitle($eventID)
	{
	/*	PURPOSE: Generate and return a displayable title (name, date, time, venue
		for a given event in a series.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			B) Global debug object has been declared.
		
		TAKES --:
		
			1) Event ID.
				
		RETURNS --:
			
		   1) A String that contains a displayable title for the event. Will
		   	contain "" if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	global $CRLF;
	$row = array();
	$dispDate = "";
	$dispTime = "";
	$dispVenue = "";
	$dispEvtName = "";

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	if(!Tennis_GetSingleRecord($row, 'qryEventDisp', $eventID))
		{
		$errLastErrorKey = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			"Unable to open qryEventDisp view to get event title.", 
			False);
		if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
		return "";
		}
	$dispDate = Tennis_DisplayDate($row['evtStart']);
	$dispTime = Tennis_DisplayTime($row['evtStart'], True);
	$dispVenue = $row['venueShtName'];
	$dispEvtName = $row['evtName'];
	$returnString = "<A HREF='dispEvent.php?ID={$eventID}'>{$dispEvtName}</A>, {$dispDate} // {$dispTime} at {$dispVenue}";

	if ($objDebug->DEBUG) $objDebug->writeDebug($returnString);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;
	
	} // END METHOD



} // END CLASS event


?>
