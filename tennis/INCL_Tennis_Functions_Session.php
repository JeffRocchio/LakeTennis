<?php
include_once('INCL_Tennis_Functions.php');

				//   Putting global variables here. Tho these should really
				//reside in their own include file.
$CRLF = "\n";
				//   Used to hold debugging output. This string can be set
				//inside a function when the optional $DEBUG parameter is
				//passed in as TRUE, then printed out from the main script.
$debugNote = "";



function Session_ServerHost()
	{
	//Determines the run environment and web-server hostname we are running on,
	//or if we are unning in CRON, then that will be returned as the run 
	//environment.
	//Returns an array with two values: Run Environment and Host URL Path.

	$runEnv = array();
	$RunningInCron = !isset($_SERVER['HTTP_HOST']);
	if ($RunningInCron)
		{
		$runEnv['Environment'] = 'CRON';
		$runEnv['Host'] = "http://laketennis.com";
		}
	else
		{
		$runEnv['Environment'] = 'WEB';
		$runEnv['Host'] = "http://" . $_SERVER['HTTP_HOST'];
		}
	return $runEnv;
} // end function





function Session_SetReturnPage()
	{

	$rtnpg = "";
	if (!array_key_exists('RtnPg', $_SESSION)) $_SESSION['RtnPg'] = "";
	if (array_key_exists('RTNPG', $_GET)) $rtnpg = $_GET['RTNPG'];
	if (array_key_exists('RTNID', $_GET))
		{
		if (strlen($_GET['RTNID']) > 0) $rtnpg .= "?ID={$_GET['RTNID']}";
		}
	if (strlen($rtnpg) == 0) $rtnpg = $_SESSION['RtnPg'];
	if (strlen($rtnpg) == 0) $rtnpg = "{$_SERVER['HTTP_HOST']}/index.php";
	if (strlen($_SESSION['RtnPg']) == 0) $_SESSION['RtnPg'] = $rtnpg;

	return $rtnpg;
} // end function



function Session_LogOut()
	{
	$_SESSION['userType'] = 'GUEST';
	$_SESSION['userName'] = 'Guest';
	$_SESSION['UserID'] = '';
	$_SESSION['recID'] = 0;
	$_SESSION['siteUser'] = FALSE;
	$_SESSION['multiClubs'] = FALSE;
	$_SESSION['clubID'] = 0;
	$_SESSION['clubName'] = "";
	$_SESSION['member'] = FALSE;
	$_SESSION['admin'] = FALSE;
	$_SESSION['evtmgr'] = FALSE;
	$_SESSION['clbmgr'] = FALSE;
	$_SESSION['clubConflict'] = FALSE;
	return;

} // END Session_LogOut()



function Session_ValidateCredentials($userID, $password, $DEBUG=FALSE)
	{
	/*
		This function validates user credentials.
	
	ASSUMES:
		1) Mysql connection is currently open.
		2) Session-Start has been invoked and the
		   global session variables have been 
		   initialized.
	
	TAKES:
		1) User ID to validate.
		2) Password of user to validate against.
		
	RETURNS:
		1) "V" if user has been validated, "I" if validation failed
			due to can't find ID, "P" if validation failed due to
			incorrect password. "X" if the query failed altogether (system
			problem).
		2) IF successful ("V"), set's the session values for [UserID], [recID]
			and [siteUser].

	*/
	
	global $CRLF;
	global $debugNote;

	$row = array();
	
	$query = "SELECT UserID, ";
	$query .= "Pass, ";
	$query .= "ID ";
	$query .= "FROM person ";
	$query .= "WHERE UserID='{$userID}'";
	if ($DEBUG)
		{
		$debugNote .= "<HR>";
		$debugNote .= "<p>===> NOTE: We are inside of Session_ValidateCredentials()";
		$debugNote .= " and ";
		$debugNote .= "in DEBUG mode. ";
		$debugNote .= "</p>{$CRLF}{$CRLF}";
		$debugNote .= "<p>QUERY used to Validate user:";
		$debugNote .= "<BR><BR>{$query}</p>{$CRLF}{$CRLF}";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		if($DEBUG) $debugNote  .= "<p>EXITING Session_ValidateCredentials()</P><HR>";
		return "X";
		}
	if (mysql_num_rows($qryResult) <=0)
		{
		if($DEBUG) $debugNote  .= "<p>EXITING Session_ValidateCredentials()</P><HR>";
		return "I";
		}
	$row = mysql_fetch_array($qryResult);
	if ($row['Pass'] == $password)
		{
		$_SESSION['recID'] = $row['ID'];
		$_SESSION['UserID'] = $row['UserID'];
		$_SESSION['siteUser'] = TRUE;
		if ($DEBUG)
			{
			$debugNote  .= "<p>User Has Been Successfully Validated:<BR>";
			$debugNote .= "recID: {$_SESSION['recID']}<BR>";
			$debugNote .= "UserID: {$_SESSION['UserID']}<BR>";
			$debugNote .= "siteUser: {$_SESSION['siteUser']}<BR>";
			$debugNote .= "</p>{$CRLF}{$CRLF}";
			}
		if($DEBUG) $debugNote  .= "<p>EXITING Session_ValidateCredentials()</P><HR>";
		return "V";
		}
		
	if($DEBUG) $debugNote  .= "<p>EXITING Session_ValidateCredentials()</P><HR>";
	return "P";

}  //END function Session_ValidateCredentials()




