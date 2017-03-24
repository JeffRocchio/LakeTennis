 <?php
/*
	=======================
	CLASS: eventViewRequests.
	=======================

	PURPOSE: To provide methods for responding to requests for views related to
	events.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) .

	01/29/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class eventViewRequests
{

		
//---GET/SET Functions-------------------------------------------------------


//-----------------------------------------------------------------------------
/**
	BRIEF: Provide displayable view of RSVP status for events in a series
	
	DESCRIPTION: For events upcoming in the next week in a given series, 
	generate and return a displayable view of their RSVP status's, which will
	include the list of specific players.

	TAKES --:
		1. Series ID to gen RSVPs for
		2. Pointer to string where the view text will be written to.
		3. [optional: spec for which events in the series to use]
				
	RETURNS --:
	   A String that contains the view to display on a web page or email
		(will be = RTN_FAILURE if an error has occurred)
*/
public function getPlayingStatus4Series($seriesID, &$returnString, $subset='UPCOMING') {
/*	
	ASSUMES:
			A) Connection to DBMS is already open.
			B) Global error object has been declared.
		
	NOTES --:
			3/11/2017: Revised for FPC. This now first uses the 
		c_eventRecFPCstatus class to obtain the rsvp status data into
		that object, and then used the eventViewChunks object to format
		the rsvp data into the appropriate view.
			1) Rec Play Only: This version of the function assumes that we are 
		generating this list only for recreational play. It does not currently
		support match events; although I do have a 'PosPLAYING' subset
		option built into the rsvp class method openRst4Event(&$objRst)
		which would be the basis for this.
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


	//   Initilization -----------------------------------------------------------

	//   Variable declarations.
	$dbmsRow = array(); // This array holds an event record as we process each event
	$eventTitle = "";
	$rsvpList = "";
	$iEvtCount = 0; //Used to control if we insert a linefeed (<BR>) before
					//the start of the next event we are listing. (I don't want
					//any spacing before or after the block of text for this view,
					//but I do want a blank line between event lists for cases
					//where we are listing for more than 1 event.)
	$rsvpStatistics = array(); 	//| These two arrays will contain the structured rsvp 
	$rsvpList = array(); 			//| data for the event
	$rsvpStatusView = "";  // This string is where the view's formatted output is built.

	$rstEvents = new recordset();
	$objEvent = new event();
	$EvtParticStatusInfo = new c_eventRecFPCstatus();

	//   Logic--------------------------------------------------------------------
					
	$objEvent->setQrySpec_id($seriesID);
	$objEvent->setQrySpec_infoSet('4Series');
	$objEvent->setQrySpec_subset('UPCOMING');
	$rstEvents = $objEvent->openRecordset();
	if ($objDebug->DEBUG) $objDebug->writeDebug("Event Rows Fetched: {$rstEvents->get_rowsAffected()}");
	if($rstEvents->get_rowsAffected()==0) {
		$returnString = "There are no current events open";
	}
	else {
		$viewChunks = new eventViewChunks();
		while ($rstEvents->getNextRecord($dbmsRow)<>RTN_EOF) {
			$viewChunks->MakeEventHeaderString($dbmsRow, $eventTitle);
			$eventTitle = $eventTitle . "<BR />";
			if($iEvtCount>0) $eventTitle = "<BR /><BR /><BR />" . $eventTitle;
			if ($objDebug->DEBUG) $objDebug->writeDebug(var_dump($dbmsRow));
			switch ($dbmsRow['evtPurpose']) {
				case "RECFP":
								//   Get the rsvp data for the event, then have the 
								//viewChunks object format it into a user-readable view.
					$EvtParticStatusInfo->constructRSVPstatData($dbmsRow['evtID']);
					$rsvpStatistics = $EvtParticStatusInfo->getRSVPsummaryStats();
					$rsvpList = $EvtParticStatusInfo->getRSVPlist();
					$viewChunks->genRSVPstatString_fpc($rsvpStatistics, $rsvpList, $rsvpStatusView);
					break;
				
				default:
					$viewChunks->genRSVPstatString_recop($dbmsRow['evtID'], $rsvpStatusView);
					break;
			}
			$returnString .= $eventTitle . $rsvpStatusView;
			$iEvtCount++;
		}
	}
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

} // END METHOD



	//---------------------------------------------------------------------------
	public function getPlayingStatus4Event($eventID, &$returnString, $order="NamePublic", $format='NA')
	{
	/*	PURPOSE: Generate and return a displayable list of the RSVP status's
		for a given event ID.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Event ID.
			2) Pointer to string where the result will be returned.
			3) $format: Not currently in use, but I belive it will prove usedful
				to be able to specify some format options (e.g., if the event is
				in the past do we want to show playing positions vs attendence
				intentions, etc.
				
		RETURNS --:
			
		   1) A String that contains a list of RSVP's for the event.
		   2) RTN_FAILURE if a fatal error has occurred.
	
		NOTES --:

				1) Rec Play Only: This version of the function assumes that we are 
			generating this list only for recreational play. It does not currently
			support match events; although I do have a 'PosPLAYING' subset
			option built into the rsvp class method openRst4Event(&$objRst)
			which would be the basis for this.
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$dbmsRow = array();
	$eventTitle = "";
	$rsvpList = "";
	


					//		Logic------------------------------------------------------

	$objEvent = new event();
	$viewChunks = new eventViewChunks();
	
	$eventRow = array();
	
						//   String to hold the list of names and their rsvp status.
	$rsvpList = "";

						//   Get the event in question, load into an array.
	$eventRow = $objEvent->getRecord4ID($eventID);
	if ($objDebug->DEBUG) $objDebug->writeDebug("ERROR Key in returned Array: {$eventRow['ERROR']}");

	if($eventRow['ERROR'] == RTN_FAILURE) {
		$returnString = "The specified event could not be found in the database (EventID: {$eventID})";
	}
	else  {
		$viewChunks->getRSVPstatString($eventID, $rsvpList, $order);
		$returnString = $rsvpList;
	}
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function getBringingList4Event($eventID, &$returnString)
	{
	/*	PURPOSE: Generate and return a displayable list of what folks are bringing
					to a particular event.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Event ID.
			2) Pointer to string where the result will be returned.
				
		RETURNS --:
			
		   1) A String that contains a displayable list.
		   2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) This, in a way, does not match up to the getPlayingStatus4Series
					function in that this returns the list of what folks are bringing
					only for one specific event. It does NOT make seperate lists for
					all events in a series as the getPlayingStatus4Series function
					does for RSVP status. IF I ever need such a capability - inspect
					the logic of the getPlayingStatus4Series function and copy that
					structure.
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$dbmsRow = array();
	$eventTitle = "";
	$iListItemCount = 0;
	


					//		Logic------------------------------------------------------

	$viewChunks = new eventViewChunks();
	
	$viewChunks->getRSVPbringingList($eventID, $bringingList);
	$returnString = $bringingList;
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function getEventHeaders4Series($seriesID, &$returnString, $subset='UPCOMING')
	{
	/*	PURPOSE: Generate and return a displayable list of events for a given 
					series.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Event ID.
			2) Pointer to string where the result will be returned.
			3) $subset: Defines which sub-set of events within the series to
				report out the rsvps for.
				
		RETURNS --:
			
		   1) A String that contains a list of RSVP's for the event.
		   2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$dbmsRow = array();
	$eventTitle = "";
	$rsvpList = "";
					//		Used to control if we insert a linefeed (<BR>) before
					//the start of the next event we are listing. (I don't want
					//any spacing before or after the block of text for this view,
					//but I do want a blank line between event lists for cases
					//where we are listing for more than 1 event.)
	$iEvtCount = 0;
	$returnString = "";
	


					//		Logic------------------------------------------------------

	$rstEvents = new recordset();
	$objEvent = new event();

	$objEvent->setQrySpec_id($seriesID);
	$objEvent->setQrySpec_infoSet('4Series');
	$objEvent->setQrySpec_subset('UPCOMING');
	$rstEvents = $objEvent->openRecordset();
	if ($objDebug->DEBUG) $objDebug->writeDebug("Event Rows Fetched: {$rstEvents->get_rowsAffected()}");
	if($rstEvents->get_rowsAffected()==0)
		{
		$returnString = "There are no current events open";
		}
	else
		{
		$viewChunks = new eventViewChunks();
		$returnString = "";
		while ($rstEvents->getNextRecord($dbmsRow)<>RTN_EOF)
			{
			$viewChunks->MakeEventHeaderString($dbmsRow, $eventTitle);
			if($iEvtCount>0) $eventTitle = "<BR /><BR />" . $eventTitle;
			if ($objDebug->DEBUG) $objDebug->writeDebug("Event Name: {$eventTitle}");
			$returnString .= $eventTitle;
			$iEvtCount++;
			}
		}
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD



} // END CLASS event


?>
