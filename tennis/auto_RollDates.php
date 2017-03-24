<?php
/*
	This script rolls the Event Date values forward so the events then
	appear 1-week after the last current event for the series. It also
	does a 'Reset' of the RSVP values for the event.
	
	This would generally be used with Recreational events, to roll the
	completed events forward to the next cycle.
	
	This script is designed to work with the Automated Actions system
	and the autoActions table.
	
	Bear in mind that when running in CRON we are *not* running on the
	web server, so we don't have access to $_SERVER[] variables like
	$_SERVER['HTTP_HOST'], etc.
	
	Also, when running in CRON we don't have a user logged in. So beware
	the use of $_SESSION[] variables.
	
	Initial work is to just get a decent script working, executing it
	manually. Then we'll figure out how to incorporate it into my vision
	of a container script which will run from a CRON job.
	
	11/20/2011 jrr: ver 1.0.
	   *	I believe I am ready to put this onto A2 and let it rip.
	   	REMEMBER to also copy across all the other files that have
	   	changed - the include files. Check the file dates to be sure.
---------------------------------------------------------------------------- */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
include_once('./INCL_Tennis_CONSTANTS.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


$DEBUG = FALSE;
$DEBUG = TRUE;


//----INITIALIZE GLOBAL VARIABLES---------------------------------------------->
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

				//   For knowing if we are running in Web-Browser vs CRON.
$RunningInCron = TRUE;

				//   Variables to help with outputting status for Web vs CRON.
$LineFeed = "<BR>";
$OpenPara = "<P>";
$ClosePara = "</P>";
$nbSpace = NBSP;



//----DECLARE LOCAL VARIABLES-------------------------------------------------->
$clubID = 0;
$seriesID = 0;

				//   Used to supply a message about the status of the requested
				//action. Generally this is used to pass status messages back
				//from a locally called function. Can be used to output to screen
				//or in CRON email.
$statusMessage = "";
				//   For general use in building text to output to the screen
				//or CRON email.
$outText = "";

				//   For general use in building text to output for debugging
				//purposes.
$debugText = "";

				//   Declare array to hold the autoAction db records.
$Actionrow = array();

				//   General purpose counter.
$i = 0;



//----DETERMINE RUN ENVIRONMENT------------------------------------------------>
$RunningInCron = !isset($_SERVER['HTTP_HOST']);
if ($RunningInCron)
	{
	$LineFeed = LF;
	$OpenPara = LF;
	$ClosePara = LF;
	$nbSpace = " ";
	}


//----CONNECT TO MYSQL--------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo $OpenPara . $lstErrMsg . $ClosePara;
	include './INCL_footer.php';
	exit;
	}


//----OPEN DISPLAY/NOTICE OUTPUT "PAGE"---------------------------------------->
				//   Set page header text and create the page, being sensitive
				//to what run environment we are in.
$tbar = "Tennis Testing autoActions Script";
$pgL1 = "Admin Function";
$pgL2 = "autoActions Script";
$pgL3 = "Rolling Event Dates Forward";
if ($RunningInCron)
	{
	echo "{$tbar}{$LineFeed}";
	echo "{$pgL1}{$LineFeed}";
	echo "{$pgL2}{$LineFeed}";
	echo "{$pgL3}{$ClosePara}";
	}
else
	{
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
	}
				//   With page 'open', output any pending info or notices.
$outText = "NOTE: We are Running In ";
if ($RunningInCron) { $outText .= "CRON."; }
else { $outText .= "WEB BROWSER."; }
echo $OpenPara . $outText . $ClosePara;



//----PRETEND TO READ RECORD FROM autoAction----------------------------------->
/*
	   11/6/2011: Before creating the actual table in the dbms I'll simulate it.
	This will help me both focus on the logic and get a firmer understanding
	of what data elements need to be in the autoAction table.
	
	   So, you know, somewhere in the container script which the CRON job
	   would have fired off we'd open the autoAction table with a:
				$qryResult = Tennis_ViewGeneric($viewName, $where, $sort);

	   Then somewhere in this area we would have read in the next (or first)
	   record in the autoAction table like:
	   		$row = mysql_fetch_array($qryResult);
	   		
	   So the below lines simulate reading in the next record from the
	   autoAction table. Simulating rolling of dates for series 
	   "Recreational Play" (ID #5) in "Test Club" (ID #2 on local).
	   
*/
						//   If running in CRON it means we are running live on
						//the A2 hosted server. In that case we need to use
						//different ClubID and SeriesID.