function Session_ValidateCredentials4ID($memRecID, $DEBUG=FALSE)
	{
	/*
		This function validates a user given the user ID.
		Created on 6/17/2012 to support autoAction: rsvpUpdateRequest. To be
		able to send a link in email messages which, when email receipient
		receives it, they click on the link and a key passed in the link's
		query string will be used to automatically log them into the site to
		perform some designated action (e.g., update their rsvp status for a
		give series).
	
	ASSUMES:
		1) Mysql connection is currently open.
		2) Session-Start has been invoked and the
		   global session variables have been 
		   initialized.
	
	TAKES:
		1) User's dbms record ID to validate.
		
	RETURNS:
		1) "V" if user has been validated, "I" if validation failed
			due to can't find the record ID. "X" if the query failed altogether
			(system problem).
		2) IF successful ("V"), set's the session values for [UserID], [recID]
			and [siteUser].
	*/
	
	global $CRLF;
	global $debugNote;

	$row = array();
	
	$query = "SELECT UserID, ";
	$query .= "Pass, ";
	$query .= "ID ";
	$query .= "FROM person ";
	$query .= "WHERE ID='{$memRecID}'";
	if ($DEBUG)
		{
		$debugNote .= "<HR>";
		$debugNote .= "<p>===> NOTE: We are inside of Session_ValidateCredentials4ID()";
		$debugNote .= " and ";
		$debugNote .= "in DEBUG mode. ";
		$debugNote .= "</p>{$CRLF}{$CRLF}";
		$debugNote .= "<p>QUERY used to Validate user:";
		$debugNote .= "<BR><BR>{$query}</p>{$CRLF}{$CRLF}";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		if($DEBUG) $debugNote  .= "<p>EXITING Session_ValidateCredentials4ID()</P><HR>";
		return "X";
		}
	if (mysql_num_rows($qryResult) <=0)
		{
		if($DEBUG) $debugNote  .= "<p>EXITING Session_ValidateCredentials4ID()</P><HR>";
		return "I";
		}
	$row = mysql_fetch_array($qryResult);
	$_SESSION['recID'] = $row['ID'];
	$_SESSION['UserID'] = $row['UserID'];
	$_SESSION['siteUser'] = TRUE;
	if ($DEBUG)
		{
		$debugNote  .= "<p>User Has Been Successfully Validated:<BR>";
		$debugNote .= "recID: {$_SESSION['recID']}<BR>";
		$debugNote .= "UserID: {$_SESSION['UserID']}<BR>";
		$debugNote .= "siteUser: {$_SESSION['siteUser']}<BR>";
		$debugNote .= "</p>{$CRLF}{$CRLF}";
		}
	if($DEBUG) $debugNote  .= "<p>EXITING Session_ValidateCredentials4ID()</P><HR>";
	return "V";
		
}  //END function Session_ValidateCredentials4ID()



function Session_CheckMulticlub($userRecID, $DEBUG=FALSE)
	{
	/*
		This function determines if a user is a member of more than one club.
		Created on 6/17/2012 to support autoAction: rsvpUpdateRequest. To be
		able to send a link in email messages which, when email receipient
		receives it, they click on the link and a key passed in the link's
		query string will be used to automatically log them into the site to
		perform some designated action (e.g., update their rsvp status for a
		give series).
	
	ASSUMES:
		1) Mysql connection is currently open.
		2) Session-Start has been invoked and the
		   global session variables have been 
		   initialized.
	
	TAKES:
		1) User's dbms record ID.
		
	RETURNS:
		1) TRUE if user is "multiclub", FALSE otherwise.
	*/
	
	global $CRLF;
	global $debugNote;

	$row = array();
	
	$multiclubs = FALSE;

	if($DEBUG) $debugNote  .= "<p>ENTERING Session_CheckMulticlub()</P><HR>";
	
	$qryResult = Tennis_ClubsUserIsIn($userRecID, "A", "A");
	if (!$qryResult)
		{
		echo $lstErrMsg;
		echo  Tennis_BuildFooter('NORM', $rtnpg);
		exit;
		}
	$numRecords = mysql_num_rows($qryResult);
	if ($numRecords > 1) $multiclubs = TRUE;

	if($DEBUG) $debugNote  .= "<p>EXITING Session_CheckMulticlub()</P><HR>";
	return $multiclubs;
		
}  //END function Session_CheckMulticlub()



