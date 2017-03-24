<?php
/*
	SPECIFICALLY FOR the Mixed-Up Doubles Social events.
	
	This works as a sort of duplicate of the 
	mobile/meditRSVPPrsnMixedDbls.php script, with this one
	handling access via an emailed URL that contains user
	login data.
	
	This is based on the script editRSVPviaEmail.php; which
	should be modified to handle the case for events which
	use the BringingTxt field of the rsvp table.
	
	This script allows a specific person to update their rsvp for a 
	Mixed-Up Doubles Social event by clicking on a link sent out 
	in an email. That link will have a query string param in it 
	that is a key which specifies which person and which event 
	to display the edit form for.
	
	The URL to use for testing: 
		- memberID = Jeff | 2  = 2+10000 = 10002
		- ObjectType = Event | OBJEVENT (43) = 43+10000 = 10043
		- ObjectID = Event | 538 = 538+10000 = 10538
		>> editRSVPviaEmailMixedDbls.php?x=100021004310538
	
	12/07/2014: Version 1.0.

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

				//   The base table and it's row of data 
				//we need to pass around.
$tblName = "rsvp";
$row = array();
 				//   RecordIDs we will need to pass around.
$seriesID = 0;
$eventID = 0;
$rsvpID = 0;



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
	local_GenSection_rsvpPostEdits();
	}
else // Build the data-entry page.
	{
	$qryResult = local_fetchData();
	if(!$qryResult)
		{
		echo "<P>{$lstErrMsg}</P>{$CRLF}";
		include '../INCL_footer.php';
		exit;
		}
	local_GenSection_rsvpEditForm();
	local_GenSection_CurrentBringingList();
	local_GenSection_WhatWeNeedList(3);
	local_GenSection_rsvpClosePage();
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
	GLOBAL $row;
	GLOBAL $seriesID;
	GLOBAL $eventID;
	GLOBAL $rsvpID;

	
	$rsvpInfo = array();
	$infoKey = "";
	$infoKeyVals = array();
	$rsvpUpdateViaEmail = new rsvpUpdateViaEmailLink();


				//----GET LOGIN KEY FROM URL AND PARSE IT.

				//   Create an invalid key to use in case there is no query string.
				//This will trigger a 'no series/id' error message later if no
				//query string.
	$infoKey = $rsvpUpdateViaEmail->loginKey_Create(0, OBJEVENT, 0);
				//   Get what is hopfully a valid key from the Qstring.
	if (array_key_exists('x', $_GET))
		{
		$infoKey = $_GET['x'];
		}
				//   Get needed values from the key.
				//Assumes that the event record ID is what
				//has been encoded into the 'x' query string
				//parameter.
	$infoKeyVals = $rsvpUpdateViaEmail->loginKey_Parse($infoKey);
	$eventID = $infoKeyVals[3];
	$prsnID = $infoKeyVals[1];

				//   Get the rsvp record we'll need, and set the
				//global variables for the master record IDs.
	$table = "qryRsvpBringing";
	$where = "WHERE (evtID={$eventID} AND prsnID={$prsnID})";
	$sort = "";
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
	$row = mysql_fetch_array($qryResult);
	$seriesID = $row['seriesID'];
	$eventID = $row['evtID'];
	$prsnID = $row['prsnID'];

				//   Now I have to trick the rsvpUpdateViaEmail object
				//into logging the user in using the seriesID since I 
				//have not yet created the code to do a login using
				//the rsvp record ID. So I am going to create a new
				//fake loginKey using the seriesID obtained from the
				//rsvp record.
	$infoKey = $rsvpUpdateViaEmail->loginKey_Create($prsnID, OBJSERIES, $seriesID);
				//   Now we can log user into club, if they are 
				//not already logged in.
	$rsvpUpdateViaEmail->userLogin($infoKey, "RSVPUPDATE");

	
	return $qryResult;
}

function local_GenFieldDropCode($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights)
	{
	/*
			This function's logic is a copy of the ADMIN_GenFieldDropCode
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


function local_GenFieldParticipationDrop($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights)
	{
	/*
			This function generates a drop-box field that is specific to the mixed-doubles
			events - so that users get only the following choice descriptions, mapped to
			code set 3 for the rsvpClaimCode:
				* Attending (15)
				* Not Attending (11) 
				* Uncertain (14)
				* No Response (10)
	
	RETURNS:
		1) A string that contains the HTML to output for a form-field.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	
	$claimSet = array();
	
	$claimSet['Attending'] =15;
	$claimSet['Not Attending'] = 11;
	$claimSet['Uncertain'] = 14;
	$claimSet['No Response'] = 10;
	
	$listBox = "<SELECT name={$fldFrmName}>";
	foreach ($claimSet as $description => $code) {
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

	$fldSpec = $listBox;
	return $fldSpec;

} //END FUNCTION


function local_GenBringingPreDefDropBox($filterID, $fldFrmName, $defaultKey)
	{
	/*
		This function duplicates the Tennis_GenLBoxTable() function
		that is in ADMIN include file. But this version does note
		generate the table-based layout HTML around the field.
	
	ASSUMES:
		1) Mysql connection is currently open.
		2) A query has already been defined for generating the
		   List Box view. This query is named using the pattern:
		   qryLBV[tablename].
	
	TAKES:
		1) The name of the table in the DB to generate the list for.
		2) The Filter-ID. An ID that filters the list in some manner.
		   E.g., if the list is for Events, then it needs to be filtered
		   on series.ID so that it lists only events for a specific
		   series. The filter available is pre-defined by the query that
		   has been set up for the specified table's List-Box-View.
		3) The name to assign to the html form field.
		4) The default value, or the current value if using the box
		   to edit an existing record vs creating a new one.
		
	RETURNS:
		1) A string that contains the Option-Select HTML for a form
		   input control.
		2) = "ERROR" if an error occurred.

	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	
	$query = "SELECT ";
	$query .= "Bringing.ID AS ID, ";
	$query .= "Bringing.ItemBringing AS description ";
	$query .= "FROM Bringing ";
	$query .= "WHERE (fkAppliesTo=43 AND fkObjectID={$filterID}) ";
	$query .= "ORDER BY SortText ";
	$query .= ";";
		
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		if ($DEBUG)
			{
			echo "<p>ERROR: {$GLOBALS['lstErrMsg']}</p>";
			}
		}
	
	$listBox = "<SELECT name={$fldFrmName}>";
	while ($row = mysql_fetch_array($qryResult))
		{
		if ($row['ID'] == $defaultKey)
			{
			$listBox .= '<OPTION SELECTED value ="';
			}
		else
			{
			$listBox .= '<OPTION value ="';
			}
		$listBox .=$row['ID'];
		$listBox .= '">';
		$listBox .= $row['description'];
		$listBox .= "</OPTION>{$CRLF}";
		}
	$listBox .= "</SELECT>{$CRLF}";
 
 	if ($DEBUG)
		{
		echo "<p>listBoxHTML: {$listBox}</p>";
		}

 
	return $listBox;


} //END FUNCTION



function local_GenFieldBringingTxt($fldFrmName, $fldDispLen, $fldMaxLen, $fldValue, $fldAuth, $usrRights)
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



function local_dbRSVPUpdate($ID, $Claim, $Pos, $Role, $BringPre, $BringTxt, $Note)
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

						//   NOTE that on A2 server quote escaping happens automatically. 
						//So if we escape there we get the slashes stored, and they 
						//just keep building on each other. I remember in some code 
						//compensating for this locally... or was it a config setting
						//on Apache, or MySql or PHP??
//	$BringTxt = mysql_real_escape_string($BringTxt);
//	$Note = mysql_real_escape_string($Note);
	$ValtblName = 'rsvp';
	$query = "UPDATE {$ValtblName} SET";
	$query .= " ClaimCode={$Claim},";
	$query .= " Position={$Pos},";
	$query .= " Role={$Role},";
	//$query .= " BringingPreDef={$BringPre},";
	$query .= " BringingTxt='{$BringTxt}',";
	$query .= " Note='{$Note}'";
	$query .= " WHERE ID={$ID};";
	if ($DEBUG)
		{
		echo "<b>query:</b><BR />{$query}<BR /><BR />";
		exit;
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
		echo "<b>ERROR:</b>{$message}<BR /><BR />";
		echo "<b>query:</b><BR />{$query}<BR /><BR />";
		exit;
		}
		
	return $qryResult;

} // END FUNCTION




function local_GenSection_rsvpEditForm()
{
	/*
	   This function generates the dat-entry page view.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	GLOBAL $CRLF;
				//   Declare the global error variables.
	GLOBAL $lstErrExist;
	GLOBAL $lstErrMsg;

	GLOBAL $tblName;
	GLOBAL $row;
	GLOBAL $seriesID;
	GLOBAL $eventID;
	GLOBAL $rsvpID;
	
				//   Variables that serve as constants.
	$tbar = "mTennis RSVP for Mixed Doubles";
	$pgL1 = "RSVP";
	$prsnID = $_SESSION['recID'];
				//   Important that this be the person
				//record ID in the fetched rsvp record,
				//and not necessarily the record ID of
				//the currently logged in person.
	$rsvpPrsnID = $row['prsnID'];

				//   Regular variables.
	$pgL2 = "";
	$pgL3 = "";
	$rtnpg = "";
	$ViewLevel = 0;
	$userPrivSeries = "";
	$tmp = "";

	

			//   Output page header stuff.
	$tbar = "mTennis e-RSVP for Mixed Doubles";
	$pgL1 = "Edit RSVP";
	$pgL2 = "Series: {$row['seriesShtName']}";
	$pgL3 = "Person: <b>{$row['prsnFullName']}</b>";
	echo Tennis_BuildHeader('MOBILE', $tbar, $pgL1, $pgL2, $pgL3);

	$rtnpg = "/tennis/mobile/meventPage.php?ID={$eventID}";
	
	
				//   Determine and handle the page display rights via the
				//series ViewLevel setting.
	$ViewLevel = $row['seriesViewLevel'];
	$userPrivSeries = Roster_GetUserRights($seriesID, $ViewLevel, "meditRSVPPrsn", $rights);
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
	echo "<form method='post' action='editRSVPviaEmailMixedDbls.php?ID={$rsvpID}&POST=T'>{$CRLF}";
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>{$CRLF}";
	echo "<input type=hidden name=meta_ADDPG value=''>{$CRLF}";
	echo "<input type=hidden name=meta_POST value=TRUE>{$CRLF}";
	echo "<input type=hidden name=meta_TBL value={$tblName}>{$CRLF}{$CRLF}";
	
	$usrRights = local_AuthorityCheck($seriesID, $eventID);
	if ($DEBUG)
		{
		echo "<BR />User Rights: {$usrRights}";
		echo " Event ID: {$row['evtID']}";
		echo " User ID: {$_SESSION['recID']}<BR />{$CRLF}";
		}
	echo "<input type=hidden name=meta_EVTPURP value={$row['evtPurposeCd']}>{$CRLF}";
	echo "<input type=hidden name=ID value={$row['ID']}>{$CRLF}";
	$eventName = "<STRONG>{$row['evtName']}</STRONG>";
	$dispDate = Tennis_DisplayDate($row['evtStart']);
	$dispTime = Tennis_DisplayTime($row['evtStart'], True);
	echo "{$eventName}<BR />{$CRLF}"; 
	echo "{$dispDate} // {$dispTime}<BR />&nbsp;<BR />{$CRLF}"; 
	
	$fldAuth = "MGR&self={$rsvpPrsnID}";
	$fldFrmName = "ClaimCode";
	$cdSet = 3;
	$fldValue = $row['rsvpClaimCode'];
	$rowHTML = local_GenFieldParticipationDrop($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights);
	echo "Participation&nbsp;&raquo;&nbsp;<BR />{$rowHTML}{$CRLF}";
	
				//   Do not create editable Position and Roles fields if the event's
				//purpose is "Recreational." Because they really only apply to
				//matches. And also note that 'Self' does not apply for
				//authority on those - you have to be an admin or event
				//manager to edit those fields.
				//   IF the event purpose is a "Party" tho, then we assume the use
				//of the 'bringing' fields.
	if (($row['evtPurposeCd'] !=6) && ($row['evtPurposeCd'] !=7) && ($row['evtPurposeCd'] !=9))
		{
		$fldAuth = "MGR";
		$fldFrmName = "Position";
		$cdSet = 5;
		$fldValue = $row['rsvpPositionCode'];
		$rowHTML = local_GenFieldDropCode($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights);
		echo "<BR />Position<BR />&nbsp;&nbsp;&raquo;{$rowHTML}{$CRLF}";
		
		$fldAuth = "MGR";
		$fldFrmName = "Role";
		$cdSet = 4;
		$fldValue = $row['rsvpRoleCode'];
		$rowHTML = local_GenFieldDropCode($fldFrmName, $cdSet, $fldValue, $fldAuth, $usrRights);
		echo "<BR />Role<BR />&nbsp;&nbsp;&raquo;{$rowHTML}{$CRLF}";
		}
	else
		{
		$fldFrmName = "Position";
		$fldValue = $row['rsvpPositionCode'];
		echo "<input type=hidden name={$fldFrmName} value={$fldValue}>{$CRLF}";
		$fldFrmName = "Role";
		$fldValue = $row['rsvpRoleCode'];
		echo "<input type=hidden name={$fldFrmName} value={$fldValue}>{$CRLF}";
		}
		
	if ($row['evtPurposeCd']=9) //A party event, use the bringing fields.
		{
		/*
		$fldAuth = "MGR&self={$prsnID}";
		$fldFrmName = "BringingPreDef_{$i}";
		$fldValue = $row['rsvpBringingPreDef'];
		$rowHTML = local_GenBringingPreDefDropBox($row['evtID'], $fldFrmName, $fldValue);
		echo "<BR />Bringing&nbsp;&raquo;&nbsp;{$rowHTML}{$CRLF}";
		*/

		$fldAuth = "MGR&self={$rsvpPrsnID}";
		$fldFrmName = "BringingTxt";
		$fldValue = $row['rsvpBringingTxt'];
		$rowHTML = local_GenFieldBringingTxt($fldFrmName, 22, 100, $fldValue, $fldAuth, $usrRights);
		echo "<BR />";
		echo "<BR />Will You Bring Anything?";
		echo "<BR /><FONT style=\"font-size:smaller\">";
		echo "(first, review below list of what others are bringing)";
		echo "</FONT>&nbsp;&raquo;&nbsp;";
		echo "<BR />{$rowHTML}<BR />&nbsp;&nbsp;{$CRLF}";
		echo "<input type=hidden name=BringingPreDef value={$row['rsvpBringingPreDef']}>{$CRLF}";
		}
			
	$fldAuth = "MGR&self={$rsvpPrsnID}";
	$fldFrmName = "Note";
	$fldValue = $row['rsvpNote'];
	$rowHTML = local_GenFieldNote($fldFrmName, 3, 22, $fldValue, $fldAuth, $usrRights);
	echo "<BR />Notes&nbsp;&raquo;&nbsp;<BR />{$rowHTML}<BR />{$CRLF}";
	echo "<BR />{$CRLF}{$CRLF}";
	
				//   Make the form's Save button.
	echo "<input type='submit' value='SAVE'>{$CRLF}";
				//   Close out the form.
	echo "</form>{$CRLF}{$CRLF}";
				//   Close out the form division.
	echo "&nbsp;</DIV>{$CRLF}";
	
} // END FUNCTION



