<?php
/*
	This script rolls the Event Date values forward so the events then
	appear 1-week after the last current event for the series. It also
	does a 'Reset' of the RSVP values for the event.
	
	This is for use with the Recreational events, to roll the
	completed events forward to the next cycle.
	
	12/30/2007 jrr: ver 1.0.
---------------------------------------------------------------------------- */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


$DEBUG = FALSE;
$DEBUG = TRUE;

$CRLF = "\n";


//----INITIALIZE GLOBAL VARIABLES--------------------------------------------->
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------------->
$clubID=$_SESSION['clubID'];
$tblName = 'qryEventDisp';

				//   Declare array to hold the detail display record.
$row = array();
$newDTVals = array();
$recID = array();


//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----GET URL QUERY-STRING DATA----------------------------------------------->
				//   Determine which event records to roll.
				//Either the IDs were passed in via query
				//sting in the URL, or else the query string
				//only specified how many of the next scheduled
				//events to roll forward.
if (array_key_exists('ID', $_GET))
	{
	if ($_GET['ID'] > 0)
		{
		$recID[1] = $_GET['ID'];
		$byRecID = True;
						//   Two other events we can build the list for.
		if ($_GET['ID2'] > 0) $recID[2] = $_GET['ID2'];
		if ($_GET['ID3'] > 0) $recID[3] = $_GET['ID3'];
		}
	}
elseif (array_key_exists('NUM', $_GET))
	{
	if ($_GET['NUM'] > 0)
		{
		$NumEvts = $_GET['NUM'];
		$seriesID = $_GET['SID'];
		if (!$qryResult = Tennis_SeriesEventsOpen($seriesID, 'FUT'))
			{
			echo "<P>{$lstErrMsg}</P>";
			include './INCL_footer.php';
			exit;
			}
		for ($i=0; $i<=($NumEvts-1); $i++)
			{
			$row = mysql_fetch_array($qryResult);
			$recID[$i] = $row['evtID'];
			}
		}
	}

//----DETERMINE IF POSTING---------------------------------------------------->
				//   Determine if we are doing the initial confirm or the
				//actual posting.
if (array_key_exists('meta_POST', $_POST) and $_POST['meta_POST'] == 'TRUE')
				//   We've confirmed the roll-forward, so do it.
	{
	//----POST ROLL-FORWARD---------------------------------------------------->

				//   Output page header stuff.
	$tbar = "Tennis Events Rolling Forward";
	$pgL1 = "Edit Events";
	$pgL2 = "";
	$pgL3 = "Rolling Events Forward";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
	
				//   Be sure user has rights to do this.
	if (!local_AuthorizeUser($_POST['meta_SERIESID'])) { exit; }

				//   Process each Event record processed in the
				//Display Confirming Data routine.
	$recCt = $_POST['RecCount'];
	for ($i=1; $i<=$recCt; $i++)
		{
		$newDTVals['Start'] = $_POST["Start{$i}"];
		$newDTVals['End'] = $_POST["End{$i}"];
		$ID = $_POST["ID{$i}"];
		local_dbWriteNewDate($ID, $newDTVals);
		}
				//   Show 'OK' link.
	echo "<P>Events Rolled Forward.<BR>";
	echo "Click OK to continue.</P>";
	echo "<P STYLE='font-size: large'>";
	echo "<A HREF='{$_SESSION['RtnPg']}'>OK</A></P>";
	}

