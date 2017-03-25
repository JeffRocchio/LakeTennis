<?php
/*
	This script allows a specific person to update their rsvps for a 
	specific series by clicking on a link sent out in an email. That link
	will have a query string param in it that is a key which specifies
	which person and which series to display the edit form for.
	This is part of the autoAction system.
	
	06/18/2012: Version 1.0.

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
include_once('clsdef_mdl/database.class.php');
include_once('clsdef_mdl/recordset.class.php');
include_once('clsdef_mdl/simulatedRecordset.class.php');
include_once('clsdef_mdl/series.class.php');
include_once('clsdef_mdl/rsvp.class.php');
include_once('clsdef_ctrl/rsvpUpdateViaEmailLink.class.php');
include_once('INCL_Tennis_GLOBALS.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


$DEBUG = FALSE;
//$DEBUG = TRUE;

//----GLOBALS ----------------------------------------------------------------->
GLOBAL $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES-------------------------------------------------->
$tblName = 'rsvp';
$row = array();
$rsvpInfo = array();
$lBoxList = array();
$infoKey = "";
$infoKeyVals = array();
$rsvpUpdateViaEmail = new rsvpUpdateViaEmailLink();



//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include "../INCL_footer.php";
	exit;
	}


//----POST THE DATA----------------------------------------------------------->
if (array_key_exists('meta_POST', $_POST))
	{
	if ($_POST['meta_POST'] == 'TRUE')
		{
		$recCount = $_POST['meta_RECCOUNT'];
		for ($i = 1; $i <= $recCount; $i++)
			{
			$evtID = $_POST["ID_{$i}"];
			$evtprp = $_POST["meta_EVTPURP_{$i}"];
			$rsvpClaim = $_POST["ClaimCode_{$i}"];
			$position = $_POST["Position_{$i}"];
			$role = $_POST["Role_{$i}"];
			$note = $_POST["Note_{$i}"];
			if ($evtprp <> 17)
				{
				if ($rsvpClaim == 10 || $rsvpClaim == 11) $position=30; //No Response or Not Available.
				elseif ($rsvpClaim == 13) $position=28; //Late.
				elseif ($rsvpClaim == 14) $position=28; //Tentative.
				elseif ($rsvpClaim == 15) $position=29; //Available.
				elseif ($rsvpClaim == 16) $position=29; //Confirmed.
				}
			if ($DEBUG)
				{
				echo "Number of Records to Post: {$recCount}<BR />{$CRLF}{$CRLF}";
				echo "Simulated Post ---->><BR />";
				echo "Event ID: {$evtID}<BR />";
				echo "Event Purpose: {$evtprp}<BR />";
				echo "Claim: {$rsvpClaim}<BR />";
				echo "Position: {$position}<BR />";
				echo "Role: {$role}<BR />";
				echo "Note: {$note}<BR /><BR />";
				}
			local_dbRSVPUpdate($evtID, $rsvpClaim, $position, $role, $note);
			}
				//   Use standard function to auto-re-direct back to
				//the display page. But we are debugging, do do this else
				//we won't be able to see the debugging messages.
		if (!$DEBUG) echo ADMIN_Post_HeaderOK($tblName, $rtnpg, $message);
		}
	}

//----BUILD PAGE DATA-ENTRY---------------------------------------------------->
else
	{
				//----GET LOGIN KEY AND PARSE IT.

				//   Create an invalid key to use in case there is no query string.
				//This will trigger a 'no series/id' error message later if no
				//query string.
	$infoKey = $rsvpUpdateViaEmail->loginKey_Create(0, OBJSERIES, 0);
				//   Get what is hopfully a valid key from the Qstring.
	if (array_key_exists('x', $_GET))
		{
		$infoKey = $_GET['x'];
		}
				//   Get needed values from the key.
	$infoKeyVals = $rsvpUpdateViaEmail->loginKey_Parse($infoKey);
	$seriesID = $infoKeyVals[3];
	$prsnID = $infoKeyVals[1];
				//   Log user into club, if they are not already logged in.
	$rsvpUpdateViaEmail->userLogin($infoKey, "RSVPUPDATE");

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


				//   Fetch the record to edit. NOTE: I
				//am getting a big joined record from
				//the qryRsvp query so that I
				//have the record IDs for the Event, the
				//series and such. So be careful about the
				//field names used for the form entry field
				//names (which have to match the 'raw' rsvp
				//table field names when handed off to the
				//post script).
	if (!$seriesID)
		{
		echo "<P>ERROR, No Series Selected.</P>{$CRLF}";
		include '../INCL_footer.php';
		exit;
		}
	if (!$prsnID)
		{
		echo "<P>ERROR, No Member Selected.</P>{$CRLF}";
		include '../INCL_footer.php';
		exit;
		}

				//   Open the set of RSVP records for this person and series.
	$qryResult = local_openRecordset($seriesID, $prsnID);
	if(!$qryResult)
		{
		echo "<P>{$lstErrMsg}</P>{$CRLF}";
		include '../INCL_footer.php';
		exit;
		}

	$row = mysql_fetch_array($qryResult);

				//   Output page header stuff.
	$tbar = "mTennis e-RSVP";
	$pgL1 = "Edit RSVPs";
	$pgL2 = "Series: {$row['seriesShtName']}";
	$pgL3 = "Person: {$row['prsnPName']}";
	echo Tennis_BuildHeader('MOBILE', $tbar, $pgL1, $pgL2, $pgL3);

	$rtnpg = "/tennis/listSeriesRoster.php?ID={$seriesID}";
	
	
	
				//   Determine and handle the page display rights via the
				//series ViewLevel setting.
	$ViewLevel = $row['seriesViewLevel'];
	$userPrivSeries = Roster_GetUserRights($seriesID, $ViewLevel, "editRSVPviaEmail", $rights);
	if ($userPrivSeries=='NON')
		{
		echo $noViewMessageTxt;
		$tmp = Tennis_dbGetNameCode($ViewLevel, FALSE);;
		echo "<p>(View Level for this Page: <b>{$tmp}</b>)</p>";
		echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);
		exit;
		}

	

				//   Create a form to enter the data into.
				//Also need to create two hidden fields to hold
				//the database and table name to pass to the
				//page we're going to post the data to.
	echo "{$CRLF}<DIV>{$CRLF}{$CRLF}";
	echo "<form method='post' action='editRSVPviaEmail.php?SID={$seriesID}&POST=T'>{$CRLF}";
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>{$CRLF}";
	echo "<input type=hidden name=meta_ADDPG value=''>{$CRLF}";
	echo "<input type=hidden name=meta_POST value=TRUE>{$CRLF}";
	echo "<input type=hidden name=meta_TBL value={$tblName}>{$CRLF}{$CRLF}";
	
				//   Build the fields for each event in the series.
	$i = 0;
	do
		{
		$i ++;
		$usrRights = local_AuthorityCheck($seriesID, $row['evtID']);
		if ($DEBUG)
			{
			echo "<BR />User Rights: {$usrRights}";
			echo " Event ID: {$row['evtID']}";
			echo " User ID: {$_SESSION['recID']}<BR />{$CRLF}";
			}
		echo "<input type=hidden name=meta_EVTPURP_{$i} value={$row['evtPurposeCd']}>{$CRLF}";
		echo "<input type=hidden name=ID_{$i} value={$row['ID']}>{$CRLF}";
		$eventName = "<STRONG>{$row['evtName']}</STRONG>";
		$dispDate = Tennis_DisplayDate($row['evtStart']);
		$dispTime = Tennis_DisplayTime($row['evtStart'], True);
		echo "{$eventName}<BR />{$CRLF}"; 
		echo "{$dispDate} // {$dispTime}<BR />{$CRLF}"; 
		
		$fldAuth = "MGR&self={$prsnID}";
		$fldFrmName = "ClaimCode_{$i}";
		$cdSet = 3;
		$fldValue = $row['rsvpClaimCode'];
		$rowHTML = local_GenFieldDropCode($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights);
		echo "Availability<BR />&nbsp;&nbsp;&raquo;{$rowHTML}{$CRLF}";
		
					//   Do not create editable Position and Roles fields if the event's
					//purpose is "Recreational." Because they really only apply to
					//matches. And also note that 'Self' does not apply for
					//authority on those - you have to be an admin or event
					//manager to edit those fields.
					   //   3/24/2017: I had to add exclusion of purpose code #65 here because I
				   //added a new code for Fully Populated Courts. This caused a bug, or really
				   //it inadvertenly caused an execution path that then invoked what was a
				   //latent bug - see Bug Fix Up note below.
		if (($row['evtPurposeCd'] !=6) && ($row['evtPurposeCd'] !=7) && ($row['evtPurposeCd'] !=65)) {
			/* ==== Bug Fix-Up Note ==========
				   I have to do a kludgy fix-up here to fix a bug. The bug is that a
			   regular user, who is not an admin or series or event manager, doesn't
			   get an editable Role field. With that field being empty it causes an
			   ill formed SQL statement, which errs out, thus the rsvp info the user
			   populates into their form never gets updated into the database. So I 
			   have to check for this condition and fix it up here.
			*/
			if($usrRights=='GST') {
				$fldFrmName = "Position_{$i}";
				$fldValue = $row['rsvpPositionCode'];
				echo "<input type=hidden name={$fldFrmName} value={$fldValue}>{$CRLF}";
				$fldFrmName = "Role_{$i}";
				$fldValue = $row['rsvpRoleCode'];
				echo "<input type=hidden name={$fldFrmName} value={$fldValue}>{$CRLF}";
			}
			else {
				$fldAuth = "MGR";
				$fldFrmName = "Position_{$i}";
				$cdSet = 5;
				$fldValue = $row['rsvpPositionCode'];
				$rowHTML = local_GenFieldDropCode($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights);
				echo "<BR />Position<BR />&nbsp;&nbsp;&raquo;{$rowHTML}{$CRLF}";
				
				$fldAuth = "MGR";
				$fldFrmName = "Role_{$i}";
				$cdSet = 4;
				$fldValue = $row['rsvpRoleCode'];
				$rowHTML = local_GenFieldDropCode($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights);
				echo "<BR />Role<BR />&nbsp;&nbsp;&raquo;{$rowHTML}{$CRLF}";
			}
		}
		else {
			$fldFrmName = "Position_{$i}";
			$fldValue = $row['rsvpPositionCode'];
			echo "<input type=hidden name={$fldFrmName} value={$fldValue}>{$CRLF}";
			$fldFrmName = "Role_{$i}";
			$fldValue = $row['rsvpRoleCode'];
			echo "<input type=hidden name={$fldFrmName} value={$fldValue}>{$CRLF}";
		}
		$fldAuth = "MGR&self={$prsnID}";
		$fldFrmName = "Note_{$i}";
		$fldValue = $row['rsvpNote'];
		$rowHTML = local_GenFieldNote($fldFrmName, 3, 22, $fldValue, $fldAuth, $usrRights);
		echo "<BR />Notes<BR />&nbsp;&nbsp;&raquo;{$rowHTML}<BR />{$CRLF}";
		echo "<BR />{$CRLF}{$CRLF}";
		}
	while ($row = mysql_fetch_array($qryResult));

	



				//   Save the number of events we processed, so we can do the
				//same number in the post loop.
	echo "<input type=hidden name=meta_RECCOUNT value={$i}>{$CRLF}{$CRLF}";
				//   Make the form's Save button.
	echo "<input type='submit' value='SAVE'>{$CRLF}";
				//   Close out the form.
	echo "</form>{$CRLF}{$CRLF}";
				//   Close out this display division.
	echo "<BR />&nbsp;</DIV>{$CRLF}";
	
					//   Close out the page.
	echo "<div>";
	$out = "<BR />&nbsp;Useful Links:<BR />{$CRLF}";
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
	
	$_SESSION['RtnPg'] = "/tennis/listSeriesRoster.php?ID={$seriesID}";
	echo  Tennis_BuildFooter('NORM', $_SESSION['RtnPg']);
	//echo  Tennis_BuildFooter('ADMIN', "/tennis/mobile/mlistSeriesRoster.php?ID={$seriesID}");
	
	} // End script for data-entry page generation.

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
	
					//   Initialize. Start by assuming user in only a Guest,
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
/*  3/24/2017: At first blush it appears there is a problem here - a regular user
falls through this series of IF statements without being identified as a
regular user - if not me (superAdmin), a club manager, a series manager or
the event's manager the set default of 'GST' doesn't get changed. But there
really isn't any problem. 
   Falling through here
as 'GST' just means that we build the data-entry form we do a check at the
field level to see if the specific event we are buiding the form for is for 
"self", meaning the personID for that item matches the currently logged in 
user.
*/
	
	return $userPriv;

} // END FUNCTION


