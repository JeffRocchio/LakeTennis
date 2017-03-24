<?php
/*
	SPECIFICALLY FOR the Mixed-Up Doubles Social events.
	
	This page is used to generate email notices to 
	all participants of the mixed doubles series asking
	them to update their RSVP records.
	
	12/06/2014: This a tactical kludge to get the notices out for
	the New Year's event.
	
	Ideally, at some point, I will construct a proper, generic
	script that will allow a series or event administrator to be
	be able to initiate a range of automated messages.
	
	The flow is:
		a. Get eventID from querystring.
		b. Get notice variable data from user via web form.
		c. Post the web form back to this page.
		d. Populate the $actionData[] array with needed data.
		e. Show a "confirm" page with all the array data.
		f. Send the emails using a call to the function 
	      handleManualRequest(array $actionData)
	      in autoActionHandler.class.php.
	
	The URL to use for testing - ID=500 is for the Test & Demo club, seriesID:50, EventID: 500: 
	   >> sendRSVPupdateRequests_MixedDbls.php?ID=500

	The URL to use for Mixed-Up Doubles - ID=538 is for EventID 538, SeriesID:56, EventID: 538: 
	   >> sendRSVPupdateRequests_MixedDbls.php?ID=538

	12/06/2014: Version 1.0.
	05/10/2015: Tweaked for the July 3, 2015 event. Did not make any logic updates.
		- Updated $defaultBodyTemplate to represent the July 3rd event.
		- Other than making the above update, no other code needs to be changed. The EventID
		  for the new event is picked up from the querystring of this page URL at runtime,
		  it is not hardcoded in anywhere.

------------------------------------------------------------------ */
session_start();
set_include_path("/tennis");
include_once('INCL_Tennis_CONSTANTS.php');
include_once('INCL_Tennis_Functions_Session.php');
include_once('INCL_Tennis_DBconnect.php');
include_once('INCL_Tennis_Functions.php');
include_once('INCL_Tennis_Functions_ADMIN_v2.php');
include_once('INCL_Roster.php');
include_once('classdefs/error.class.php');
include_once('classdefs/debug.class.php');
include_once('./clsdef_mdl/database.class.php');
include_once('./clsdef_mdl/simulatedRecordset.class.php');
include_once('./clsdef_mdl/recordset.class.php');
include_once('./clsdef_mdl/series.class.php');
include_once('./clsdef_mdl/event.class.php');
include_once('./clsdef_mdl/rsvp.class.php');
include_once('./clsdef_mdl/autoAction.class.php');
include_once('./clsdef_mdl/link.class.php');
include_once('./clsdef_mdl/txtBlock.class.php');
include_once('./clsdef_view/eventViewChunks.class.php');
include_once('./clsdef_view/html2text.class.php');
include_once('./clsdef_view/linkViews.class.php');
include_once('./clsdef_ctrl/emailNotice.class.php');
include_once('./clsdef_ctrl/eventViewRequests.class.php');
include_once('./clsdef_ctrl/viewFromTemplate.class.php');
include_once('./clsdef_ctrl/txtBlockViewRequests.class.php');
include_once('./clsdef_ctrl/autoActionHandlerMixedDbls.class.php');
include_once('./clsdef_ctrl/rsvpUpdateViaEmailLink.class.php');
include_once('INCL_Tennis_GLOBALS.php');

Session_Initalize();
$rtnpg = Session_SetReturnPage();


$DEBUG = FALSE;
//$DEBUG = TRUE;

//----GLOBALS ----------------------------------------------------------------->
GLOBAL $CRLF;
global $objDebug;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";



				//   The event and related series table rows.
$rowEvent = array();
$rowSeries = array();

 				//   RecordIDs we will need to pass around.
$seriesID = 0;
$eventID = 0;
$clubID4series = 0;




