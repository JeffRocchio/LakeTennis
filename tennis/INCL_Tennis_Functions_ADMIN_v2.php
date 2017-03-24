<?php
/*
   05/10/2009: Significant modifications made to implement the
multiclubs features.

   02/28/2009: Made very significant changes to the Tennis_dbRecordInsert()
function. These need to be tested well and then use this file to replace
the existing ADMIN_v2 file. When this is done you also have to change the
include file name in the addClubMember.php file.

   02/15/2009: NOTE that Ken Sussewell alerted me to an issue -- On forms 
where we are entering data for a new record insert into the DB, if the user
is not authorized with edit rights on a given field, the functions here do
not generate any HTML INPUT strings for those fields. The functions simply
generate a non-editable disply as a proxy for that field. The result is that
once the form is posted to the DB the value in such fields is entirely
dependent upon the default value defined in the database's schema for that
table/column. Once consequence of this is where a 'default' value is passed
in via the function call, it will be displayed as static text for the user,
but if that passed-in default value is different than the default in the
database, it is the database's default that will be applied, thus confusing
the user and potentially causing unintended results. So the field-gen functions
really need to be updated so that will generate hidden INPUT form-fields
that contain the passed-in default values for cases where we are creating a 
form that is used to create a new record.

	1/21/2007. This is version 2.0 of this include file. The field-gen functions
have been modified to make use of the 'authority' table edit-rights
capability.

*/
include_once('INCL_Tennis_Email.php');

