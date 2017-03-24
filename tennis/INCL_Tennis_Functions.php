<?php

/*
	Requires Includes:
	
	12/10/2014: Modified the Tennis_ContactListOpen() function to add a new case to
	return a list of names for an event where folks have not yet posted their
	RSVP.
	
*/
include_once('INCL_Tennis_Functions_QUERIES.php');





function Tennis_BuildFooter($format, $rtnPage)
	{
	/*
		This function outputs the page footer,
		which includes the mini-login form.
	*/
	
	global $CRLF;
	
	$server = "http://".$_SERVER['HTTP_HOST'];
	$homePage = "/index.php";
	if ($_SESSION['clubID'] <> 0)
		{
		$homePage = "/ClubHome.php?ID={$_SESSION['clubID']}";
		}

	$footer = "{$CRLF}{$CRLF}<P STYLE='margin-top: 0px'>&nbsp;</P>{$CRLF}";
	$URLHome = $server.$homePage;
	switch ($format)
		{
		case 'ADMIN':
			if ($_SESSION['member'] == False)
				{
				$footer .= "<P>You do not have permission to edit this information.</P>{$CRLF}";
				}
			else
				{
				$footer .= "<table CELLSPACING=0 CLASS=\"tblAdminFooter\">{$CRLF}";
				$footer .= "<tr>{$CRLF}";
				$footer .= "<td CLASS='tdAdminFooter'><A HREF='{$URLHome}'>HOME</A>{$CRLF}";
				if (($_SESSION['admin']==True) OR ($_SESSION['clbmgr']==True))
					{
					$footer .= "&nbsp;&nbsp<A HREF='{$server}/tennis/admin.php'>Admin</A>{$CRLF}";
					}
				if($_SESSION['multiClubs'] == TRUE)
					{
					$footer .= "&nbsp;&nbsp<A HREF=\"{$server}/tennis/login.php?POST=S\">Switch Clubs</A>";
					}
				$footer .= "</td>{$CRLF}";
				$footer .= "<td CLASS='tdAdminFooterUser'>{$CRLF}";
				$footer .= "{$_SESSION['userName']}";
				$footer .= "<BR>{$_SESSION['clubName']}";
				$footer .= "</td>{$CRLF}";
				$footer .= "</tr>{$CRLF}";
				$footer .= "</TABLE>{$CRLF}{$CRLF}";
				}
			break;
		
		case 'NORM':
		default:
			if ($_SESSION['member'] == False)
				{
				$footer .= "<table CELLSPACING=0 CLASS=\"tblLogin\">{$CRLF}";
				$footer .= "<tr>{$CRLF}";
				$footer .= "<td CLASS='tdLinks'><A HREF='{$URLHome}'>HOME</A>{$CRLF}";
				$footer .= "</td>{$CRLF}";
				$footer .= "<td CLASS='tdLogin'>{$CRLF}";
				$footer .= "<form id='frmLogin' method='post' action='/tennis/login.php?POST=T'>{$CRLF}";
				$footer .= "<INPUT TYPE=hidden NAME=meta_RTNPG VALUE='{$rtnPage}' ";
				$footer .= "class=\"text\"></label>{$CRLF}";
				$footer .= "<input type=hidden name=ID value=0>";
				$footer .= "<label>ID: <INPUT TYPE=text NAME=UserID SIZE=10 MAXLENGTH=100 ";
				$footer .= "VALUE='' class='text'></label>{$CRLF}";
				$footer .= "&nbsp;";
				$footer .= "<label>Pass: <INPUT TYPE=text NAME=Password SIZE=10 MAXLENGTH=100 ";
				$footer .= "VALUE='' class='text'>{$CRLF}";
				$footer .= "&nbsp;";
				$footer .= "</lable>{$CRLF}{$CRLF}";
				$footer .= "<input type='submit' value='login' class='button'>{$CRLF}{$CRLF}";
				$footer .= "</form>{$CRLF}";
				$footer .= "</td>{$CRLF}";
				$footer .= "</tr>{$CRLF}";
				$footer .= "</TABLE>{$CRLF}{$CRLF}";
				}
			else
				{
				$footer .= "<table CELLSPACING=0 CLASS=\"tblLogin\">{$CRLF}";
				$footer .= "<tr>{$CRLF}";
				$footer .= "<td CLASS='tdLinks'><A HREF='{$URLHome}'>HOME</A>{$CRLF}";
				if (($_SESSION['admin']==True) OR ($_SESSION['clbmgr']==True))
					{
					$footer .= "&nbsp;&nbsp<A HREF='{$server}/tennis/admin.php'>Admin</A>{$CRLF}";
					}
				if($_SESSION['multiClubs'] == TRUE)
					{
					$footer .= "&nbsp;&nbsp<A HREF=\"{$server}/tennis/login.php?POST=S\">Switch Clubs</A>";
					}
				$footer .= "</td>{$CRLF}";
				$footer .= "<td CLASS='tdLogin'>{$CRLF}";
				$footer .= "{$_SESSION['userName']}";
				$footer .= "<BR>{$_SESSION['clubName']}";
				$footer .= "</td>{$CRLF}";
				$footer .= "</tr>{$CRLF}";
				$footer .= "</TABLE>{$CRLF}{$CRLF}";
				}
		}
	$footer .= "<P>&nbsp;</P>{$CRLF}{$CRLF}";
	$footer .= "</BODY>{$CRLF}{$CRLF}";
	$footer .= "</HTML>{$CRLF}";
	return $footer;

} // END FUNCTION


