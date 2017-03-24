<?php
/*
	This script shows all the details for a given
	Series, including the list of people who are
	Eligible to participate in it.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');

Session_Initalize();


$DEBUG = FALSE;
//$DEBUG = TRUE;


$CRLF = "\n";
$row = array();

				//   Get the series ID from the query string. If
				//empty, report error and do nothing.
$seriesID = $_GET['ID'];
if (!$seriesID)
	{
	echo "<P>ERROR, No Series Selected.</P>";
	include './INCL_footer.php';
	exit;
	}

$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

				//   Get series record.
if (!Tennis_GetSingleRecord($row, 'series', $seriesID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


				//   Build the page header.
$tbar = "Tennis: {$row['ShtName']} Details";
$pgL1 = "Details";
$pgL3 = "Series Detail";
$pgL2 = $row['LongName'];
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);

				//   Display Series details.
$out = "<TABLE CLASS='ddTable' CELLSPACING='2'>{$CRLF}";
$SeriesType = ADMIN_dbGetNameCode($row['Type'],FALSE);
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Series Type</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$SeriesType}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Series Full Name</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['LongName']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Reference Web Site</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
$out .= "<A HREF='{$row['URL']}'>{$row['URL']}</A></P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Series Description</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldDataLong'>{$row['Description']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Notes & Comments</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldDataLong'>{$row['Notes']}</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";
echo $out;

					//   List the eligible participants.
$out = "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Eligible Participants</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
if (!$qryResult = Tennis_EligibleForSeriesOpen($seriesID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
while ($row = mysql_fetch_array($qryResult))
	{
	if ($_SESSION['member'] == TRUE)
		{
		$out .= "{$row['prsnFullName']}<BR>{$CRLF}";
		}
	else
		{
		$out .= "{$row['prsnPName']}<BR>{$CRLF}";
		}

	}
$out .= "</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";
$out .= "</TR>{$CRLF}";
echo $out;


					//   Make a link to show an email address list.
$out = "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TD CLASS='ddTblCellLabel'><P CLASS='ddFieldName'>Make Email List</P></TD>{$CRLF}";
$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
$out .= "<A HREF=\"listEmails.php?OBJ=SERIES&ID={$seriesID}\">Make Email List</A>";
$out .= "</P></TD>{$CRLF}";
$out .= "</TR>{$CRLF}";
$out .= "</TABLE>{$CRLF}";
echo $out;



echo  Tennis_BuildFooter("NORM", "dispSeries.php?ID={$seriesID}");

?> 