function local_openRecordset($seriesID, $prsnID)
	{
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;
	global $lstErrMsg;

	$table = "qryRsvp";
	$where = "WHERE ((seriesID={$seriesID}) AND (prsnID={$prsnID}))";
	$sort = "ORDER BY evtStart";
	if ($DEBUG)
		{
		echo "<BR />{$table}<BR />{$where}<BR />{$sort}<BR /><BR />";
		}
	if(!$qryResult = Tennis_OpenViewGeneric($table, $where, $sort))
		{
		echo "<P>{$lstErrMsg}</P>";
		echo  Tennis_BuildFooter('NORM', $_SESSION['RtnPg']);
		exit;
		}
	
	return $qryResult;
}

function local_GenFieldDropCode($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights)
	{
	/*
			This function's logic is a copy of the ADMIB_GenFieldDropCode
			function that is in the ADMIN v2 INCL file. BUT, we don't want to
			build a table to hold the form as we don't have a big enough 
			screen, so this version of the function just builds the
			form-field HTML and none of the display stuff.
			
		   This function generates the HTML for a drop-down
		box data-entry field.
		   A drop-down box that contains the value-list for the
		code-set passed in param $cdSet.
	
	RETURNS:
		1) A string that contains the HTML to output for a form-field.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	if (ADMIN_EditAuthorized($fldAuth, $usrRights))
		{
		$fldSpec = Tennis_GenLBoxCodeSet($fldFrmName, $cdSet, $fldValue);
		}
	else
		{
						//   Convert the code-key to it's
						//text value for display-only purposes.
		$fldSpec = ADMIN_dbGetNameCode($fldValue, FALSE);
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



function local_dbRSVPUpdate($ID, $Claim, $Pos, $Role, $Note)
	{
	/*
		This function updates the RSVP records during the POST process.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) The values to update in the RSVP table.
		
	RETURNS:
		1) The number of records updated.

	*/
	
$DEBUG = FALSE;
//$DEBUG = TRUE;

	$ValtblName = 'rsvp';
	$query = "UPDATE {$ValtblName} SET";
	$query .= " ClaimCode={$Claim},";
	$query .= " Position={$Pos},";
	$query .= " Role={$Role},";
	$query .= " Note='{$Note}'";
	$query .= " WHERE ID={$ID};";
	if ($DEBUG)
		{
		echo "<b>query:</b><BR />{$query}<BR /><BR />";
		}
	$qryResult = mysql_query($query);
//	$qryResult = TRUE;
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR><BR>Query Sent: ' . $query;
		$message = $GLOBALS['lstErrMsg'];
		if ($DEBUG) { echo "lstErrMsg: <BR />{$message}<BR /><BR />"; }
		}
		
	return $qryResult;

} // END FUNCTION


?> 
