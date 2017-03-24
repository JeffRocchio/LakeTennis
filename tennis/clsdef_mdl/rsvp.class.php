 <?php
/*
	=======================
	CLASS: rsvp.
	=======================
	
	PURPOSE: To provide an abstraction of the rsvp DBMS table.

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


	02/04/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class rsvp
{

					//   The name of the table this class abstracts, 
					//as known by the underlying dbms.
	protected $primeTable = "rsvp";
	
						//   Params that must be set to specify queries to open.
					//			ID: The record ID that will drive the subset. 
					//		E.g., could be an event or seriesID.
					//			$infoSet: The name of a pre-defined query, as defined
					//		in the switch{} statement within openRecordset().
					//			$subSet: String param that defines which subset of
					//		$infoSet to get records for (e.g., events of 
					//		type "match play."
	protected $infoSet = NULL;
	protected $ID = NULL;
	protected $subset= NULL;
	protected $sort= NULL;



	//---GET/SET Functions-------------------------------------------------------
	public function setQrySpec_id($value) {
	$this->ID = $value; return $this->ID; }

	public function setQrySpec_infoSet($value) {
	$this->infoSet = $value; return $this->infoSet; }

	public function setQrySpec_subset($value) {
	$this->subset = $value; return $this->subset; }

	public function setQrySpec_sort($value) {
	$this->sort = $value; return $this->sort; }



	//---------------------------------------------------------------------------
	public function openRecordset()
	{
	/*	PURPOSE: Open a MySQL view into the rsvp table, based on the object's
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
			
		   1.1) A recordset object with the query open. OR
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
	if ($objDebug->DEBUG) $objDebug->writeDebug("...this->sort: {$this->sort}");
	if ($objDebug->DEBUG) $objDebug->writeDebug("...Entering SWITCH Statement");
	switch ($this->infoSet)
		{
		case '4Event':
			if ($objDebug->DEBUG) $objDebug->writeDebug("......In Case: 4Event");
			$result = $this->openRst4Event($objRst);
		}


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $objRst;

	} // END METHOD




	//---------------------------------------------------------------------------
	public function openRst4Event(&$objRst, $sort="NameLastFirst")
	{
	/*	PURPOSE: Open a MySQL view into the RSVP recordset for a given event.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
			D) Assumes all required query specs have already been into 
				the object-instance variables.
		
		TAKES --:
		
			1) Pointer to Recordset Object.
			2) Sort specifier, which must be one of the values as defined in
				the switch/case statment that sets the SQL sort clause.
				
		RETURNS --:
			
		   1.1) A recordset object with the query open. OR
		   1.2) RTN_FAILURE if an error has occurred.	
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilize.
	$result = FALSE;
	$subset= $this->subset;
	$eventID = $this->ID;
	$orderby = $this->sort;
	$queryName = 'qrySeriesRsvps';
	$where = "";
	$selCrit = "";
	
				//   Gotta work out the sort. For some reason I seem
				//to be all bolixed up about setting the sort. I have it
				//being passed in via a function param, yet it seems it
				//is also, and should be, set into the objRst object that
				//is also passed into this function. Perhaps I have left in
				//the $sort param to preserve some some backward compatability?
				//This will need to be investigated. Meanwhile, I will check
				//to see if there is a string in $this->sort, and if so, I
				//will use that. If not, I'll the passed in param $sort.
	if(strlen($this->sort)>3) $sort=$this->sort;
	switch ($sort) {
		case 'NamePublic':
			$orderby = "ORDER BY prsnPName";
			break;

		case 'NameLastFirst':
			$orderby = "ORDER BY prsnLName, prsnFName";
			break;

		case 'NameFirstLast':
			$orderby = "ORDER BY prsnFName, prsnLName";
			break;

		case 'ClaimCode':
			$orderby = "ORDER BY rsvpClaimCode";
			break;

		case 'TSfsa':
			$orderby = "ORDER BY rsvpTSfsa";
			break;

		default:
			$orderby = "ORDER BY prsnLName, prsnFName";
			break;
		
	}


	switch ($subset)
		{
		case 'claimTENT':
			$selCrit = "rsvpClaimCode=14"; // ="Tentative"
			break;
		
		case 'claimLATE':
			$selCrit = "rsvpClaimCode=13"; // ="Late"
			break;
		
		case 'claimAVAIL':
			// $selCrit = "rsvpPositionCode=29 AND rsvpClaimCode<>13 AND rsvpClaimCode<>14"; // ="Playing"
			$selCrit = "rsvpClaimCode=15 OR rsvpClaimCode=16"; // ="Available" or "Confirmed"
			break;

		case 'PosPLAYING':
			$selCrit = "rsvpPositionCode=29"; // ="Playing"
			break;

		case 'bringingSomething':
			$selCrit = "((rsvpClaimCode=15 OR rsvpClaimCode=13 OR rsvpClaimCode=16)"; // ="Available," "Confirmed" or "Late"
			$selCrit .= " AND ";
			$selCrit .= "(rsvpBringingTxt IS NOT NULL))"; // =bringing something along.
			if (strlen($orderby) <3) $orderby = "ORDER BY prsnLName, prsnFName";
			break;

		default:
			$selCrit = NULL; // ="ALL rsvp records for event.
		}


	$where = "WHERE (evtID={$eventID}";
	if ($objDebug->DEBUG) $objDebug->writeDebug("selCrit={$selCrit}");
	if($selCrit==NULL)
		{
		$where .= ")";
		}
	else
		{
		$where .= "  AND ({$selCrit}))";
		}

	if ($objDebug->DEBUG)
		{
		$debugTxt = "Params To Be Sent to objRst->openQuery() --<BR />";
		$debugTxt .= "...<i>queryName</i>: ";
		$debugTxt .= $queryName;
		$debugTxt .= "<BR />...<i>where</i>: ";
		$debugTxt .= $where;
		$debugTxt .= "<BR />...<i>OrderBy</i>: ";
		$debugTxt .= $orderby;
		$objDebug->writeDebug($debugTxt);
		}

	$result = $objRst->openQuery($queryName, $where, $orderby, TRUE, 43);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	if(!$result) { return RTN_FAILURE; } else { return RTN_SUCCESS; }

	} // END METHOD



} // END CLASS rsvp


?>