if ($RunningInCron)
	{
	$Actionrow['ID'] = 1;
	$Actionrow['ClubID'] = 2;
	$Actionrow['AutoActClassID'] = 61;
	$Actionrow['ActTitle'] = "TEST Auto Date Roll";
	$Actionrow['TrggrObjType'] = 42;
	$Actionrow['TrggrObjID'] = 28;
	$Actionrow['Notes'] = "Testing rolling of dates via CRON for series Recreational Play (ID #28) in Demo and Test Club (ID #2 on A2 Host).";

	$Actionrow['ID'] = 2;
	$Actionrow['ClubID'] = 1;
	$Actionrow['AutoActClassID'] = 61;
	$Actionrow['ActTitle'] = "Roll Rocchio/Fox Rec Grid";
	$Actionrow['TrggrObjType'] = 42;
	$Actionrow['TrggrObjID'] = 1;
	$Actionrow['Notes'] = "Rolling of dates for the Rocchio/Fox club, Recreational Playing Grid. (Series ID #1 in Club ID #1 A2).";
	}
else
	{
	$Actionrow['ID'] = 1;
	$Actionrow['ClubID'] = 2;
	$Actionrow['AutoActClassID'] = 61;
	$Actionrow['ActTitle'] = "TEST Auto Date Roll";
	$Actionrow['TrggrObjType'] = 42;
	$Actionrow['TrggrObjID'] = 5;
//	$Actionrow['TrggrObjID'] = 6;
	$Actionrow['Notes'] = "Simulating rolling of dates for series Recreational Play (ID #5) in Test Club (ID #2 on local).";
	}



//----SET NEEDED VARIABLES FROM autoAction RECORD------------------------------>
	$clubID = $Actionrow['ClubID'];
	$actOnID = $Actionrow['TrggrObjID'];


//----DECODE ACTION CLASS AND ROUTE-------------------------------------------->

	switch ($Actionrow['AutoActClassID']) {

		case 61: //Roll Dates
			if(!$resultStatus = local_61_RollDates($actOnID, $statusMessage))
				{
				$lstErrMsg = "Function local_61_RollDates() Failed. {$statusMessage}";
				echo $OpenPara . $lstErrMsg . $ClosePara;
				include './INCL_footer.php';
				exit;
				}
			else
				{
				$outText = "{$nbSpace}{$nbSpace}{$nbSpace}ACTION ID {$Actionrow['ID']} || {$statusMessage}";
				$outText .= "{$LineFeed}Function local_61_RollDates() returned status code: {$resultStatus}";
				echo $OpenPara . $outText . $ClosePara;
				}
			break;
			
		case 62: //Request RSVPs or Availability.
			$lstErrMsg = "Script Not Yet Defined for this Action.";
			echo $OpenPara . $lstErrMsg . $ClosePara;
			include './INCL_footer.php';
			exit;
			break;
			
		case 63: //Send RSVPs or Playing Assignments.
			$lstErrMsg = "Script Not Yet Defined for this Action.";
			echo $OpenPara . $lstErrMsg . $ClosePara;
			include './INCL_footer.php';
			exit;
			break;
			
		default:
			//Output an error.
			$lstErrMsg = "Incorrect (or no) action specified in autoAction record.";
			echo $OpenPara . $lstErrMsg . $ClosePara;
			include './INCL_footer.php';
			exit;

		}


//----CLOSE OUT THE DISPLAY/NOTICE OUTPUT "PAGE"------------------------------->

if ($RunningInCron)
	{
	$outText = "---END JOB";
	echo $OpenPara . $outText . $ClosePara;
	}
else
	{
	echo  Tennis_BuildFooter('ADMIN', "listSeriesRoster.php?ID={$seriesID}");
	}







//------------------------------------------------------------------------------
//====FUNCTIONS ================================================================