//----DECLARE LOCAL VARIABLES-------------------------------------------------->

				//   The current user's edit rights on the current series.
				//Initial default value is "Guest."
				//Note that declare of the rights[] array is for compatability
				//with the Roster_GetUserRights() function and is not
				//currently used in this script; this array approach is for
				//possible future flexibility.
$userPrivSeries = 'GST';
$rights = array('view'=>'GST','edit'=>'GST');

				//   Holds Message to display when user does not have view
				//rights to this page.
$noViewMessageTxt = "<P>You do not have permission to view this page.";
$noViewMessageTxt .= " If you believe you are supposed to have the ability";
$noViewMessageTxt .= " to view this page, please contact your Series or";
$noViewMessageTxt .= " Club administrator.";
$noViewMessageTxt .= "</P>";

				//   This is the email template. Each participant will
				//receive the below email asking them to click the link
				//represented by the token '|URLstring|' in order to
				//RSVP. This email template is used in 
				//"autoActionHandlerMixedDbls.class->handle_RsvpUpdateRequest()"
				//to generate each individual email.
$defaultBodyTemplate = "--------------------<BR />
INDEPENDENCE DAY MIXED-UP DOUBLES SOCIAL<BR />
--------------------<BR />
<BR />
DATE: July 3rd (Fridayy)<BR />
TIME: 7:00pm - 11:00pm<BR />
LOCATION: North Meck Park, Huntersville<BR />
<BR />
Time to RSVP for our Independence Day Mixed Doubles Social.<BR />
<BR />
Please click on the link below to declare your RSVP.<BR />
<BR />
If you will bring something to the event, please note on
the RSVP page what others have already signed up to bring
along with the basic items we'll need, also shown on
the RSVP page. Then as you RSVP you can declare what you
plan to bring along.<BR />
<BR />
Thanks!<BR />
<BR />
(<i>NOTE: This is an automated message. Please use
the supplied link to update your RSVP. 
Replies to this email will not been seen.</i>)<BR />
<BR />
> |URLstring| < <BR />
<BR />
*-*-*-*-*-<BR />
Sent on Behalf of: Jeff Rocchio<BR />
For the Independence Day Mixed-Up Doubles Social<BR />
*-*-*-*-*-<BR />
This is an automated message, <BR />
please do not reply to it.<BR />
Questions? Contact Jeff at:<BR />
jeffrocchio@gmail.com";


$callSuccess = true;




//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include "../INCL_footer.php";
	exit;
	}


//----BUILD THE APPROPRIATE PAGE-VIEW----------------------------------------->
if (array_key_exists('meta_POST', $_POST)) //Post data to server.
	{
	$callSuccess = local_fetchData();
	if(!$callSuccess)
		{
		echo "<P>{$lstErrMsg}</P>{$CRLF}";
		include '../INCL_footer.php';
		exit;
		}
	local_GenSection_sendingInProcess();
	$objDebug->DEBUG = TRUE;
	local_sendNotices();
	local_GenSection_ClosePage();
	}
else // Build the data-entry page.
	{
	$callSuccess = local_fetchData();
	if(!$callSuccess)
		{
		echo "<P>{$lstErrMsg}</P>{$CRLF}";
		include '../INCL_footer.php';
		exit;
		}
	local_GenSection_EditForm();
	local_GenSection_ClosePage();
	}
	
	
	
//---END MAIN BODY OF CODE --------------------------------------------------->


