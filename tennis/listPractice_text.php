<?php
/*
	PURPOSE: Display the current rsvp-playing status for the week's
	upcoming events for a recreational series. NOTE: This script is
	for recreational series' only.
	
	Note-1: The original script permitted specifying the number of events
	to list out by passing in a query string parameter. And I did use 
	that in my link to this page. While I am now ignoring that, I am
	leaving that capability in place as I could see it might be useful
	sometime down the road.

	Note-2: The original script also permitted an option whereby you could
	specify which events to list out by passing in a list of event IDs.
	Same as Note-1 above, I am ignoring that, but leaving that capability 
	in place as I could see it might be useful sometime down the road.

	3/14/2017: Rebuilt this script to use the eventViewRequests object
------------------------------------------------------------------------------------- */

session_start();
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
include_once('./INCL_Tennis_CONSTANTS.php');
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./classdefs/error.class.php');
include_once('./classdefs/debug.class.php');
include_once('./clsdef_mdl/database.class.php');
include_once('./clsdef_mdl/recordset.class.php');
include_once('./clsdef_mdl/event.class.php');
include_once('./clsdef_mdl/rsvp.class.php');
include_once('./clsdef_view/eventViewChunks.class.php');
include_once('./clsdef_ctrl/eventViewRequests.class.php');
include_once('./clsdef_ctrl/c_eventRecFPCstatus.class.php');
include_once('./INCL_Tennis_GLOBALS.php');

Session_Initalize();

//---Declare access to global variables -----------------------------------------------
	global $objError;
	global $objDebug;
	global $CRLF;

	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;



//---Declare local variables ----------------------------------------------------------

	/**	To hold the result of function calls. Assumes return result is a string. */
$sFunctResult = "";

	/**	Number of events actually returned from the query. */
$NumEvtsReturned = 0;

	/**	Will hold list of event id#'s if this method was specified 
			in the query string passed in by the URL. */
$recID = array();

	/**	Will hold the number of event to show rsvps for if this
			methos was specified in the passed in query string. */
$NumEvts = 0;

	/**	Series to show rsvp status for. */
$seriesID = 0;

	/**	To hold the resulting rsvp status display string. */
$rsvpDisplayString = "";

	/**	Controller object that will do the work of creating the 
			view we need for display on this page. */
$viewEvtParticStatus = new eventViewRequests();



//---Connect to mysql------------------------------------------------------------------
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

//---Decode the query string (see notes 1-2 above)--------------------------------------
					//   Determine what the passed in query string is saying about which 
					//event records to list; even tho for now I am going to ignore this.
					//Either the IDs were passed in via query sting in the URL, or else 
					//the query string only specified how many of the next scheduled
					//events to list for.
if (array_key_exists('ID', $_GET)) {
	$recID[1] = $_GET['ID'];
	$byRecID = True;
	if ($objDebug->DEBUG) $objDebug->writeDebug("recID[1]: {$recID[1]}");
	if ($recID[1] < 1) {
		echo "<P>ERROR, No Event Selected.</P>";
		include './INCL_footer.php';
		exit;
	}
					//   Two other events we can build the list for.
	if ($_GET['ID2'] > 0) $recID[2] = $_GET['ID2'];
	if ($_GET['ID3'] > 0) $recID[3] = $_GET['ID3'];
}
elseif ($_GET['NUM'] > 0) {
	$NumEvts = $_GET['NUM'];
	$seriesID = $_GET['SID'];
	if ($objDebug->DEBUG) $objDebug->writeDebug("NUM: {$NumEvts} | SID: {$seriesID}");
	if (!$qryResult = Tennis_SeriesEventsOpen($seriesID, 'FUT')) {
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
	}
				//   Check to be sure there actually are enough events to list.
				//If not, adjust the number of events to list.
	$NumEvtsReturned = mysql_num_rows($qryResult);
	if ($NumEvtsReturned < $NumEvts) $NumEvts = $NumEvtsReturned;
}
//---End decode query string section----------------------------------------------------


//---Output page header stuff.----------------------------------------------------------
$tbar = "Tennis RSVP Status";
$pgL1 = "RSVP Status";
$pgL2 = "";
$pgL3 = "RSVPs for Email";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);

//---Using the eventViewRequests object, get the view to display------------------------

					//   I do need to validate that I have a series ID. I may not have one
					//due to the old (and retained) option to pass in a list of event IDs
					//instead of a series ID (see notes 1 & 2 in the preamble comments to this
					//script). Am using the usual, older and somewhat crude, method of 
					//showing an error and then exiting out; just cause I am in a hurry.
	if($seriesID<=0) {
		echo "<P>ERROR, No Series specified.</P>";
		include './INCL_footer.php';
		exit;
	}
	$sFunctResult = $viewEvtParticStatus->getPlayingStatus4Series($seriesID, $rsvpDisplayString, 'UPCOMING');

//---Display the view-------------------------------------------------------------------
	echo $rsvpDisplayString;
	

//---Display some other actions---------------------------------------------------------
$out = "<DIV><BR /><BR />";
if ($_SESSION['recID'] > 0) {
	$out .= "<BR />Actions:<BR />{$CRLF}";
	$out .= "&nbsp;&nbsp;&nbsp;* <A HREF=\"emailPracticeRSVP.php?{$_SERVER['QUERY_STRING']}\">";
	$out .= "Email This Page To Yourself</A>{$CRLF}";
	$out .= "<BR>&nbsp;&nbsp;&nbsp;* ";
	$out .= "<A HREF=\"listEmails.php?OBJ=SERIES&ID={$seriesID}\">";
	$out .= "Make Email Address List</A><BR />{$CRLF}";
}

//---Provide the usual other useful links-----------------------------------------------
$out .= "<BR />Useful Links:<BR />{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='listSeriesRoster.php?ID={$seriesID}'>Full RSVP Grid</A><BR />{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='dispSeries.php?ID={$seriesID}'>Recreational Play Notes</A><BR />{$CRLF}";
$hreftxt = "http://laketennis.com";
if ($_SESSION['clubID'] > 0) {
	$hreftxt = "http://laketennis.com/ClubHome.php?ID={$_SESSION['clubID']}";
}
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='{$hreftxt}'>Club Home Page</A><BR />{$CRLF}";
$out .= "</DIV>{$CRLF}";
echo $out;

//---Close out the page-----------------------------------------------------------------
echo  Tennis_BuildFooter('NORM', "listPractice_text.php?{$_SERVER['QUERY_STRING']}");

?>