function Tennis_BuildHeader($format, $tbar, $pgL1, $pgL2, $pgL3)
	{
	/*
		This function builds the HTML header string.
	*/
	
	global $CRLF;;
	
	$hdrTxt = "";
	
	switch ($format)
		{
		case 'ADMIN':
				$params['css'] = '/tennis/dataEntry.css';
				$params['bdyclass'] = 'bdyNormal';
				$params['pghdL1class'] = 'pgHdrPurpose';
				$params['pghdL2class'] = 'pgHdrTopic1';
				$params['pghdL3class'] = 'pgHdrTopic2';
				break;
		
		case 'METRIC':
				$css['2'] = '/main.css';
				$css['1'] = '/metric.css';
				$params['bdyclass'] = 'bdyNormal';
				$params['pghdL1class'] = 'pgHdrPurpose';
				$params['pghdL2class'] = 'pgHdrTopic1';
				$params['pghdL3class'] = 'pgHdrTopic2';
				break;
		
		case 'MOBILE':
				$css['1'] = '/main.css';
				$params['bdyclass'] = 'bdyNormal';
				$params['pghdL1class'] = 'pgHdrPurpose';
				$params['pghdL2class'] = 'pgHdrTopic1';
				$params['pghdL3class'] = 'pgHdrTopic2';
				break;
		
		case 'NORM':
		default:
				$params['css'] = '/main.css';
				$params['bdyclass'] = 'bdyNormal';
				$params['pghdL1class'] = 'pgHdrPurpose';
				$params['pghdL2class'] = 'pgHdrTopic1';
				$params['pghdL3class'] = 'pgHdrTopic2';
		
		
		} // end switch

	if (strlen($pgL1) > 0) $hdrTxt = "<p CLASS={$params['pghdL1class']}>$pgL1</P>{$CRLF}";
	if (strlen($pgL2) > 0) $hdrTxt .= "<p CLASS={$params['pghdL2class']}>$pgL2</P>{$CRLF}";
	if (strlen($pgL3) > 0) $hdrTxt .= "<p CLASS={$params['pghdL3class']}>$pgL3</P>{$CRLF}";
	$hdrTxt .= "<P STYLE='margin-top: 0; margin-bottom: 0'>&nbsp;</P>{$CRLF}";

				//   12/28/2006: I added a bit of a kludge here. I needed to create the
				//ability to specify more than 1 style-sheet to link to when I implemented
				//the metrics feature. So I had to change way I built style-sheet links.
				//In order to not break all my prior code I had to insert a 2nd switch
				//structure. In some future maintenance this should be made to go away.
	switch ($format)
		{
		case 'METRIC':
			$out = "<html><head>{$CRLF}";
			foreach($css as $key => $value)
				{
				$out .= "<LINK REL=StyleSheet HREF=\"{$value}\" TYPE=\"text/css\">{$CRLF}";
				}
			break;
				
		case 'MOBILE':
			$out = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML Mobile 1.0//EN\"{$CRLF}";
			$out .= "\"http://www.wapforum.org/DTD/xhtml-mobile10.dtd\">{$CRLF}";
			$out .= "<html xmlns=\"http://www.w3.org/1999/xhtml\"";
			$out .= " xml:lang=\"en\" lang=\"en\">{$CRLF}{$CRLF}";
			$out .= "<head>{$CRLF}{$CRLF}";
			foreach($css as $key => $value)
				{
				$out .= "<LINK REL=StyleSheet HREF=\"{$value}\" TYPE=\"text/css\">{$CRLF}";
				}
				$out .= "<LINK REL=StyleSheet HREF=\"http://laketennis.com/handheld.css\" media=\"handheld\" TYPE=\"text/css\">{$CRLF}";
			$hdrTxt = "<div>{$CRLF}";
			if (strlen($pgL1) > 0) $hdrTxt .= "{$pgL1}<BR />{$CRLF}";
			if (strlen($pgL2) > 0) $hdrTxt .= "{$pgL2}<BR />{$CRLF}";
			if (strlen($pgL3) > 0) $hdrTxt .= "{$pgL3}<BR />{$CRLF}";
			$hdrTxt .= "&nbsp;</div>{$CRLF}";
			break;
				
		default:
			$out = "<html><head>{$CRLF}";
			$out .= "<LINK REL=StyleSheet HREF=\"{$params['css']}\" TYPE=\"text/css\">{$CRLF}";
			$out .= "<LINK REL=StyleSheet HREF=\"http://laketennis.com/handheld.css\" media=\"handheld\" TYPE=\"text/css\">{$CRLF}";
		}
				//   01/26/2014: The below meta tag was added so that users on
				//on mobile devices would get a properly scaled display.
		$out .= "<meta name=viewport content=\"width=device-width, initial-scale=1\">";
		$out .= "{$CRLF}<title>{$tbar}</title>{$CRLF}{$CRLF}";
		$out .= "</head>{$CRLF}{$CRLF}";
		$out .= "<body CLASS={$params['bdyclass']}>{$CRLF}{$CRLF}";
		$out .= $hdrTxt;
		
		return $out;

} //END FUNCTION