function local_AuthorityCheck($sID, $eID)
	{
	/* This function determines the user's authority to edit the info. It
		asks the question: "Can we make the field editable, or should we
		just render it display-only"?
		
		USER EDIT RIGHTS -->
			Levels of rights on this page:
			   1) GUEST. Can view only, but can see only public name.
				2) MEMBER. Can view only, but can see full names.
				3) SELF. Can edit their own information.
				4) EVENT MANAGER. Can edit information of the event they own.
				5) SERIES MANAGER. Can edit any field.
		
		TAKES:
			$sID = Series ID.
			$eID = Event ID.
		
		RETURNS:
			TRUE if user has update rights for the field,
			FALSE if user does not have update rights.
			
	*/
	
					//   Initialize. Start by assuming user is only a Guest,
					//then check to see if the user has site or club-level
					//Admin rights. If not, see what rights the user has on
					//the series object. If not high enought there, check on
					//rights for the event.
					//   Also - to avoid hitting the DB for every record in
					//the case where the user has manager rights to the entire
					//series I am using the static variable $srsMgr to keep
					//track of this fact so we can do an immediate return in
					//that case.
	static $srsMgr = FALSE;
	if ($srsMgr)
		{
		$userPriv = "MGR";
		}
	else
		{
		$userPriv='GST';
		if ($_SESSION['admin']==True) { $userPriv='MGR'; $srsMgr=TRUE; }
		elseif ($_SESSION['clbmgr']==True) { $userPriv='MGR'; $srsMgr=TRUE; }
		else
			{
			$tmp=Session_GetAuthority(42, $sID); //42=Object ID for 'Series'
			if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='MGR'; $srsMgr=TRUE; }
			else
			$tmp=Session_GetAuthority(43, $eID); //43=Object ID for 'Event'
			if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='MGR'; }
			}
		}
	
	return $userPriv;

} // END FUNCTION


function local_fetchData()
	{
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;
	global $lstErrMsg;

	GLOBAL $tblName;
	GLOBAL $rowEvent;
	GLOBAL $rowSeries;
	GLOBAL $seriesID;
	GLOBAL $eventID;
	GLOBAL $clubID4series;

	
	$bSuccess = true;

	
				//   Get the query string data and determine how to
				//obtain the Event ID based on how we are coming into
				//the page.
	if (array_key_exists('ID', $_GET)) {
		$eventID = $_GET['ID'];
		}
	elseif (array_key_exists('POST', $_GET)) {
		$postAction = $_GET['POST'];
		if ($postAction == "T") {
			$eventID = $_POST["ID"];
			}
		}
	else {
		$bSuccess = false;
		return $bSuccess;
		}
		
				//   Get the event record, and set the
				//seriesID global variable.
	if(!Tennis_GetSingleRecord($rowEvent, "Event", $eventID))
		{
		echo "<P>{$lstErrMsg}</P>";
		echo  Tennis_BuildFooter('NORM', $_SESSION['RtnPg']);
		$bSuccess = false;
		return $bSuccess;
		}
	$seriesID = $rowEvent['Series'];
	
				//   Now get the associated series record, and set the
				//cludbID global variable the event/series is for.
	if(!Tennis_GetSingleRecord($rowSeries, "series", $seriesID))
		{
		echo "<P>{$lstErrMsg}</P>";
		echo  Tennis_BuildFooter('NORM', $_SESSION['RtnPg']);
		$bSuccess = false;
		return $bSuccess;
		}
	$clubID4series = $rowSeries['ClubID'];

	return $bSuccess;
}

function local_GenFieldTxt($fldFrmName, $fldDispLen, $fldMaxLen, $fldValue, $fldAuth, $usrRights)
	{
	/*

	RETURNS:
		1) A string that contains the form-field HTML for the text field.
	*/
	
	//---INITILIZE---------------------------------------------------------
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	if (ADMIN_EditAuthorized($fldAuth, $usrRights))
		{
		$fldValue = htmlentities($fldValue,ENT_QUOTES,"UTF-8",FALSE);
		$fldSpec = "<INPUT TYPE=text NAME={$fldFrmName} ";
		$fldSpec .= "SIZE={$fldDispLen} MAXLENGTH={$fldMaxLen} ";
		$fldSpec .= "VALUE='{$fldValue}'>{$CRLF}";
		}
	else
		{
		$fldSpec = $fldValue;
		}

	return $fldSpec;

} //END FUNCTION