else
				//   We're not posting, we're figuring out the info we need to
				//roll the events. Show that to the user for a confirmation. And
				//permit user to modify dates/times on the events we are going
				//to roll. Store the needed data into a form and give the user
				//a 'Submit' button to fire off the posting.
	{

	//----DISPLAY CONFIRMING DATA---------------------------------------------->
					
					//   Output page header stuff.
	$tbar = "Tennis Events to Roll Forward";
	$pgL1 = "Edit Events";
	$pgL2 = "";
	$pgL3 = "Events to Roll Forward";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
				
				//   Check to be sure that we have at least one event ID. 
if ($recID[0] < 1)
	{
	echo "<P>ERROR, No Event Selected.</P>";
	include './INCL_footer.php';
	exit;
	}
	
				//   Open an HTML form so we can record values to pass to the
				//posting routine and make a 'SAVE' button for the use to
				//click on.
	echo "<form method='post' action='editEvent_RollDates.php'>";
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";
	echo "<input type=hidden name=meta_POST value=TRUE>";
	echo "<input type=hidden name=meta_TBL value=Event>";

				//   Process each Event.
				//   Use a counter to build form-fields holding the data for
				//each Event record we process - to pass to the Post routine.
	$k=0;
	foreach ($recID as $rID)
		{
		$k++;
					//   Get Event Record(s).
		if(!Tennis_GetSingleRecord($row, $tblName, $rID))
			{
			echo "<P>{$lstErrMsg}</P>";
			include './INCL_footer.php';
			exit;
			}
					//   Get the Series ID so we can use it later.
		$seriesID = $row['seriesID'];
					//   Build event list - in plain-text form.
		$dispDate = Tennis_DisplayDate($row['evtStart']);
		$DayoWeek = $row['evtDayofWeek'];
		$dispTime = Tennis_DisplayTime($row['evtStart'], True);
		$dispVenue = $row['venueShtName'];
		$dispEvtName = $row['evtName'];
		$dispTitle = "<A HREF='dispEvent.php?ID={$rID}'>{$dispEvtName}</A>,";
		$dispTitle .= " {$dispDate} at {$dispVenue}:";

		$newDTVals['Start'] = $row['evtStart'];
		$newDTVals['End'] = $row['evtEnd'];
		local_dbFetchNewDate($seriesID, $DayoWeek, $newDTVals);
		$dispNewDate = Tennis_DisplayDate($newDTVals['Start']);
		$dispNewTime = Tennis_DisplayTime($newDTVals['Start'], True);

		$out = "<P>&nbsp;&nbsp;&nbsp;{$dispEvtName}<BR>{$CRLF}";
		$out .= "&nbsp;&nbsp;&nbsp;{$dispVenue}<BR>{$CRLF}";
		$out .= "&nbsp;&nbsp;&nbsp;{$dispDate} ({$DayoWeek})";
		$out .= " // {$dispTime}<BR>{$CRLF}";

		$out .= "CHANGE TO--------------------<BR>{$CRLF}";
					//   Build event ID, Start, and End fields into the form
					//to pass to the Post call.
		echo "<input type=hidden name=ID{$k} value={$rID}>";
					//   Have to replace the black space in date/time string
					//with HTML blank-space character code, or else the form-filed
					//truncates the value at the blank space between the date
					//part and the time part.
		$tmp=substr_replace($newDTVals['Start'],"&#x0020;", 10, 1);
		$out .= "&nbsp;&nbsp;&nbsp;Start:<BR><input type=text name=Start{$k}";
		$out .= " size=20 maxlength=20 value={$tmp}><BR>{$CRLF}";
		$tmp=substr_replace($newDTVals['End'],"&#x0020;", 10, 1);
		$out .= "&nbsp;&nbsp;&nbsp;End:<BR><input type=text name=End{$k}";
		$out .= " size=20 maxlength=20 value={$tmp}><BR><BR>{$CRLF}";

		echo $out;
		}

					//   Close out the table. Put a field in the form that
					//will tell us how many Event records we processed.
	echo "<input type=hidden name=RecCount value={$k}>";
	echo "<input type=hidden name=meta_SERIESID value={$seriesID}>";
	echo "<input type='submit' value='ROLL EVENTS'>";
	echo "</td>{$CRLF}</tr>{$CRLF}";

	echo "</form>{$CRLF}";


}  // END of POST or DISPLAY If-Then Construct.

	


//----CLOSE OUT THE PAGE------------------------------------------------------>