function Tennis_DisplayDate($date)
	{
	/*
		This function takes a MySql ugly date/time string
		and returns a nice display date in MM/DD/YYYY
		format.
	*/
	
	$tmp = substr ($date, 5, 2);
	$tmp .= "-";
	$tmp .= substr ($date, 8, 2);
	$tmp .= "-";
	$tmp .= substr ($date, 0, 4);
	return $tmp;
	
} //END FUNCTION

function Tennis_DisplayTime($date, $apm)
	{
	/*
		This function takes a MySql ugly date/time string
		and returns a nice display time in HH:MM am/pm
		format.
	*/
	
	$ampm = "&nbsp;am";
	$hr = substr ($date, 11, 2);
	$mn = substr ($date, 14, 2);
	if ($hr >= 12)
		{
		$ampm = "&nbsp;pm";
		}
	if ($hr > 12)
		{
		$hr = $hr - 12;
		}
	$tmp = sprintf ("%02s:%02s",$hr,$mn);
	if ($apm) $tmp .= $ampm;
	return $tmp;
	
	
} //END FUNCTION






function Tennis_GetSingleRecord(&$row, $tblName, $recID)
	{
	/*
		This function fetches a single record from a named
		table or query.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) $row: Pointer to an array where the fetched
		   row will be stored.
		2) $tblName: Name of a valid table (or query) in the
		   tennis database.
		3) $recID: The ID of the record to fetch.
	
	RETURNS:
		1) TRUE if all is well
		2) FALSE if an error has occurred. If an error has
		   occurred the global error variables have been
		   set accordingly.
	
	*/
	
	//---INITILIZE---------------------------------------------------------
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

	
	//---CODE--------------------------------------------------------------
	
	$tmp = query_qryGetQuery($tblName);
	$query = "SELECT * ";
	$query .= "FROM {$tmp} ";
	$query .= "WHERE ({$tblName}.ID={$recID});";
	if ($DEBUG)
		{
		echo "<p>Get Single Record, tmp: {$tmp}</p>";
		echo "<p>QUERY: {$query}</p>";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		}
	

				//   Got the record. Load it into the
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
	
	
	return TRUE;


} //END FUNCTION


function Tennis_OpenViewGeneric($viewName, $where, $sort)
	{
	/*
	
		***************************************************************************
		** NOTE: This function has been replaced with the class:function         **
		** database::openView($viewName, $where, $sort, $auth=FALSE, $ObjType=0) **
		***************************************************************************

		This function open a table or view for listing.
	
	ASSUMES:
		1) A connection the MySql database is open.
		2) The right database has been selected as the current
		   database to operate on.
	TAKES:
		2) Table or view name to open.
		2) An optional Where clause in valid SQL syntax.
		3) An optional sort clause in valid SQL syntax.
		
	RETURNS:
		1) The $qryResult object.

	*/
	
	$DEBUG = FALSE;
//	$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

	$tmp = query_qryGetQuery($viewName);
	$query = "SELECT * ";
	$query .= "FROM {$tmp}";
	if (strlen($where) > 0) $query .= " {$where}";
	if (strlen($sort) > 0) $query .= " {$sort}";
	$query .= ";";

	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		}
	return $qryResult;


} //END FUNCTION