function local_61_RollDates($seriesID, &$statusMessage)
	{
	/*
		This function rolls event dates from left to right on the series
		grid.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) Series ID.
		
	RETURNS:
		1) TRUE if success, FALSE otherwise.
		
	*/
	$DEBUG = FALSE;
	$DEBUG = TRUE;
	
	global $CRLF;
	global $link;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;


	$row = array();
	$recID = array();
	$newDTVals = array();
	$DayoWeek = 0;
	$seriesType = "";
	$seriesName =  "";
	$seriesViewLevel =  "";
	$seriesDescript =  "";
	$seriesNotes =  "";
	$NumEvtsToRoll = "";

	$statusMessage = "Action: RollDates. For SeriesID: {$seriesID}.";

	if ($DEBUG) { echo "{$OpenPara}Entering Function: local_61_RollDates(){$ClosePara}"; }


				//   (1) Get series record so we can get needed info.
	if ($DEBUG) { echo "{$OpenPara}Begin Step 1.{$ClosePara}"; }
	if (!$qryResult = Tennis_GetSingleRecord($row, "series", $seriesID))
		{
		$statusMessage = "Failed to open Tennis_GetSingleRecord()";
		return RTN_FAILURE;
		}
	
				//   (2) Extract needed and useful info from the series record.
	if ($DEBUG) { echo "{$OpenPara}Begin Step 2.{$ClosePara}"; }
	$NumEvtsToRoll = $row["EvtsIREmail"];
	$seriesType = $row["Type"];
	$seriesName = $row["LongName"];
	$seriesViewLevel = $row["ViewLevel"];
	$seriesDescript = $row["Description"];
	$seriesNotes = $row["Notes"];
	
	$statusMessage .= " NumEventsToRoll: {$NumEvtsToRoll}.";


				//   (3) Determine which event records to roll, if any. 
				//Store their event IDs in the array $recID().
				//   Also, remember that we have to account for event grouping;
				//that is, for the series, how many 'Events in Reminder Emails' 
				//have been set for the series? We think of this value as a 
				//grouping of events within a week. The variable $NumEvtsToRoll, 
				//gotten earlier, does this.
	if ($DEBUG) { echo "{$OpenPara}Begin Step 3.{$ClosePara}"; }
	if (!$qryResult = Tennis_SeriesEventsOpen($seriesID, 'PAST'))
		{
		$statusMessage = "{$OpenPara}Failed to open Tennis_SeriesEventsOpen(){$ClosePara}";
		return RTN_FAILURE;
		}

//print_r(get_loaded_extensions());
//echo "<P> MYSql Functions: " . print_r(get_extension_funcs("mysql")) . "</P>";
//$i = mysql_affected_rows($link);
//if ($DEBUG) { echo $OpenPara. "msql_num_rows(qryResult): " . get_resource_type($qryResult) . $ClosePara; }
$i = mysql_num_rows($qryResult);
echo "{$OpenPara}Rows Returned: {$i}{$ClosePara}";
	if ($i < $NumEvtsToRoll)
		{
			$statusMessage .= " Result: No Event Qualifies to be Rolled.";
			if ($DEBUG) { echo "{$OpenPara}Exiting Function: local_61_RollDates() with No Action{$ClosePara}"; }
			return RTN_NOACTION;
		}

				//   (4) Calculate new dates for the events to roll. 
				//   And within this procedure let us also check to be sure
				//that we do have at least 1 date eligible to be rolled. If not,
				//then we have an error condition. Test for this is:
				//"if ($recID[0] < 1)"
	if ($DEBUG) { echo "{$OpenPara}Begin Step 4.{$ClosePara}"; }
	for ($i=0; $i<=($NumEvtsToRoll-1); $i++)
		{
		$row = mysql_fetch_array($qryResult);
		$recID[$i] = $row['evtID'];
		if ($recID[0] < 1)
			{
			$statusMessage .= " Result: No Event Qualifies to be Rolled.";
			if ($DEBUG) { echo "{$OpenPara}Exiting Function: local_61_RollDates() with No Action{$ClosePara}"; }
			return RTN_NOACTION;
			}
		$DayoWeek = $row['evtDayofWeek'];
		$newDTVals['Start'] = $row['evtStart'];
		$newDTVals['End'] = $row['evtEnd'];
		if ($DEBUG) { echo "{$OpenPara}BEFORE > newDTVals[Start]: {$newDTVals['Start']}{$LineFeed}newDTVals[End]: {$newDTVals['End']}{$ClosePara}"; }
		local_61_dbFetchNewDate($seriesID, $DayoWeek, $newDTVals);
		if ($DEBUG) { echo "{$OpenPara}AFTER > newDTVals[Start]: {$newDTVals['Start']}{$LineFeed}newDTVals[End]: {$newDTVals['End']}{$ClosePara}"; }
		local_61_dbWriteNewDate($recID[$i], $newDTVals);
		}


	$statusMessage .= " Result: Events Have Been Rolled.";
	if ($DEBUG) { echo "{$OpenPara}Exiting Function: local_61_RollDates() with Success{$ClosePara}"; }
	return RTN_SUCCESS;

} //END FUNCTION





