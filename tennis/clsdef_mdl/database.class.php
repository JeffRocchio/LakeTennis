  <?php
/*
	=======================
	CLASS: database.
	=======================
	Include file that defines the database class.

	PURPOSE: To provide an abstraction of the MySQL Database.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:

			1) Goal here is to provide a standard set of dbms functions for 
		opening a query, going to next record, etc. These would then be
		inherited by lower level classes.


	01/18/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class database
{

					//   The name of the table or view being operated on. 
	protected $dbObjectName = "";
	
					//   The SQL string used to open a view or otherwise
					//operate on a set of records. 
	protected $dbSQL = "";
	
					//		Number of records returned, updated, inserted or 
					//		deleted by the last query run.
	protected $rowsAffected = 0;

					//   Number of records read from the currently open view to-date.
	protected $recsRead = 0;

					//   If an error occurred on the last dbms operation, this
					//will contain the key into the error list for it.
	protected $lastOpError = 0;

					//   Contains the MySQL resource pointer to the currently open view.
	protected $viewRsc = 0;
			

	//---GET/SET Functions-------------------------------------------------------
	public function get_rowsAffected() {
	return $this->rowsAffected; }

	public function get_recsRead() {
	return $this->recsRead; }

	public function get_lastOpError() {
	return $this->lastOpError; }


	//---------------------------------------------------------------------------
	public function openQuery($view, $where, $sort, $auth=FALSE, $ObjType=0)
	{
	/*	PURPOSE: Open a MySQL view.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
		
				1) View to open. The value passed in for this must be either:
						a) One of the queries defined in the 
						'INCL_Tennis_Functions_QUERIES.php' file. OR
						b) A syntatically correct MySQL Select query.
				2) An optional Where clause in valid SQL syntax. "WHERE ..."
				3) An optional sort clause in valid SQL syntax. "ORDER BY ..."
				4) $auth: TRUE if the view should join the authority records to it.
				5) $ObjType: If #auth is true, then this param must contain the
			code for which db object authority records need to be joined
			(e.g., Club vs Series, etc).
				
		RETURNS --:
			
		   1) RTN_SUCCESS if successful, RTN_FAILURE if an error has occurred.
		   2) $this->lastOpError will be set to the error key value if an
		   	error has occurred. Otherwise it will be set to 0 (false).
	
		NOTES --:

				1) The $auth feature has not yet been implemented.
	
	*/
	global $objError;
	global $objDebug;


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilize.
	$this->lastOpError = 0;
	$qryResult = FALSE;
	$queryName = "";
	$debugTxt = "";

	$tmp = query_qryGetQuery($view);
	if($auth)
		{
		$query = "SELECT {$view}.*, ";
		$query .= "IF(authority.Privilege,authority.Privilege,0) AS userPriv, ";
		$query .= "Code.LongName ";
		$query .= "FROM {$tmp} ";
		$query .= "LEFT JOIN ";
		$query .= "authority ";
		$query .= "ON authority.ObjType={$ObjType} AND ";
		$query .= "{$view}.ID=authority.ObjID AND ";
		$query .= "authority.Person={$_SESSION['recID']} ";
		$query .= "LEFT JOIN ";
		$query .= "Code ";
		$query .= "ON Code.ID=authority.Privilege";
		}
	else
		{
		$query = "SELECT * ";
		$query .= "FROM {$tmp}";
		}
	if (strlen($where) > 0) $query .= " {$where}";
	if (strlen($sort) > 0) $query .= " {$sort}";
	$query .= ";";
	$this->dbSQL = $query;

	if ($objDebug->DEBUG)
		{
		$debugTxt = "QUERY To Be Executed --:<BR />";
		$debugTxt .= $query;
		$objDebug->writeDebug($debugTxt);
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$temp = "Unable to Open requested view. MySQL Error:<BR />";
		$temp .= mysql_error();
		$temp .= "<BR />QUERY SENT --:<BR />" . $query;
		$this->lastOpError = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			$temp, 
			False);
		$qryResult = RTN_FAILURE;
		$this->viewRsc = 0;
		$this->rowsAffected = 0;
		}
	else
		{
		$this->viewRsc = $qryResult;
		$this->lastOpError = 0;
		$this->rowsAffected = mysql_num_rows($qryResult);
		$debugTxt = "MySql Query Info: <BR />";
		$debugTxt .= "...MySQL Resource ID: {$qryResult}";
		$debugTxt .= " | Rows Returned from Query: {$this->rowsAffected}";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugTxt);
		}

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
		   2) $this->lastOpError will be set to the error key value if an
		   	error has occurred. Otherwise it will be set to 0 (false).
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilize.
	$this->lastOpError = 0;
	$errLastErrorKey = 0;
	$returnResult = FALSE;

	if ($this->viewRsc <= 0)
		{
		$errLastErrorKey = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_OBJDATA, 
			__FUNCTION__, 
			__LINE__, 
			"Trying to read next record, but no view is open.", 
			False);
		$returnResult = RTN_FAILURE;
		}

	if ($objDebug->DEBUG)
		{
		$debugTxt = "MySql Resource Info for GetNextRecord: <BR />";
		$debugTxt .= "...MySQL Resource ID: {$this->viewRsc}";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugTxt);
		}

	$recArray = mysql_fetch_array($this->viewRsc);

	if(!$recArray)
		{
		$returnResult = RTN_EOF;
		}
	else
		{
		$returnResult = RTN_SUCCESS;
		$this->recsRead++;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function closeQuery()
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
		   2) $this->lastOpError will be set to the error key value if an
		   	error has occurred. Otherwise it will be set to 0 (false).
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilize.
	$this->lastOpError = 0;
	$returnResult = FALSE;

	$this->recsRead = 0;
	$this->rowsAffected = 0;
	$this->viewRsc = 0;
	$this->dbObjectName = "";
	$this->dbSQL = "";
	$returnResult = RTN_SUCCESS;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD




	//---------------------------------------------------------------------------
	public function getDBExtensionFunctionList($format="STRING")
	{
	/*	PURPOSE: Generate the list of currently installed and available
					MySQL functions within PHP.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			B) Global debug object has been declared.
		
		TAKES --:
		
			1) $format: String that defines what format to return the list in.
				
		RETURNS --:
			
		   1) Either a String or an array, based on the $format parameter.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	global $CRLF;
	$functArray = array();
	$functDispString = "MySQL Functions Installed --:<BR />";
	$returnVal = NULL;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	$functArray = get_extension_funcs("mysql");
	
	switch ($format)
		{
		case "STRING":
			foreach($functArray as $value)
				{
				$functDispString .= $value . "<BR />";
				}
			$returnVal = $functDispString;
			break;
			
		default:
			$returnVal = $functArray;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnVal;
	
	} // END METHOD



} // END CLASS


?>