function Tennis_OpenViewGenericAuth($viewName, $where, $sort, $ObjType)
	{
	/*

		***************************************************************************
		** NOTE: This function has been replaced with the class:function         **
		** database::openView($viewName, $where, $sort, $auth=FALSE, $ObjType=0) **
		***************************************************************************

		This function open a table or view for listing, but
	it joins it with the "authority" record, if any, for the
	user so that user-rights come in as a column for each
	returned record.
	
	ASSUMES:
		1) A connection the MySql database is open.
		2) The right database has been selected as the current
		   database to operate on.
		3) The table or query passed in $vewName must have a 
		   unique field in it named 'ID,' and that ID field
		   must be the ID field for the table that is
		   represented by $ObjType.
			
	TAKES:
		1) Table or view name to open.
		2) An optional Where clause in valid SQL syntax.
		3) An optional sort clause in valid SQL syntax.
		
	RETURNS:
		1) MySQL result-set pointer.

	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

	$tmp = query_qryGetQuery($viewName);
	$query = "SELECT {$viewName}.*, ";
	$query .= "IF(authority.Privilege,authority.Privilege,0) AS userPriv, ";
	$query .= "Code.LongName ";
	$query .= "FROM {$tmp} ";
	$query .= "LEFT JOIN ";
	$query .= "authority ";
	$query .= "ON authority.ObjType={$ObjType} AND ";
	$query .= "{$viewName}.ID=authority.ObjID AND ";
	$query .= "authority.Person={$_SESSION['recID']} ";
	$query .= "LEFT JOIN ";
	$query .= "Code ";
	$query .= "ON Code.ID=authority.Privilege";
	if (strlen($where) > 0) $query .= " {$where}";
	if (strlen($sort) > 0) $query .= " {$sort}";
	$query .= ";";

	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		}
	return $qryResult;


} //END FUNCTION


function Tennis_OpenViewCustom($queryString)
	{
	/*
		This function opens a custom-built query for listing.
	
	ASSUMES:
		1) A connection the MySql database is open.
		2) The right database has been selected as the current
		   database to operate on.
	TAKES:
		2) The query-string to open.
		
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

	if ($DEBUG)
		{
		echo "<p>QUERY: {$queryString}</p>";
		}
	$qryResult = mysql_query($queryString);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $queryString;
		return FALSE;
		}
	return $qryResult;


} //END FUNCTION



function Tennis_dbGetNameCode($cdID, $shtName)
	{

	/* 
	   This function fetches the descriptive name of a code value from
	a CodeSet.
	   NOTE that this is a duplicate of the same function that is in the
	ADMIN include file. (Originally thought it would only be used as part
	of admin activities, but then I did need it also for normal display
	scripts. At some point the ADMIN version should be deprecated.)
	
	TAKES:
	      1) cdID: The code's record ID we want the text name for.
	      2) shrName: TRUE if we should return the code's short name and not
	   the long name.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

	if ($cdID == 0)
		{
		return "";
		}
	$query = "SELECT ID, LongName, ShtName ";
	$query .= "FROM Code ";
	$query .= "WHERE (ID={$cdID});";
	if ($DEBUG)
		{
		echo "<p>In TENNIS_dbGetCodeName()</p>";
		echo "<p>QUERY: {$query}</p>";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		}
	

				//   Got the record. Load it into the
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

	$fldName = 'LongName';
	if ($shtName) $fldName = 'ShtName';
	return $row[$fldName];

} // end function



function Tennis_ContactListOpen($Object, $ID, $Scope)
	{
	/*
		This function opens a list on the person table which can be used to 
		generate a contact list (phone list). The people included in this list
		are based on the parameters passed in.
		
	ASSUMES:
		1) A connection the MySql database is open.
		2) The right database has been selected as the current
		   database to operate on.
		3) The global variable 'DEBUG' has been defined by the
		   calling program prior to invoking this function.

	TAKES:
		1)	$Object: CLUB, SERIES, EVENT.
		2)	$ID: The ID for the $Object.
		3)	$Scope: The sub-set of persons to select into the list
			for given the $Object and $ID. (E.g., for an event we might want
			to list only those person's who are scheduled to play in
			the match.)
		
	RETURNS:
		1)	A database Query result that is used to fetch the list row-by-row to
			generate an display page.
	*/
	
	//---Global Variables--------------------------------------------------------
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";


	//---Local Variables---------------------------------------------------------

	$orderby="ORDER BY prsnFName, " . "prsnLName";


	//---Code--------------------------------------------------------------------

	switch ($Object)
		{
		case OBJCLUB:
			$tblName = 'qryClubMembers';
			$where="WHERE (Active=1 AND clubID={$ID})";
			break;


		case OBJSERIES:
			$tblName = 'qrySeriesEligible';
			$where="WHERE (ID={$ID})";
			//$list = "IN ObjSeries:<BR />";
			break;
		

		case OBJEVENT:
			$tblName = 'qryRsvpPerson';
				switch ($Scope)
					{
					case 'AVAIL': // Not scheduled to play, but AVAIL.
						$selCrit = " AND ";
						$selCrit .= "(rsvpClaimCode=15 OR rsvpClaimCode=13 OR rsvpClaimCode=16) AND ";
						$selCrit .= "(rsvpPositionCode=28 OR rsvpPositionCode=30 ";
						$selCrit .= "OR rsvpPositionCode=27)";
						break;
		
					case 'PLAY': // Scheduled to play.
						$selCrit = " AND ";
						$selCrit .= "(rsvpPositionCode<>28 AND rsvpPositionCode<>30)";
						//$selCrit .= "AND rsvpPositionCode<>27)";
						break;
				
					case 'NOTPLAY': // Everyone not scheduled to play, dispite AVAIL.
						$selCrit = " AND ";
						$selCrit .= "(rsvpPositionCode=28 OR rsvpPositionCode=30 ";
						$selCrit .= "OR rsvpPositionCode=27)";
						break;
		
					case 'NORSVP': // Everyone who has not yet rsvp'd.
						$selCrit = " AND ";
						$selCrit .= "(rsvpClaimCode=10)";
						break;
		
					case 'TENT': // Those who have RSVP'd as Tentative.
						$selCrit = " AND ";
						$selCrit .= "(rsvpClaimCode=14)";
						break;
		
					case 'NORSVP+TENT': // Union of NORSVP and TENT.
						$selCrit = " AND ";
						$selCrit .= "(rsvpClaimCode=14 OR rsvpClaimCode=10)";
						break;
		
				default:
					$selCrit = ""; // All event members (which would = all series participants).
					}
			$where="WHERE (evtID={$ID}{$selCrit})";
			break;
		

		case OBJPERSON: //Site-level lists.
			$tblName = 'qryPersonDisp';
				switch ($Scope)
					{
					case 'ACTIVE': // Only persons who are active on the site.
						$where = "WHERE Currency=39";
						break;

				default:
					$where = ""; // All members of the site.
					}
		
			break;
		

		default:
			return FALSE;
	
		}

					//   Open the list.
	$qryResult = Tennis_OpenViewGeneric($tblName, $where, $orderby);

	return $qryResult;

} // end function








