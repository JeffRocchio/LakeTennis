<?php
/*
	This script allows a user to login. NOTE: The logic here is very
	complex. If trying to debug or modify this script you are strongly
	advised to diagram out the various decision-paths.
	
Functions this Script Performs (controlled by the value passed via the
$_GET['POST'] query parameter):

	A.	GETs UserID and Pass from the standard page-footer login form and
		processes the user login. (See Login Logic steps below for how this
		logic works). $_GET['POST'] == "T"
		
	B.	Logs a user out. $_GET['POST'] == "LOUT"
	
	C.	Displays a form for a user to login with. $_GET['POST'] == null or "D"
	
	D.	Allows a logged in user to switch clubs. $_GET['POST'] == "S"
	
Login Logic (for A above):

	1.	First validate the user's credentials. ID/PASS combo. If this
		validation fails, given an error message and let them try again.

   2. Run query to see how many clubs userID is an active member of.
   	I.e., 1-and-only-1 (active) club, several clubs, or no clubs.
   	
   3. If userID is valid, and if user is logging in from a page within a club
   	they we log them into that club (assuming they are a member of that
   	club) even if they belong to other clubs also. (i.e., they have already
   	been viewing pages within a club as a 'guest,' but are now attempting
   	to login to gain their full access rights on that page).

   4. IF active member of only 1 (active) club, log them into that
   	club and take them to it's home page.
   
   5. IF active member of >1 (active) club, present them with a list of 
   	clubs they are active in and have them select which club to login
   	to. Then log them into that club and take them to it's home page.
   	
   6. DO NOT NEED THIS STEP, SEE NOTE-1 BELOW.
   	IF not an active member of any (active) clubs, check to see if they
   	are INACTIVE member of any clubs. If so, give them some kind of
   	message, then take them back to the lobby where all the
   	clubs are listed, but showing them logged in - but their rights
   	will be 'guest' for all clubs.
   	
   7.	IF active member of 0 (active) clubs, check to see if they are in
   	the person table at all. IF yes, then give them some kind of message
   	back to this effect, then take them back to the lobby, where all the
   	clubs are listed, but showing them logged in - but their rights
   	will be 'guest' for all clubs.

	NOTE-1:	We are not permitting them to login to clubs they are inactive
				in. We are not even concerned about showing them any list of clubs
				they are inactive in.   	
	
------------------------------------------------------------------ */
session_start();
if (!isset($_SESSION['userType'])) Session_LogOut();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
$rtnpg = Session_SetReturnPage();



$DEBUG = FALSE;
//$DEBUG = TRUE;



//----DECLARE GLOBAL VARIABLES------------------------------------------------>
global $CRLF;
global $debugNote;




				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------------->

				//   Server string so we can make URLs that are valid
				//for whatever server (local or INET) we are running on.
$server = "http://".$_SERVER['HTTP_HOST'];
$clubhome = $server . "/ClubHome.php";

				//   Holds the database query result resource.
$qryResult = "";

				//   Used to fetch the number of records the database
				//query returned or acted upon.
$numRecords = "0";

				//   Holds the action to take on this page. Passed in via
				//the URL's query-string parameters.
$formAction = "";

				//   To hold debug message header.
$debugHeader = "<p>****DEBUG MESSAGE****</P>";

				//   To hold debug output.
$debugMessage = $debugHeader;

				//   To hold display output.
$out = "";

				//   Scratch strings we can use for whatever.
$tmp = "";
$tmp2 = "";

				//   To hold the current user's identifying info.
$UserID = "";
$pass = "";
$personRecID = "";

				//   To hold clubID.
$clubID = "";

				//   Is set to TRUE when/if we detect that a user is a member of 
				//more than one club.
$multiclubs = FALSE;

$row = array();



//----GET URL QUERY STRING DATA------------------------------------------------>
if (array_key_exists('POST', $_GET))
	{
	$formAction = $_GET['POST'];
	}
else
	{
	$formAction = "D";
	}
if (array_key_exists('ID', $_GET)) $clubID = $_GET['ID'];



//----CONNECT TO MYSQL--------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	