function Tennis_ResetRSVPs($event, $claimCd)
	{
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

	
	//---CODE--------------------------------------------------------------
	
	$query = "UPDATE rsvp ";
	$query .= "SET ClaimCode={$claimCd}, ";
	$query .= "Position=30, ";
	$query .= "Role=20, ";
	$query .= "Note='' ";
	$query .= "WHERE (rsvp.Event={$event});";
	if ($DEBUG)
		{
		echo "<p>Reset RSVP Records</p>";
		echo "<p>QUERY:</P>";
		echo "<P>{$query}</p>";
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

	return TRUE;

} // end function




function Tennis_GenLBoxSeries($name, $defaultKey)
	{
	/*
		This function generates the HTML that builds a drop-down
		selection box for the series table. For use in a form.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) The default series, or the current series if using the box
		   to edit an existing record vs creating a new one.
		
	RETURNS:
		1) A string that contains the Option-Select HTML for a form
		   input control.
		2) = "ERROR" if an error occurred.

		   
		   
	VARIABLES USED IN FUNCTION --------------------------------------------

	$CRLF
		:: AS String.
		:: Contains a carriage-return / Line-Feed string.

	$row
		:: AS Array.
		:: Holds one row of the result-set from a query to mysql.
		
	$query
		:: AS String.
		:: Working string to hold a query to send to the DBMS.
	
	$qryResult
		:: AS Resource.
		:: Contains the resource that holds the result of the query.
		Returns FALSE if the query failed.
	
	
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$query = "SELECT series.ID AS seriesID, ";
	$query .= "series.ShtName AS seriesName ";
	$query .= "FROM series ";
	$query .= "ORDER BY series.Sort;";
	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$listBox = "ERROR: ";
		$listBox .= '<P>Invalid query: ' . mysql_error() . "<\p>";
		$listBox .= '<P>Query Sent: ' . $query . "</P>";
		return $listBox;
		}
	
	$listBox = "<SELECT name={$name}>";
	while ($row = mysql_fetch_array($qryResult))
		{
		if ($row['seriesID'] == $defaultKey)
			{
			$listBox .= '<OPTION SELECTED value ="';
			}
		else
			{
			$listBox .= '<OPTION value ="';
			}
		$listBox .=$row['seriesID'];
		$listBox .= '">';
		$listBox .= $row['seriesName'];
		$listBox .= "</OPTION>{$CRLF}";
		}
	$listBox .= "</SELECT>{$CRLF}";
					
	return $listBox;


} //END FUNCTION


function Tennis_GenLBoxVenue($name, $defaultKey)
	{
	/*
		07/24/2008: OBSOLETE.But verify that it is no longer being used 
									before actually removing it.
		
		This function generates the HTML that builds a drop-down
		selection box for the venue table. For use in a form.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) The default venue, or the current venue if using the box
		   to edit an existing record vs creating a new one.
		
	RETURNS:
		1) A string that contains the Option-Select HTML for a form
		   input control.
		2) = "ERROR" if an error occurred.

		   
		   
	VARIABLES USED IN FUNCTION --------------------------------------------

	$CRLF
		:: AS String.
		:: Contains a carriage-return / Line-Feed string.

	$row
		:: AS Array.
		:: Holds one row of the result-set from a query to mysql.
		
	$query
		:: AS String.
		:: Working string to hold a query to send to the DBMS.
	
	$qryResult
		:: AS Resource.
		:: Contains the resource that holds the result of the query.
		Returns FALSE if the query failed.
	
	
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$query = "SELECT venue.ID AS venueID, ";
	$query .= "venue.LongName AS venueName ";
	$query .= "FROM venue ";
	$query .= "ORDER BY venue.Sort;";
	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$listBox = "ERROR: ";
		$listBox .= '<P>Invalid query: ' . mysql_error() . "<\p>";
		$listBox .= '<P>Query Sent: ' . $query . "</P>";
		return $listBox;
		}
	
	$listBox = "<SELECT name={$name}>";
	while ($row = mysql_fetch_array($qryResult))
		{
		if ($row['venueID'] == $defaultKey)
			{
			$listBox .= '<OPTION SELECTED value ="';
			}
		else
			{
			$listBox .= '<OPTION value ="';
			}
		$listBox .=$row['venueID'];
		$listBox .= '">';
		$listBox .= $row['venueName'];
		$listBox .= "</OPTION>{$CRLF}";
		}
	$listBox .= "</SELECT>{$CRLF}";
 
	return $listBox;


} //END FUNCTION


function Tennis_GenLBoxTable($tblName, $filterID, $name, $defaultKey)
	{
	/*
		This function generates the HTML that builds a drop-down
		selection box for the specifid table. For use in a form.
	
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

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";

	$tmp = query_qryGetQuery("qryLBV{$tblName}");
	$query = "SELECT * ";
	$query .= "FROM {$tmp}";
	if ($filterID > 0)
		{
		$query .= " WHERE filterID={$filterID}";
		}
	$query .= ";";
	if ($DEBUG)
		{
		echo "<p>tmp: {$tmp}</p>";
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
	
	$listBox = "<SELECT name={$name}>";
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
 
	return $listBox;


} //END FUNCTION



function Tennis_GenLBoxCodeSet($name, $setID, $defaultKey)
	{
	/*
		This function generates the HTML that builds a drop-down
		selection box for a given code-set. For use in a form.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) The code-set ID.
		2) Default code value, or the current code value if using
		   the box to edit an existing record vs creating a new one.
		
	RETURNS:
		1) A string that contains the Option-Select HTML for a form
		   input control.
		2) = "ERROR" if an error occurred.

		   
		   
	VARIABLES USED IN FUNCTION --------------------------------------------

	$CRLF
		:: AS String.
		:: Contains a carriage-return / Line-Feed string.

	$row
		:: AS Array.
		:: Holds one row of the result-set from a query to mysql.
		
	$query
		:: AS String.
		:: Working string to hold a query to send to the DBMS.
	
	$qryResult
		:: AS Resource.
		:: Contains the resource that holds the result of the query.
		Returns FALSE if the query failed.
	
	
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$query = "SELECT Code.ID AS codeID, ";
	$query .= "Code.LongName AS codeName ";
	$query .= "FROM Code ";
	$query .= "WHERE Code.fkCodeSet={$setID} ";
	$query .= "ORDER BY Code.Sort;";
	if ($DEBUG)
		{
		echo "<p>QUERY: {$query}</p>";
		}
	
	$qryResult = mysql_query($query);
	if (!$qryResult)
		{
		$listBox = "ERROR: ";
		$listBox .= '<P>Invalid query: ' . mysql_error() . "<\p>";
		$listBox .= '<P>Query Sent: ' . $query . "</P>";
		return $listBox;
		}
	
	$listBox = "<SELECT name={$name}>";
	while ($row = mysql_fetch_array($qryResult))
		{
		if ($row['codeID'] == $defaultKey)
			{
			$listBox .= '<OPTION SELECTED value ="';
			}
		else
			{
			$listBox .= '<OPTION value ="';
			}
		$listBox .=$row['codeID'];
		$listBox .= '">';
		$listBox .= $row['codeName'];
		$listBox .= "</OPTION>{$CRLF}";
		}
	$listBox .= "</SELECT>{$CRLF}";
	
	return $listBox;


} //END FUNCTION




function Tennis_GenDataEntryField(&$fldSpec, &$fldLabel, &$fldHelp)
	{
	/*
		This function generates the HTML for 1 row of a table
		that is being used in a data-entry form
	
	ASSUMES:
		1) $fldSpec contains all the HTML that defines the field
		form's <INPUT> filed, including any default value set
		in the field.
	
	TAKES:
		1) $fldSpec: Contains all the HTML that defines the field
		   form's <INPUT> filed, including any default value set
		   in the field.
		2) $fldLabel: The text used for the user-friendly lable
		   for the field.
		3) $fldHelp: Any help-text for the field.
		
	RETURNS:
		1) A string that contains the entire HTML spec to output
		   one row of the data-entry table.
		   
		   
	VARIABLES USED IN FUNCTION --------------------------------------------

	$CRLF
		:: AS String.
		:: Contains a carriage-return / Line-Feed string.

	$rowHTML
		:: AS String.
		:: String we will build the table row HTML in.
	
	$tmp
		:: AS String.
		:: Temporary working string.
		
	
	*/
	
	//---INITILIZE---------------------------------------------------------
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;


	//---BUILD TABLE ROW---------------------------------------------------
	
	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>{$fldLabel}</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	$rowHTML .= $fldSpec;
	$rowHTML .= "</P>";
	if (strlen($fldHelp) >= 0)
		{
		$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>";
		}
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "</TR>{$CRLF}";

	return $rowHTML;


} //END FUNCTION



function ADMIN_GenFieldText(&$fldLabel, &$fldHelp, $fldFrmName, $fldMaxLen, $fldDispLen, &$fldValue, $fldAuth, $usrRights)
	{
	/*
		This function generates the HTML for 1 row of a table
		that is being used in a data-entry form
	
	TAKES:
		1) $fldSpec: Contains all the HTML that defines the field
		   form's <INPUT> filed, including any default value set
		   in the field.
		2) $fldLabel: The text used for the user-friendly lable
		   for the field.
		3) $fldHelp: Any help-text for the field.
		4) $fldAuth: The minimum edit-authority level for the
		   field. If the current user does not have this
		   level of authority, the filed will be generated
		   as display-only.
		5) $usrRights: The actual, current, user edit rights, as
		   determined by the calling script or page.
		
	RETURNS:
		1) A string that contains the entire HTML spec to output
		   one row of the data-entry table.
	*/
	
	//---INITILIZE---------------------------------------------------------
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;


	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>{$fldLabel}</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	if (ADMIN_EditAuthorized($fldAuth, $usrRights))
		{
		$fldSpec = "<INPUT TYPE=text NAME={$fldFrmName} ";
		$fldSpec .= "SIZE={$fldDispLen} MAXLENGTH={$fldMaxLen} ";
		$fldSpec .= "VALUE='{$fldValue}'>{$CRLF}";
		}
	else
		{
		$fldSpec = $fldValue;
		}
	$rowHTML .= $fldSpec;
	$rowHTML .= "</P>{$CRLF}";
	if (strlen($fldHelp) >= 0)
		{
		$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>{$CRLF}";
		}
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "</TR>{$CRLF}{$CRLF}";

	return $rowHTML;

} //END FUNCTION



function ADMIN_GenFieldYN(&$fldLabel, &$fldHelp, $fldFrmName, $fldValue, $fldAuth, $usrRights)
	{
	/*
		   This function generates the HTML for 1 row of a table
		that is being used in a data-entry form
		   Specifically it generates a set of radio buttons for
		indicating a "Yes / No" choice.
	
	TAKES:
		1) $fldLabel: The text used for the user-friendly lable
		   for the field.
		2) $fldHelp: Any help-text for the field.
		3) $fldAuth: The minimum edit-authority level for the
		   field. If the current user does not have this
		   level of authority, the filed will be generated
		   as display-only.
		4) $fldFrmName: The name to use for the form field.
			Generally this needs to be the same text-string as
			the field's name in the MYSql table to which we are
			going to post the data.
		5) $fldValue: A 1 if current field value is "Yes." A 0
			if the current field value is "No."
		
	RETURNS:
		1) A string that contains the entire HTML spec to output
		   one row of the data-entry table.
	*/
	
	//---INITILIZE---------------------------------------------------------
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;


	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>{$fldLabel}</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	if (ADMIN_EditAuthorized($fldAuth, $usrRights))
		{
		if ($fldValue == 1)
			{
			$fldSpec = "Yes <INPUT type='radio' name='{$fldFrmName}' value='1' CHECKED> ";
			$fldSpec .= "No <INPUT type='radio' name='{$fldFrmName}' value='0'> ";
			}
		else
			{
			$fldSpec = "Yes <INPUT type='radio' name='{$fldFrmName}' value='1'> ";
			$fldSpec .= "No <INPUT type='radio' name='{$fldFrmName}' value='0' CHECKED> ";
			}
		}
	else
		{
		if ($fldValue == 1) { $fldState = 'YES'; } else { $fldState = 'NO'; }
		$fldSpec = $fldState;
		}
	$rowHTML .= $fldSpec;
	$rowHTML .= "</P>{$CRLF}";
	if (strlen($fldHelp) >= 0)
		{
		$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>{$CRLF}";
		}
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "</TR>{$CRLF}{$CRLF}";

	return $rowHTML;

} //END FUNCTION



function ADMIN_GenFieldGender(&$fldLabel, &$fldHelp, $fldFrmName, $fldValue, $fldAuth, $usrRights)
	{
	/*
		   This function generates the HTML for 1 row of a table
		that is being used in a data-entry form
		   Specifically it generates a set of radio buttons for
		selecting gender of a person.
	
	TAKES:
		1) $fldLabel: The text used for the user-friendly lable
		   for the field.
		2) $fldHelp: Any help-text for the field.
		3) $fldAuth: The minimum edit-authority level for the
		   field. If the current user does not have this
		   level of authority, the filed will be generated
		   as display-only.
		4) $fldFrmName: The name to use for the form field.
			Generally this needs to be the same text-string as
			the field's name in the MYSql table to which we are
			going to post the data.
		5) $fldValue: "M", "F", "U" (unknown) or it may also be NULL.
		
	RETURNS:
		1) A string that contains the entire HTML spec to output
		   one row of the data-entry table.
	*/
	
	//---INITILIZE---------------------------------------------------------
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;


	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>{$fldLabel}</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	if (ADMIN_EditAuthorized($fldAuth, $usrRights)) {
		switch ($fldValue) {
			case "M":
				$fldSpec = "M <INPUT type='radio' name='{$fldFrmName}' value='M' CHECKED> ";
				$fldSpec .= "F <INPUT type='radio' name='{$fldFrmName}' value='F'> ";
				$fldSpec .= "U <INPUT type='radio' name='{$fldFrmName}' value='U'> ";
				break;
				
			case "F":
				$fldSpec = "M <INPUT type='radio' name='{$fldFrmName}' value='M'> ";
				$fldSpec .= "F <INPUT type='radio' name='{$fldFrmName}' value='F' CHECKED> ";
				$fldSpec .= "U <INPUT type='radio' name='{$fldFrmName}' value='U'> ";
				break;
				
			case "U":
				$fldSpec = "M <INPUT type='radio' name='{$fldFrmName}' value='M'> ";
				$fldSpec .= "F <INPUT type='radio' name='{$fldFrmName}' value='F'> ";
				$fldSpec .= "U <INPUT type='radio' name='{$fldFrmName}' value='U' CHECKED> ";
				break;
				
			default:
				$fldSpec = "M <INPUT type='radio' name='{$fldFrmName}' value='M'> ";
				$fldSpec .= "F <INPUT type='radio' name='{$fldFrmName}' value='F'> ";
				$fldSpec .= "U <INPUT type='radio' name='{$fldFrmName}' value='U'> ";
				break;
		}
	}
	else {
		$fldSpec = $fldValue;
	}
	$rowHTML .= $fldSpec;
	$rowHTML .= "</P>{$CRLF}";
	if (strlen($fldHelp) >= 0) {
		$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>{$CRLF}";
	}
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "</TR>{$CRLF}{$CRLF}";

	return $rowHTML;

} //END FUNCTION



function ADMIN_GenFieldNote(&$fldLabel, &$fldHelp, $fldFrmName, $fldRows, $fldCols, $fldValue, $fldAuth, $usrRights)
	{
	/*
		This function generates the HTML for 1 row of a table
		that is being used in a data-entry form
	
	TAKES:
		1) $fldSpec: Contains all the HTML that defines the field
		   form's <INPUT> filed, including any default value set
		   in the field.
		2) $fldLabel: The text used for the user-friendly lable
		   for the field.
		3) $fldHelp: Any help-text for the field.
		4) $fldAuth: The minimum edit-authority level for the
		   field. If the current user does not have this
		   level of authority, the filed will be generated
		   as display-only.
		
	RETURNS:
		1) A string that contains the entire HTML spec to output
		   one row of the data-entry table.
	*/
	
	//---INITILIZE---------------------------------------------------------
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;


	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>{$fldLabel}</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
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
	$rowHTML .= $fldSpec;
	$rowHTML .= "</P>{$CRLF}{$CRLF}";
	if (strlen($fldHelp) >= 0)
		{
		$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>{$CRLF}";
		}
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "</TR>{$CRLF}{$CRLF}";

	return $rowHTML;

} //END FUNCTION



function ADMIN_GenFieldDropCode(&$fldLabel, &$fldHelp, $fldFrmName, $cdSet, $fldValue, $shtName, $fldAuth, $usrRights)
	{
	/*
		   This function generates the HTML for 1 row of a table
		that is being used in a data-entry form.
		   Specifically, this function generates a drop-down
		box date-entry row.
		   A drop-down box that contains the value-list for the
		code-set passed in param $cdSet.
	
	RETURNS:
		1) A string that contains the entire HTML spec to output
		   one row of the data-entry table.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>{$fldLabel}</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	if (ADMIN_EditAuthorized($fldAuth, $usrRights))
		{
		$fldSpec = Tennis_GenLBoxCodeSet($fldFrmName, $cdSet, $fldValue);
		}
	else
		{
						//   Convert the code-key to it's
						//text value for display purposes.
		$fldSpec = ADMIN_dbGetNameCode($fldValue, $shtName);
		}
	$rowHTML .= $fldSpec;
	$rowHTML .= "</P>{$CRLF}{$CRLF}";
	if (strlen($fldHelp) >= 0)
		{
		$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>{$CRLF}";
		}
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "</TR>{$CRLF}{$CRLF}";

	return $rowHTML;

} //END FUNCTION



function ADMIN_GenFieldDropTbl(&$fldLabel, &$fldHelp, $fldFrmName, $tblName, $fltrID, $fldValue, $shtName, $fldAuth, $usrRights)
	{
	/*
		   This function generates the HTML for 1 row of a table
		that is being used in a data-entry form
		   Specifically, this function generates a drop-down
		box date-entry row.
		   A drop-down box that contains a selection-list
		built from the records of a table in the DB. The
		records included are defined by the $tblName param
		and the $fltrID as used within the called function
		Tennis_GenLBoxTable().
	
	RETURNS:
		1) A string that contains the entire HTML spec to output
		   one row of the data-entry table.
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	global $CRLF;

	$rowHTML = "<TR class=deTblRow>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
	$rowHTML .= "<P class=deFieldName>{$fldLabel}</P>";
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
	if ($DEBUG) { $rowHTML .= "tblName: {$tblName}<BR>fldValue: {$fldValue}"; }
	if (ADMIN_EditAuthorized($fldAuth, $usrRights))
		{
		$fldSpec = Tennis_GenLBoxTable($tblName, $fltrID, $fldFrmName, $fldValue);
		}
	else
		{
						//   Convert the code-key to it's text value for
						//display purposes. Also, create the hidden field
						//in the form to hold the current value so it can be
						//posted to DB when submitted.
		$fldSpec ="<input type=hidden name={$fldFrmName} value={$fldValue}>";
		$fldSpec .= ADMIN_dbGetNameTbl($tblName, $fldValue, $shtName);
		}
	$rowHTML .= $fldSpec;
	$rowHTML .= "</P>{$CRLF}{$CRLF}";
	if (strlen($fldHelp) >= 0)
		{
		$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>{$CRLF}";
		}
	$rowHTML .= "</TD>{$CRLF}";
	$rowHTML .= "</TR>{$CRLF}{$CRLF}";

	return $rowHTML;

} //END FUNCTION


function Tennis_dbRecordInsert(&$postData, $tblName, $DEBUG = FALSE)
	{
	/*
		This function inserts a new record into a table
		in the tennis database.
	
	ASSUMES:
		1) Mysql connection is currently open and the link to it is named
			$link.
		2) The new data is contained in an array and passed in by reference
			via the $postData argument. (What is typically passed in is the
			$_POST array from an entry/edit form page, but it doesn't have to be
			$_POST, it can be any array of the form.)
		   (typically as a result of an edit form having been posted to a
		   page which called this funtion).
		4) Every field to be posted into the table has a key=>value pair
			in the array.
		3) The ['key'] strings of array exactly match the
		   field names in the database.
		5) ['key'] strings that begin with the sub-string 'meta_'
		   are NOT database field entries, but are used for
		   other purposes and should be ignored by this function.
	
	TAKES:
		1) A pointer to the array.
		2) The name of the table we are to insert into.
		
	RETURNS:
		1) A message indicating the number of records inserted.

		   
		   
	VARIABLES USED IN FUNCTION --------------------------------------------

	$CRLF
		:: AS String.
		:: Contains a carriage-return / Line-Feed string.

	$row
		:: AS Array.
		:: Holds one row of the result-set from a query to mysql.
		
	$query
		:: AS String.
		:: Working string to hold a query to send to the DBMS.
	
	$qryResult
		:: AS Resource.
		:: Contains the resource that holds the result of the query.
		Returns FALSE if the query failed.
	
	
	*/
	
	//$DEBUG = FALSE;
	//$DEBUG = TRUE;
		
	global $CRLF;
	global $link;
	
	$numRecords = 0;
	
	$debugNote = "";
	$debugArrayDump = "";
	$message = "";

				//   Build the insert query. And note the addslashes() call. This is
				//to be sure any text strings with quotes or apostrophies are
				//escaped so they won't get truncated or cause a MySQL error.
				//BUT- it seems that on the server this is done automatically,
				//so don't do it here after-all or else you'll get too many
				//slashes.
	$i=0;
	$query = "INSERT INTO {$tblName} SET ";
	foreach ($postData as $key => $value)
		{
		if ($DEBUG)
			{
			$debugArrayDump .= "<P>key: {$key} // Value: {$value}</P>";
			}
		if (substr($key, 0, 5) <> 'meta_')
			{
			if ($i <> 0) $query .= ", ";
//			$value = addslashes($value);
			$query .= "{$key}='{$value}'";
			$i++;
			}
		}
	if ($DEBUG)
		{
		$debugNote  = "<p>===> NOTE: We are inside of Tennis_dbRecordInsert() and ";
		$debugNote .= "in DEBUG mode. ";
		$debugNote .= "By design of the DEBUG coding, no updates were made ";
		$debugNote .= "to the database.</p>{$CRLF}{$CRLF}";

		$debugNote .= "<p>===> Insert Query Which Would Have Been Sent:";
		$debugNote .= "<BR><BR>{$query}</p>{$CRLF}{$CRLF}";
		
		$debugNote .= "<p>====> Dump of the $_POST Array:<BR><BR>";
		$debugNote .= $debugArrayDump;

		$qryResult = true;
		$numRecords = 1;
		}
	else
		{
		$qryResult = mysql_query($query);
		$numRecords = mysql_affected_rows($link);
		}
				//   Check result. 
				//   IF error, show the actual query sent to MySQL, and the
				//error, if any.
	if (!$qryResult)
		{
		$message  = 'ERROR: <P>Invalid query: ' . mysql_error() . "<\p>";
		$message .= '<P>Whole query: ' . $query . "</P>";
		return $message;
		}
	
				//   Report results.
	$message = "<p>Records Added: {$numRecords}</p>";
	$message .= $debugNote;

	return $message;

	
} // END FUNCTION
	




function Tennis_dbRecordDeleteByID($tblName, $recID, $DEBUG = FALSE)
	{
	/*
		This function deletes a record from a table
		in the tennis database.
	
	ASSUMES:
		1) Mysql connection is currently open and the link to it is named
			$link.
	
	TAKES:
		1) The name of the table we are to delete from.
		2) The record ID to delete.
		
	RETURNS:
		1) A message indicating the number of records removed.

		   
		   
	VARIABLES USED IN FUNCTION --------------------------------------------

	$CRLF
		:: AS String.
		:: Contains a carriage-return / Line-Feed string.

	$query
		:: AS String.
		:: Working string to hold a query to send to the DBMS.
	
	$qryResult
		:: AS Resource.
		:: Contains the resource that holds the result of the query.
		Returns FALSE if the query failed.
	
	
	*/
	
	//$DEBUG = FALSE;
	//$DEBUG = TRUE;
		
	global $CRLF;
	global $link;
	
	$numRecords = 0;
	
	$debugNote = "";
	$message = "";

				//   Build the delete query.
	$query = "DELETE FROM {$tblName} WHERE ID={$recID}";
	if ($DEBUG)
		{
		$debugNote  = "<p>===> NOTE: We are inside of Tennis_dbRecordDelete()";
		$debugNote .= " and ";
		$debugNote .= "in DEBUG mode. ";
		$debugNote .= "By design of the DEBUG coding, no updates were made ";
		$debugNote .= "to the database.</p>{$CRLF}{$CRLF}";

		$debugNote .= "<p>===> Delete Query Which Would Have Been Sent:";
		$debugNote .= "<BR><BR>{$query}</p>{$CRLF}{$CRLF}";

		$qryResult = true;
		$numRecords = 1;
		}
	else
		{
		$qryResult = mysql_query($query);
		$numRecords = mysql_affected_rows($link);
		}
				//   Check result. 
				//   IF error, show the actual query sent to MySQL, and the
				//error, if any.
	if (!$qryResult)
		{
		$message  = 'ERROR: <P>Invalid query: ' . mysql_error() . "<\p>";
		$message .= '<P>Whole query: ' . $query . "</P>";
		return $message;
		}
	
				//   Report results.
	$message = "<p>Records Removed: {$numRecords}</p>";
	$message .= $debugNote;

	return $message;

	
} // END FUNCTION
	




function Tennis_dbRecordUpdate(&$post, $tblName)
	{
	/*
		This function updates a record in a table
		in the tennis database.
	
	ASSUMES:
		1) Mysql connection is currently open.
		2) The updated data is contained in the global $_POST array,
		   as a result of an edit form having been posted to a
		   page which called this funtion.
		3) The ['key'] strings of $_POST array exactly match the
		   field names in the database.
		4) Every field in the table has a key=>value pair in the
		   $_POST array.
		5) ['key'] strings that begin with the sub-string 'meta_'
		   are NOT database field entries, but are used for
		   other purposes and should be ignored by this function.
	
	TAKES:
		1) A pointer to the $_POST array (even tho this is global).
		2) The name of the table we are to insert into.
		
	RETURNS:
		1) A message indicating the number of records inserted.

		   
		   
	VARIABLES USED IN FUNCTION --------------------------------------------

	$CRLF
		:: AS String.
		:: Contains a carriage-return / Line-Feed string.

	$row
		:: AS Array.
		:: Holds one row of the result-set from a query to mysql.
		
	$query
		:: AS String.
		:: Working string to hold a query to send to the DBMS.
	
	$qryResult
		:: AS Resource.
		:: Contains the resource that holds the result of the query.
		Returns FALSE if the query failed.
	
	
	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
		
	$EMAIL = FALSE;
	$EMAIL = TRUE;
		
	global $CRLF;

				//   Build the query. And note the addslashes() call. This is
				//to be sure any text strings with quotes or apostrophies are
				//escaped so they won't get truncated or cause a MySQL error.
				//BUT- it seems that on the server this is done automatically,
				//so don't do it here after-all or else you'll get too many
				//slashes.
	$i=0;
	
	$query = "UPDATE {$tblName} SET ";
	foreach ($post as $key => $value)
		{
		if ($DEBUG)
			{
			echo "<P>key: {$key} // Value: {$value}</P>";
			}
		if (substr($key, 0, 5) <> 'meta_')
			{
			if ($i <> 0) $query .= ", ";
//			$value = addslashes($value);
			$query .= "{$key}='{$value}'";
			$i++;
			}
		}
	$query .= " WHERE ID={$post['ID']};";
	if ($DEBUG)
		{
		echo "<p>Update Query:<BR>{$query}</p>";
		}
	
	$qryResult = mysql_query($query);
				//   Check result. 
				//   IF error, show the actual query sent to MySQL, and the
				//error, if any.
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR><BR>Query Sent: ' . $query;
		$message = $GLOBALS['lstErrMsg'];
		return FALSE;
		}


				//   Notify JEFF via email that record has
				//been changed.
if (array_key_exists('meta_EMAIL', $post))
	{
	if($post['meta_EMAIL']=="Y")
		{
		$EmailBody = '';
		$i=0;
		foreach ($post as $key => $value)
			{
			if (substr($key, 0, 5) <> 'meta_')
				{
				if ($i <> 0) $EmailBody .= ", ";
				$EmailBody .= "{$key}='{$value}'";
				$i++;
				}
			}
		EMAIL_dbUpdateNotify($tblName, $post['ID'], $EmailBody);
		echo "<p>Email Notification Sent to Club Administrator.</p>";
		}
	}
	
				//   Report results.
	$message = "<p>Records Updated: {$qryResult}</p>";
	if ($DEBUG)
		{
		$message .= '<P>Whole query: ' . $query . "</P>";
		}
	return $message;

	
} // END FUNCTION



function Tennis_dbLineupUpdate()
	{
	/*
		This function updates multiple RSVP records. This
		set of RSVP records represents a Lineup for a given
		event.
	
	ASSUMES:
		1) Mysql connection is currently open.
		2) The updated data is contained in the global $_POST array,
		   as a result of an edit form having been posted to a
		   page which called this funtion.
		3) The ['key'] strings of $_POST array are of the form
			'xPOS#' where '#' is the RSVP record ID that is to
			be updated.
		5) ['key'] strings that begin with any string other
			than 'xPOS' are NOT RSVP records to be updated
		   and should be ignored by this function.
	
	TAKES:
		1) nothing.
		
	RETURNS:
		1) The number of records inserted.

	*/
	
	$DEBUG = FALSE;
//	$DEBUG = TRUE;
		
	global $CRLF;


	$tblName = 'rsvp';
	$i=0;
	foreach ($_POST as $key => $field)
		{
		if (strpos($key, 'POS') == 1)
			{
			$ID = substr ($key, 4, strlen($key));
			$query = "UPDATE {$tblName} SET Position='{$field}' WHERE ID={$ID};";
			if ($DEBUG)
				{
				echo "<P>{$key} - ID: {$tmp} VALUE: {$field}</P>";
				echo "<P><b>query:</b><BR>{$query}";
				}
			$qryResult = mysql_query($query);
			if (!$qryResult)
				{
				$GLOBALS['lstErrExist'] = TRUE;
				$GLOBALS['lstErrMsg'] = "ERROR";
				$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
				$GLOBALS['lstErrMsg'] .= '<BR><BR>Query Sent: ' . $query;
				$message = $GLOBALS['lstErrMsg'];
				return $i;
				}
			$i = $i + 1;
			}
		}
	return $i;

	
} // END FUNCTION



function ADMIN_dbDeleteEvent($eventID)
	{
	/*
		This function removes an Event from the Roster-Grid, including
		all the RSVP associative records for the Event/Series combination.
	
	ASSUMES:
		1) Mysql connection is currently open.
	
	TAKES:
		1) Event Record ID.
		
	RETURNS:
		1) TRUE is successful, FALSE otherwise.

	*/
	
	$DEBUG = FALSE;
	//$DEBUG = TRUE;

				//   Build the Delete queries.
	$rsvpQuery = "DELETE rsvp";
	$rsvpQuery .= " FROM rsvp";
	$rsvpQuery .= " WHERE (rsvp.Event={$eventID});";

	$eventQuery = "DELETE Event";
	$eventQuery .= " FROM Event";
	$eventQuery .= " WHERE (Event.ID={$eventID});";
	
	if ($DEBUG)
		{
		echo "<P><b>RSVP Delete Query:</b><BR />{$rsvpQuery}";
		echo "<BR /><BR /><b>Event Delete Query:</b><BR />{$eventQuery}";
		}

				//   Run each query in turn.
	$qryResult = mysql_query($rsvpQuery);
	//$qryResult = TRUE; //For Testing, don't post to DB yet....
	if ($qryResult) $qryResult = mysql_query($eventQuery);
	//$qryResult = TRUE; //For Testing, don't post to DB yet....
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR><BR>Query Sent:<BR>' . $query;
		$message = $GLOBALS['lstErrMsg'];
		return FALSE;
		}

	return TRUE;
	
} // END FUNCTION


