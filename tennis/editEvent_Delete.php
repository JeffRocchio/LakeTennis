<?php
/*
	This script deletes an Event.
	(It also deletes all the RSVP associative records.)
	
	02/03/2008 jrr: ver 1.0.
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
$seriesClubID = 0;
$tblName = "Event";

				//record IDs of the event to remove and the series it belongs to.
$recIDevt = 0;
$recIDseries = 0;

				//   Declare array to hold the detail record.
array($row);

				//   Declare array to hold display information for the event.
				//This is passed to local function where basic info for the event
				//is collected to do things like make the page header and the
				//confirmation page.
array($dispInfo);



//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----DETERMINE IF POSTING---------------------------------------------------->
				//   Determine if we are doing the initial confirm or the
				//actual posting.

if ($_POST['meta_POST'] == 'TRUE')
				//   We've confirmed the delete, so do it.
	{
	//----DO THE DELETE-------------------------------------------------------->

	$recIDevt = $_POST['ID'];

				//   Get display info.
	if (!local_GetDisplayInfo($recIDevt, $dispInfo))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	$seriesClubID = $dispInfo['ClubID'];
	
				//   Output page header stuff.
	$tbar = "Tennis Delete Event";
	$pgL1 = "Edit Events";
	$pgL2 = "Delete Event";
	$pgL3 = $dispInfo['dispTitle'];
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
	
				//   Be sure user has rights to do this.
	if (!local_AuthorizeUser($_POST['meta_SERIESID'],$seriesClubID)) { exit; }

				//   Delete the event.
	if (!ADMIN_dbDeleteEvent($recIDevt))
		{
		$statusMessage = "Unable to Delete Event";
		}
	else { $statusMessage = "Event Deleted"; }
	
	
				//   Show 'OK' link.
	echo "<P>{$statusMessage}<BR /><BR />";
	echo "Click OK to continue.<BR />";
	echo "<FONT STYLE='font-size: large'>";
	echo "<A HREF='{$_SESSION['RtnPg']}'>OK</A></FONT>";
	echo "</P>";
	}

else
				//   We're not posting, we're displaying confirming page.
				//Store the needed data into a form and give the user
				//a 'Submit' button to fire off the delete posting.
	{

	//----GET URL QUERY-STRING DATA-------------------------------------------->
				//   Get event record ID.
	if ($_GET['EID'] > 0) $recIDevt = $_GET['EID'];

	//----DISPLAY CONFIRMING DATA---------------------------------------------->

				//   Get display info.
	if (!local_GetDisplayInfo($recIDevt, $dispInfo))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}

					//   Output page header stuff.
	$tbar = "Tennis Delete Event";
	$pgL1 = "Edit Events";
	$pgL2 = "Delete Event";
	$pgL3 = $dispInfo['dispTitle'];
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
				
				//   Check to be sure that we have an event ID. 
	if ($recIDevt < 1)
 		{
		echo "<P>ERROR, No Event Selected.</P>";
		include './INCL_footer.php';
		exit;
		}
	
				//   Open an HTML form so we can record values to pass to the
				//posting routine and make a 'SAVE' button for the use to
				//click on.
	echo "<form method='post' action='editEvent_Delete.php'>{$CRLF}";
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>{$CRLF}";
	echo "<input type=hidden name=meta_POST value=TRUE>{$CRLF}";
	echo "<input type=hidden name=meta_TBL value=Event>{$CRLF}";

	$out = "<P>&nbsp;&nbsp;&nbsp;{$dispInfo['dispTitleURL']}<BR>{$CRLF}";
	$out .= "&nbsp;&nbsp;&nbsp;{$dispInfo['dispVenue']} at {$dispInfo['dispTime']}<BR>{$CRLF}";

	echo "<input type=hidden name=ID value={$recIDevt}>{$CRLF}{$CRLF}";
	echo $out;

				//   Close out the table. Put a field in the form that
				//will tell us how many Event records we processed.
	echo $CRLF;
	echo "<input type=hidden name=meta_SERIESID value={$recIDseries}>";
	echo "<input type='submit' value='DELETE EVENT'>";
	echo "</td>{$CRLF}</tr>{$CRLF}";

	echo "</form>{$CRLF}{$CRLF}";


}  // END of POST or DISPLAY If-Then Construct.


//----CLOSE OUT THE PAGE------------------------------------------------------>

$out = "<P><BR><BR>Useful Links:<BR>{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='listSeriesRoster.php";
$out .= "?ID={$recIDseries}'>Full RSVP Grid</A><BR>{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='dispSeries.php";
$out .= "?ID={$recIDseries}'>Series Notes</A><BR>{$CRLF}";
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

function local_GetDisplayInfo($eventID, &$dispInfo)
	{
	/* This function gets basic display information for a given
		Event record.
		
		TAKES:
			$EventID = The ID of the Event to get the info for.
		
		RETURNS:
			An array populated with the information.
			TRUE if successful, FALSE otherwise.

	*/

$DEBUG = FALSE;
//$DEBUG = TRUE;
	
	array($row);
	$dispView = 'qryEventDisp';

	if(!Tennis_GetSingleRecord($row, $dispView, $eventID))
		{
		return FALSE;
		}
				//   Get the Series ID so we can use it later.
	$dispInfo['recIDseries'] = $row['seriesID'];
					
				//   Make displayable event info.
	$dispInfo['dispDate'] = Tennis_DisplayDate($row['evtStart']);
	$dispInfo['dispTime'] = Tennis_DisplayTime($row['evtStart'], True);
	$dispInfo['dispVenue'] = $row['venueShtName'];
	$dispInfo['dispEvtName'] = $row['evtName'];
	$dispInfo['dispTitle'] = "{$dispInfo['dispEvtName']} on {$dispInfo['dispDate']}";
	$dispInfo['dispTitleURL'] = "<A HREF='dispEvent.php?ID={$recIDevt}'>";
	$dispInfo['dispTitleURL'] .= "{$dispInfo['dispEvtName']}</A>";
	$dispInfo['dispTitleURL'] .= " on {$dispInfo['dispDate']}";
	$dispInfo['ClubID'] = $row['seriesClubID'];

	if ($DEBUG)
		{
		echo "<p>";
		foreach ($dispInfo as $key => $value)
			{
			echo "<BR>{$key}: {$value}";
			}
		echo "</p>";
		}
	
	return TRUE;

} // END FUNCTION




function local_AuthorizeUser($seriesID, $seriesClubID)
	{
	/* This function determines if the user has the right to perform this
		action.
		
		USER EDIT RIGHTS -->
			Levels of rights on this page:
				1)	MANAGER. Series manager (code 42 in codeset 9).
				2)	SuperUser (jeff rocchio).
				3)	Club Admin.
		
		TAKES:
			$ID = The seriesID of the series we are rolling events for.
		
		RETURNS:
			TRUE if user has rights, FALSE otherwise.
			
	*/
	
					//   Initialize. Start by assuming user in only a Guest,
					//then check to see if the user has site or club-level
					//Admin rights. If not, see what rights the user has on
					//this specific object (series object).
	$userPriv='GST';
	if (($_SESSION['clubID']==$seriesClubID) AND ($_SESSION['clbmgr']==True))
		{
		$userPriv='ADM';
		}
	elseif ($_SESSION['admin']==True) { $userPriv='SADM'; } // Superuser.
	else
		{
		$tmp=Session_GetAuthority(42, $seriesID); //42=Object ID for 'Series'
		if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='MGR'; }
		}
	
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