//============================================================================>>
//====FORM ACTION D===========================================================>>
//----FORM ACTION: SWITCH CLUB------------------------------------------------->
//============================================================================>>
if ($formAction == 'S')
	{

	$tbar = "Member Switch Club";
	$pgL1 = "";
	$pgL2 = "Login";
	$pgL3 = "Member Switch Club";
	echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);
	
	if ($DEBUG)
		{
		$debugMessage .= "<P>IN FORM ACTION D - SWITCH CLUBS</p>";
		$debugMessage .= "SESSION['multiClubs']: {$_SESSION['multiClubs']} <BR>";
		$debugMessage .= "SESSION['siteUser']: {$_SESSION['siteUser']}";
		}
	if($_SESSION['multiClubs'] == TRUE and $_SESSION['siteUser'] == TRUE)
		{
		$UserID = $_SESSION['UserID'];
		$personRecID = $_SESSION['recID'];
		Session_LogOut();
		$_SESSION['UserID'] = $UserID;
		$_SESSION['recID'] = $personRecID;
		$_SESSION['multiClubs'] = TRUE;
		local_SelectClub($UserID);
		}
	else
		{
		$out = "<P>Cannot switch clubs - you are not a member of any other clubs.</P>";
		$out .= "<P>Click OK to continue.</P>";
		$out .= "<P><A HREF='{$rtnpg}'>OK</A></P>";
		echo $out;	
		}
	if ($DEBUG)
		{
		echo $debugMessage;
		$debugMessage = $debugHeader;
		echo $debugNote;
		$debugNote = "";
		}

	echo  Tennis_BuildFooter('NORM', $rtnpg);
	exit;
	}



//============================================================================>>
//====FORM ACTION A===========================================================>>
//----FORM ACTION: PROCESS LOGIN REQUEST - 1ST ATTEMPT------------------------->
//============================================================================>>
if ($formAction == 'T')
				//   User has entered ID/Pass into form and clicked on LOGIN
				//button. Form fields contain the ID/Pass values.
	{
				//---->>STEP #1: Validate user credentials.

				//   Collect the data from the input form.
	$UserID = $_POST['UserID'];
	$pass = $_POST['Password'];

				//   See if user ID/PASS is valid. If not, show the user a
				//message and take them someplace appropriate.
	if (!local_ValidateUser($UserID, $pass, $out, $DEBUG))
		{
		exit;
		}
				//   Having a valid user, collect some info we'll need about
				//this user and which was set by the call to local_ValidateUser().
	$UserID = $_SESSION['UserID'];
	$personRecID = $_SESSION['recID'];


				//---->>STEP #2: Have a valid user, how many clubs?.
	$tbar = "Member Login Confirm";
	$pgL1 = "";
	$pgL2 = "Login";
	$pgL3 = "Member Login Confirm";
	echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);
	$qryResult = Tennis_ClubsUserIsIn($UserID, "A", "A", $DEBUG);
	if ($DEBUG)
		{
		echo $debugMessage;
		$debugMessage = $debugHeader;
		echo $debugNote;
		$debugNote = "";
		}
	if (!$qryResult)
		{
		echo $lstErrMsg;
		echo  Tennis_BuildFooter('NORM', $rtnpg);
		exit;
		}
	$numRecords = mysql_num_rows($qryResult);
	if ($numRecords > 1) $multiclubs = TRUE;

				//---->>STEP #3: User is valid. Now, is user logging in from a
				//page within a club they have already been browsing? And if so,
				//are they actually a member of that club? IF SO, log them into
				//that club immediately even if they are members of multiple
				//clubs.
	$clubID = $_SESSION['clubID'];
	if($clubID > 0)
		{
		if ($DEBUG)
			{
			$debugMessage .= "<P>In STEP#2: User is already on a page in a club.<p>";
			$debugMessage .= "<P>ClubID = {$clubID} // UserID= {$UserID}";
			$debugMessage .= " // personRecID= {$personRecID}";
			$debugMessage .= " // return page= {$_POST['meta_RTNPG']}<p>";
			echo $debugMessage;
			$debugMessage = $debugHeader;
			}
		if(Tennis_IsUserInClub($personRecID, $clubID, FALSE) > 0)
			{
			$rtnpg = $_POST['meta_RTNPG'];
			local_LoginToClub($clubID, $personRecID, $multiclubs);
			if ($DEBUG)
				{
				echo $debugNote;
				$debugNote = "";
				}
			echo  Tennis_BuildFooter('NORM', $rtnpg);
			exit;
			}
		}




				//---->>STEP #4, 5 and 7: Based on number of records returned, take
				//the specified action.
	switch ($numRecords)
		{
		case 0:
				//This is for case where user is not an active member
				//of any active club.
			if ($DEBUG)
				{
				$debugMessage .= "<P>In Case 0: NumRecs= {$numRecords} <p>";
				}
			$_SESSION['siteUser'] = TRUE;
			$_SESSION['UserID'] = $UserID;
			$failReasonTxt = "NOT A CURRENTLY ACTIVE MEMBER OF ANY CLUB.<BR>";
			$failReasonTxt .= "(Tho it appears that you may have been an active ";
			$failReasonTxt .= "Club member at one time, with UserID: <b>{$UserID}</b>)";
			local_LogInErrorPage($failReasonTxt, FALSE);
			if ($DEBUG)
				{
				echo $debugMessage;
				$debugMessage = $debugHeader;
				echo $debugNote;
				$debugNote = "";
				}
			exit;
			break;

		case 1:
				//   User is active member of only 1 club; go ahead and log
				//them in.
				//   Get the clubID of the one club this user is a member of.
			$row = mysql_fetch_array($qryResult);
			$clubID = $row['clubID'];
			if ($DEBUG)
				{
				$debugMessage .= "<P>In Case 1: NumRecs= {$numRecords} <p>";
				$debugMessage .= "<P>ClubID = {$clubID} // UserID= {$UserID}";
				$debugMessage .= " // personRecID= {$personRecID}<p>";
				echo $debugMessage;
				$debugMessage = $debugHeader;
				echo $debugNote;
				$debugNote = "";
				}
			local_LoginToClub($clubID, $personRecID, FALSE);
			break;

		default:
				//   User is active member of more than one active club;
				//Show them a list of their clubs to select from, then once we
				//have the selected club, log them into it.
				//NOTE that the prior successful call to 
				//Session_ValidateCredentials() will have set the
				//session variables for [UserID] and [recID], se we don't have
				//to worry about saving them here.
			if ($DEBUG)
				{
				$debugMessage .= "<P>In Case default: NumRecs= {$numRecords} <p>";
				}
			local_SelectClub($UserID);
		}
	

	if ($DEBUG)
		{
		$debugMessage .= "<hr>";
		echo $debugMessage;
		$debugMessage = $debugHeader;
		}
	echo  Tennis_BuildFooter('NORM', $rtnpg);
	exit;
	}