function Tennis_EligibleForSeriesOpen($seriesID)
	{
	/*
		This function fetches a list of all the eligible members
		for a given series.
	
	ASSUMES:
		1) A connection the MySql database is open.
		2) The right database has been selected as the current
		   database to operate on.
		3) The global variable 'DEBUG' has been defined by the
		   calling program prior to invoking this function.
	TAKES:
		1) The Series ID number.
		
	RETURNS:
		1) An array that contains rows corresponding to the list
		   of eligible members for the Series.
		   ($eligiblelList).
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";
	$tblName = 'qrySeriesEligible';

	$tmp = query_qryGetQuery($tblName);
	$query = "SELECT * ";
	$query .= "FROM {$tmp} ";
	$query .= "WHERE ({$tblName}.ID={$seriesID}) ";
	$query .= "ORDER BY prsnFName, prsnLName";
	$query .= ";";

	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		}
	return $qryResult;


} //END FUNCTION


function Tennis_NotEligibleForSeriesOpen($seriesID, $clubID)
	{
	/*
		This function fetches a list of all those persons who are members
		of the club, but who have NOT been marked as "eligible" for the
		given series.
		
		03/07/2009: Updated for multi-club.
	*/
	
	$DEBUG = FALSE;
//	$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

				//   Build a query of club member who are not currently in 
				//the series.
				//   This is a bit dicy. I am building 2 sub-queries. 1st is
				//producing a list of current club members. 2nd is a list of
				//folks who *are* currently participating. Then I am LEFT JOINING
				//these two sub-queries such that the left table is the list
				//of a all club members. The LEFT JOIN will produce a list
				//with a column for each club member that shows that member's
				//eligibility record ID. If this is NULL for a given member,
				//it means that member is not a member of the series, so that
				//is what I am selecting on.
	$query = "SELECT ";
	$query .= "qryClubMem.prsnID, ";
	$query .= "qryClubMem.Person, ";
	$query .= "qryClubMem.ClubID, ";
	$query .= "qryClubMem.Active, ";
	$query .= "qryClubMem.FName, ";
	$query .= "qryClubMem.LName, ";
	$query .= "qryClubMem.PName, ";
	$query .= "CONCAT(qryClubMem.FName,' ',qryClubMem.LName) AS prsnFullName,";
	$query .= "eligibleForSeries.Series ";
	$query .= "FROM ";
	$query .= "	(SELECT ";
	$query .= "		ClubMember.Person, ";
	$query .= "		ClubMember.Active, ";
	$query .= "		ClubMember.Club AS clubID, ";
	$query .= "		person.ID AS prsnID, ";
	$query .= "		person.LName, ";
	$query .= "		person.FName, ";
	$query .= "		person.PName ";
	$query .= "	FROM ClubMember, person ";
	$query .= "	WHERE (ClubMember.Person=person.ID AND ClubMember.Club={$clubID})) ";
	$query .= "	AS qryClubMem ";
	$query .= "	LEFT JOIN ";
	$query .= "	  (SELECT * ";
	$query .= "	  FROM eligible ";
	$query .= "	  WHERE eligible.Series={$seriesID}) ";
	$query .= "	  AS eligibleForSeries"; 
	$query .= "	  ON qryClubMem.prsnID=eligibleForSeries.Person ";
	$query .= "WHERE (qryClubMem.Active=1 AND qryClubMem.ClubID={$clubID} AND eligibleForSeries.Series IS NULL) ";
	$query .= "ORDER BY prsnFullName;";
	$query .= ";";

/*
OLD QUERY
	$query = "SELECT person.ID AS prsnID,";
	$query .= "  CONCAT(person.FName,' ',person.LName) AS prsnFullName,";
	$query .= "  person.PName AS prsnPName,";
	$query .= "  person.FName AS prsnFName,";
	$query .= "  person.LName AS prsnLName,";
	$query .= "  eligibleForSeries.Series";
	$query .= "  FROM person";
	$query .= "    LEFT JOIN";
	$query .= "    (SELECT *";
	$query .= "    FROM eligible";
	$query .= "    WHERE eligible.Series={$seriesID})";
	$query .= "    AS eligibleForSeries";
	$query .= "    ON person.ID=eligibleForSeries.Person";
	$query .= "    WHERE (person.Currency=39 AND person.ClubID={$_SESSION['clubID']} AND eligibleForSeries.Series IS NULL)";
	$query .= "  ORDER BY prsnLName, prsnFName;";
*/
	
	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		}
	return $qryResult;


} //END FUNCTION