function Session_SetUserSessionValues($clubID, $prsnRecID, $multiClub=FALSE, $DEBUG=FALSE)
	{
	/*
		This function sets the user's session variables after their
		credentials have been validated by Session_ValidateCredentials().
	
	ASSUMES:
		1) Mysql connection is currently open.
		2) Session-Start has been invoked and the
		   global session variables have been 
		   initialized.
		3) User's credentials have been validated before this function
			is called.
		4) As a result of #3 above, the session value $_SESSION['recID']
			has been set. This will be compared to the value for Person.ID
			passed in to be sure we are setting the values for the right 
			person.
	
	TAKES:
		
	RETURNS:
		1) TRUE if session values successfully set. FALSE otherwise.

	*/
	
	global $CRLF;
	global $debugNote;


	$row = array();

				//   For getting the text name of the club based on clubID.
	$rowClub = array();

				//   Build a query to fetch the ClubMember record and join
				//it with the club, person and authority records that it goes
				//with.
	$qryBase = "SELECT ";
	$qryBase .= "UserID, ";
	$qryBase .= "prsnID, ";
	$qryBase .= "clubID, ";
	$qryBase .= "clubName, ";
	$qryBase .= "clubActive, ";
	$qryBase .= "Active AS memActive, ";
	$qryBase .= "prsnFName, ";
	$qryBase .= "prsnLName, ";
	$qryBase .= "HighPriv, ";
	$qryBase .= "IF(authority.Privilege,authority.Privilege,0) AS userPriv ";
	$qryBase .= "FROM ";
	$tmp = query_qryGetQuery("qryClubMembers");
	$qryBase .= $tmp;
	$qryBase .= " LEFT JOIN ";
	$qryBase .= "authority ";
	$qryBase .= "ON authority.ObjType=55 AND clubID=authority.ObjID AND authority.Person=prsnID ";
	$qryBase .= "WHERE prsnID='{$prsnRecID}' AND clubID='{$clubID}';";

	$query = $qryBase;
	if ($DEBUG)
		{
		$debugNote .= "<HR>";
		$debugNote .= "<p>===> NOTE: We are inside of ";
		$debugNote .= "Session_SetUserSessionValues()";
		$debugNote .= " and in DEBUG mode</p>";
		$debugNote .= "<p>QUERY to fetch the ClubMember record and join";
		$debugNote .= " it with the club, person and authority records that";
		$debugNote .= " it goes with.";
		$debugNote .= "<BR><BR>";
		$debugNote .= $query;
		$debugNote .= "</p>";
		}

	$query = $qryBase;
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		if ($DEBUG) $debugNote .= "<p>EXITING: Session_SetUserSessionValues() with FALSE</p><HR>";
		return FALSE;
		}
	if (mysql_num_rows($qryResult) <=0)
		{
		if ($DEBUG) $debugNote .= "<p>EXITING: Session_SetUserSessionValues() with FALSE</p><HR>";
		return FALSE;
		}
	$row = mysql_fetch_array($qryResult);

	if ($DEBUG)
		{
		$debugNote .= "<p>ClubMember joined query executed.<BR><BR>";
		$debugNote .= "HighPriv: {$row['HighPriv']}<BR>ClubID: {$row['clubID']}</p>"; 
 		}

				//   We have our user record, set the session variables.
				//NOTE that the session variables for UserID, recID and
				//siteUser were already set by the Session_ValidateCredentials()
				//function, which we require to be called prior to this one.
				//$_SESSION['recID'] = ;
				//$_SESSION['UserID'] = ;
				//$_SESSION['siteUser'] = ;
				
				//   Determine if we have a conflict with the club the user is
				//currently in vs the one they are logging into.
	if (($_SESSION['clubID'] != 0) && ($_SESSION['clubID']!=$row['clubID']))
		{ $_SESSION['clubConflict'] = TRUE; }
	$_SESSION['clubID'] = $row['clubID'];

				//   Set the additional basics.
	if ($multiClub) $_SESSION['multiClubs'] = TRUE;
	$_SESSION['userName'] = $row['prsnFName'] . " " . $row['prsnLName'];
	$_SESSION['RSTR_PhListOff'] = FALSE;
	$_SESSION['siteUser'] = TRUE;
	Tennis_GetSingleRecord($rowClub, "club", $_SESSION['clubID']);
	$_SESSION['clubName'] = $rowClub['ClubName'];
	
	
				//   Set values that are dependent upon user's site rights.
	if($row['memActive'] >0 ) $_SESSION['userType'] = 'MEMBER';
	if($row['memActive'] >0 ) $_SESSION['member'] = TRUE;
	$_SESSION['evtmgr'] = FALSE;
	$_SESSION['clbmgr'] = FALSE;
	$_SESSION['admin'] = FALSE;
	if ($row['userPriv']==48) { $_SESSION['clbmgr'] = TRUE; }

				//   If 'super user' (Jeff Rocchio) reset 
				//userType as ADMIN.
	if($row['HighPriv'] == 4)
		{
		$_SESSION['userType'] = 'ADMIN';
		$_SESSION['member'] = TRUE;
		$_SESSION['evtmgr'] = TRUE;
		$_SESSION['clbmgr'] = TRUE;
		$_SESSION['admin'] = TRUE;
		}

	if ($DEBUG) $debugNote .= "<p>EXITING: Session_SetUserSessionValues() with TRUE</p><HR>";
	return TRUE;

} //END FUNCTION