function local_GenSection_rsvpPostEdits()
{
	/*
	   This function generates the script for posting the
	data entered into data-entry page view.
	*/
	
	GLOBAL $CRLF;
				//   Declare the global error variables.
	GLOBAL $lstErrExist;
	GLOBAL $lstErrMsg;
	
	GLOBAL $tblName;

	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
				//   Variables that serve as constants.
				
				//   Regular variables.
				

	if ($_POST['meta_POST'] == 'TRUE')
		{
		$recID = $_POST["ID"];
		$evtprp = $_POST["meta_EVTPURP"];
		$rsvpClaim = $_POST["ClaimCode"];
		$position = $_POST["Position"];
		$role = $_POST["Role"];
		$BringPre = $_POST["BringingPreDef"];
		$BringTxt = $_POST["BringingTxt"];
		$note = $_POST["Note"];
		$gotoPageAfterPostng = $_POST["meta_RTNPG"];
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
			echo "Simulated Post ---->><BR />";
			echo "Event ID: {$evtID}<BR />";
			echo "Event Purpose: {$evtprp}<BR />";
			echo "Claim: {$rsvpClaim}<BR />";
			echo "Position: {$position}<BR />";
			echo "Role: {$role}<BR />";
			echo "BringPre: {$BringPre}<BR />";
			echo "BringTxt: {$BringTxt}<BR />";
			echo "Note: {$note}<BR /><BR />";
			//include "../INCL_footer.php";
			//exit;
			}
		local_dbRSVPUpdate($recID, $rsvpClaim, $position, $role, $BringPre, $BringTxt, $note);
			//   Use standard function to auto-re-direct back to
			//the display page.
		echo ADMIN_Post_HeaderOK($tblName, $gotoPageAfterPostng, $message, "GO");
		}

} // END FUNCTION



