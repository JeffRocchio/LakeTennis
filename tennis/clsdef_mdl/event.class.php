 <?php
/*
	=======================
	CLASS: event.
	=======================

	PURPOSE: To provide a variety of functions that fetch records and data from
	the event table in the DBMS.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) The this works is:
				a) You make a series of calls to a 'SetParam()' function to
					put into the object the "specs" for the recordset you want
					to open.
				b)	Once you have set all the spec-params into the object, you
					then call "OpenRecordset()." That function will then have
					a switch statement in it which calls the appropriate
					private function to open the correct recordset object.

	01/22/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class event
{

					//   The name of the primary table this class abstracts info 
					//for (as known by the underlying dbms).
	protected $primeTable = "Event";

					//   Params that must be set to specify queries to open.
					//			ID: The record ID that will drive the subset. 
					//		E.g., could be a seriesID.
					//			$infoSet: The name of a pre-defined query, as defined
					//		in the switch{} statement within openRecordset().
					//			$subSet: String param that defines which subset of
					//		$infoSet to get records for (e.g., events of 
					//		type "match play."
	protected $infoSet = NULL;
	protected $ID = NULL;
	protected $subset= NULL;
	


	//---GET/SET Functions-------------------------------------------------------
	public function setQrySpec_id($value) {
	$this->ID = $value; return $this->ID; }

	public function setQrySpec_infoSet($value) {
	$this->infoSet = strtoupper($value); return $this->infoSet; }

	public function setQrySpec_subset($value) {
	$this->subset = strtoupper($value); return $this->subset; }



	//---------------------------------------------------------------------------
	public function openRecordset()
	{
	/*	PURPOSE: Open a MySQL view into the Event table, based on the object's
		parameters as set by prior calls to setParam().SETQryParam

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
			D) Assumes all required query specs have already been into 
				the object-instance variables.
		
		TAKES --:
		
			1) Nothing. ** See Assumption D above **
				
		RETURNS --:
			
		   1.1) A recordset object with the query open if there are now errors.
		   2) $this->lastOpError: Will be set to the error key value if an
		   	error has occurred. Otherwise it will be set to 0 (false).
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	$result = FALSE;


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	$objRst = new recordset();

	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->ID: {$this->ID}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->infoSet: {$this->infoSet}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->subset: {$this->subset}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...Entering SWITCH Statement");
	switch ($this->infoSet)
		{
		case '4SERIES':
			if ($objDebug->DEBUG) $objDebug->writeDebug("......In Case: 4Series");
			$result = $this->openRst4Series($objRst);
		}


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $objRst;

	} // END METHOD




	//---------------------------------------------------------------------------
	public function openRst4Series(&$objRst)
	{
	/*	PURPOSE: Open a MySQL view into the Event table for a given series.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
			D) Assumes all required query specs have already been into 
				the object-instance variables.
		
		TAKES --:
		
			1) Pointer to Recordset Object.
				
		RETURNS --:
			
		   1.1) A recordset object with the query open. OR
		   1.2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	$result = FALSE;
	
	$seriesID = $this->ID;
	$subset= $this->subset;

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
		$debugTxt = "Where Clause Created:<BR />";
		$debugTxt .= $where;
		$objDebug->writeDebug($debugTxt);
		}

	$result = $objRst->openQuery($queryName, $where, $orderby, TRUE, OBJEVENT);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	if(!$result) { return RTN_FAILURE; } else { return RTN_SUCCESS; }

	} // END METHOD





	//---------------------------------------------------------------------------
	public function getRecord4ID($ID)
	{
	/*	PURPOSE: Get the whole dbms record for a given event ID.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			B) Global debug object has been declared.
		
		TAKES --:
		
			1) Event ID.
				
		RETURNS --:
			
		   1) An array that contains all the event field values.
		   2) Array index 'ERROR' will be set to RTN_SUCCESS or RTN_FAILURE.
	
		NOTES --:

				1) Attempting to make this as generic as possible so that it
			could be implemented in a parent class as a standard function
			available to all 'dbmsTable' classes.
					(a)	See series.class.php. Made an exact copy of this function
							in that class.
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$row = array();
	$result = RTN_FAILURE;


					//		Logic------------------------------------------------------

	$result = Tennis_GetSingleRecord($row, $this->primeTable, $ID);
	$row['ERROR'] = RTN_SUCCESS;
	if(!$result)
		{
		$errLastErrorKey = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			"Unable to open table {$this->primeTable} on dbms.", 
			False);
		$row['ERROR'] = RTN_FAILURE;
		}
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $row;
	
	} // END METHOD



	//---------------------------------------------------------------------------
	public function getEventTitle($eventID)
	{
	/*	
		**************************************************************************
		DEPRACATED: Replaced by simular function in eventViewChunks class.
		**************************************************************************
	
		PURPOSE: Generate and return a displayable title (name, date, time, venue
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
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$runEnv = Session_ServerHost();
					//		Scratch variables.
	$row = array();
	$dispDate = "";
	$dispTime = "";
	$dispVenue = "";
	$dispEvtName = "";
	

					//		Logic------------------------------------------------------

	$errLastErrorKey = $objError->RegisterErr(
		ERRSEV_WARNING, 
		ERRCLASS_OTHER, 
		__FUNCTION__, 
		__LINE__, 
		"Function event::getEventTitle() Has Been Depracated.", 
		False);
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return "";


					//		Original, Depracated, Logic--------------------------------

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
	$returnString = "<A HREF='{$runEnv['Host']}/tennis/dispEvent.php";
	$returnString .= "?ID={$eventID}'>{$dispEvtName}</A>,";
	$returnString .= " {$dispDate} // {$dispTime} at {$dispVenue}";

	if ($objDebug->DEBUG) $objDebug->writeDebug($returnString);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;
	
	} // END METHOD



} // END CLASS event


?>