//----FORM ACTION: PROCESS LOGIN AFTER MULTI-CLUB SELECT LIST------------------>
				//   In this situation we have already validated the user's
				//credentials, and stored the User's recordID in the session
				//variable: $_SESSION['recID']. The $clubID is passed in via
				//the URL query-string from the 'login' links that were on the
				//club selection list we showed the user (see $_GET['ID'] earlier
				//in this script).
if ($formAction == 'T2')
	{
				//   Output page header.
	$tbar = "Member Login Confirm";
	$pgL1 = "";
	$pgL2 = "Login";
	$pgL3 = "Member Login Confirm";
	echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);
	
				//NOTE that the prior successful call to 
				//Session_ValidateCredentials() will have set the
				//session variables for [UserID] and [recID], which we can
				//use here.
	$personRecID = $_SESSION['recID'];
	if ($DEBUG)
		{
		$debugMessage .= "<p>In Posting Section; AFTER selection from";
		$debugMessage .= " multi-club list.</p>";
		echo $debugMessage;
		$debugMessage = $debugHeader;
		}

	$rtnpg = $clubhome;
	local_LoginToClub($clubID, $personRecID, TRUE);
	if ($DEBUG)
		{
		echo $debugMessage;
		$debugMessage = $debugHeader;
		echo $debugNote;
		$debugNote = "";
		}

	$rtnpg = $clubhome;
	echo  Tennis_BuildFooter('NORM', $rtnpg);
	exit;
	}




//============================================================================>>
//====FORM ACTION B===========================================================>>
//----FORM ACTION: LOG USER OUT------------------------------------------------>
//============================================================================>>
if ($formAction == 'LOUT')
	{
	$rtnpg = "/index.php";

	Session_LogOut();

	$tbar = "Member Logout Confirm";
	$pgL1 = "";
	$pgL2 = "Logout";
	$pgL3 = "Member Logout Confirm";

	echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);

	$out = "<P>You have been logged out.</P>";
	$out .= "<P>Click OK to continue.</P>";
	$out .= "<P><A HREF='{$rtnpg}'>OK</A></P>";
	
	echo $out;	

	echo  Tennis_BuildFooter('NORM', $rtnpg);
	exit;
	}