function local_GenFieldNote($fldFrmName, $fldRows, $fldCols, $fldValue, $fldAuth, $usrRights)
	{
	/*
			This function's logic is a copy of the ADMIB_GenFieldNote
			function that is in the ADMIN v2 INCL file. BUT, we don't want to
			build a table to hold the form as we don't have a big enough 
			screen, so this version of the function just builds the
			form-field HTML and none of the display stuff.
			
		   This function generates the HTML for a
			text-area data-entry field.
		This function generates the HTML for 1 row of a table
		that is being used in a data-entry form
	
	RETURNS:
		1) A string that contains the form-field HTML for the text-area field.
	*/
	
	//---INITILIZE---------------------------------------------------------
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	if (ADMIN_EditAuthorized($fldAuth, $usrRights))
		{
		$fldValue = htmlentities($fldValue,ENT_QUOTES,"UTF-8",FALSE);
		$fldSpec = "<TEXTAREA NAME={$fldFrmName} ROWS={$fldRows} COLS={$fldCols}>{$CRLF}";
		$fldSpec .= $fldValue;
		$fldSpec .= "</TEXTAREA>";
		}
	else
		{
		$fldSpec = $fldValue;
		}

	return $fldSpec;

} //END FUNCTION



function local_GenFieldSelBox_ContactList($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights)
	{
	/*
			This function generates a drop-box field to select which sub-set of event
			participants to sent the RSVP request to. The purpose is to match up
			to the valid options in autoActionHandlerMixedDbls->handle_RsvpUpdateRequest()
				40 = All participants in the event
				41 = Those who have not yet RSVP'd to the event.
				42 = Those who have RSVP'd as Tentative to the event.
				43 = Those who have either not yet RSVP'd or are Tentative.
	
	RETURNS:
		1) A string that contains the HTML to output for a form-field.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	
	$codeSet = array();
	
	$codeSet["ALL Participants"] = 40;
	$codeSet["Not Yet RSVP'd"] = 41;
	$codeSet["RSVP'd As Tentative"] = 42;
	$codeSet["Both no RSVP yet or Tentative"] = 43;

	if (ADMIN_EditAuthorized($fldAuth, $usrRights))
		{
		$listBox = "<SELECT name={$fldFrmName}>";
		foreach ($codeSet as $description => $code) {
			if ($code == $fldValue) {
				$listBox .= '<OPTION SELECTED value ="';
				}
			else {
				$listBox .= '<OPTION value ="';
				}
			$listBox .=$code;
			$listBox .= '">';
			$listBox .= $description;
			$listBox .= "</OPTION>{$CRLF}";
			}
			$listBox .= "</SELECT>{$CRLF}";
			
		if ($DEBUG) {
			echo "<p>listBoxHTML: {$listBox}</p>";
			}
		}
	else
		{
						//   Just display current value.
		$listBox = "ERROR - Undefined Value";
		foreach ($codeSet as $description => $code) {
			if ($code == $fldValue) {
				$listBox = $description;
				}
			}
		}

	$fldSpec = $listBox;
	return $fldSpec;

} //END FUNCTION



function local_GenSection_EditForm()
{
	/*
	   This function generates the data-entry page view.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	GLOBAL $CRLF;
				//   Declare the global error variables.
	GLOBAL $lstErrExist;
	GLOBAL $lstErrMsg;

	GLOBAL $rowEvent;
	GLOBAL $rowSeries;
	GLOBAL $seriesID;
	GLOBAL $eventID;
	GLOBAL $clubID4series;
	GLOBAL $defaultBodyTemplate;
	
				//   Variables that serve as constants.
	$tbar = "Tennis - Send RSVP Update Requests for Mixed Doubles";
	$pgL1 = "Send RSVP Update Requests";
	$LoggedInprsnID = $_SESSION['recID'];

				//   Regular variables.
	$pgL2 = "";
	$pgL3 = "";
	$rtnpg = "";
	$ViewLevel = 0;
	$userPrivSeries = "";
	$tmp = "";

	

			//   Output page header stuff.
	$pgL2 = "Series: {$rowSeries['LongName']} (ID: {$rowSeries['ID']})";
	$pgL3 = "Event: {$rowEvent['Name']} (ID: {$rowEvent['ID']})";
//	$pgL3 = "ClubID: <b>{$rowSeries['ClubID']}</b>";
	echo Tennis_BuildHeader('MOBILE', $tbar, $pgL1, $pgL2, $pgL3);

	$rtnpg = "/tennis/mobile/meventPage.php?ID={$eventID}";
	
	
				//   Determine and handle the page display rights via the
				//series ViewLevel setting.
	$ViewLevel = $rowSeries['ViewLevel'];
	$userPrivSeries = Roster_GetUserRights($seriesID, $ViewLevel, "sendRSVPupdateRequests_MixedDbls", $rights);
	if ($userPrivSeries=='NON')
		{
		echo $noViewMessageTxt;
		$tmp = Tennis_dbGetNameCode($ViewLevel, FALSE);;
		echo "<p>(View Level for this Page: <b>{$tmp}</b>)</p>";
		echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);
		exit;
		}

				//   Create a form to enter the data into.
	echo "{$CRLF}<DIV>{$CRLF}{$CRLF}";
	echo "<form method='post' action='sendRSVPupdateRequests_MixedDbls.php?POST=T'>{$CRLF}";
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>{$CRLF}";
	echo "<input type=hidden name=meta_ADDPG value=''>{$CRLF}";
	echo "<input type=hidden name=meta_POST value=TRUE>{$CRLF}";
	echo "<input type=hidden name=meta_EVTPURP value={$rowEvent['Purpose']}>{$CRLF}";
	echo "<input type=hidden name=ID value={$rowEvent['ID']}>{$CRLF}";
	
	$usrRights = local_AuthorityCheck($seriesID, $eventID);
	if ($DEBUG)
		{
		echo "<BR />User Rights: {$usrRights}";
		echo " Event ID: {$rowEvent['ID']}";
		echo " User ID: {$_SESSION['recID']}<BR />{$CRLF}";
		}

						//   Display event info so user is sure they 
						//are doing the correct event.
	$eventName = "<STRONG>{$rowEvent['Name']}</STRONG>";
	$dispDate = Tennis_DisplayDate($rowEvent['Start']);
	$dispTime = Tennis_DisplayTime($rowEvent['Start'], True);
	echo "{$eventName}<BR />{$CRLF}"; 
	echo "{$dispDate} // {$dispTime}<BR />&nbsp;<BR />{$CRLF}"; 
	

						//   Create data entry fields to get user data.

						//   Make drop-box to select sub-set of folks to sent email to.
	$fldAuth = "MGR&self={$LoggedInprsnID}";
	$fldFrmName = "contactSubSet";
	$fldValue = 41;
	$rowHTML = local_GenFieldSelBox_ContactList($fldFrmName, 0, $fldValue, $fldAuth, $usrRights);
	echo "Who Should the RSVP Request be Sent To?&nbsp;&raquo;&nbsp;<BR />{$rowHTML}<BR />{$CRLF}";
	echo "<BR />{$CRLF}{$CRLF}";
			
						//   Make email subject field.
	$fldAuth = "MGR&self={$LoggedInprsnID}";
	$fldFrmName = "emailSubject";
	$fldValue = "Mixed-Up Tennis Doubles Social - Please RSVP";
	$rowHTML = local_GenFieldTxt($fldFrmName, 50, 150, $fldValue, $fldAuth, $usrRights);
	echo "Subject Line&nbsp;&raquo;&nbsp;<BR />{$rowHTML}<BR />{$CRLF}";
	echo "<BR />{$CRLF}{$CRLF}";
			
						//   Make email body template field.
	$fldAuth = "MGR&self={$LoggedInprsnID}";
	$fldFrmName = "emailBodyTemplate";
	$fldValue = $defaultBodyTemplate;
	$rowHTML = local_GenFieldNote($fldFrmName, 20, 75, $fldValue, $fldAuth, $usrRights);
	echo "Body Template&nbsp;&raquo;&nbsp;<BR />{$rowHTML}<BR />{$CRLF}";
	echo "<BR />{$CRLF}{$CRLF}";
	
				//   Make the form's Save button.
	echo "<input type='submit' value='Send Emails'>{$CRLF}";
				//   Close out the form.
	echo "</form>{$CRLF}{$CRLF}";
				//   Close out the form division.
	echo "&nbsp;</DIV>{$CRLF}";
	
} // END FUNCTION



function local_sendNotices()
{
	/*
	   This function causes the rsvp update request
	emails to go out.
	*/
	
	GLOBAL $CRLF;
				//   Declare the global error variables.
	GLOBAL $lstErrExist;
	GLOBAL $lstErrMsg;
	global $objDebug;
	
	GLOBAL $rowEvent;
	GLOBAL $rowSeries;
	GLOBAL $seriesID;
	GLOBAL $eventID;
	GLOBAL $clubID4series;
	GLOBAL $defaultBodyTemplate;

	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	
				//   Variables that serve as constants.
				
				//   Regular variables.
	$actionData = array();
	$bSuccess = true;
	$objAutoAction = new autoActionHandlerMixedDbls;
	
	

	if ($_POST['meta_POST'] <> 'TRUE') {
		$bSuccess = false;
		return $bSuccess;
		}

	$recID = $_POST["ID"];
	$evtprp = $_POST["meta_EVTPURP"];
		
						//   Set the page we redirect back to after
						//we are done sending out the emails.
	$gotoPageAfterPostng = $_POST["meta_RTNPG"];

						//   Set the autoaction array values.
	$actionData['AutoActClassID'] = AACT_SENDRSVPREQUEST;
	$actionData['ClubID'] = $clubID4series;
	$actionData['ActTitle'] = "Send RSVP Request for Mixed-Up Doubles";
	$actionData['TrggrObjType'] =OBJEVENT;
	$actionData['TrggrObjID'] = $eventID;
	$actionData['ToGroup'] = $_POST["contactSubSet"];
	$actionData['ToAddresses'] = ""; //Not used for RSVP update request action.
	$actionData['EmailEncodeFormat'] = "HTML";
	$actionData['EmailSubject'] = $_POST["emailSubject"];
	$actionData['EmailBodyTmplate'] = $_POST["emailBodyTemplate"];
	$actionData['ForEventTypes'] = "05,06,07,09"; //Recreational events.
	$actionData['ForEventStatus'] = "34"; //Result Code "TBD")

	if ($DEBUG)
		{
		echo "Simulated Run ---->><BR />";
		$debugText = $objDebug->displayDBRecord($actionData, FALSE);
		echo $debugText;
		echo "<BR /><BR />";
		echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);
		exit;
		}

	$objAutoAction->handleManualRequest($actionData);
		
			//   Use standard function to auto-re-direct back to
			//the display page.
	echo ADMIN_Post_HeaderOK("Event", $gotoPageAfterPostng, $message, "STOP");

} // END FUNCTION



