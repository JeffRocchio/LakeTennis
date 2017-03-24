 <?php
/*
	=======================
	CLASS: eventViewChunks.
	=======================

	PURPOSE: Provide services to produce and format displayable view chunks 
	or snippits or blocks that are related to - driven by - the event 
	table and associated	queries.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) Some of the methods take a recordset object or a dbms row array. 
		In those cases the structure of the data is defined in this classes' 
		attributes. The methods then read the recordset (or record row array),
		using the struction info to generate the view. This way we maintain a 
		high degree of decoupling,	yet we don't have to pass through the 
		recordset data more than once.

	CHANGE LOG ==
	
			02/12/2017: Started coding changes to accomodate 'fully populated
		courts' updates. Added capability to generate RSVP status view that
		lists players in time-stamp (TSfsa) order, and shows the number of
		players. NOTE: I need to add a new 'Event Type' (and thus associated
		configuration field in the Event table) to mark events as 'Fully Populated
		Courts.' I have decided to allow series that can mix event types between
		normal recreational and fully populated courts. Thus, I will need to 
		revise code (where-all I am not yet sure) so that when generating views
		it reads that config value and creates the appropriate view.
	
			01/25/2011:	Initial creation as part of building the automated action
		system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class eventViewChunks
{

		//		Fields returned by a query on RSVP records. The defaults here
		//are based on the 'qrySeriesRsvps' query. If a different query is
		//used to supply the data, then the calling function needs to first
		//set the correct values for the keys.
	protected $fldkeyName = 'prsnFullName';
	protected $fldkeyAvail = 'rsvpClaimCode';
	protected $fldkeyPosition = 'rsvpPositionCode';
	protected $fldkeyRole = 'rsvpRoleCode';
	protected $fldkeyNotes = 'rsvpNote';
	protected $fldkeyBringingTxt = 'rsvpBringingTxt';

		//		Fields returned by a query on Event records. The defaults here
		//are based on the 'qrySeriesEvts' query. If a different query is
		//used to supply the data, then the calling function needs to first
		//set the correct values for the keys.
	protected $fldkeyEvtID = 'evtID';
	protected $fldkeyEvtName = 'evtName';
	protected $fldkeyEvtStart = 'evtStart';
	protected $fldkeyEvtEnd = 'evtEnd';
	protected $fldkeyEvtVenue = 'venueName';
	protected $fldkeyEvtResultCode = 'evtResultCode';
		
	//---GET/SET Functions-------------------------------------------------------
	public function setSpec_fldkeyName($value) {
	$this->fldkeyName = $value; return $this->fldkeyName; }

	public function setSpec_fldkeyAvail($value) {
	$this->fldkeyAvail = $value; return $this->fldkeyAvail; }

	public function setSpec_fldkeyPosition($value) {
	$this->fldkeyPosition = $value; return $this->fldkeyPosition; }

	public function setSpec_fldkeyRole($value) {
	$this->fldkeyRole = $value; return $this->fldkeyRole; }

	public function setSpec_fldkeyNotes($value) {
	$this->fldkeyNotes = $value; return $this->fldkeyNotes; }

	public function setSpec_fldkeyBringingTxt($value) {
	$this->fldkeyNotes = $value; return $this->fldkeyNotes; }

	public function setSpec_fldkeyEvtID($value) {
	$this->fldkeyEvtID = $value; return $this->fldkeyEvtID; }

	public function setSpec_fldkeyEvtName($value) {
	$this->fldkeyEvtName = $value; return $this->fldkeyEvtName; }

	public function setSpec_fldkeyEvtStart($value) {
	$this->fldkeyEvtStart = $value; return $this->fldkeyEvtStart; }

	public function setSpec_fldkeyEvtEnd($value) {
	$this->fldkeyEvtEnd = $value; return $this->fldkeyEvtEnd; }

	public function setSpec_fldkeyEvtVenue($value) {
	$this->fldkeyEvtVenue = $value; return $this->fldkeyEvtVenue; }

	public function setSpec_fldkeyEvtResultCode($value) {
	$this->fldkeyEvtResultCode = $value; return $this->fldkeyEvtResultCode; }



	//---------------------------------------------------------------------------
	public function getRSVPstatString($eventID, &$returnString, $order="NamePublic")
	{
	/*	DEPRACATED. Replaced by the getRSVPstatString_recop() function. Retaining
		for now just to be safe in case this function is called from other places
		I can't remember. But eventually this needs to be removed.
	
		PURPOSE: Generate and return a displayable list of the RSVP status's
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

				1) 2/12/2017 -- The original function was refactored for the
				'Fully Populated Courts' model. This function now determines
				the event type from the CodeSet ID #02 and uses that to call
				the appropriate private function that generates the actual
				RSVP status string.
	
	*/
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	
	$funcResult = false;

					//		Logic------------------------------------------------------

					//		Simply call the new function that makes the default RSVP
					//status string.
	$funcResult = $this->genRSVPstatString_recop($eventID, $returnString, $order);
	

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD


	//---------------------------------------------------------------------------
	public function getRSVPbringingList($eventID, &$returnString)
	{
	/*	PURPOSE: Generate and return a displayable list of what everyone is
		bringing to the event.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) The event is one in which participants are asked to bringing
				something. If nobody is bringing anything then the returned
				string state that.
		
		TAKES --:
		
			1) Event ID.
			2) A pointer to string to hold the displayable HTML list.
				
		RETURNS --:
			
		   1) A String that contains a list of what folks are brining to the 
				event. Will be empty if nobody is bringing anything.
		   2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	global $CRLF;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
					//		Clear out the return string, in case it hasn't been 
					//cleared by the caller.
	$returnString = "";
	$numResponses = 0;
					//   Which name column in the query shall we use?.
	$keyPrsnName = $this->fldkeyName;
					//   Bringing Text field name.
	$keyBringing = $this->fldkeyBringingTxt;
					//   Create needed objects.
	$objRSVP = new rsvp();
	$rstRSVPs = new recordSet();
					//		Initilize Other Variables.
	$rsvpRow = array();
	$errLastErrorKey = 0;
	$numResponses = 0;
	$funcResult = false;
	$returnValue = RTN_SUCCESS;
	$htmlIndent = "&nbsp;&nbsp;&nbsp;*&nbsp;";
	$out = "";

					//		Logic------------------------------------------------------

					//		Make the bringing list....
	$objRSVP->setQrySpec_id($eventID);
	$objRSVP->setQrySpec_infoSet('4Event');
	$objRSVP->setQrySpec_subset('bringingSomething');
	$rstRSVPs = $objRSVP->openRecordset();
	if ($rstRSVPs->get_lastOpError()>0) { // Any error opening the reco?
		$objError->ReportAllErrs(0);
	}
	else {
		while ($rstRSVPs->getNextRecord($rsvpRow) <> RTN_EOF) {
			if (strlen(trim($rsvpRow[$this->fldkeyBringingTxt]))>1) {
				$out .=$htmlIndent;
				$out .=$rsvpRow[$this->fldkeyBringingTxt];
				$out .=" (";
				$out .=$rsvpRow[$keyPrsnName];
				$out .=")";
				$out .="<BR />{$CRLF}";
				$numResponses ++;
			}
		}
		if ($numResponses == 0) {
			$returnString = "";
		}
		else {
			$returnString = $out;
		}
		$rstRSVPs->closeQuery();
	}
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD


	//---------------------------------------------------------------------------
	public function MakeEventHeaderString(&$eventRow, &$returnString, $format='01')
	{
	/*	PURPOSE: Generate and return a displayable header line for a given
		event (meaning the event's title, start date, etc.).

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Caller has set the $fldkeyEvt___ properties that define the
				dbms key's in the passed in array.
		
		TAKES --:
		
			1) An array with the event's dbms row in it.
			2) Pointer to string where result will be put.
			3) $format: Code which allows us to produce different formats for
				the displayable header.
				
		RETURNS --:
			
		   1) A String that contains a displayable event title.
		   2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
	if (!array_key_exists($this->fldkeyEvtID,$eventRow)) {
		$returnString = "No event selected.";
		$this->lastOpError = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_DBOPEN, 
			__FUNCTION__, 
			__LINE__, 
			"No eventID or ID invalid. Therefore no event record fetched.", 
			False);
	}
	else {
		$inEvtID = $eventRow[$this->fldkeyEvtID];
		$inEvtName = $eventRow[$this->fldkeyEvtName];
		$inEvtStart = $eventRow[$this->fldkeyEvtStart];
		$inEvtVenue = $eventRow[$this->fldkeyEvtVenue];
		$funcResult = false;
		$runEnv = Session_ServerHost();
		$server = $runEnv['Host'];
		$returnValue = RTN_SUCCESS;

						//		Scratch variables.
		$dispDate = "";
		$dispTime = "";
		$EventTitle = "";

						//		Logic------------------------------------------------------

		if ($objDebug->DEBUG) $objDebug->writeDebug("...Host URL in variable <i>server</i>: {$server}");

		$dispDate = Tennis_DisplayDate($inEvtStart);
		$dispTime = Tennis_DisplayTime($inEvtStart, True);
		$returnString = "<A HREF='{$server}/tennis/dispEvent.php?ID={$inEvtID}'>";
		$returnString .= "{$inEvtName}</A>, {$dispDate} // ";
		$returnString .= "{$dispTime} at {$inEvtVenue}";

		if ($objDebug->DEBUG) $objDebug->writeDebug("...Event Header Created: {$returnString}");
	}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD


	
//-----------------------------------------------------------------------------
/**
	BRIEF: Make displayable view of RSVPs status for an FPC type of event
	
	DESCRIPTION: Taking in pre-structured data, build a human-readable view of
	the RSVP status, including a list of specific named players, for an event
	of type EVTYPE_RECFULLCOURTS (fully populated courts).

	TAKES --:
		1. Array holding rsvp statistics (as per c_eventRecFPCstatus object).
		2. Array holding list of players (as per c_eventRecFPCstatus object).
		3. Pointer to string where the view text will be written to.
				
	RETURNS --:
	   A String that contains the view to display on a web page or email
		(will be = RTN_FAILURE if an error has occurred)
*/
public function genRSVPstatString_fpc($rsvpStatistics, $rsvpList, &$returnString) {
/*
	ASSUMES:
			A) The c_eventRecFPCstatus object has been used by some controller to 
		generate the pre-structured data arrays needed by this function.
			B) Global error object has been declared.
			C) Event is of type EVTYPE_RECFULLCOURTS = Code ID #65.
		
	NOTES --:

			3/11/2017 -- I have built this version to assume that we *only*
		play doubles, there is no consideration for singles in this version
		at all. I figure that once I figure out how to deal with doubles I
		will then be better able to scope out adding in the add'l complexity
		of handling singles as well.
			
			3/11/2017 -- Having created the new controller class
		c_eventRecFPCstatus(), I reengineered this function
		to consume the arrays from that class (vs hitting the dbms
		directly here) and use the data in those arrays to build out
		the desired status text.
		
		   3/5/2017 -- It *might* be worth considering
		generating both a plain-text version and an HTML formatted
		version. However, the HTML version should use a standard set 
		of CSS styles that would then be mapped to desktop, mobile and 
		email style sheets. So that schema design should probably wait.

			2/12/2017 -- Created this as a new function to be able to
		seperate out RSVP status text formats by different event
		types. This function is expected to be called by some
		controller function.
	
	*/
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

	//   Initilization ------------------------------------------------------------

	//   Declare and Initilize Local Variables
	$errLastErrorKey = 0;
	$funcResult = FALSE;
	$iGroupSize = 0;
	$iRsvpIndex = 0;
	$iFullGroups = 0;
					//		Clear out the return string, in case it hasn't been 
					//cleared by the caller.
	$returnString = "";

	//   Logic--------------------------------------------------------------------

	//   Using the data in the arrays passed in, make the RSVP list....
	//doubles only for now, see the notes under the function declaration.
	//   The method is to use the statistics array to determine how many groups
	//we have, and then use a loop to generate the string for each group. And
	//within that outer loop we use an inner loop to list out the (<=four) names
	//of the players associated to that group (which is by the players' priority
	//rank order; and this does not imply specifically who they will be playing
	//with once everyone has arrived at the courts).

	/* TODO: Augument below to also handle singles players. 
	I am thinking the cleanest way to do this is to put the structured rsvpList 
	data into a three-dimensional array. The 3rd dimension becomes what was my visual 
	representation of 'swiping singles left,' 'swiping doubles right.'
	So in the third dimension 'S' is the index for Singles, 'D' for Doubles.
	*/

					//   IF we have any rsvps for the event then we are building at least
					//one group. Otherwise return a string saying we don't have anyone
					//rsvp'ing as playing.
	if ($rsvpStatistics['numTotalRSVP']>0) {
		$iRsvpIndex = 1; //Index into rsvpList[] starts at 1
		$iFullGroups = $rsvpStatistics['numFullDoublesGroups'];
		if($iFullGroups > 0) {
			$iGroupSize = 4;
			for($g=1; $g<=$iFullGroups; $g++) {
				$returnString .= $this->makeGroupViewFPC($g, $iGroupSize, $iRsvpIndex, $rsvpList, "FULLD");
				$iRsvpIndex += $iGroupSize; //Advance index to next as yet unlisted player.
			}
		}
		$iGroupSize = $rsvpStatistics['numDoublesLeftOver'] + $rsvpStatistics['numTentative'];
		if ($iGroupSize > 0) {
			$returnString .= $this->makeGroupViewFPC(0, $iGroupSize, $iRsvpIndex, $rsvpList, "PARTD");
			$iRsvpIndex += $iGroupSize; //Advance index to next as yet unlisted player.
		}
	}
	else {
		$returnString .= "<BR />&nbsp;&nbsp;&nbsp;*** NO RSVP RESPONSES ***";
	}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

} // END METHOD


	//---------------------------------------------------------------------------
	public function genRSVPstatString_recop($eventID, &$returnString, $order="NamePublic")
	{
	/*	PURPOSE: Generate and return a displayable list of the RSVP status's
		for a given event in a series. This is the normal, default, view; that is
		'Recreational Open Play.'

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Event ID.
			2) Sort specifier (as accepted by the rsvp class)
				
		RETURNS --:
			
		   1) A String that contains a list of RSVP's for the event.
		   2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) 2/12/2017 -- Moved the prior function for this here
					so that I could implement the new Fully Populated Courts 
					event type. This function is, essentially, the original 
					function for generating the normal rec play
					RSVP status text string. This function would normally be
					called by a controller function that has decoded the event
					type. Note, however, that to prevent possible errors I have
					retained the original genRSVPstatString() function, now just
					having it call this function. Eventually that original
					function should be removed.
					
	
	*/
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
					//		Clear out the return string, in case it hasn't been 
					//cleared by the caller.
	$returnString = "";
	$numResponses = 0;
					//   Which name column in the query shall we use?.
	$keyPrsnName = $this->fldkeyName;
					//   Create needed objects.
	$objRSVP = new rsvp();
	$rstRSVPs = new recordSet();
	
					//		Initilize Other Variables.
	$rsvpRow = array();
	$errLastErrorKey = 0;
	$numResponses = 0;
	$funcResult = false;
	$returnValue = RTN_SUCCESS;
	$EventTitle = "";

					//		Logic------------------------------------------------------

					//		Make the RSVP list....

					//		1st - Make list for "Playing" status.
	$objRSVP->setQrySpec_id($eventID);
	$objRSVP->setQrySpec_infoSet('4Event');
	$objRSVP->setQrySpec_sort($order);
	$objRSVP->setQrySpec_subset('claimAVAIL');
	$rstRSVPs = $objRSVP->openRecordset();
	$funcResult = $rstRSVPs->getNextRecord($rsvpRow);
	if (strlen($rsvpRow['prsnPName']) > 0)
		{
		do
			{
			$returnString .= "<BR />&nbsp;&nbsp;&nbsp;*&nbsp;{$rsvpRow[$keyPrsnName]}";
			$numResponses ++;
			}
		while (($rstRSVPs->getNextRecord($rsvpRow))<>RTN_EOF);
		}
	$rstRSVPs->closeQuery();
	
						//   2nd - Make RSVP list for "Late" status.
	$objRSVP->setQrySpec_id($eventID);
	$objRSVP->setQrySpec_infoSet('4Event');
	$objRSVP->setQrySpec_sort($order);
	$objRSVP->setQrySpec_subset('claimLATE');
	$rstRSVPs = $objRSVP->openRecordset();
	$funcResult = $rstRSVPs->getNextRecord($rsvpRow);
	if (strlen($rsvpRow['prsnPName']) > 0)
		{
		do
			{
			$returnString .= "<BR />&nbsp;&nbsp;&nbsp;*&nbsp;will be late> {$rsvpRow[$keyPrsnName]}";
			$numResponses ++;
			}
		while (($rstRSVPs->getNextRecord($rsvpRow))<>RTN_EOF);
		}
	$rstRSVPs->closeQuery();
	
						//   Make RSVP list for "Tentative" status.
	$objRSVP->setQrySpec_id($eventID);
	$objRSVP->setQrySpec_infoSet('4Event');
	$objRSVP->setQrySpec_sort($order);
	$objRSVP->setQrySpec_subset('claimTENT');
	$rstRSVPs = $objRSVP->openRecordset();
	$funcResult = $rstRSVPs->getNextRecord($rsvpRow);
	if (strlen($rsvpRow['prsnPName']) > 0)
		{
		do
			{
			$returnString .= "<BR />&nbsp;&nbsp;&nbsp;*&nbsp;tentative> {$rsvpRow[$keyPrsnName]}";
			$numResponses ++;
			}
		while (($rstRSVPs->getNextRecord($rsvpRow))<>RTN_EOF);
		}
	$rstRSVPs->closeQuery();


	if ($numResponses == 0)
		{
		$returnString .= "<BR />&nbsp;&nbsp;&nbsp;*** NO RSVP RESPONSES ***";
		}
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD




/**
	BRIEF: Build out one group of players for FPC rsvp status views

	TAKES --:
		1. Sequential group number we are building
		2. Number of players in this group (e.g., 2, 4 or if partial group some other number)
		3. Index into the rsvpList array to start from
		4. Pointer to the rsvpList array that holds the players list
		5. Type of group to build: Full/Partial; Singles/Doubles
			...[FULLD | PARTD | FULLS | PARTS]
				
	RETURNS --:
	   A String that contains the view for the requested group
*/
private function makeGroupViewFPC($iGrpNumber, $iGroupSize, $iRsvpIndex, &$rsvpList, $grpType="FULLD") {

	$returnString = "";
	$sGroupHeader = "";
	$sPrePendToName = "<BR />|&nbsp;&nbsp;&nbsp;*&nbsp;";
	$sRsvpStatus = "";
	$lastClaimCode = 0; //Controls line-feed in partical groups to visually seperate
					//players who are avail, late and tenative. Note the tricky code in
					//the 2nd innter switch statement that causes Avail and Confirm to be 
					//treated as if they are the same claimCode for line-feed purposes.

	switch ($grpType) {
		case "FULLD":
			$sGroupHeader = "<BR />---Priority-%#% Doubles Group------------";
			break;
		case "PARTD":
			$sGroupHeader = "<BR />---Potential for Add'l Doubles Group---";
			break;
		case "FULLS":
			$sGroupHeader = "<BR />---Priority-%#% Singles Group------------";
			break;
		case "PARTS":
			$sGroupHeader = "<BR />---Potential for Add'l Singles Group---";
			break;
	}
	$sGroupHeader = str_replace("%#%", $iGrpNumber, $sGroupHeader);
	$returnString = $sGroupHeader;
	$lastClaimCode = $rsvpList[$iRsvpIndex]['rsvpStat'];
	for($i=$iRsvpIndex; $i<$iRsvpIndex+$iGroupSize; $i++) {
		switch ($grpType) {
			case "FULLD":
			case "FULLS":
				$sRsvpStatus = "";
				if($rsvpList[$i]['rsvpStat']==RSVP_CLAIM_LATE) $sRsvpStatus="late >";
				break;
			case "PARTD":
			case "PARTS":
				switch ($rsvpList[$i]['rsvpStat']){
					case RSVP_CLAIM_AVAIL:
						$sRsvpStatus="available >"; 
						if($lastClaimCode==RSVP_CLAIM_CNFRM) $lastClaimCode=RSVP_CLAIM_AVAIL; 
						break;
					case RSVP_CLAIM_CNFRM: 
						$sRsvpStatus="available(c) >"; 
						if($lastClaimCode==RSVP_CLAIM_AVAIL) $lastClaimCode=RSVP_CLAIM_CNFRM; 
						break;
					case RSVP_CLAIM_TENT: $sRsvpStatus="tentative ?>"; break;
					case RSVP_CLAIM_LATE: $sRsvpStatus="late >"; break;
					default: $sRsvpStatus=""; break;
				}
				if ($lastClaimCode<>$rsvpList[$i]['rsvpStat']) $returnString .= "<BR />|";
				break;
		}
		$returnString .= $sPrePendToName . $sRsvpStatus . $rsvpList[$i]['playerFullName'];
		$lastClaimCode = $rsvpList[$i]['rsvpStat'];
	}
	$returnString .= "<BR />"; //Issue a linefeed to close the group
	return $returnString;
	
} // END PRIVATE METHOD


} // END CLASS


?>