function Tennis_NonClubMembersOpen($clubID, $DEBUG=FALSE)
	{
	/*
		This function opens a list of persons who are not associated
	(via ClubMember associative table) to the given club.

	ASSUMES:
		1) A connection the MySql database is open.

	TAKES:
		1) The club ID number.
	
	RETURNS:
		1) The query result resource so that the calling script can then
			fetch each row to build the display list.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

	$query = "SELECT person.ID AS ID, ";
	$query .= "CONCAT(person.FName,' ',person.LName) AS prsnFullName, ";
	$query .= "person.PName AS prsnPName, ";
	$query .= "person.FName AS prsnFName, ";
	$query .= "person.LName AS prsnLName, ";
	$query .= "CONCAT(person.FName,' ',person.LName) AS prsnFullName, ";
	$query .= "person.UserID AS prsnUserID, ";
	$query .= "person.Currency AS Currency, ";
	$query .= "InClub.Club ";
	$query .= "FROM person ";
	$query .= "  LEFT JOIN "; 
	$query .= "  (SELECT * ";
	$query .= "  FROM ClubMember ";
	$query .= "  WHERE ClubMember.Club={$clubID}) ";
	$query .= "  AS InClub"; 
	$query .= "  ON person.ID=InClub.Person ";
	$query .= "  WHERE (InClub.Club IS NULL) ";
	$query .= "ORDER BY prsnLName, prsnFName ";
	$query .= ";";
	
	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		}
	return $qryResult;


} //END FUNCTION




function Tennis_ClubMembersOpen($clubID, $DEBUG=FALSE)
	{
	/*
		This function opens a list of all persons who are currently members
	of the given club as defined by entries in the associative table
	ClubMember.
	
	ASSUMES:
		1) A connection the MySql database is open.

	TAKES:
		1) The club ID number.
		
	RETURNS:
		1) The query result resource so that the calling script can then
			fetch each row to build the display list.
	*/
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";
	$tblName = 'qryClubMembers';

	$tmp = query_qryGetQuery($tblName);
	$query = "SELECT * ";
	$query .= "FROM {$tmp} ";
	$query .= "WHERE ({$tblName}.clubID={$clubID}) ";
	$query .= "ORDER BY prsnLName, prsnFName;";

	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		}
	return $qryResult;


} //END FUNCTION