function Session_Initalize()
	{
	/*
		This function ...
	
	ASSUMES:
		2) Session-Start has been invoked and the
		   global session variables have been 
		   initialized.
	
	TAKES:
		
	RETURNS:

	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

				//   For getting the text name of the club based on clubID.
	$row = array();


	if (!isset($_SESSION['userType']))
		{
		$_SESSION['userType'] = 'GUEST';
		$_SESSION['userName'] = 'Guest';
		$_SESSION['UserID'] = '';
		$_SESSION['recID'] = 0;
		$_SESSION['siteUser'] = FALSE;
		$_SESSION['member'] = FALSE;
		$_SESSION['evtmgr'] = FALSE;
		$_SESSION['clbmgr'] = FALSE;
		$_SESSION['admin'] = FALSE;
		$_SESSION['clubID'] = 0;
					//   Blanking these two statements out because when this
					//routine is called I have not yet opened a link to the
					//MySQL DB so it generates an error. PLUS, I don't think I
					//need this because the club name will get set during login
					//anyway.
		//Tennis_GetSingleRecord($row, "club", $_SESSION['clubID']);
		//$_SESSION['clubName'] = $row['ClubName'];
		$_SESSION['clubName'] = "";
		//$_SESSION['clubName'] = $clubName; <<-- I commented this out on 11/29/2014 after seeing a warning that the $clubName variable 'isn't defined.' And looking at the code I can't see a reason this line of code is here.
		$_SESSION['multiClubs'] = FALSE;
		$_SESSION['RSTR_PhListOff'] = TRUE;
		$_SESSION['clubConflict'] = FALSE;
		}

} // END FUNCTION



function Session_GetAuthority($objectType, $objectID)
	{
	//---INITILIZE---------------------------------------------------------
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	$CRLF = "\n";

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

	//---DECLARE VARIABLES------------------------------------------------
	
				//   To hold the authority record.
	$row = array();
				//   Holds the where clause of SQL query for fetching the
				//desired authority record.
	$where = '';
				//   Holds the privilege level for eventual function
				//return value.
	$Privilege = 'GST';
	
	//---CODE--------------------------------------------------------------
	
	$where = "WHERE ObjType={$objectType} AND ObjID={$objectID} AND Person={$_SESSION['recID']}";
	
	$qryResult = Tennis_OpenViewGeneric('authority', $where, '');
	
	if (!$qryResult)
		{
		if ($_SESSION['userType'] == 'MEMBER') $Privilege = 'USR';
		}
	elseif (mysql_num_rows($qryResult) == 0)
		{
		if ($_SESSION['userType'] == 'MEMBER') $Privilege = 'USR';
		}
	else
		{
						//   Got a record. Load it into the
					//array and return.
		$row = mysql_fetch_array($qryResult);
		if ($DEBUG)
			{
			echo "<P>LISTING RECORD AND row keys:<BR>";
			foreach($row as $key => $value)
				{
				echo "row[{$key}] = {$value}<BR>";
				}
			echo "<BR></P>";
			}

		switch ($row['Privilege'])
			{
			case 46:
				$Privilege = 'USR';
				break;
			
			case 47:
				$Privilege = 'MGR';
				break;
			
			case 48:
				$Privilege = 'ADM';
				break;
			
			default:
				$Privilege = 'GST';
	
			}
		}
	
	return $Privilege;
	
	} // END FUNCTION

?>