function local_61_dbFetchNewDate($seriesID, $DayofWeek, &$newDTVals)
	{
	/*
		This function determines the next date an event in the series should
		start on, given a day-of-the-week. This function determines the
		latest Event.Start date in the set of Event records for a given
		Series and on a given day-of-the-week, then calculates the date 
		one week out from that.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) Series ID.
		2) Day-Of-Week Index Value (SUN=1, SAT=7).
		
	RETURNS:
		1) New values for Event.Start and Event.End that are 1-week past the
			current latest date in the Event record set for given series
			and given day-of-the-week. There are written to the $newDTVals array.

	*/
	
$DEBUG = FALSE;
$DEBUG = TRUE;

global $CRLF;
global $link;
global $LineFeed;
global $OpenPara;
global $ClosePara;
global $nbSpace;


$row =array();

					//   Build the query.
	$query = "
SELECT 
	Event.ID AS evtID, 
	Event.Name AS evtName, 
	Event.Start AS evtStart, 
	Event.End AS evtEnd, 
	DAYOFWEEK(Event.Start) AS evtDayoWeek, 
	DATE_ADD(Event.Start,INTERVAL 7 DAY) AS evtStartNextWeek, 
	DATE_ADD(Event.End,INTERVAL 7 DAY) AS evtEndNextWeek 
FROM 
	Event 
WHERE 
	Event.Series={$seriesID} 
	AND (DAYOFWEEK(Event.Start)={$DayofWeek})
ORDER BY 
	Event.Start DESC
;";


	if ($DEBUG) { echo "{$OpenPara}Entering Function: local_61_dbFetchNewDate(){$ClosePara}"; }

	if ($DEBUG) { echo "{$OpenPara}<b>query:</b>{$LineFeed}{$query}"; }

					//   Open the list.
	$qryResult = Tennis_OpenViewCustom($query);
	if (!$qryResult) { return FALSE; }

					//   Get first record in list.
	$row = mysql_fetch_array($qryResult);

					//   Get the calculated Date 1-Week-Out from the Event.Start
					//value of the first record in list (list is sorted in
					//decending order so that 1st record is the latest date in
					//the list for the specified weekday).
	if ($DEBUG) { echo "{$OpenPara}BEFORE > {$LineFeed}newDTVals[Start]: {$newDTVals['Start']}{$LineFeed}newDTVals[End]: {$newDTVals['End']}{$ClosePara}"; }
	$newDTVals['Start'] = $row['evtStartNextWeek'];
	$newDTVals['End'] = $row['evtEndNextWeek'];
	if ($DEBUG) { echo "{$OpenPara}AFTER > {$LineFeed}newDTVals[Start]: {$newDTVals['Start']}{$LineFeed}newDTVals[End]: {$newDTVals['End']}{$ClosePara}"; }
	
					//   Return the evtNextWeek value and exit function.
	if ($DEBUG) { echo "{$OpenPara}Exiting Function: local_61_dbFetchNewDate(){$ClosePara}"; }
	return TRUE;

	
} // END FUNCTION


function local_61_dbWriteNewDate($ID, &$newDTVals)
	{
	/*
		This function updates a specified event record with a new Event.Start
		and Event.End value (time value will be preserved) Start and End dates
		will be set to the same value).
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) Event ID.
		
	RETURNS:
		1) TRUE if successful, FALSE otherwise.

	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;
	global $link;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;

	$row =array();
	$ValtblName = 'Event';
	$claimCd = 10; //Code for 'No Response.'

					//   Build the update query.
	$NewStart = $newDTVals['Start'];
	$NewEnd = $newDTVals['End'];
	$query = "UPDATE {$ValtblName} 
		SET Event.Start='{$NewStart}', 
		Event.End='{$NewEnd}' 
		WHERE Event.ID={$ID};";
	if ($DEBUG) { echo "{$OpenPara}QUERY: {$LineFeed}{$query}"; }

					//   Post the Updates.
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= "{$LineFeed}Invalid query: " . mysql_error();
		$GLOBALS['lstErrMsg'] .= "{$LineFeed}{$LineFeed}Query Sent:{$LineFeed}" . $query;
		$message = $GLOBALS['lstErrMsg'];
		return FALSE;
		}

					//   Reset the RSVP values for the Event.
					//(There is a function for this in
					//INCL_Tennis_Functions_ADMIN)
	if (!Tennis_ResetRSVPs($ID, $claimCd))
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= "{$LineFeed}Invalid Update Query: " . mysql_error();
		$GLOBALS['lstErrMsg'] .= "{$LineFeed}{$LineFeed}Query Sent:{$LineFeed}" . $query;
		$message = $GLOBALS['lstErrMsg'];
		return FALSE;
		}
	return TRUE;
	
} // END FUNCTION




?> 