//============================================================================>>
//====FORM ACTION C===========================================================>>
//----NOT LOGGING IN, DISPLAYING A FORM TO ENTER ID/PASS----------------------->
//============================================================================>>
else
	{

					//   Output page header stuff.
	$tbar = "Member Login";
	$pgL1 = "";
	$pgL2 = "Login";
	$pgL3 = "Member Login";
	echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);
	


				//   Create a form for the user to enter
				//their login credentials.
	echo "<form method='post' action='login.php?POST=T'>";
	
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";
	
	echo "<input type=hidden name=ID value=0>";
	
	echo "<table border='1' CELLPADDING='3' rules='rows'>";
	


				//   User ID.
	$fldSpecStr = "<INPUT TYPE=text NAME=UserID ";
	$fldSpecStr .= "SIZE=30 MAXLENGTH=100 ";
	$fldSpecStr .= "VALUE=''>";
	$fldLabel = "User ID";
	$fldHelp = "";
	$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
	echo $rowHTML;

				//   Password.
	$fldSpecStr = "<INPUT TYPE=text NAME=Password ";
	$fldSpecStr .= "SIZE=30 MAXLENGTH=100 ";
	$fldSpecStr .= "VALUE=''>";
	$fldLabel = "Password";
	$fldHelp = "";
	$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
	echo $rowHTML;
	
	echo "<tr>{$CRLF}<td colspan='2'><p align='center'><input type='submit' value='Enter record'>";
	echo "</td>{$CRLF}</tr>{$CRLF}";
	
	echo "</table>{$CRLF}";
	
	echo "</form>{$CRLF}";
	
	
	include 'INCL_footer.php';
	
	}
	





//============================================================================>>
//====LOCAL FUNCTIONS==========================================================>
//============================================================================>>


function local_ValidateUser($UserID, $pass, &$out, $DEBUG=FALSE)
	/*
		This function performs STEP #1 of the login procedure -- validate
	the user's credentials.
	
	RETURNS:
		1) TRUE if ID/PASS has been validated. FALSE otherwise.
		2) The passed-by-reference parameter $out will contain an appropriate
			message to the user if validation fails (if function returns
			FALSE).
		
	*/
	{
	
	global $debugMessage;
	global $rtnpg;
	
	$fnResult = "";
	
	
	$fnResult = Session_ValidateCredentials($UserID, $pass, $DEBUG);
	switch ($fnResult)
		{
		case "V":  //User is valid.
						//   NOTE that the successful call to 
						//Session_ValidateCredentials() will have set the
						//session variables for [UserID], [recID] and [siteUser].
			if ($DEBUG)
				{
				$debugMessage .= "<P>In Case V: Valid User.<p>";
				$debugMessage .= "<P>UserID = {$UserID}</p>";
				}
			break;
			
		case "I":  //User ID is Unknown.
			$_SESSION['siteUser'] = FALSE;
			$failReasonTxt = "UNKNOWN USER ID: {$UserID}";
			if ($DEBUG)
				{
				$debugMessage .= "<P>In Case I: ID Unknown.<p>";
				$debugMessage .= "<P>UserID = {$UserID}</p>";
				}
			local_LogInErrorPage($failReasonTxt);
			break;

		case "P": //User password is incorrect.
			$_SESSION['siteUser'] = FALSE;
			$failReasonTxt = "INCORRECT PASSWORD.";
			if ($DEBUG)
				{
				$debugMessage .= "<P>In Case P: Password Unknown<p>";
				$debugMessage .= "<P>UserID = {$UserID}</p>";
				$debugMessage .= "<P>pass = {$pass}</p>";
				}
			local_LogInErrorPage($failReasonTxt);
			break;

		default:  //Other system error.
			$_SESSION['siteUser'] = FALSE;
			$failReasonTxt = "A System Error Occurred.<BR>";
			$failReasonTxt .= "[Report error as being in Session_ValidateCredentials().]";
			$failReasonTxt .= "<BR><BR>System Error Message:<BR><BR>";
			$failReasonTxt .= $lstErrMsg;
			if ($DEBUG)
				{
				$debugMessage .= "<P>In Case Default: Other System Error.<p>";
				$debugMessage .= "<P>UserID = {$UserID}</p>";
				}
			local_LogInErrorPage($failReasonTxt);
		}


	if($fnResult != "V")
		{
		return FALSE;
		}
	else
		{
		return TRUE;
		}


} // END function local_ValidateUser()









