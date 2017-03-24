 <?php
/*
	=======================
	CLASS: series.
	=======================

	PURPOSE: To provide a variety of functions that fetch records and data from
	the series table in the DBMS.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) The way this works is:
				a) You make a series of calls to a 'SetParam()' function to
					put into the object the "specs" for the recordset you want
					to open.
				b)	Once you have set all the spec-params into the object, you
					then call "OpenRecordset()." That function will then have
					a switch statement in it which calls the appropriate
					private function to open the correct recordset object.

	02/11/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class series
{

					//   The name of the primary table this class abstracts info 
					//for (as known by the underlying dbms).
	protected $primeTable = "series";

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
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
	$result = FALSE;
					//		Scratch variables.

					//		Logic------------------------------------------------------
	
	$objRst = new recordset();

	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->ID: {$this->ID}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->infoSet: {$this->infoSet}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->subset: {$this->subset}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...Entering SWITCH Statement");
	switch ($this->infoSet)
		{
		case 'SERIESLIST':
			if ($objDebug->DEBUG) $objDebug->writeDebug("......In Case: SeriesList");
			$result = $this->openRstSeriesList($objRst);
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $objRst;

	} // END METHOD




	//---------------------------------------------------------------------------
	public function openRstSeriesList(&$objRst)
	{
	/*	PURPOSE: Open a MySQL view into the Series table for a given series.

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
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$result = FALSE;
	$drivingID = $this->ID;
	$subset= $this->subset;
	$queryName = $this->primeTable;
	$orderby = "ORDER BY Sort";

					//		Scratch variables.
	$where = "";
	$debugTxt = "";


					//		Logic------------------------------------------------------
					
	switch ($subset)
		{
		case '4CLUB':
				$where = "WHERE (";
				$where .= "ClubID={$drivingID}";
				$where .= ")";
			break;
		
			default:
				$where = "";
		}
	if ($objDebug->DEBUG)
		{
		$debugTxt = "Where Clause Created:<BR />";
		$debugTxt .= $where;
		$objDebug->writeDebug($debugTxt);
		}

	$result = $objRst->openQuery($queryName, $where, $orderby);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	if(!$result) { return RTN_FAILURE; } else { return RTN_SUCCESS; }

	} // END METHOD





	//---------------------------------------------------------------------------
	public function getRecord4ID($ID)
	{
	/*	PURPOSE: Get the whole dbms record for a given series ID.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			B) Global debug object has been declared.
		
		TAKES --:
		
			1) Series ID.
				
		RETURNS --:
			
		   1) An array that contains all the series field values.
		   2) Array index 'ERROR' will be set to RTN_SUCCESS or RTN_FAILURE.
	
		NOTES --:

				1) Attempting to make this as generic as possible so that it
			could be implemented in a parent class as a standard function
			available to all 'dbmsTable' classes.
					(a)	See event.class.php. Made an exact copy of this function
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
	public function getClubID4Series($seriesID)
	{
	/*	PURPOSE: Get the club ID given a series ID.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			B) Global debug object has been declared.
		
		TAKES --:
		
			1) Series ID.
				
		RETURNS --:
			
		   1) ClubID that series is in.
	
		NOTES --:

	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$row = array();
	$clubID = 0;
	$result = 0;


					//		Logic------------------------------------------------------

	$result = Tennis_GetSingleRecord($row, $this->primeTable, $seriesID);
	if(!$result)
		{
		$errLastErrorKey = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			"Unable to open table {$this->primeTable} on dbms.", 
			False);
		$result = 0;
		}
	else
		{
		$result = $row['ClubID'];
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $result;
	
	} // END METHOD


	//---------------------------------------------------------------------------
	public function IsUserParticipant($seriesID, $memRecID)
	{
	/*	PURPOSE: Determine if a specific user is an active participant of a
		give series.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			B) Global debug object has been declared.
		
		TAKES --:
		
			1) Series ID.
			2)	Person's dbms record ID.
				
		RETURNS --:
			
		   1) TRUE if person has an entry in the series-to-person table (this
		   	is the table 'eligible').
	
		NOTES --:
		
			1) A return condition of 'true' here does NOT necessarily mean that
				the person is also currently active on the club. E.g., for league
				play it is common practice to leave person records associated to
				the league series for historical purposes, even long after the
				individual may have dropped out of the club.

	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Scratch variables.
	$dbms = new database;
	
					//		Initilization ---------------------------------------------
	$prsnInSeries = FALSE;


					//		Logic------------------------------------------------------
	$view = "eligible";
	$where = "WHERE (Series={$seriesID} AND Person={$memRecID})";
	$sort = "";
	$auth = FALSE;
	$objType = OBJSERIES;
	$dbms->openQuery($view, $where, $sort, $auth, $objType);
	if ($dbms->get_rowsAffected() > 0) $prsnInSeries = TRUE;

	return $prsnInSeries;

	} // END METHOD



} // END CLASS event


?>
