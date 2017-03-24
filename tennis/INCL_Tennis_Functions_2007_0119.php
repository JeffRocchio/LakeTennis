<?php

/*
	Requires Includes:
	
*/
include_once('INCL_Tennis_Functions_QUERIES.php');





function Tennis_BuildFooter($format, $rtnPage)
	{
	/*
		This function outputs the page footer,
		which includes the mini-login form.
	*/
	
	$CRLF = "\n";
	
	
	$footer = "{$CRLF}{$CRLF}<P STYLE='margin-top: 0px'>&nbsp;</P>{$CRLF}";
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
				$footer .= "<td CLASS='tdAdminFooter'><A HREF='../index.php'>HOME</A>{$CRLF}";
				if ($_SESSION['admin'] == True)
					{
					$footer .= "&nbsp;&nbsp<A HREF='admin.php'>Admin</A>{$CRLF}";
					}
				$footer .= "</td>{$CRLF}";
				$footer .= "<td CLASS='tdAdminFooter'>{$CRLF}";
				$footer .= "USER: {$_SESSION['userName']}";
				$footer .= "</td>{$CRLF}";
				$footer .= "</tr>{$CRLF}{$CRLF}";
				$footer .= "</TABLE>{$CRLF}";
				}
			break;
		
		case 'NORM':
		default:
			if ($_SESSION['member'] == False)
				{
				$footer .= "<table CELLSPACING=0 CLASS=\"tblLogin\">{$CRLF}";
				$footer .= "<td CLASS='tdLinks'><A HREF='../index.php'>HOME</A>{$CRLF}";
				$footer .= "</td>{$CRLF}";
				$footer .= "<td CLASS='tdLogin'>{$CRLF}";
				$footer .= "<form id='frmLogin' method='post' action='login.php?POST=T'>{$CRLF}";
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
				$footer .= "</tr>{$CRLF}{$CRLF}";
				$footer .= "</TABLE>{$CRLF}";
				}
			else
				{
				$footer .= "<table CELLSPACING=0 CLASS=\"tblLogin\">{$CRLF}";
				$footer .= "<td CLASS='tdLinks'><A HREF='../index.php'>HOME</A>{$CRLF}";
				if ($_SESSION['admin'] == True)
					{
					$footer .= "&nbsp;&nbsp<A HREF='admin.php'>Admin</A>{$CRLF}";
					}
				$footer .= "</td>{$CRLF}";
				$footer .= "<td CLASS='tdLogin'>{$CRLF}";
				$footer .= "USER: {$_SESSION['userName']}";
				$footer .= "</td>{$CRLF}";
				$footer .= "</tr>{$CRLF}{$CRLF}";
				$footer .= "</TABLE>{$CRLF}";
				}
		}
	$footer .= "<P>&nbsp;</P>{$CRLF}";
	$footer .= "</HTML>{$CRLF}";
	return $footer;

} // END FUNCTION


function Tennis_BuildHeader($format, $tbar, $pgL1, $pgL2, $pgL3)
	{
	/*
		This function builds the HTML header string.
	*/
	
	$CRLF = "\n";
	
	switch ($format)
		{
		case 'ADMIN':
				$params['css'] = './dataEntry.css';
				$params['bdyclass'] = 'bdyNormal';
				$params['pghdL1class'] = 'pgHdrPurpose';
				$params['pghdL2class'] = 'pgHdrTopic1';
				$params['pghdL3class'] = 'pgHdrTopic2';
				break;
		
		case 'METRIC':
				$css['2'] = '../main.css';
				$css['1'] = '../metric.css';
				$params['bdyclass'] = 'bdyNormal';
				$params['pghdL1class'] = 'pgHdrPurpose';
				$params['pghdL2class'] = 'pgHdrTopic1';
				$params['pghdL3class'] = 'pgHdrTopic2';
				break;
		
		case 'NORM':
		default:
				$params['css'] = '../main.css';
				$params['bdyclass'] = 'bdyNormal';
				$params['pghdL1class'] = 'pgHdrPurpose';
				$params['pghdL2class'] = 'pgHdrTopic1';
				$params['pghdL3class'] = 'pgHdrTopic2';
		
		
		} // end switch
	
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
				
		default:
			$out = "<html><head>{$CRLF}";
			$out .= "<LINK REL=StyleSheet HREF=\"{$params['css']}\" TYPE=\"text/css\">{$CRLF}";
		}
		$out .= "<title>{$tbar}</title>{$CRLF}";
		$out .= "</head>{$CRLF}";
		$out .= "<body CLASS={$params['bdyclass']}>{$CRLF}";
		if (strlen($pgL1) > 0) $out .= "<p CLASS={$params['pghdL1class']}>$pgL1</P>{$CRLF}";
		if (strlen($pgL2) > 0) $out .= "<p CLASS={$params['pghdL2class']}>$pgL2</P>{$CRLF}";
		if (strlen($pgL3) > 0) $out .= "<p CLASS={$params['pghdL3class']}>$pgL3</P>{$CRLF}";
		$out .= "<P STYLE='margin-top: 0; margin-bottom: 0'>&nbsp;</P>{$CRLF}";
		
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
	
	$ampm = " am";
	$hr = substr ($date, 11, 2);
	$mn = substr ($date, 14, 2);
	if ($hr >= 12)
		{
		$ampm = " pm";
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
	
	$CRLF = "\n";

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

	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	$CRLF = "\n";

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
	
	$CRLF = "\n";

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
		2) Row 0 of the $eligiblelList array contains user
		   friendly column display titles.
		3) $eligiblelList[0][0] = "ERROR" if an error occurred.
		   In this case $eligiblelList[1][0] will contain an error
		   message in HTML form than can be output to the user.

	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	$CRLF = "\n";

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";
	$tblName = 'qrySeriesEligible';

	$tmp = query_qryGetQuery($tblName);
	$query = "SELECT * ";
	$query .= "FROM {$tmp} ";
	$query .= "WHERE ({$tblName}.ID={$seriesID}) ";
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


function Tennis_NotEligibleForSeriesOpen($seriesID)
	{
	/*
		This function fetches a list of all person IDs
		that have NOT been marked as "eligible" for the
		given series.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	$CRLF = "\n";

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

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
	$query .= "    WHERE (person.Currency=39 AND eligibleForSeries.Series IS NULL)";
	$query .= "  ORDER BY prsnLName, prsnFName;";
	
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
	
	$CRLF = "\n";

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
//$DEBUG = TRUE;

$CRLF = "\n";


$GLOBALS['lstErrExist'] = FALSE;
$GLOBALS['lstErrMsg'] = "";
$tblName = 'qrySeriesRsvps';

	$tmp = query_qryGetQuery($tblName);
	$query = "SELECT * ";
	$query .= "FROM {$tmp} ";
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
	$query .= "ORDER BY prsnPName, evtStart;";

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

	*/
	
$DEBUG = FALSE;
$DEBUG = TRUE;

$CRLF = "\n";


$GLOBALS['lstErrExist'] = FALSE;
$GLOBALS['lstErrMsg'] = "";
$tblName = 'qrySeriesEvts';

	$tmp = query_qryGetQuery($tblName);
	$query = "SELECT * ";
	$query .= "FROM {$tmp} ";
	switch ($subSet)
		{
		case 'DON':
			$query .= "WHERE ({$tblName}.ID={$seriesID} AND (evtResultCode=36 OR evtResultCode=37 OR evtResultCode=38));";
			break;
		
		case 'FUT':
			$query .= "WHERE ({$tblName}.ID={$seriesID} AND (evtResultCode=34 OR evtResultCode=35));";
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