function local_LoginToClub($clubID, $personID, $multiClub)
	/*
		This function sets the Session variables to, in effect, log a user
	into a specific club.

		This function assumes:

		1) The user has been authenticated as a user of the web site.
		2)	The user is a member of the club we are going to log them into
			(and that this fact has been verified before calling this
			function.)
		
	RETURNS:
		1) Examines the $_SESSION['clubConflict'] and if there is a conflict it
			sets the $rtnpg to the logged-in club's home page and resets
			the $_SESSION['clubConflict'] value to FALSE.
		
	*/
	{
	global $CRLF;
	global $DEBUG;
	global $rtnpg;
	global $debugMessage;
	global $debugHeader;
	global $debugNote;
	global $server;
	
	if(Session_SetUserSessionValues($clubID, $personID, $multiClub, $DEBUG) == TRUE)
		{
		if($_SESSION['clubConflict']==TRUE)
			{
			$rtnpg = "HTTP://{$server}/ClubHome.php";
			$_SESSION['clubConflict'] = FALSE;
			}
		echo "<P>You are logged in AS: {$_SESSION['userName']}.<BR><BR>";
		echo "Click OK to continue:</P>";
		echo "<P STYLE='font-size: large'><A HREF='{$rtnpg}'>OK</A></P>";
		}
	else
					//   If we execute this code, some unanticipated bug has
					//occurred.
		{
		$failReasonTxt = "LOGIN FAILED DUE TO SYSTEM ERROR.<BR>";
		$failReasonTxt .= "(local_LoginToClub function, clubID: {$clubID} // personID {$personID})";
		local_LogInErrorPage($failReasonTxt, FALSE);
		}

	if ($DEBUG)
		{
		$debugMessage .= "<P>Credentials Set:<p>";
		$debugMessage .= "<P>UserType: {$_SESSION['userType']}<BR>";
		$debugMessage .= "Name: {$_SESSION['userName']}<BR>";
		$debugMessage .= "userID: {$_SESSION['UserID']}<BR>";
		$debugMessage .= "recID: {$_SESSION['recID']}<BR>";
		if($_SESSION['siteUser'] == TRUE) $debugMessage .= "siteUser: TRUE<BR>";
		if($_SESSION['multiClubs'] == TRUE) $debugMessage .= "multiClubs: TRUE<BR>";
		$debugMessage .= "clubID: {$_SESSION['clubID']}<BR>";
		$debugMessage .= "clubName: {$_SESSION['clubName']}<BR>";
		$debugMessage .= "admin: {$_SESSION['admin']}<BR>";
		$debugMessage .= "evtmgr: {$_SESSION['evtmgr']}<BR>";
		if($_SESSION['member'] == TRUE) $debugMessage .= "member: TRUE<BR>";
		if($_SESSION['admin'] == TRUE) $debugMessage .= "admin: TRUE<BR>";
		if($_SESSION['evtmgr'] == TRUE) $debugMessage .= "evtmgr: TRUE<BR>";
		if($_SESSION['clbmgr'] == TRUE) $debugMessage .= "clbmgr: TRUE<BR>";
		$debugMessage .= "</p>";
		$debugMessage .= "<hr>";
		}

}