function Tennis_ClubsUserIsIn($UserID, $activeMember="ALL", $activeClub="ALL", $DEBUG=FALSE)
	{
	/*
		This function takes a userID and opens a list of clubs the user is in
	(via the ClubMember associative table).
		The returned columns will include what rights the user has on each
	club (from the authority table).
		IF the UserID does not exist the behavior of this function is
	undefined.
	
	TAKES:
		1) UserID.
		2) $activeMember: "A" if result should include only those clubs for
			which the person is set to "ACTIVE" status. "I" if results should 
			include only those for which the person is set to "INACTIVE"
			status. "ALL" if for all regardless of active/inactive status.
		3) $activeClub: "A" if result should include only those clubs for
			which the club itself is set to "ACTIVE" status. "I" if results
			should include only those for which the club is set to "INACTIVE"
			status. "ALL" if for all clubs regardless of active/inactive status.
			
	RETURNS:
	
		1) The query result resource so that the calling script can then
			fetch each row to build the display list. FALSE if an error
			occurred.
	*/

	global $CRLF;
	global $debugNote;
	global $lstErrExist;
	global $lstErrMsg;

	$lstErrExist = FALSE;
	$lstErrMsg = "";
	
	$row = array();
	$tmp = "";
	
	$query = "SELECT *, ";
	$query .= "IF(authority.Privilege,authority.Privilege,0) AS userPriv, ";
	$query .= "LongName AS userPrivText ";
	$query .= "FROM ";
	$query .= query_qryGetQuery("qryClubMembers");
	$query .= " LEFT JOIN ";
	$query .= "authority ";
	$query .= "ON authority.ObjType=55 AND authority.ObjID=clubID AND authority.Person=prsnID ";
	$query .= "LEFT JOIN ";
	$query .= "Code ";
	$query .= "ON Code.ID=authority.Privilege ";
	$query .= "WHERE UserID='{$UserID}'";
	switch ($activeClub) {
		case "A": $query .= " AND clubActive != 0"; break;
		case "I": $query .= " AND clubActive = 0"; break;
		default:
		}
	switch ($activeMember) {
		case "A": $query .= " AND Active != 0"; break;
		case "I": $query .= " AND Active = 0"; break;
		default:
		}

	if ($DEBUG)
		{
		$debugNote .= "<HR>";
		$debugNote .= "<p>===> NOTE: We are inside of Tennis_ClubsUserIsIn()";
		$debugNote .= " and in DEBUG mode</p>";
		$debugNote .= "<p>QUERY for getting list of clubs a user is a";
		$debugNote .= " member of:<BR><BR> {$query}</p>";
		}

	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$lstErrExist = TRUE;
		$lstErrMsg = "ERROR";
		$lstErrMsg .= '<BR>Invalid query: ' . mysql_error();
		$lstErrMsg .= '<BR>Query Sent:<BR><BR> ' . $query;
		}

	if($DEBUG) $debugNote  .= "<p>EXITING Session_Tennis_ClubsUserIsIn()</P><HR>";
	return $qryResult;

} //END function Tennis_ClubsUserIsIn()



function Tennis_IsUserInClub($PersonID, $ClubID, $DEBUG=FALSE)
	{
	/*
		This function takes a personID and and a clubID and determines if the
	person is a current member of that club.
	(via the ClubMember associative table).
	
	TAKES:
		1) Record ID from person table.
		2) Record ID from the club table.
			
	RETURNS:
	
		1) ClubMember RecID if person is member of the club, 
		2) 0 if person is not a member of the club.
	*/

	global $CRLF;
	global $debugNote;
	global $lstErrExist;
	global $lstErrMsg;

	$lstErrExist = FALSE;
	$lstErrMsg = "";
	
	$row = array();
	$where = "";
	$numRecords = 0;
	$rtnval = 0;
	
	$where = "WHERE Club={$ClubID} AND Person={$PersonID}";
	$rtnval = FALSE;

	if ($DEBUG)
		{
		$debugNote .= "<HR>";
		$debugNote .= "<p>===> NOTE: We are inside of Tennis_IsUserInClub()";
		$debugNote .= " and in DEBUG mode</p>";
		$debugNote .= "<p>WHERE clause for the QUERY:";
		$debugNote .= " <BR><BR> {$where}</p>";
		}
	$qryResult = Tennis_OpenViewGeneric("ClubMember", $where, "");
	if ($qryResult)
		{
		$numRecords = mysql_num_rows($qryResult);
		if($numRecords >0)
			{
			$row = mysql_fetch_array($qryResult);
			$rtnval = $row['ID'];
			}
		}
	else
		{
		$lstErrExist = TRUE;
		$lstErrMsg = "ERROR";
		$lstErrMsg .= '<BR>Invalid query: ' . mysql_error();
		$lstErrMsg .= '<BR>Query Sent:<BR><BR> ' . $query;
		$rtnval = 0;
		}
	if($DEBUG) $debugNote  .= "<p>EXITING Session_Tennis_IsUserInClub()</P><HR>";
	return $rtnval;

} //END function Tennis_IsUserInClub()



