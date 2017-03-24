 <?php
/*
	=======================
	CLASS: autoAction.
	=======================

	PURPOSE: To provide a variety of functions that fetch records and data from
	the autoAction table in the DBMS.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
			1)	For purposes of this class, I am treating the dbms tables 
				autoAction and autoActionParam as if they were just one single
				table. The reasoning is that, logically, the Params table is
				thought of as a set of columns added onto any given autoAction
				record.
				a)	I might want to consider creating a new class specifically
					to handle what I might call a 'denormalized recordset.' Such
					a class would automatically fetch each individual param
					record that is associated to the master record and present
					it out as added on members of the master records' row array.

			2) HOWEVER: For now I am simulating the dbms tables with
				hard-coded data. I want to get my understanding of the data
				requirements stable before implementing into the dbms.
				a)	So I am using simulatedRecordset vs the standard
					recordset class.

			3) The way this works is:
				a) You make a series of calls to a 'SetParam()' function to
					put into the object the "specs" for the recordset you want
					to open.
				b)	Once you have set all the spec-params into the object, you
					then call "OpenRecordset()." That function will then have
					a switch statement in it which calls the appropriate
					private function to open the correct recordset object.

	12/10/2011: Initial creation.

*/



//==============================================================================
//   Include Dependencies
//==============================================================================
					//		The below include can be removed once we are done with
					//the simulated approach and have implemented the
					//autoAction tables into the dbms. At that point use the
					//normal recordset class.



//==============================================================================
//   CLASS DEFINITION
//==============================================================================

class autoAction
{


					//   The name of the primary table this class abstracts info 
					//for (as known by the underlying dbms).
	protected $primeTable = "autoAction";

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
	/*	PURPOSE: Open a simulated view into the autoAction table, based on the 
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
			
		   1) A simulatedRecordset object with the query open if there are no 
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
	$objRst = new simulatedRecordset();

					//		Logic------------------------------------------------------

	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->ID: {$this->ID}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->infoSet: {$this->infoSet}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->subset: {$this->subset}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...Entering SWITCH Statement");
	switch ($this->infoSet)
		{
		case 'NOTICES':
			if ($objDebug->DEBUG) $objDebug->writeDebug("......In Case: NOTICES");
			$result = $this->openRst4Notices($objRst);
			$objRst->set_detailTranspose("qryAutoActionParm", "ID", "masterID");
			break;

		case 'PARAMS':
			if ($objDebug->DEBUG) $objDebug->writeDebug("......In Case: PARAMS");
			$result = $this->openRst4Params($objRst);
		}


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $objRst;

	} // END METHOD


	//---------------------------------------------------------------------------
	private function openRst4Notices(&$objRst)
	{
	/*	PURPOSE: Open a MySQL view into the autoAction table to fetch automated
		notice requests.

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
					//		Scratch variables.
	$returnString = "";


					//		Logic------------------------------------------------------

					//		These will be needed for real once we have implemented
					//the autoAction tables into the DBMS. For now thise are
					//just simulated placeholders.
	$where = "";
	$orderby = "ORDER BY autoID";
	$queryName = "qryAutoAction";


	$result = $objRst->openQuery($queryName, $where, $orderby);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	if(!$result) { return RTN_FAILURE; } else { return RTN_SUCCESS; }

	} // END METHOD



	//---------------------------------------------------------------------------
	private function openRst4Params(&$objRst)
	{
	/*	PURPOSE: Open a MySQL view into the autoActionParam table to fetch 
		automated notice request parameter values.

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
					//		Scratch variables.
	$returnString = "";


					//		Logic------------------------------------------------------

					//		These will be needed for real once we have implemented
					//the autoAction tables into the DBMS. For now thise are
					//just simulated placeholders.
	$where = "WHERE autoID={$this->ID}";
	$orderby = "ORDER BY autoID";
	$queryName = "qryAutoActionParm";


	$result = $objRst->openQuery($queryName, $where, $orderby);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	if(!$result) { return RTN_FAILURE; } else { return RTN_SUCCESS; }

	} // END METHOD





} // END CLASS


?>