function local_SelectClub($UserID)
/*
	   This function generates a list of all active clubs the person
	   is a member of in a way that the user can select which club
	   to login to.
*/
	{
	global $CRLF;
	global $DEBUG;
	global $debugMessage;
	global $debugHeader;
	global $debugNote;
	
	global $qryResult;
	
	$row = array();
	$yourRole = "";
	$where = "";
	$out = "";

	if ($DEBUG)
		{
		$debugMessage .= "<P>Inside local_SelectClub()<p>";
		$debugMessage .= "</p>";
		echo $debugMessage;
		$debugMessage = "";
		}

				//   Explain to the user what is going on.
	$out = "<p>You are a member of several clubs. Please select which";
	$out .= " club you would like to log into at this time.";
	$out .= "</P>";
	$out .= "<P>(NOTE: Once logged in, you may switch clubs by using the";
	$out .= " link at the bottom of any page by your name.)";
	$out .= "</P>";
	echo $out;

				//   Open the DB view to gen the list from.
	$qryResult = Tennis_ClubsUserIsIn($UserID, $activeMember="ALL", $activeClub="ALL", $DEBUG);
	if ($DEBUG)
		{
		echo $debugMessage;
		$debugMessage = $debugHeader;
		echo $debugNote;
		$debugNote = "";
		}
	if (!$qryResult)
		{
		echo $lstErrMsg;
		echo  Tennis_BuildFooter('NORM', $rtnpg);
		exit;
		}
	

				//   Open club list display table in standard record-detail-display format.
	$out = "{$CRLF}{$CRLF}<TABLE CLASS='ddTable' CELLSPACING='2' CELLPADDING='2'>{$CRLF}";

					//   Header Row.
	$out .= "<THEAD>{$CRLF}";
	$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'style=\"width: 1%\">";
	$out .= "<P CLASS='ddSectionTitle'>&nbsp;&nbsp;&nbsp;</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Club Name</P></TD>{$CRLF}";
	$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddSectionTitle'>Your Role</P></TD>{$CRLF}";
	$out .= "</TR></THEAD>{$CRLF}";
					//   Open table body.
	$out .= "<TBODY>{$CRLF}";
	echo $out;
	while ($row = mysql_fetch_array($qryResult))
		{
				//   Set the Role In Club text. The check for 
				//row['HighPriv']==4 is to see if the user is a
				//site super-admin (jeff rocchio).
		if ($row['userPriv']==0) {	$yourRole = "MEMBER"; }
		else { $yourRole = $row['userPrivText']; }
		if ($row['HighPriv']==4) { $yourRole = "SUPER ADMIN"; }

				//   Output the table.
		$out = "<TR CLASS='ddTblRow'>{$CRLF}";
					//   Column for a 'Login' link.
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
		$out .= "<A HREF='login.php?ID={$row['clubID']}&POST=T2'>";
		$out .= "LOGIN</A>";
		$out .= "</P></TD>{$CRLF}";
					//   Club Name.
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['clubName']}</P></TD>{$CRLF}";
					//   User's rights on the club.
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$yourRole}</P></TD>{$CRLF}";
		$out .= "</TR>{$CRLF}{$CRLF}";
		echo $out;
		}
				//   Close table.
	$out = "</TBODY></TABLE>{$CRLF}{$CRLF}";
	echo $out;
}


function local_LogInErrorPage($failReasonTxt, $makePgHeader=TRUE)
/*
	   This function outputs a message page for cases where there was a 
	   login error.
*/
	{
	global $CRLF;
	global $DEBUG;
	global $debugMessage;
	global $debugHeader;
	global $debugNote;
	global $rtnpg;
	global $server;
	
	$out ="";

	if($makePgHeader)
		{
		$tbar = "Member Login Error";
		$pgL1 = "";
		$pgL2 = "Login";
		$pgL3 = "Member Login Error";
		echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);
		}

	if ($DEBUG)
		{
		$debugMessage .= "<p>In local_LogInErrorPage().</p>";
		$debugMessage .= "<p>Session clubID: {$_SESSION['clubID']}";
		$debugMessage .= "<BR>Session UserType: {$_SESSION['userType']}";
		$debugMessage .= "<BR>Session UserID: {$_SESSION['UserID']}";
		$debugMessage .= "<BR>Session (person) recID: {$_SESSION['recID']}";
		$debugMessage .= "<BR>Session siteUser: {$_SESSION['siteUser']}";
		$debugMessage .= "<BR>Session member: {$_SESSION['member']}</p>";
	
		$debugMessage .= "<p>rtnpg before adjust= {$rtnpg}.</P>";
		}
	if($_SESSION['clubID'] == 0) $rtnpg="http://{$server}/index.php";
	$out = "<P>Login Failed.</P";
	$out .= "<p>{$failReasonTxt}</P>";
	$out .= "<p>(If you believe you have received this message in error, ";
	$out .= "please contact your club administrator or manager.)</P>";
	$out .= "<P>Click OK to continue.</P>";
	$out .= "<P><A HREF='{$rtnpg}'>OK</A></P>";
	echo $out;
	
	if ($DEBUG)
		{
		$debugMessage .= "<BR>rtnpg after adjust= {$rtnpg}.</P>";
		echo $debugMessage;
		$debugMessage = $debugHeader;
		echo $debugNote;
		$debugNote = "";
		}
	echo  Tennis_BuildFooter('NORM', $rtnpg);


} // END of local_LogInErrorPage()

?> 