function Tennis_MetricsForSeriesOpen($seriesID, $visibility)
	{
	/*
		This function fetches a list of metrics 
		for a given series. The list returned is based
		on the Visibility flag.
	
	ASSUMES:
		1) A connection the MySql database is open.
	
	TAKES:
		1) The Series ID number.
		2) A "visibility" flag. V=Visible, H=Hidden, X=Don't care.
		
	RETURNS:
		1) An array that contains rows corresponding to the list
		   of eligible members for the Series.
		   ($eligiblelList).
		2) $eligiblelList[0][0] = "ERROR" if an error occurred.
		   In this case $eligiblelList[1][0] will contain an error
		   message in HTML form than can be output to the user.

	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";
	$tblName = 'qrySeriesMetrics';

	$tmp = query_qryGetQuery($tblName);
	$query = "SELECT * ";
	$query .= "FROM {$tmp} ";
	$query .= "WHERE ({$tblName}.SeriesID={$seriesID}) ";
	if ($visibility == 'V')
		{
		$query .= "AND (metricDisplayCode=31 OR metricDisplayCode=33) ";
		}
	if ($visibility == 'H')
		{
		$query .= "AND metricDisplayCode=32 ";
		}

	if ($DEBUG)
		{
		echo "<p>VISIBILITY: {$visibility}</p>";
		echo "<p>QUERY: {$query}</p>";
		}
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
		return FALSE;
		}
	return $qryResult;


} //END FUNCTION


function Tennis_SeriesRosterOpen($seriesID, $subSet)
	{
	/*
		This function opens a result-set for all RSVP records for a
		given series

	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) The Series ID number.
		
	RETURNS:
		1) Resource ID of the result set.
		   
	*/
	
	$DEBUG = FALSE;
//	$DEBUG = TRUE;

	global $CRLF;


	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";
	$tblName = 'qrySeriesRsvps';

		$tmp = query_qryGetQuery($tblName);
		$query = "SELECT {$tblName}.*, IF(authority.Privilege,authority.Privilege,0) AS userPrivEvt ";
		$query .= "FROM {$tmp} ";
		$query .= "LEFT JOIN authority ON ";
		$query .= "authority.ObjType=43 AND evtID=authority.ObjID AND authority.Person={$_SESSION['recID']} ";
		switch ($subSet)
			{
			case 'DON':
				$query .= "WHERE ({$tblName}.ID={$seriesID} AND (evtResultCode=36 OR evtResultCode=37 OR evtResultCode=38)) ";
				break;
		
			case 'FUT':
				$query .= "WHERE ({$tblName}.ID={$seriesID} AND (evtResultCode=34 OR evtResultCode=35)) ";
				break;
		
			default:
				$query .= "WHERE ({$tblName}.ID={$seriesID}) ";
			}
		$query .= "ORDER BY prsnFName, prsnLName, evtStart;";

		if ($DEBUG)
			{
			echo "<p>QUERY: {$query}</p>";
			}
		$qryResult = mysql_query($query);
		if (!$qryResult)
			{
			$GLOBALS['lstErrExist'] = TRUE;
			$GLOBALS['lstErrMsg'] = "ERROR";
			$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
			$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $query;
			return FALSE;
			}
		return $qryResult;

} //END FUNCTION Tennis_SeriesRosterOpen








function Tennis_SeriesEventsOpen($seriesID, $subSet)
	{
	/*
						***********************************************************
		1/17/2012: 	**	This function has been superceded by the function in 	**
						**	class "event" -> openView($eventID, $subset="")			**
						***********************************************************

		This function opens a query that supplies a
		set of Event records for a given series.

	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) The Series ID number.
		2) $subSet: Defines which sub-set of the events
		   to include in the query.
		
	RETURNS:
		1) Resource ID of the result set.
		
	REVISIONS:
		12/30/2011: For the autoActions system, I may need to extend this function
		with an added $subSet case. I need the ability to specify a subset of
		events whose start date/times are within a specific window of time,
		and regardless of the event's result-code status. This is going to
		require me to add two add'l parameters for the time-window boundaries.

	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;

	global $CRLF;


	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";
	$tblName = 'qrySeriesEvts';

					//   Using the base query in $tblName, build a
					//query that will return a column that shows the
					//current user's rights for each event returned.
		$tmp = query_qryGetQuery($tblName);
		$query = "SELECT {$tblName}.*, IF(authority.Privilege,authority.Privilege,0) AS userPrivEvt ";
		$query .= "FROM {$tmp} ";
		$query .= "LEFT JOIN authority ON ";
		$query .= "authority.ObjType=43 AND evtID=authority.ObjID AND authority.Person={$_SESSION['recID']} ";
		switch ($subSet)
			{
			case 'DON':
				$query .= "WHERE ({$tblName}.ID={$seriesID} AND (evtResultCode=36 OR evtResultCode=37 OR evtResultCode=38));";
				break;
		
			case 'FUT':
				$query .= "WHERE ({$tblName}.ID={$seriesID} AND (evtResultCode=34 OR evtResultCode=35));";
				break;
		
			case 'PAST':
				$query .= "WHERE ({$tblName}.ID={$seriesID} AND (evtResultCode=34 OR evtResultCode=35) AND {$tblName}.evtStart<NOW());";
				break;
		
			default:
				$query .= "WHERE ({$tblName}.ID={$seriesID});";
			}
		if ($DEBUG)
			{
			echo "<p>QUERY: {$query}</p>";
			}
		$qryResult = mysql_query($query);
		if (!$qryResult)
			{
			$GLOBALS['lstErrExist'] = TRUE;
			$GLOBALS['lstErrMsg'] = "ERROR";
			$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
			$GLOBALS['lstErrMsg'] .= '<BR><BR>Query Sent: ' . $query;
			return FALSE;
			}
		return $qryResult;


} //END FUNCTION Tennis_SeriesEventsOpen

?>