function local_GenSection_rsvpDisplayForm()
{

} // END FUNCTION



function local_GenSection_CurrentBringingList()
{
	/*
	   This function generates the HTML for listing out
	what everyone is bringing to the event.
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
	$htmlHR = "<HR style=\"border-style:inset; border-width:1px\">";
	$htmlSectionTitle = "<b>What Folks are Bringing</b>:";
	$htmlIndent = "&nbsp;&nbsp;&nbsp;*&nbsp;";

				
				//   Regular variables.
	$out = "";
	$selCrit = "";
	$dbRow = array();


	
			//   Get the recordset for:
			// * all in the event who have marked themselves as Available, Confirmed or Late AND
			// * BringingTxt is NOT NULL.
	$selCrit = "((rsvpClaimCode=15 OR rsvpClaimCode=13 OR rsvpClaimCode=16)";
	$selCrit .= " AND ";
	$selCrit .= "(rsvpBringingTxt IS NOT NULL))";
	
	if(!$qryResult = Tennis_OpenViewGeneric('qryRsvpBringing', "WHERE (evtID={$eventID} AND {$selCrit})", "ORDER BY prsnLName, prsnFName"))
		{
		echo "<P>Error in Query:</P>";
		echo "<P>{$lstErrMsg}</P>";
		echo "<P>Unable to list what everyone is bringing due to the above error.</P>";
		}
	

			// Open a new html container to hold the list.
	$out ="";
	$out .= $htmlHR;
	$out .= "<P>";
	$out .= $htmlSectionTitle;
	$out .= "<BR />";
	while ($dbRow = mysql_fetch_array($qryResult)) {
		if (strlen(trim($dbRow['rsvpBringingTxt']))>1) {
			$out .=$htmlIndent;
			$out .=$dbRow['rsvpBringingTxt'];
			$out .=" (";
			$out .=$dbRow['prsnFullName'];
			$out .=")";
			$out .="<BR />{$CRLF}";
		}
	}
	$out .="</P>"; // Close the list.

	echo $out;
		
	return $qryResult;
	
	
	
} // END FUNCTION



function local_GenSection_WhatWeNeedList($textBlockID)
{
	/*
	   This function generates the HTML for listing out
	what items we need folks to bring.
	   This function is drawing the text from a Text Block
	record. BUT I have hard-coded the text block record ID
	into the code that calls this function. So this
	section of this page is hard-wired for the Mixed-doubles
	Social event. To make this generic I'd need to figure 
	out a decent way to parameter-itize this.
	*/
	
	GLOBAL $CRLF;
				//   Declare the global error variables.
	GLOBAL $lstErrExist;
	GLOBAL $lstErrMsg;
	
	GLOBAL $seriesID;
	GLOBAL $eventID;
	GLOBAL $rsvpID;

	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
				//   Variables that serve as constants.
	$htmlSectionTitle = "<b>What We Need</b>:";
	$htmlIndent = "&nbsp;&nbsp;&nbsp;*&nbsp;";
	$textBlockTableName = "txtBlock";

				
				//   Regular variables.
	$out = "";
	$selCrit = "";
	$dbRow = array();


			//   If no text block given, then do nothing,
			//leave this section a complete blank.
	IF ($textBlockID == 0)
		{
		return;
		}
			//   Get the text block record.
	if(!$qryResult = Tennis_GetSingleRecord($dbRow, $textBlockTableName, $textBlockID))
		{
		echo "<P>Error fetching the list of what we need:</P>";
		echo "<P>{$lstErrMsg}</P>";
		echo "<P>Unable to list what we need due to the above error.</P>";
		}
	
			//   If the text block is inactive, then do nothing,
			//leave this section a complete blank.
	IF ($dbRow['BlockText'] == FALSE)
		{
		return;
		}

			//   Show the text block.
	$out ="";
	$out .= "<P>";
	$out .= $htmlSectionTitle;
	$out .= "<BR />";
	$out .=$dbRow['BlockText'];
	$out .="</P>";
	echo $out;


} // END FUNCTION



function local_GenSection_rsvpClosePage()
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
	$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='/tennis/mobile/meventPage.php";
	$out .= "?ID={$eventID}'>Event Page</A><BR />{$CRLF}";
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
	
	$_SESSION['RtnPg'] = "/tennis/mobile/meventPage.php?ID={$eventID}";
	echo  Tennis_BuildFooter('NORM', $_SESSION['RtnPg']);
	//echo  Tennis_BuildFooter('ADMIN', "/tennis/mobile/mlistSeriesRoster.php?ID={$seriesID}");


} // END FUNCTION


?> 