function ADMIN_dbGetNameCode($cdID,$shtName)
	{

	/* This function fetches the descriptive name from
	a CodeSet.
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
		echo "<p>In ADMIN_dbGetCodeName()</p>";
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



function ADMIN_dbGetNameTbl($tblName, $recID, $shtName)
	{
	
	$DEBUG = FALSE;
	//DEBUG = TRUE;
	
	global $CRLF;

	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";


	if ($recID == 0)
		{
		return "";
		}

	$tmp = query_qryGetQuery("qryLBV{$tblName}");
	$query = "SELECT * ";
	$query .= "FROM {$tmp}";
	$query .= " WHERE ID={$recID}";
	$query .= ";";
	if ($DEBUG)
		{
		echo "{$CRLF}{$CRLF}<p>in ADMIN_dbGetNameTbl()</p>";
		echo "{$CRLF}{$CRLF}<p>QUERY: {$query}</p>";
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
	
	$row = mysql_fetch_array($qryResult);
	$fldName = 'description';
						//   Short names not yet implemented
						//in the table LBV queries.
//	if ($shtName) $fldName = 'ShtName';
	return $row[$fldName];

} //END FUNCTION



function ADMIN_EditAuthorized($fldAuth, $usrRights)
	{
	$tmpSelf = '';
	
	$rslt = False;
	$tmpAuth = $fldAuth;
	if (strpos($fldAuth, "&") > 0)
		{
		$tmpAuth = substr($fldAuth, 0, strpos($fldAuth, "&"));
		$tmpSelf = substr($fldAuth, strpos($fldAuth, "&")+1);
		$perID = substr($tmpSelf, strpos($tmpSelf, "=")+1);
		}
	if ($tmpAuth=='SADM' and $usrRights=='SADM') $rslt = True;
	if ($tmpAuth=='ADM' and ($usrRights=='SADM' or $usrRights=='ADM')) $rslt = True;
	if ($tmpAuth=='MGR' and ($usrRights=='SADM' or $usrRights=='ADM' or $usrRights=='MGR')) $rslt = True;
	elseif (strlen($tmpSelf) > 0)
		{
		if ($_SESSION['recID'] == $perID)
			{
			$rslt = True;
			}
		}
	
	return $rslt;

} //END FUNCTION



function ADMIN_Post_HeaderOK($tblName, $rtnPage, &$message, $act="GO")
	{
	
				//   Output a page header and 'OK' link
				//for pages where we have successfully
				//posted data to the DB.
	$html = "<html><head>";
	$html .= "<title>Updated Record in {$tblName}</title>";
	if ($act == 'GO')
		{
		$html .= "<meta http-equiv='REFRESH' content='0;url={$rtnPage}'>";
		}
	$html .= "</head>";
	$html .= "<body>";
	$html .= "<h2>UPDATED Record in Table <u>{$tblName}</u></h2>";
	
	$html .= $message;

	$html .= "<P>";
	$html .= "<a href='{$rtnPage}'>OK</a>";
	$html .= "&nbsp;&nbsp;";
	$html .= "</P>";

	return $html;

} //END FUNCTION



?>
