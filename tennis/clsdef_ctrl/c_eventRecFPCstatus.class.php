 <?php
/*
	=======================
	CLASS: c_eventRecFPCstatus
	=======================

	PURPOSE: For a given event, obtain and calculate the current 
	status of rsvps and who-all is playing, tentative, etc for a
	'Fully Populated Courts' type of event. The primary output is 
	structured data that can then then be used to format displayable
	view of the current status.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) I am viewing this as a 'controller' object in MVC terms. That
		is, a request has been made for the playing status of an event, it has
		been determined (I assume via some other controller) that the event
		in question is of the type 'Fully Populated Courts,' and so then that
		controller is now calling on this controller to interact with the 
		'Model' to obtain the necessary data, make the necessary calculations,
		and thereby produce the data that a vew object can then consume to
		produce a user-display based on the context and medium of that view.
		
			2) 2/26/2017: I am building this to make a step-wise advance in the
		Fully Populated Courts revisions; trying to nudge toward MVC model. I
		believe that this object should become a sub-class under a higher-order
		object named 'eventViewController.' So, for example, fields like
		$eventTitle would be in the parent class. My thought is that a number
		of the functions to get data from the Model, reformat stuff, etc would
		be in the parent so they don't get duplicated over time.

	CHANGE LOG ==
	
			02/26/2017: Initial creation.
*/

//==============================================================================
//---CLASS DEFINITION
//==============================================================================

/**
BRIEF: For a Fully Populated Courts event, provide data on the playing status
 
PURPOSE: For a given event, obtain and calculate the current 
status of rsvps and who-all is playing, tentative, etc for a
'Fully Populated Courts' type of event. The primary output is 
structured data that can then then be used to format displayable
view of the current status.
 */
class c_eventRecFPCstatus {

	protected $databuilt = FALSE;  //True after the data is collected and calculated.

	protected $eventID = 0;
	
	protected $eventTitle = "";
	protected $eventDateTime = 0; //Event start date/time as numeric date/time value.
	protected $eventVenue = "";
	
	protected $eventRSVPlist = array();  //An array holding the list of RSVPs for event.

		//   To keep a count of singles/doubles playing preferences
		//as we read in and process individual rsvp records
	protected $numPefSingles_P = 0;
	protected $numPefSingles_W = 0;
	protected $numPefSingles_U = 0;
	protected $numPefDoubles_P = 0;
	protected $numPefDoubles_W = 0;
	protected $numPefDoubles_U = 0;

		//   Final tablulation of playing results.
	protected $numDoublesPlaying = 0; //These are individual people.
	protected $numSinglesPlaying = 0;
	protected $numDoublesLeftOver = 0;
	protected $numSinglesLeftOver = 0;
	protected $numFullDoublesGroups = 0; //These are # of groups.
	protected $numFullSinglesGroups = 0;

