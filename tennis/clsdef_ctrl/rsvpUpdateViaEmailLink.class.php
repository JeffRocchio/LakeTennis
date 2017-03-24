 <?php
/*
	=======================
	CLASS: rsvpUpdateViaEmailLink.
	=======================

	PURPOSE: To provide methods that handle the update rsvp email link. That is,
	when we send out emails that request series participants to update their rsvp
	status. These emails contain a link which is specific to that member and
	the series. The script for that link needs to: (a) Validate the member, just 
	to be	sure they aren't using an old email. (b) Log them into the correct club
	based on the series ID in the link. (c) Serve them up a page where
	they can update their rsvps.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) .

	06/17/2012:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class rsvpUpdateViaEmailLink
{

		
	//---GET/SET Functions-------------------------------------------------------


	//---------------------------------------------------------------------------
	public function loginKey_Create($memRecID, $objType, $objID)
	{
	/*	PURPOSE: Create a key that can be used to ID and login the user based
		on this key being passed to a page-script from the query string.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) $memRecID: The club member's dbms record ID.
			2) $objType: Object of interest. Must be one of the "OBJECT IDs"
				defined in the INCL_Tennis_CONSTANTS.php file.
			3) $objID: The dbms record # associated with #2 above.
				
		RETURNS --:
			
		   1) A hash-key that can be used in a URL's query string. This can then
		   	be passed to the loginKey_Decode() function to parse out the
		   	relevant individual parameters.
		   2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$returnString = "";
	$field1 = 0;
	$field2 = 0;
	$field3 = 0;
	$keyAsNumber = 0;
	$keyAsString = "";
	
					//		Logic------------------------------------------------------
	$field1 = $memRecID + 10000;
	$field2 = $objType + 10000;
	$field3 = $objID + 10000;
	$keyAsString = $field1 . $field2 . $field3;

	$debugText = "";
	$debugText .= "...Fields Expanded: ";
	$debugText .= "<BR />... ...field1: " . $field1;
	$debugText .= "<BR />... ...field2: " . $field2;
	$debugText .= "<BR />... ...field3: " . $field3;
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

	$debugText = "";
	$debugText .= "...keyAsString: " . $keyAsString;
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $keyAsString;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function loginKey_Parse($key)
	{
	/*	PURPOSE: Parse out a login key that was created using the 
		loginKey_Create() function.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) $key: The hash key created from the loginKey_Create() function.
				
		RETURNS --:
			
			1) An array with the parsed values in it.
				1.1) $vals['memRecID']: The club member's dbms record ID.
				1.2) $vals['objType']: Object of interest. Will be one of the "OBJECT IDs"
				defined in the INCL_Tennis_CONSTANTS.php file.
				1.3) $vals['objID']: The dbms record # associated with #2 above.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Scratch variables.
	$vals = array();
	$subStrStart = 0;
	$fieldsToParse = 0;
	$fieldLength = 0;
	
					//		Initilization ---------------------------------------------
	$fieldsToParse = 3;
	$fieldLength = 5;
	$subStrStart = 0;


					//		Logic------------------------------------------------------
	$debugText = "...Passed In Key: " . $key;
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

	for ($i=0; $i<$fieldsToParse; $i++)
		{
		$subStrStart = ($fieldLength * $i) + 1;
		$vals[$i+1] = (int)substr($key, $subStrStart, ($fieldLength-1));
		}
	if ($objDebug->DEBUG)
		{
		$debugText = "";
		$debugText .= "...Parsed Out Value Array:";
		foreach ($vals as $arrkey => $value)
			{
			$debugText .= "<BR />... ...{$arrkey}: {$value}";
			}
		$objDebug->writeDebug($debugText);
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $vals;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function userLogin($key, $infoset="RSVPUPDATE")
	{
	/*	PURPOSE: Given a unique key value (as created by the loginKey_Create()
		function) parse it into it's constituant parts and use that information
		to log a member into the site for the purpose of serving them up the
		rsvp update page; or else return the appropriate error if they are
		no longer active club or series participants.
		
		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) $memRecID: The club member's dbms record ID.
				
		RETURNS --:
			
		   1) TRUE if user is already logged in or if we have been able to
		   	successfully log them in. FALSE otherwise. If false is returned,
		   	then the error log will have an entry in it that describes the 
		   	situation. This may be used to create a user-displayable error page.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$vals = array();
	$userState = "";
	$seriesClass = new series;
	$returnVal = FALSE;
	$memRecID = 0;
	$objType = 0;
	$seriesID = 0;
	$clubID = 0;
	$multiClub = FALSE;
	$errorMessage = "";

	
					//		Logic------------------------------------------------------
	$debugText = "";
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

	$vals = $this->loginKey_Parse($key);

	switch($infoset)
		{
		case "RSVPUPDATE": //$key contains: memRecID, objType, objID.
			$memRecID = $vals[1];
			$objType = $vals[2]; //had better be=OBJSERIES.
			$seriesID = $vals[3];
			$clubID = $seriesClass->getClubID4Series($seriesID);
			$userState = $this->assessUserState($memRecID, $clubID, $objType, $seriesID);
			switch ($userState)
				{
				case 'a': //user is logged into desired club.
					$returnVal = TRUE;
					break;
				
				case 'd': //user is not part of series or club.
				case 'e':
					$errLastErrorKey = $objError->RegisterErr(
						ERRSEV_ERROR, 
						ERRCLASS_NOTAUTH, 
						__FUNCTION__, 
						__LINE__, 
						"Not a member of club ({$clubID}) or series ({$seriesID}).", 
						False);
					$returnVal = FALSE;
					break;
				
				case 'b': //user is ok, but not logged in as needed.
				case 'c':
					Session_LogOut();
					$funResult = Session_ValidateCredentials4ID($memRecID);
					$multiClub = Session_CheckMulticlub($memRecID);
					$funResult = Session_SetUserSessionValues($clubID, $memRecID, $multiClub);
					$returnVal = TRUE;
					break;
				
				default: //case 'x' or some other undefined error.
					$errorMessage = "A system error has occurred.";
					$errorMessage .= " Contact Jeff Rocchio.";
					$errorMessage .= " (UsrID:{$memRecID}, ClubID:{$clubID},";
					$errorMessage .= " SeriesID:{$seriesID})";
					$errLastErrorKey = $objError->RegisterErr(
						ERRSEV_ERROR, 
						ERRCLASS_OTHER, 
						__FUNCTION__, 
						__LINE__, 
						$errorMessage, 
						False);
					$returnVal = FALSE;
				}
			break;
			
		default:
			$errorMessage = "A system error has occurred.";
			$errorMessage .= " Contact Jeff Rocchio.";
			$errorMessage .= " (Appears to be incorrect value passed";
			$errorMessage .= " for infoset:{$infoset}).";
			$errLastErrorKey = $objError->RegisterErr(
				ERRSEV_ERROR, 
				ERRCLASS_OTHER, 
				__FUNCTION__, 
				__LINE__, 
				$errorMessage, 
				False);
			$returnVal = FALSE;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnVal;

	} // END METHOD



//==============================================================================
//----PRIVATE FUNCTIONS
//==============================================================================


/*
	NOTES on logging a user in from a key in a URL's query string.
	
	1. We assume we are effecting the login using (a) the user dbms record ID #
		and (b) the clubID (so we know which club to log them into).
		
	2.	We have to account for the following existing login states:
	
		a.	User is already logged into the desired club.  By definition, this 
			state means the user is an active member of specified club and objType.
		
		b.	User is already logged in, but to a different club.  By definition, 
			this state means the user is an active member of specified 
			club and objType.
		
		c.	User is not logged in at all. By definition, this state means
			the user is an active member of specified club and objType.
		
		d.	User is no longer a valid user of the page we are logging them in
			to use. E.g., the URL is for the rsvp update page, but the user is
			using an old email and this user has, in the meantime, been removed
			from the series.
			
		e.	User is no longer a valid member of the specified club. (Which means
			that #d above is also true.)
			
	3.	So I think I want a function dedicated to assessing the user's current
		state with respect to the items in #2 above. That function would then
		return a login state value representing 2.a-2.e.

*/




	//---------------------------------------------------------------------------
	public function assessUserState($memRecID, $clubID, $objType, $objID)
	{
	/*	PURPOSE: .

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) $memRecID: A users dbms record ID #.
			
			2) $clubID: A club dbms record ID #.
			
			3) $objType: Object, or table, we need to be sure user still has
				access rights on. E.g., a Series, Event, etc. Must be one of the
				constant values defined in INCL_Tennis_CONSTANTS.php for "OBJECT IDs."

			4) $objID: dbms instance ID associated to #3 above.
				
		RETURNS --:
			
		   1) A lower-case letter that matches one of the below-described user
		   	states; or 'x' if an error has occurred:

		   	a.	User is already logged into the desired club.  By definition, 
		   		this state means the user is an active member of specified club 
		   		and objType.
		
				b.	User is already logged in, but to a different club.  By definition, 
					this state means the user is an active member of specified 
					club and objType.
		
				c.	User is not logged in at all. By definition, this state means
					the user is an active member of specified club and objType.
		
				d.	User is no longer a valid user of the page we are logging them in
					to use. E.g., the URL is for the rsvp update page, but the user is
					using an old email and this user has, in the meantime, been 
					removed from the series.
			
				e.	User is no longer a valid member of the specified club. (Which 
					means that #d above is also true.)
					
				x.	Some system error has occurred.
			
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Scratch variables.
	$tempFuncBoolResult = FALSE;
	$userState = "";
	$dbmsSeries = new series;

					//		Initilization ---------------------------------------------

					//		Logic------------------------------------------------------
					
					//		2.c -- We start with the premise that the user is not
					//currently logged in, but is an active member of the club and
					//the specified objID.
	$userState = "c";
	$debugText = "...User State Initilized to {$userState}.";
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

					//		2.a|2.b -- Is user already logged in? If so, to the
					//desired club?
	if ($_SESSION['recID'] == $memRecID) $userState = "b";
	if (($userState=='b') && ($_SESSION['clubID']==$clubID)) $userState = "a";
	$debugText = "...Tested for User Being Logged In, User State is Now= {$userState}.";
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);


					//		2.d -- Is user a member of the objType/objID?
	switch ($objType)
		{
		case OBJSERIES:
			$debugText = "...objType is SERIES.";
			if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
			if (!$dbmsSeries->IsUserParticipant($objID, $memRecID)) $userState = "d";
			$debugText = "...User State is Now= {$userState}.";
			if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
			break;
			
		case OBJEVENT:
			$debugText = "...objType is EVENT. Code not yet implemented for this type.";
			if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
			break;
	
		default:
			//invalid parameter passed in... need to declare an error here.
			$debugText = "...**ERROR: Invalid Parameter Passed in for objType.";
			if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
		}

					//		2.e -- Is user an active member of the club?
	if (!Tennis_IsUserInClub($memRecID, $clubID)) $userState = "e";
	$debugText = "...Tested for User In Club, State is Now= {$userState}";
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $userState;

	} // END METHOD





private function validateUser($UserID, &$out)
	/*
		This function performs STEP #1 of the login procedure -- validate
	the user's credentials.
	
	RETURNS:
		1) TRUE if validated. FALSE otherwise.
		2) The passed-by-reference parameter $out will contain an appropriate
			message to the user if validation fails (if function returns
			FALSE).
		
	*/
	{
	
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
		return RTN_FAILURE;
		}
	else
		{
		return RTN_SUCCESS;
		}


} // END Method


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




} // END CLASS event


?>
