 <?php
/*
	=======================
	CLASS: txtBlock.
	=======================

	PURPOSE: To provide a variety of functions that fetch records and data from
	the txtBlock table in the DBMS.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:

	02/29/2012: Initial creation.

*/


//==============================================================================
//   CLASS DEFINITION
//==============================================================================

class txtBlock
{


					//   The name of the primary table this class abstracts info 
					//for (as known by the underlying dbms).
	protected $primeTable = "txtBlock";

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
	
	protected $lastOpError = 0;
	


	//---GET/SET Functions-------------------------------------------------------
	public function setQrySpec_id($value) {
	$this->ID = $value; return $this->ID; }

	public function setQrySpec_infoSet($value) {
	$this->infoSet = strtoupper($value); return $this->infoSet; }

	public function setQrySpec_subset($value) {
	$this->subset = strtoupper($value); return $this->subset; }

	public function get_lastOpError() {
	return $this->lastOpError; }


	//---------------------------------------------------------------------------
	public function fetch_Record_byID($ID)
	{
	/*	PURPOSE: Obtain a single txtBlock record, using the record's ID#.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
		
			1) $ID: Record ID of the txtBlock to fetch.
				
		RETURNS --:
			
		   1.1)	An array that contains the record's field values. OR
		   1.2)	FALSE if error has occurred.
		   2)		$this->lastOpError: Will be set to the error key value if an
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
	$row = array();


					//		Logic------------------------------------------------------
	if(!Tennis_GetSingleRecord($row, 'txtBlock', $ID))
		{
		$this->lastOpError = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			"Unable to open txtBlock table to fetch block for ID: {$ID}.", 
			False);
		$row = FALSE;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $row;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function fetch_Record_byWhere($whereClause, $sort="")
	{
	/*	PURPOSE: Obtain a single txtBlock record, using the where clause
		critera.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
		
			1) A string that contains the where clause to use. This must begin
				with "WHERE "
			2) $sort: This is for potential future use. For some situation where
				we may need to specify a sort so that we return the 1st record
				in that sort order vs a random record in a case where the where
				clause does not fully qualify to a single record.
				
		RETURNS --:
			
		   1.1)	An array that contains the record's field values. OR
		   1.2)	FALSE if error has occurred.
		   2)		$this->lastOpError: Will be set to openRst(&$objRst, $queryName, $where, $orderby)the error key value if an
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
	$row = array();
	$qryResult = 0;
	
	$objRst = new recordset();

					//		Logic------------------------------------------------------
	$queryName = $this->primeTable;
	
	$result = $this->openRst($objRst, $queryName, $whereClause, $sort);
	if(!$result=Tennis_OpenViewGeneric('txtBlock', $whereClause, $sort))
		{
		$this->lastOpError = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			"Unable to open txtBlock table to fetch block {$whereClause}.", 
			False);
		$row = FALSE;
		if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
		return $row;
		}

	$result = $objRst->getNextRecord($row);

	if(!$result)
		{
		$this->lastOpError = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			"No txtBlock records match critera: {$whereClause}.", 
			False);
		$row = FALSE;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $row;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function openRecordset()
	{
	/*	PURPOSE: Open a view into the txtBlock table, based on the 
		object's parameters as set by prior calls to setParam().SETQryParam

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
			D) Assumes all required query specs have already been into 
				the object-instance variables.
		
		TAKES --:
		
			1) Nothing. ** See Assumption D above **
				
		RETURNS --:
			
		   1) A Recordset object with the query open if there are no 
		   	errors.
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
	$objRst = new recordset();

					//		Logic------------------------------------------------------

	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->ID: {$this->ID}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->infoSet: {$this->infoSet}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->subset: {$this->subset}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...Entering SWITCH Statement");
	switch ($this->subset)
		{
		case 'ACTIVE':
			if ($objDebug->DEBUG) $objDebug->writeDebug("......In Case: ACTIVE");
			$queryName = $this->primeTable;
			$where = "WHERE ACTIVE=1";
			$orderby = "ORDER BY ID";
			$result = $this->openRst($objRst, $queryName, $where, $orderby);
			break;

		case 'INACTIVE':
			if ($objDebug->DEBUG) $objDebug->writeDebug("......In Case: INACTIVE");
			$queryName = $this->primeTable;
			$where = "WHERE ACTIVE=0";
			$orderby = "ORDER BY ID";
			$result = $this->openRst($objRst, $queryName, $where, $orderby);
			break;
			
		default:
			if ($objDebug->DEBUG) $objDebug->writeDebug("......In Case: default (all)");
			$queryName = $this->primeTable;
			$where = "";
			$orderby = "ORDER BY ID";
			$result = $this->openRst($objRst, $queryName, $where, $orderby);
			break;
		}


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $objRst;

	} // END METHOD




	//---------------------------------------------------------------------------
	public function update_Record($post)
	{
	/*	PURPOSE: updates a single txtBlock record in the database.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
			1) An array that contains the data for each field.
				1.1)	The ['key'] strings of $_POST array exactly match the
						field names in the database.
				1.2)	There MUST be a key=>value for the record ID# that we
						are updating.
				1.3)	Every field in the table must have a key=>value pair in the
						array.
				1.4)	['key'] strings that begin with the sub-string 'meta_'
						are NOT database field entries, but are used for
						other purposes and will be ignored by this function.
				
		RETURNS --:
			
		   1.1)	RTN_SUCCESS if no errors, OR
		   1.2)	RTN_FAILURE if error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$result = RTN_FAILURE;

					//		Scratch variables.
	

					//		Logic------------------------------------------------------

					//		This function does not return a boolean value if it is
					//successful, it returns a text message. So I think I need to 
					//implement a version of it in the database class that is a 
					//bit cleaner.
	$result = Tennis_dbRecordUpdate($post, $this->primeTable);
	if(!$result)
		{
		$this->lastOpError = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			"txtBlock record could not be updated for ID: {$post['ID']}",
			False);
		$result = RTN_FAILURE;
		}
	else 	$result = RTN_SUCCESS;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $result;

	} // END METHOD




	//---------------------------------------------------------------------------
	public function update_BlockStatus($blockID, $newStatus)
	{
	/*	PURPOSE: Sets a value for the Active field of the indicated txtBlock record.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
			1) $blockID: Record ID of the text block to be updated.
			2) $newValue: New value to set into the Active field:
				1.1)	Value is either TRUE (block is active) or FALSE (inactive).
				
		RETURNS --:
			
		   1.1)	RTN_SUCCESS if no errors, OR
		   1.2)	RTN_FAILURE if error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$result = RTN_FAILURE;

					//		Scratch variables.
	$tempArray = array();

					//		Logic------------------------------------------------------

	$tempArray['ID'] = $blockID;
	$tempArray['Active'] = $newStatus;
					//		This function does not return a boolean value if it is
					//successful, it returns a text message. So I think I need to 
					//implement a version of it in the database class that is a 
					//bit cleaner and begin using that new version instead.
	$result = Tennis_dbRecordUpdate($tempArray, $this->primeTable);
	if(!$result)
		{
		$this->lastOpError = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			"txtBlock Active field could not be updated for ID: {$post['ID']}",
			False);
		$result = RTN_FAILURE;
		}
	else 	$result = RTN_SUCCESS;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $result;

	} // END METHOD




	//---------------------------------------------------------------------------
	private function openRst(&$objRst, $queryName, $where, $orderby)
	{
	/*	PURPOSE: Open a MySQL view into the txtBlock table to fetch 
		a list of txtBlock records.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.function Tennis_dbRecordUpdate(&$post, $tblName)
	{
	/*
		This function updates a record in a table
		in the tennis database.
	
	ASSUMES:
		1) Mysql connection is currently open.
		2) The updated data is contained in the global $_POST array,
		   as a result of an edit form having been posted to a
		   page which called this funtion.
		3) The ['key'] strings of $_POST array exactly match the
		   field names in the database.
		4) Every field in the table has a key=>value pair in the
		   $_POST array.
		5) ['key'] strings that begin with the sub-string 'meta_'
		   are NOT database field entries, but are used for
		   other purposes and should be ignored by this function.
	
	TAKES:
		1) A pointer to the $_POST array (even tho this is global).
		2) The name of the table we are to insert into.
		
	RETURNS:
		1) A message indicating the number of records inserted.

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

					//		Scratch variables.
	$returnString = "";


					//		Logic------------------------------------------------------
	$result = $objRst->openQuery($queryName, $where, $orderby);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	if(!$result) { return RTN_FAILURE; } else { return RTN_SUCCESS; }

	} // END METHOD




	//---------------------------------------------------------------------------
	public function determineIfActive(&$recArray)
	{
	/*	PURPOSE: Determine if the text block is currently active. Meaning that
		the text block record is (a) [Active]=Y, (b) [EffStart] >= NOW and
		(c)[EffEnd] < NOW.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
		
			1) &$recArray: Pointer to an array that holds the record to test.
				This would typically be the array that resulted from a call to
				this->fetch_Record_byID($ID) or the like.
				
		RETURNS --:
			
		   1.1)	TRUE if text block is current. OR
		   1.2)	FALSE otherwise.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$result = FALSE;
	$nowDateTime = new DateTime();
	$startDateTime = new DateTime($recArray['EffStart']);
	$endDateTime = new DateTime($recArray['EffEnd']);

					//		Scratch variables.
	$debgTxt = "";
	$gtStart = FALSE;
	$ltEnd = FALSE;

					//		Logic------------------------------------------------------
	$debgTxt = "Current TimeStamp: ";
	$debgTxt .= $nowDateTime->format('Y-m-d H:i:s');
	if ($objDebug->DEBUG) $objDebug->writeDebug($debgTxt);
	$debgTxt = "";

	$result = FALSE;
	if($recArray['Active'])
		{
		$gtStart = ($nowDateTime >= $startDateTime);
		$ltEnd = ($nowDateTime < $endDateTime);
		$result = ($gtStart && $ltEnd);
		$debgTxt = "result= "; if($result) $debgTxt .= "TRUE"; else $debgTxt .= "FALSE";  
		if ($objDebug->DEBUG) $objDebug->writeDebug($debgTxt);
		$debgTxt = "";
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $result;

	} // END METHOD



} // END CLASS


?>