	protected $numOnTime = 0;
	protected $numLate = 0;
	protected $numTentative = 0; //Note that tentatives are not assigned to play.

	
//---GET/SET Functions-------------------------------------------------------

/**
PURPOSE: Return the list of those playing

PARAM: void

RETURNS: Array with list of players (index to 1st player row is 1, not 0)

ASSUMES:
   A)	constructRSVPstatData() has been called already.
*/
public function getRSVPlist() {
	return $this->eventRSVPlist; 
	}

/**
PURPOSE: Return rsvp summary statistics

PARAM: void

RETURNS: Array with all the summary statistics

ASSUMES:
   A)	constructRSVPstatData() has been called already.
*/
public function getRSVPsummaryStats() {
	$stats = array();
	$stats['evtID'] = $this->eventID;
	$stats['evtName'] = $this->eventTitle;
	$stats['evtStart'] = $this->eventDateTime;
	$stats['numPefSingles_P'] = $this->numPefSingles_P;
	$stats['numPefSingles_W'] = $this->numPefSingles_W;
	$stats['numPefSingles_U'] = $this->numPefSingles_U;
	$stats['numPefDoubles_P'] = $this->numPefDoubles_P;
	$stats['numPefDoubles_W'] = $this->numPefDoubles_W;
	$stats['numPefDoubles_U'] = $this->numPefDoubles_U;
	$stats['numSinglesPlaying'] = $this->numSinglesPlaying;
	$stats['numSinglesLeftOver'] = $this->numSinglesLeftOver;
	$stats['numFullSinglesGroups'] = $this->numFullSinglesGroups;
	$stats['numDoublesPlaying'] = $this->numDoublesPlaying;
	$stats['numDoublesLeftOver'] = $this->numDoublesLeftOver;
	$stats['numFullDoublesGroups'] = $this->numFullDoublesGroups;
	$stats['numOnTime'] = $this->numOnTime;
	$stats['numLate'] = $this->numLate;
	$stats['numTentative'] = $this->numTentative;
	$stats['numTotalRSVP'] = $this->numOnTime+$this->numLate+$this->numTentative;
	return $stats; 
	}
	
	
/**
PURPOSE: Making new object instance will also build the data

PARAM: int $eventID | ID of event to get data for

RETURNS: RTN_FAILURE if error has occurred, else RTN_SUCCESS

ASSUMES:
   A)	Connection to DBMS is already open.
   B) Global error object has been declared.
   C) Event is of type EVTYPE_RECFULLCOURTS = Code ID #65.
*/
function __construct($eventID=0) {
	$funcResult = RTN_SUCCESS;
	if($eventID>0) $funcResult = $this->constructRSVPstatData($eventID);
	return $funcResult;
	} // End Method


/**
PURPOSE: Tell object to build the data structures for playing status

PARAM: int $eventID | ID of event to get data for

RETURNS: RTN_FAILURE if error has occurred, else RTN_SUCCESS

ASSUMES:
   A)	Connection to DBMS is already open.
   B) Global error object has been declared.
   C) Event is of type EVTYPE_RECFULLCOURTS = Code ID #65.
*/
public function constructRSVPstatData($eventID)
	{
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$this->eventID = $eventID;
	$numResponses = 0;
	
					//   Create needed objects.
	$objRSVP = new rsvp();
	$rstRSVPs = new recordSet();
	
					//		Initilize Other Variables.
	$rsvpRow = array();
	$errLastErrorKey = 0;
	$numResponses = 0;
	$funcResult = false;
	$returnValue = RTN_SUCCESS;
	$orderby = "";

					//		Logic------------------------------------------------------

					//   Set the order in which we want the names to appear.
					//The sting used here must be one of the 'rsvp' object's sort 
					//specifiers (as defined by the switch/case statement in that
					//class definition).
	$orderby = "TSfsa";

					//		Get the list of RSVPs where some response was made.

					//		1st - Populate list for "Playing" status.
	$objRSVP->setQrySpec_id($eventID);
	$objRSVP->setQrySpec_infoSet('4Event');
	$objRSVP->setQrySpec_sort($orderby);
	$objRSVP->setQrySpec_subset('claimAVAIL');
	$rstRSVPs = $objRSVP->openRecordset();
	$funcResult = $rstRSVPs->getNextRecord($rsvpRow);
	if (strlen($rsvpRow['prsnPName']) > 0)
					//   This way of checking to see if I have
					//seems awfully crude. Doesn't the rstRSVPs
					//object give me this back? I need to check.
					
					//   On the first record, populate the event
					//info properties of this object. NOTE: from
					//the query used I cannot get the event's
					//venue. Probably I don't need to populate 
					//event-level info in this object as, in principle,
					//it would be called from a controller that already
					//has that sort of event descriptive information ??
		$this->eventTitle = $rsvpRow['evtName'];
		$this->eventDateTime = $rsvpRow['evtStart'];
		$this->eventVenue = "Do Not have Event Venue Info Available";
		{
		do
			{
			$numResponses ++;
			$this->loadRSVParrayRow($rsvpRow, $numResponses);
			}
		while (($rstRSVPs->getNextRecord($rsvpRow))<>RTN_EOF);
		}
	$rstRSVPs->closeQuery();
	
						//   2nd - Populate the list for "Late" status.
	$objRSVP->setQrySpec_id($eventID);
	$objRSVP->setQrySpec_infoSet('4Event');
	$objRSVP->setQrySpec_sort($orderby);
	$objRSVP->setQrySpec_subset('claimLATE');
	$rstRSVPs = $objRSVP->openRecordset();
	$funcResult = $rstRSVPs->getNextRecord($rsvpRow);
	if (strlen($rsvpRow['prsnPName']) > 0)
		{
		do
			{
			$numResponses ++;
			$this->loadRSVParrayRow($rsvpRow, $numResponses);
			}
		while (($rstRSVPs->getNextRecord($rsvpRow))<>RTN_EOF);
		}
	$rstRSVPs->closeQuery();
	
	$this->databuilt = TRUE;
						//   Populate the list for "Tentative" status.
	$objRSVP->setQrySpec_id($eventID);
	$objRSVP->setQrySpec_infoSet('4Event');
	$objRSVP->setQrySpec_sort($orderby);
	$objRSVP->setQrySpec_subset('claimTENT');
	$rstRSVPs = $objRSVP->openRecordset();
	$funcResult = $rstRSVPs->getNextRecord($rsvpRow);
	if (strlen($rsvpRow['prsnPName']) > 0)
		{
		do
			{
			$numResponses ++;
			$this->loadRSVParrayRow($rsvpRow, $numResponses);
			}
		while (($rstRSVPs->getNextRecord($rsvpRow))<>RTN_EOF);
		}
	$rstRSVPs->closeQuery();

	
							//   CONSIDER IN FUTURE: Populate the list for "Not Avail" status.
							
							
							//   OK, now make the calculations of how many are playing, and
							//how that translates into how many groups. NOTE that in this
							//first iteration I am making a bunch of simplifying assumptions,
							//I have not implemented the full capability of singles/doubles
							//preferences. I just assume everyone plays doubles, and treat
							//those marked as Late the same as those who will be on time.
	
							//   First, find out how many even groups of four we have.
	$totalAvailToPlay = $this->numOnTime + $this->numLate;
	$this->numFullDoublesGroups = intval($totalAvailToPlay / 4);
	$this->numDoublesLeftOver = $totalAvailToPlay % 4;
	$this->numDoublesPlaying = $this->numFullDoublesGroups * 4;
	$this->numSinglesPlaying = 0;
	$this->numSinglesLeftOver = 0;
	$this->numFullSinglesGroups = 0;
	

	$this->databuilt = TRUE;
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnValue;

	} // END METHOD

	
	
	
/**
PURPOSE: Load one query record into the rsvp list array

PARAM: array $rsvpRow | The array holding the rsvp record returned by the database query
PARAM: int $rowNumber | Index value for array row to load

RETURNS: void
*/
private function loadRSVParrayRow(&$rsvpRow, $rowNumber)
	{
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Logic------------------------------------------------------
					
					//		I am here building in the notion of "Priority Rank Order" for 
					//each row. Meaning, for each person. In this initial implementation
					//this is simply a sorted rank-order based on the initial rsvp
					//timestamp (TSfsa). We are getting this rank ordering 'automatically'
					//due to how we are doing the queries for each rsvpClaim type group.
					//But as some point we may want to make this a score based on some
					//model; like, e.g., accounting for seniority in the group.
	$this->eventRSVPlist[$rowNumber]['priorityRank'] = $rowNumber;
	$this->eventRSVPlist[$rowNumber]['ID'] = $rsvpRow['rsvpID'];
	$this->eventRSVPlist[$rowNumber]['playerFullName'] = $rsvpRow['prsnFullName'];
	$this->eventRSVPlist[$rowNumber]['rsvpStat'] = $rsvpRow['rsvpClaimCode'];
	$this->eventRSVPlist[$rowNumber]['playPosition'] = $rsvpRow['rsvpPositionCode'];
	$this->eventRSVPlist[$rowNumber]['role'] = $rsvpRow['rsvpRoleCode'];
	$this->eventRSVPlist[$rowNumber]['note'] = $rsvpRow['rsvpNote'];
	$this->eventRSVPlist[$rowNumber]['bringing'] = $rsvpRow['rsvpBringingTxt'];
	$this->eventRSVPlist[$rowNumber]['firstTimeStamp'] = $rsvpRow['rsvpTSfsa'];
	$this->eventRSVPlist[$rowNumber]['lastTimeStamp'] = $rsvpRow['rsvpTSlru'];
	
					//   I haven't yet implemented the database field for folks to be
					//able to set their preference for singles or doubles. But in order
					//to have this piece account for to be able to make that enhancement,
					//I am here faking it by saying that everyone 'unwilling to play
					//singles'
	$this->eventRSVPlist[$rowNumber]['prefSingles'] = "U";
	$this->eventRSVPlist[$rowNumber]['prefDoubles'] = "P";
	
					//   Update the counts.
	$this->numPefSingles_U ++;
	$this->numPefDoubles_P ++;
	switch ($this->eventRSVPlist[$rowNumber]['rsvpStat']) {
		case RSVP_CLAIM_AVAIL:
		case RSVP_CLAIM_CNFRM:
			$this->numOnTime ++;
			break;

		case RSVP_CLAIM_LATE:
			$this->numLate ++;
			break;
			
		case RSVP_CLAIM_TENT:
			$this->numTentative ++;
			break;
		}
	
	return;
	
	} // END METHOD





} // End Class


?>