function local_GenSection_ClosePage()
{
	/*
	   This function generates the HTML for closing
	out the page.
	*/
	
	GLOBAL $CRLF;
				//   Declare the global error variables.
	GLOBAL $lstErrExist;
	GLOBAL $lstErrMsg;
	
	GLOBAL $tblName;
	GLOBAL $row;
	GLOBAL $seriesID;
	GLOBAL $eventID;
	GLOBAL $rsvpID;

	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
				//   Variables that serve as constants.

				
				//   Regular variables.
	$out = "";
	$hreftxt = "";
				
	echo "<div>";
	$out = "<BR />&nbsp;Useful Links:<BR />{$CRLF}";
	$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='/tennis/mobile/mlistSeriesRoster.php";
	$out .= "?ID={$seriesID}'>Mobile Phone View of Roster</A><BR />{$CRLF}";
	$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='/tennis/listSeriesRoster.php";
	$out .= "?ID={$seriesID}'>Full RSVP Grid</A><BR />{$CRLF}";
	$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='/tennis/dispSeries.php";
	$out .= "?ID={$seriesID}'>Series Notes</A><BR />{$CRLF}";
	$hreftxt = "http://laketennis.com";
	if ($_SESSION['clubID'] <> 1)
		{
		$hreftxt = "http://laketennis.com/ClubHome.php?ID={$_SESSION['clubID']}";
		}
	$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='{$hreftxt}'>";
	$out .= "Club Home Page</A><BR />{$CRLF}";
	$out .= "</div>{$CRLF}";
	echo $out;
	
	$_SESSION['RtnPg'] = "/tennis/mobile/mlistSeriesRoster.php?ID={$seriesID}";
	echo  Tennis_BuildFooter('NORM', $_SESSION['RtnPg']);
	//echo  Tennis_BuildFooter('ADMIN', "/tennis/mobile/mlistSeriesRoster.php?ID={$seriesID}");


} // END FUNCTION