$out = "<P><BR><BR>Useful Links:<BR>{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='listSeriesRoster.php";
$out .= "?ID={$seriesID}'>Full RSVP Grid</A><BR>{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='dispSeries.php";
$out .= "?ID={$seriesID}'>Recreational Play Notes</A><BR>{$CRLF}";
$hreftxt = "http://laketennis.com";
if ($_SESSION['clubID'] <> 1)
	{
	$hreftxt = "http://laketennis.com/ClubHome.php?ID={$_SESSION['clubID']}";
	}
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='{$hreftxt}'>Club Home Page</A>";
$out .= "<BR>{$CRLF}";
$out .= "</P>{$CRLF}";
echo $out;

echo  Tennis_BuildFooter('ADMIN', "listSeriesRoster.php?ID={$seriesID}");




//---FUNCTIONS ----------------------------------------------------------------

function local_dbFetchNewDate($seriesID, $DayofWeek, &$newDTVals)
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
//$DEBUG = TRUE;

$ValtblName = 'Event';
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

	if ($DEBUG)
		{
		echo "<P><b>query:</b><BR>{$query}";
		}

					//   Open the list.
	$qryResult = Tennis_OpenViewCustom($query);
	if (!$qryResult) { return FALSE; }

					//   Get first record in list.
	$row = mysql_fetch_array($qryResult);

					//   Get the calculated Date 1-Week-Out from the Event.Start
					//value of the first record in list (list is sorted in
					//decending order so that 1st record is the latest date in
					//the list for the specified weekday).
	$newDTVals['Start'] = $row['evtStartNextWeek'];
	$newDTVals['End'] = $row['evtEndNextWeek'];
	
					//   Return the evtNextWeek value and exit function.
	return TRUE;

	
} // END FUNCTION


function local_dbWriteNewDate($ID, &$newDTVals)
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
	
	$ValtblName = 'Event';
	$claimCd = 10; //Code for 'No Response.'
	array($row);

					//   Build the update query.
	$NewStart = $newDTVals['Start'];
	$NewEnd = $newDTVals['End'];
	$query = "UPDATE {$ValtblName} 
		SET Event.Start='{$NewStart}', 
		Event.End='{$NewEnd}' 
		WHERE Event.ID={$ID};";
	if ($DEBUG)
		{
		echo "<P><b>query:</b><BR>{$query}";
		}

					//   Post the Updates.
	$qryResult = mysql_query($query);
	//$qryResult = TRUE; //For Testing, don't post to DB yet....
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR><BR>Query Sent:<BR>' . $query;
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
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid Update Query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR><BR>Query Sent:<BR>' . $query;
		$message = $GLOBALS['lstErrMsg'];
		return FALSE;
		}
	return TRUE;
	
} // END FUNCTION


function local_AuthorizeUser($ID)
	{
	/* This function determines if the user has the right to perform this
		action.
		
		USER EDIT RIGHTS -->
			Levels of rights on this page:
				1)	MANAGER. Series manager (code 42 in codeset 9).
					Can edit any field.
		
		TAKES:
			$ID = The ID of the series we are rolling events for.
		
		RETURNS:
			TRUE if user has rights, FALSE otherwise.
			
	*/


	//----GET USER RIGHTS------------------------------------------------------->
					//   NOTE that in the nested IF statement, SESSION['clbmgr']=TRUE
					//really means Club ADM level rights. If this session value is
					//FALSE for the current user then the below code will look up
					//the user's authority on the club, which may be at the (lower)
					//'MGR' level of rights (or of course, no rights at all).
	$userPriv='GST';
	$userPriv = Session_GetAuthority(42, $seriesID);
	if ($_SESSION['admin']==True) $userPriv = 'ADM';
	if ($_SESSION['clbmgr']==True) $userPriv = 'ADM';
	
					//   Check rights against what rights are required for this
					//page.
	if(!ADMIN_EditAuthorized("MGR", $userPriv))
		{
		echo "<P>You are Not Authorized to Edit This Infomation.";
		echo "<BR>Updates Were Not Applied.</P>";
		echo "<P>Your User Rights For This Information Are: {$userPriv}</P>";
		echo "<P><A HREF='$rtnpg'>RETURN</A></P>";
		include './INCL_footer.php';
		return FALSE;
		}
	return TRUE;

} // END FUNCTION


?> 
