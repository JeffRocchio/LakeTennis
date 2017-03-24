  <?php
/*
	=======================
	CLASS: simulatedRecordset.
	=======================

	PURPOSE: To provide a means of simulating an open dbms view. Created for
	the autoAction system. Allows me to prototype data tables and views
	before implementing them into the dbms itself. The intent here is to
	look and act as much like the normal recordset class as possible.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:

			1) Goal here is to look and act as much like the normal recordset 
		class as possible, yet provide data that is 'hard coded' vs coming
		from the dbms.


	07/15/2012:	Put into production for Rocchio/Fox group. Both for Tues
					 RSVP request and for Thursday results.
	02/10/2011:	Initial creation as part of building the automated action
					system,

*/



//==============================================================================
//   Include Dependencies
//==============================================================================






//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class simulatedRecordset
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

					//   Contains the MySQL resource pointer to the currently open
					//view.
					//		For simulated data these will be values in the range
					//701-999.
					//			701: autoAction simulated table.
					//			702: autoActionParam simulated table.
	protected $viewRsc = 0;
	
					//		PROPERTIES TO ADD FOR THE TRANSPOSE FUNCTION ....
					//		TRUE if the open recordset has a paramaters detail
					//relationship to another table. For Example, the
					//autoAction and autoActioParm tables.
	protected $detailRel = FALSE;
					//		For master-detail transpose function, what is the
					//query name or SQL string to use to fetch the detail records?
	protected $detailView = "";
					//		For master-detail transpose function, what field in the
					//master view points down to the detail records that
					//contain the additional parameters?
	protected $masterFkFieldName = "";
					//		For master-detail transpose function, what field in the
					//detail view points back to the master record it is associated
					//to?
	protected $detailFkFieldName = "";

					//		PROPERTIES FOR SIMULATION ONLY....			
					//		The ID# of the simulated autoAction master record. Used
					//to generate the correct set of detail records - the
					//autoActionParam records.
	protected $masterID = 0;



	//---GET/SET Functions-------------------------------------------------------
	public function get_rowsAffected() {
	return $this->rowsAffected; }

	public function get_recsRead() {
	return $this->recsRead; }

	public function get_lastOpError() {
	return $this->lastOpError; }

	public function set_detailTranspose($detailView, $masterFkFieldName, $detailFkFieldName) {
	$this->detailView = $detailView;
	$this->masterFkFieldName = $masterFkFieldName;
	$this->detailFkFieldName = $detailFkFieldName;
	$this->detailRel = TRUE;
	return $this->detailRel; }


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

					//		Initilization ---------------------------------------------
	$this->lastOpError = 0;
	$qryResult = FALSE;
					//		Scratch variables.
	$strPos = 0;
	$strLen = 0;
	$debugText = "";
	$capturedID = "";
	
					//		Logic------------------------------------------------------
	switch ($view)
		{
		case 'qryAutoAction':
			$this->viewRsc = 701;
			$this->lastOpError = 0;
			$this->rowsAffected = 1;
			$qryResult = RTN_SUCCESS;
			break;

		case 'qryAutoActionParm':
			$this->viewRsc = 702;
			$this->lastOpError = 0;
			$this->rowsAffected = 7;
			$strPos = strpos($where,"=");
			$strLen = strlen($where);
			$capturedID = substr($where,$strPos+1,$strLen-1-$strPos);
			$this->masterID = (int)$capturedID;
			$qryResult = RTN_SUCCESS;
			if ($objDebug->DEBUG)
				{
				$debugText = "...<i>where clause</i>: \"{$where}\" |";
				$debugText .= "<i>strLenID</i>: {$strLen} |";
				$debugText .= "<i>strPosID</i>: {$strPos} |";
				$debugText .= "<i>capturedID as Text</i>: \"{$capturedID}\" |";
				$debugText .= "<i>this->masterID</i>: {$this->masterID}";
				$objDebug->writeDebug($debugText);
				}
			break;

		default:	//Error - invalid simulated view.
			$this->lastOpError = $objError->RegisterErr(
				ERRSEV_ERROR, 
				ERRCLASS_DBOPEN, 
				__FUNCTION__, 
				__LINE__, 
				"Invalid Simulated View: {$view}.", 
				False);
			$qryResult = RTN_FAILURE;
			$this->viewRsc = 0;
			$this->rowsAffected = 0;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $qryResult;

	} // END METHOD





	//---------------------------------------------------------------------------
	public function getNextRecord(&$recArray)
	{
	/*	PURPOSE: Fetch the next record from the currently open view.

	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$this->lastOpError = 0;
	$returnResult = RTN_FAILURE;
					//		Scratch variables.

					//		Logic------------------------------------------------------
	if ($this->viewRsc <= 0)
		{
		$this->lastOpError = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_OBJDATA, 
			__FUNCTION__, 
			__LINE__, 
			"Trying to read next record, but no view is open.", 
			False);
		$returnResult = RTN_FAILURE;
		}

	$returnResult = $this->makeSimData($this->viewRsc, $recArray);
	if($returnResult == RTN_SUCCESS)
		{
		if($this->detailRel) $returnResult = $this->getColumnTranspose($recArray);
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function getColumnTranspose(&$mstrRecArray)
	{
	/*	PURPOSE: For a rare situation, such as the autoAction system, where we
		have a 'master' table with a 'detail' that contains a variable set of
		parameters for each record in that master. In such a case we want to
		process those detail records as if they were columns added onto the
		master records' column-set in the rowArray returned from normal 
		getNextRecord() function. So, what this function does is get all the
		detail records for a given master record and add them onto the
		current master's row array so that they appear as added columns.

	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$this->lastOpError = 0;
	$returnResult = RTN_FAILURE;
	$currMstrRecFkValue = $mstrRecArray[$this->masterFkFieldName];
	$approach = "A";

					//		Scratch variables.
	$debugText = "";
	$where = "";
	$orderby = "";
	$paramDBArray = array();
	$paramTransposeArray = array();
	$rstParams = new simulatedRecordset();

					//		Logic------------------------------------------------------
	if ((!$this->detailRel) && ($this->viewRsc<=0) && strlen($this->detailView <= 0))
		{
		$this->lastOpError = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_OBJDATA, 
			__FUNCTION__, 
			__LINE__, 
			"Trying to transpose detail records to columns, but detail view specs are invalid.", 
			False);
		$returnResult = RTN_FAILURE;
		}
	else
		{
		$where = "WHERE {$this->detailFkFieldName}={$currMstrRecFkValue}";

		if ($objDebug->DEBUG) $objDebug->writeDebug("...<i>WHERE</i> clause: {$where}");

		$returnResult = $rstParams->openQuery($this->detailView, $where, $orderby);

		while($rstParams->getNextRecord($paramDBArray)<>RTN_EOF)
			{
			$debugText = $objDebug->displayDBRecord($paramDBArray, FALSE);
			//if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
			$paramTransposeArray[$paramDBArray['paramName']] = $paramDBArray['paramValue'];
			switch ($approach)
				{
				case 'A':
					$mstrRecArray[$paramDBArray['paramName']] = $paramDBArray['paramValue'];
					break;
					
				default:
				}
			}
		$debugText = "<i>Simulated Full Master Array with Added Columns</i><BR />";
		$debugText .= "***********************************<BR />";
		$debugText .= $objDebug->displayDBRecord($mstrRecArray, FALSE);
		$debugText .= "<i>...Returning Result: </i>{$returnResult}<BR />";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
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

					//		Initilization ---------------------------------------------
					//		Scratch variables.

					//		Logic------------------------------------------------------
	$this->lastOpError = 0;
	$returnResult = FALSE;
	$this->recsRead = 0;
	$this->rowsAffected = 0;
	$this->viewRsc = 0;
	$returnResult = RTN_SUCCESS;
	$this->dbObjectName = "";
	$this->dbSQL = "";

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD






//==============================================================================
//   PRIVATE FUNCTIONS
//==============================================================================

	//---------------------------------------------------------------------------
	private function makeSimData($simulatedViewID, &$recArray)
	{	
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$returnResult = RTN_FAILURE;


					//		Logic------------------------------------------------------
	
	
	
		if ($objDebug->DEBUG) $objDebug->writeDebug("...<i>simulatedViewID</i>: {$simulatedViewID}");
		switch ($simulatedViewID)
		{
		case 701:	//autoAction table.
			$returnResult = $this->makeSimData_701($recArray);
			break;

		case 702:	//autoActionParams table.
			$returnResult = $this->makeSimData_702($recArray);
			break;

		default:		//Error Condition.
			$this->lastOpError = $objError->RegisterErr(
				ERRSEV_ERROR, 
				ERRCLASS_DBOPEN, 
				__FUNCTION__, 
				__LINE__, 
				"Invalid Simulated View; simulatedViewID: {$simulatedViewID}.", 
				False);
			$returnResult = RTN_FAILURE;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD


	//---------------------------------------------------------------------------
	private function makeSimData_701(&$recArray)
	{	
			//		Router function for making autoAction simulated records.

	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
	$RunningInCron = !isset($_SERVER['HTTP_HOST']);
					//		Scratch variables.
	$returnResult = RTN_FAILURE;


					//		Logic------------------------------------------------------
	switch ($this->recsRead)
		{
		case 0: // Create 1st record. For test club, send to all participants.
			$this->makeSimData_701_rec01($recArray, $RunningInCron);
			$this->masterID = $recArray['ID'];
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 1: // Create 2nd record. For Rocchio/Fox, send to all.
			$this->makeSimData_701_rec02($recArray, $RunningInCron);
			$this->masterID = $recArray['ID'];
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 2: // Create 3rd record. rsvp Update Request emails.
			$this->makeSimData_701_rec03($recArray, $RunningInCron);
			$this->masterID = $recArray['ID'];
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		default:	// Simulate EOF when requesting more records than we are
					//simulating.
			$returnResult = RTN_EOF;
		}
	$this->masterID = $recArray['ID'];

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD



	//---------------------------------------------------------------------------
	private function makeSimData_702(&$recArray)
	{	
			//		Router function for making autoActionParam simulated records.

	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$returnResult = RTN_FAILURE;

					//		Logic------------------------------------------------------
	switch ($this->masterID)
		{
		case 1:	//Create param records that match to the autoAction master ID #1.
			$returnResult = $this->makeSimData_702_rec01($recArray);
			break;

		case 2:	//Create param records that match to the autoAction master ID #2.
			$returnResult = $this->makeSimData_702_rec02($recArray);
			break;

		case 3:	//Create param records that match to the autoAction master ID #3.
			$returnResult = $this->makeSimData_702_rec03($recArray);
			break;

		default:	// Simulate EOF when requesting from non-existant master
			$returnResult = RTN_EOF;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD



//==============================================================================
//   Functions to generate simulated autoAction records
//==============================================================================

	//---------------------------------------------------------------------------
	private function makeSimData_701_rec01(&$recArray, $RunningInCron)
	{
		//		Generate simulated autoAction data-record.
		//		For Record ID 01 : For test club, send weekly rsvp status
		//to all participants.

	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	if ($RunningInCron)
		{
		$recArray['ID'] = 1;
		// $recArray['TriggerFreq'] = 'D'; //Daily.
		$recArray['TriggerFreq'] = 'X'; //Turn test series off with an invalid value here.
		$recArray['TriggerPeriodL1'] = -1; //NA for Weekly (but must exist).
		$recArray['TriggerPeriodL2'] = 3; //Sunday=0.
		$recArray['TriggerPeriodL3'] = 9; //9:00 am.
		$recArray['ClubID'] = 2;
		$recArray['AutoActClassID'] = 63;
		$recArray['ActTitle'] = "TEST Sending RSVP Results";
		$recArray['TrggrObjType'] = 42;
		$recArray['TrggrObjID'] = 50;
		$recArray['Notes'] = "Testing sending of weekly RSVP Results email.";
		$recArray['Notes'] .= " For series Recreational Play (ID #28) in";
		$recArray['Notes'] .= " Demo and Test Club (ID #2 on A2 Host).";
		}
	else
		{
		$seriesID = 50;
			if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 5;
		$recArray['ID'] = 1;
		$recArray['TriggerFreq'] = 'D'; //Daily.
		// $recArray['TriggerFreq'] = 'W'; //Weekly.
		$recArray['TriggerPeriodL1'] = -1; //NA for Weekly (but must exist).
		$recArray['TriggerPeriodL2'] = -1; //Sunday=0. NA for Daily (but must exist).
		$recArray['TriggerPeriodL3'] = idate('H',time()); //In web browser, always run.
		$recArray['ClubID'] = 2;
		$recArray['AutoActClassID'] = 63;
		$recArray['ActTitle'] = "TEST Sending RSVP Results";
		$recArray['TrggrObjType'] = 42;
		$recArray['TrggrObjID'] = $seriesID;
		$recArray['Notes'] = "Simulating sending of weekly RSVP Results email";
		$recArray['Notes'] .= " On Local this is for series Recreational Play (ID #5)";
		$recArray['Notes'] .= " in Test Club (ID #2 on local).";
		$recArray['Notes'] .= " On A2 this is for series";
		$recArray['Notes'] .= " SPRING USTA MEN'S 3.5 TEAM (ID #50)";
		$recArray['Notes'] .= " in Demo and Test Club (ID #2 on local).";
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return;

	} // END METHOD


	//---------------------------------------------------------------------------
	private function makeSimData_701_rec02(&$recArray, $RunningInCron)
	{
		//		Generate simulated autoAction data-record.
		//Record ID 02 : For Rocchio/Fox, send weekly rsvp status to all.

	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	if ($RunningInCron)
		{
		$recArray['ID'] = 2;
		//	$recArray['TriggerFreq'] = 'D'; //Daily.
		$recArray['TriggerFreq'] = 'W'; //Weekly.
		$recArray['TriggerPeriodL1'] = -1; //NA for Weekly (but must exist).
		$recArray['TriggerPeriodL2'] = 4; //Sunday=0.
		$recArray['TriggerPeriodL3'] = 9; //9:00 am.
		$recArray['ClubID'] = 1;
		$recArray['AutoActClassID'] = 63;
		$recArray['ActTitle'] = "Send Rocchio/Fox RSVP Results to all";
		$recArray['TrggrObjType'] = 42;
		$recArray['TrggrObjID'] = 1;
		$recArray['Notes'] = "Sending of weekly RSVP Results email.";
		$recArray['Notes'] .= " For series Recreational Play (ID #1) in";
		$recArray['Notes'] .= " Rocchio/Fox Club (ID #1 on A2 Host).";
		}
	else
		{
		$recArray['ID'] = 2;
		$recArray['TriggerFreq'] = 'D'; //Daily.
		// $recArray['TriggerFreq'] = 'W'; //Weekly.
		$recArray['TriggerPeriodL1'] = -1; //NA for Weekly (but must exist).
		$recArray['TriggerPeriodL2'] = -1; //Sunday=0. NA for Daily (but must exist).
		$recArray['TriggerPeriodL3'] = idate('H',time()); //In web browser, always run.
		$recArray['ClubID'] = 1;
		$recArray['AutoActClassID'] = 63;
		$recArray['ActTitle'] = "Send Rocchio/Fox RSVP Results to all";
		$recArray['TrggrObjType'] = 42;
		$recArray['TrggrObjID'] = 1;
		$recArray['Notes'] = "Sending of weekly RSVP Results email.";
		$recArray['Notes'] .= " For series Recreational Play (ID #1) in";
		$recArray['Notes'] .= " Rocchio/Fox Club (ID #1 on A2 Host).";
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return;

	} // END METHOD


	//---------------------------------------------------------------------------
	private function makeSimData_701_rec03(&$recArray, $RunningInCron)
	{
		//Generate simulated autoAction data-record.
		//Record ID 03 : For sending weekly rsvpStatusUpdate Request email notice.

	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	if ($RunningInCron)
		{
		$recArray['ID'] = 3;
		// $recArray['TriggerFreq'] = 'D'; //Daily.
		$recArray['TriggerFreq'] = 'W'; //Weekly.
		$recArray['TriggerPeriodL1'] = -1; //NA for Weekly or Daily (but must exist).
		$recArray['TriggerPeriodL2'] = 2; //Sunday=0.
		$recArray['TriggerPeriodL3'] = 9; //9:00 am.
		$recArray['ClubID'] = 1;
		$recArray['AutoActClassID'] = AACT_SENDRSVPREQUEST;
		$recArray['ActTitle'] = "Send RSVP Update Request for Rocchio/Fox.";
		$recArray['TrggrObjType'] = OBJSERIES;
		$recArray['TrggrObjID'] = 1;
		$recArray['Notes'] = "Sending weekly RSVP Update Request email.";
		$recArray['Notes'] .= "...Club ID #1 (Rocchio/Fox)";
		$recArray['Notes'] .= "...Series ID #1 (Recreational Play)";
		}
	else
		{
		$seriesID = 1;
			if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 5;
		$recArray['ID'] = 3;
		$recArray['TriggerFreq'] = 'D'; //Daily.
		// $recArray['TriggerFreq'] = 'W'; //Weekly.
		$recArray['TriggerPeriodL1'] = -1; //NA for Weekly or Daily (but must exist).
		$recArray['TriggerPeriodL2'] = -1; //Sunday=0. NA for Daily (but must exist).
		$recArray['TriggerPeriodL3'] = idate('H',time()); //In web browser, always run.
		$recArray['ClubID'] = 1;
		$recArray['AutoActClassID'] = AACT_SENDRSVPREQUEST;
		$recArray['ActTitle'] = "Send RSVP Update Request for Rocchio/Fox";
		$recArray['TrggrObjType'] = OBJSERIES;
		$recArray['TrggrObjID'] = $seriesID;
		$recArray['Notes'] = "Sending weekly RSVP Update Request email";
		$recArray['Notes'] .= "...On Local:";
		$recArray['Notes'] .= "... ...Club ID #2.";
		$recArray['Notes'] .= "... ...Series ID #5 (Recreational Play)";
		$recArray['Notes'] .= "...On A2:";
		$recArray['Notes'] .= "... ...Club ID #1 (Rocchio/Fox)";
		$recArray['Notes'] .= "... ...Series ID #1 (Recreational Play)";
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return;

	} // END METHOD



//==============================================================================
//   Functions to generate simulated autoActionParam records
//==============================================================================

	//---------------------------------------------------------------------------
	private function makeSimData_702_rec01(&$recArray)
	{
		//		Generate simulated autoActionParam data-records.
		//		For Master Record ID 01 : For test club, send weekly rsvp status
		//to all participants.

	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	switch ($this->recsRead)
		{
		case 0: // Create 1st parameter record.
			$recArray['ID'] = 1;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'ToGroup';
			$recArray['paramValue'] = 30; // Send email to all members of the series.
			// $recArray['paramValue'] = 10; // Send email to a specific list. 
													//Do this while in beta test. 
													//See param below for the actual list.
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 1: 	// Create 2nd parameter record. Needed while in beta 
					//test to match up with setting for ToGroup above.
			$recArray['ID'] = 2;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'ToAddresses';
			$recArray['paramValue'] = "rocchio@rocketmail.com, jroc@activeage.com";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 2: 	// Create next parameter record. This defines if we 
					//send as HTML (HTML) or Plain-Text (TEXT) or 
					//Both (BOTH) (Multi-Part/Alternative).
			$recArray['ID'] = 3;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'EmailEncodeFormat';
			$recArray['paramValue'] = "BOTH";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 3: // Create next parameter record.
			$recArray['ID'] = 4;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'EmailSubject';
			$recArray['paramValue'] = "TENNIS - RSVP Results";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 4: // Create parameter record.
			$seriesID = 50;
				if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 5;
			$recArray['ID'] = 5;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'EmailBodyTmplate';
			$recArray['paramValue'] = "RSVPs for Recreational Play this Week:<BR />";
			$recArray['paramValue'] .= "<BR />|%DCbegin rsvpstat {$seriesID} DCend%|";
			$recArray['paramValue'] .= "<BR /><BR /><BR />Useful Links ---<BR />";
			$recArray['paramValue'] .= "<BR />|%DCbegin links S {$seriesID} DCend%|<BR />";
			$recArray['paramValue'] .= "<BR />--";
			$recArray['paramValue'] .= "<BR />This notice sent automatically";
			$recArray['paramValue'] .= " on behalf of Jeff Rocchio";
			$recArray['paramValue'] .= "<BR />REPLY TO: jroc@activeage.com";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 5: // Create parameter record. NOT CURRENTLY USED
			$recArray['ID'] = 6;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'ForEventTypes';
			$recArray['paramValue'] = "05,06,07,09";	//Recreational events.
						//or:
						//	$this->actParams['ForEventTypes'] = "17";	//Matches.
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 6: // Create parameter record.
						//   I also need a param to define which event status
						//types to include, or potentially include, in the notice.
						//E.g., only 'incomplete' events, or maybe I want to 
						//include "Complete - Other" so that some sort of notice
						//that the event does exist, but that no playing will
						//occur is also included.
						//   Also note that it may be worth my adding some add'l
						//status codes to this code set (set #2) to better enable
						//these notifications. E.g., "Bye", "Holiday", "Cancelled."
			$recArray['ID'] = 7;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'ForEventStatus';
			$recArray['paramValue'] = "34";	//Result Code "TBD".
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		default:	// Simulate EOF when requesting more records than we are
					//simulating.
			$returnResult = RTN_EOF;

		} //end switch

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD


	//---------------------------------------------------------------------------
	private function makeSimData_702_rec02(&$recArray)
	{
		//		Generate simulated autoActionParam data-records.
		//		For Master Record ID 02 : For Rocchio/Fox, send weekly rsvp 
		//status only to JRR.

	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	switch ($this->recsRead)
		{
		case 0: // Create 1st parameter record.
			$recArray['ID'] = 10;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'ToGroup';
			$recArray['paramValue'] = 30; // Send email to all members of the series.
			//$recArray['paramValue'] = 10; // Send email to a specific list. 
													//Do this while in beta test. 
													//See param below for the actual list.
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 1: 	// Create 2nd parameter record. Needed while in beta 
					//test to match up with setting for ToGroup above.
			$recArray['ID'] = 20;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'ToAddresses';
			$recArray['paramValue'] = "rocchio@rocketmail.com, jroc@activeage.com";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 2: 	// Create next parameter record. This defines if we 
					//send as HTML (HTML) or Plain-Text (TEXT) or 
					//Both (BOTH) (Multi-Part/Alternative).
					//NOTE: 'BOTH' does not render on iPhone email 
					//clients and I cannot figure out why.
			$recArray['ID'] = 30;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'EmailEncodeFormat';
			$recArray['paramValue'] = "TEXT";
			// $recArray['paramValue'] = "BOTH";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 3: // Create next parameter record.
			$recArray['ID'] = 40;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'EmailSubject';
			$recArray['paramValue'] = "TENNIS - RSVP Results";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 4: // Create parameter record.
			$seriesID = 1;
				if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 1;
			$recArray['ID'] = 50;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'EmailBodyTmplate';
			$recArray['paramValue'] = "";
			$recArray['paramValue'] .= "|%DCbegin textblock 1 1 1 TRUE DCend%|";
			$recArray['paramValue'] .= "<BR />";
			$recArray['paramValue'] .= "<BR />";
			$recArray['paramValue'] .= "RSVPs for Recreational Play this Week.";
			$recArray['paramValue'] .= "<BR />";
			$recArray['paramValue'] .= "<BR />|%DCbegin rsvpstat {$seriesID} DCend%|";
			$recArray['paramValue'] .= "<BR /><BR /><BR />Useful Links ---<BR />";
			$recArray['paramValue'] .= "<BR />|%DCbegin links S {$seriesID} DCend%|<BR />";
			$recArray['paramValue'] .= "<BR />*-*-*-*-*-";
			$recArray['paramValue'] .= "<BR />Sent on Behalf of: Jeff Rocchio";
			$recArray['paramValue'] .= "<BR />For: Rocchio/Fox Tennis Group";
			$recArray['paramValue'] .= "<BR />*-*-*-*-*-";
			$recArray['paramValue'] .= "<BR />This is an automated message. ";
			$recArray['paramValue'] .= "<BR />Questions? Contact Jeff at:";
			$recArray['paramValue'] .= "<BR />jroc@activeage.com";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 5: // Create parameter record. NOT CURRENTLY USED
			$recArray['ID'] = 60;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'ForEventTypes';
			$recArray['paramValue'] = "05,06,07,09";	//Recreational events.
						//or:
						//	$this->actParams['ForEventTypes'] = "17";	//Matches.
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 6: // Create parameter record.
						//   I also need a param to define which event status
						//types to include, or potentially include, in the notice.
						//E.g., only 'incomplete' events, or maybe I want to 
						//include "Complete - Other" so that some sort of notice
						//that the event does exist, but that no playing will
						//occur is also included.
						//   Also note that it may be worth my adding some add'l
						//status codes to this code set (set #2) to better enable
						//these notifications. E.g., "Bye", "Holiday", "Cancelled."
			$recArray['ID'] = 70;
			$recArray['masterID'] = 1;
			$recArray['paramName'] = 'ForEventStatus';
			$recArray['paramValue'] = "34";	//Result Code "TBD".
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		default:	// Simulate EOF when requesting more records than we are
					//simulating.
			$returnResult = RTN_EOF;

		} //end switch.


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD


	//---------------------------------------------------------------------------
	private function makeSimData_702_rec03(&$recArray)
	{
		//		Generate simulated autoActionParam data-records.
		//		For Master Record ID 03 : Weekly rsvpStatusUpdate Request 
		//email notice for Rocchio/Fox.

	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	switch ($this->recsRead)
		{
		case 0: // Create 1st parameter record.
			$recArray['ID'] = 301;
			$recArray['masterID'] = 3;
			$recArray['paramName'] = 'ToGroup';
			$recArray['paramValue'] = 30; // Send email to all members of the series.
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 1: 	// Create 2nd parameter record. DO NOT NEED FOR rsvp Send Update.
			$recArray['ID'] = 302;
			$recArray['masterID'] = 3;
			$recArray['paramName'] = 'ToAddresses';
			$recArray['paramValue'] = "rocchio@rocketmail.com, jroc@activeage.com";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 2: 	// Create next parameter record. This defines if we 
					//send as HTML (HTML) or Plain-Text (TEXT) or 
					//Both (BOTH) (Multi-Part/Alternative).
			$recArray['ID'] = 303;
			$recArray['masterID'] = 3;
			$recArray['paramName'] = 'EmailEncodeFormat';
			$recArray['paramValue'] = "HTML";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 3:
			$recArray['ID'] = 304;
			$recArray['masterID'] = 3;
			$recArray['paramName'] = 'EmailSubject';
			$recArray['paramValue'] = "TENNIS - Schedule and RSVP Declarations";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 4:
			$seriesID = 1;
				if ($_SERVER['HTTP_HOST'] == "tennis") $seriesID = 5;
			$recArray['ID'] = 305;
			$recArray['masterID'] = 3;
			$recArray['paramName'] = 'EmailBodyTmplate';
			$recArray['paramValue'] = "";
			$recArray['paramValue'] .= "|%DCbegin textblock 4 1 1 TRUE DCend%|";
			$recArray['paramValue'] .= "<BR />";
			$recArray['paramValue'] .= "Using the below link, please declare your RSVPs for ";
			$recArray['paramValue'] .= " this week's upcoming Recreational Play:<BR />";
			$recArray['paramValue'] .= "<BR />(<i>NOTE: This is an automated message. Please use";
			$recArray['paramValue'] .= " the supplied link to update your playing intentions.";
			$recArray['paramValue'] .= " Replies to this email will not been seen.</i>)<BR />";
			$recArray['paramValue'] .= "<BR /> > |%DCbegin ITERATE_rsvpUpdateURL {$seriesID} DCend%| < <BR />";
			$recArray['paramValue'] .= "<BR /><BR /><B>Recreational Play Schedule This Week:</B>";
			$recArray['paramValue'] .= "<BR /><BR />|%DCbegin upcomingevents {$seriesID} DCend%|";
			$recArray['paramValue'] .= "<BR /><BR />Useful Links:";
			$recArray['paramValue'] .= "<BR /><BR />|%DCbegin links S {$seriesID} DCend%|<BR />";
			$recArray['paramValue'] .= "<BR />*-*-*-*-*-";
			$recArray['paramValue'] .= "<BR />Sent on Behalf of: Jeff Rocchio";
			$recArray['paramValue'] .= "<BR />For: Rocchio/Fox Tennis Group";
			$recArray['paramValue'] .= "<BR />*-*-*-*-*-";
			$recArray['paramValue'] .= "<BR />This is an automated message, ";
			$recArray['paramValue'] .= "<BR />please do not reply to it.";
			$recArray['paramValue'] .= "<BR />Questions? Contact Jeff at:";
			$recArray['paramValue'] .= "<BR />jroc@activeage.com";
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 5: // Create parameter record. NOT CURRENTLY USED
			$recArray['ID'] = 306;
			$recArray['masterID'] = 3;
			$recArray['paramName'] = 'ForEventTypes';
			$recArray['paramValue'] = "05,06,07,09";	//Recreational events.
						//or:
						//	$this->actParams['ForEventTypes'] = "17";	//Matches.
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		case 6: // Create parameter record.
						//   I also need a param to define which event status
						//types to include, or potentially include, in the notice.
						//E.g., only 'incomplete' events, or maybe I want to 
						//include "Complete - Other" so that some sort of notice
						//that the event does exist, but that no playing will
						//occur is also included.
						//   Also note that it may be worth my adding some add'l
						//status codes to this code set (set #2) to better enable
						//these notifications. E.g., "Bye", "Holiday", "Cancelled."
			$recArray['ID'] = 307;
			$recArray['masterID'] = 3;
			$recArray['paramName'] = 'ForEventStatus';
			$recArray['paramValue'] = "34";	//Result Code "TBD".
			$this->recsRead++;
			$returnResult = RTN_SUCCESS;
			break;

		default:	// Simulate EOF when requesting more records than we are
					//simulating.
			$returnResult = RTN_EOF;

		} //end switch


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD


} // END CLASS


?>