function local_GenSection_sendingDone()
{
	/*
	   This function generates a page to confirm the email sending is done.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	GLOBAL $CRLF;
				//   Declare the global error variables.
	GLOBAL $lstErrExist;
	GLOBAL $lstErrMsg;

	GLOBAL $rowEvent;
	GLOBAL $rowSeries;
	GLOBAL $seriesID;
	GLOBAL $eventID;
	GLOBAL $clubID4series;
	
				//   Variables that serve as constants.
	$tbar = "Tennis - Send RSVP Update Requests for Mixed Doubles";
	$pgL1 = "Send RSVP Update Requests - SENDING COMPLETE";
	$LoggedInprsnID = $_SESSION['recID'];

				//   Regular variables.
	$pgL2 = "";
	$pgL3 = "";
	$rtnpg = "";
	$ViewLevel = 0;
	$userPrivSeries = "";
	$tmp = "";

	

			//   Output page header stuff.
	$pgL2 = "Series: {$rowSeries['LongName']} (ID: {$rowSeries['ID']})";
	$pgL3 = "ClubID: <b>{$rowSeries['ClubID']}</b>";
	echo Tennis_BuildHeader('MOBILE', $tbar, $pgL1, $pgL2, $pgL3);

	$rtnpg = "/tennis/mobile/meventPage.php?ID={$eventID}";
	
	
				//   Determine and handle the page display rights via the
				//series ViewLevel setting.
	$ViewLevel = $rowSeries['ViewLevel'];
	$userPrivSeries = Roster_GetUserRights($seriesID, $ViewLevel, "sendRSVPupdateRequests_MixedDbls", $rights);
	if ($userPrivSeries=='NON')
		{
		echo $noViewMessageTxt;
		$tmp = Tennis_dbGetNameCode($ViewLevel, FALSE);;
		echo "<p>(View Level for this Page: <b>{$tmp}</b>)</p>";
		echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);
		exit;
		}

				//   Create a form.
	echo "{$CRLF}<DIV>{$CRLF}{$CRLF}";
	echo "<form method='post' action='sendRSVPupdateRequests_MixedDbls.php?POST=T'>{$CRLF}";
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>{$CRLF}";
	echo "<input type=hidden name=meta_ADDPG value=''>{$CRLF}";
	echo "<input type=hidden name=meta_POST value=TRUE>{$CRLF}";
	echo "<input type=hidden name=meta_EVTPURP value={$rowEvent['Purpose']}>{$CRLF}";
	echo "<input type=hidden name=ID value={$rowEvent['ID']}>{$CRLF}";

						//   Display event info so user is sure they 
						//are doing the correct event.
	$eventName = "<STRONG>{$rowEvent['Name']}</STRONG>";
	$dispDate = Tennis_DisplayDate($rowEvent['Start']);
	$dispTime = Tennis_DisplayTime($rowEvent['Start'], True);
	echo "{$eventName}<BR />{$CRLF}"; 
	echo "{$dispDate} // {$dispTime}<BR />&nbsp;<BR />{$CRLF}"; 
	

						//   Create data entry fields to get user data.

				//   Make the form's OK button.
	echo "<input type='submit' value='OK'>{$CRLF}";
				//   Close out the form.
	echo "</form>{$CRLF}{$CRLF}";
				//   Close out the form division.
	echo "&nbsp;</DIV>{$CRLF}";
	
} // END FUNCTION



function local_GenSection_sendingInProcess()
{
	/*
	   This function generates a page for while we are sending.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	GLOBAL $CRLF;
				//   Declare the global error variables.
	GLOBAL $lstErrExist;
	GLOBAL $lstErrMsg;

	GLOBAL $rowEvent;
	GLOBAL $rowSeries;
	GLOBAL $seriesID;
	GLOBAL $eventID;
	GLOBAL $clubID4series;
	
				//   Variables that serve as constants.
	$tbar = "Tennis - Send RSVP Update Requests for Mixed Doubles";
	$pgL1 = "Send RSVP Update Requests - Sending In Process";
	$LoggedInprsnID = $_SESSION['recID'];

				//   Regular variables.
	$pgL2 = "";
	$pgL3 = "";
	$rtnpg = "";
	$ViewLevel = 0;
	$userPrivSeries = "";
	$tmp = "";

	

			//   Output page header stuff.
	$pgL2 = "Series: {$rowSeries['LongName']} (ID: {$rowSeries['ID']})";
	$pgL3 = "ClubID: <b>{$rowSeries['ClubID']}</b>";
	echo Tennis_BuildHeader('MOBILE', $tbar, $pgL1, $pgL2, $pgL3);

	$rtnpg = "/tennis/mobile/meventPage.php?ID={$eventID}";
	
	
				//   Determine and handle the page display rights via the
				//series ViewLevel setting.
	$ViewLevel = $rowSeries['ViewLevel'];
	$userPrivSeries = Roster_GetUserRights($seriesID, $ViewLevel, "sendRSVPupdateRequests_MixedDbls", $rights);
	if ($userPrivSeries=='NON')
		{
		echo $noViewMessageTxt;
		$tmp = Tennis_dbGetNameCode($ViewLevel, FALSE);;
		echo "<p>(View Level for this Page: <b>{$tmp}</b>)</p>";
		echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);
		exit;
		}

				//   Create a form.
	echo "{$CRLF}<DIV>{$CRLF}{$CRLF}";
	echo "<form method='post' action='sendRSVPupdateRequests_MixedDbls.php?POST=T'>{$CRLF}";
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>{$CRLF}";
	echo "<input type=hidden name=meta_ADDPG value=''>{$CRLF}";
	echo "<input type=hidden name=meta_POST value=TRUE>{$CRLF}";
	echo "<input type=hidden name=meta_EVTPURP value={$rowEvent['Purpose']}>{$CRLF}";
	echo "<input type=hidden name=ID value={$rowEvent['ID']}>{$CRLF}";

						//   Display event info so user is sure they 
						//are doing the correct event.
	$eventName = "<STRONG>{$rowEvent['Name']}</STRONG>";
	$dispDate = Tennis_DisplayDate($rowEvent['Start']);
	$dispTime = Tennis_DisplayTime($rowEvent['Start'], True);
	echo "{$eventName}<BR />{$CRLF}"; 
	echo "{$dispDate} // {$dispTime}<BR />&nbsp;<BR />{$CRLF}"; 
	

						//   Create data entry fields to get user data.

				//   Make the form's OK button.
	echo "<input type='submit' value='OK'>{$CRLF}";
				//   Close out the form.
	echo "</form>{$CRLF}{$CRLF}";
				//   Close out the form division.
	echo "&nbsp;</DIV>{$CRLF}";
	
} // END FUNCTION



?> 
