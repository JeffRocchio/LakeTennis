<?php
/*
	This script lists all the people eligible for a series.
	The series ID is assumed to be passed in via the
	query string.
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

				//   Open the Event for this series query-result-set.
if (!$qryResult = Tennis_EligibleForSeriesOpen($seriesID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
				
				//   Build the page header and the display table
				//heading row.
$iCols = 0;
while ($row = mysql_fetch_array($qryResult))
	{

				//   Get the table's column meta-data so we can get the primary
				//index field.
$eligibleList = Tennis_GetEligibleForSeries($seriesID);
if ($eligibleList[0][0] == "ERROR")
	{
	echo $eligibleList[1][0];
	include './INCL_footer.php';
	exit;
	}
				


				//   Output page header stuff.
echo "<html><head>
<title>List Eligible Players for Series " . $eligibleList[1]['seriesName'] ."</title>
</head>
<body>
<P><FONT SIZE=-1>List Eligible Players for Series</FONT><BR>
<FONT SIZE=+1><u>" . $eligibleList[1]['seriesName'] . "</u></FONT></P>{$CRLF}";

				
				//   Display the result in a table.
echo "<TABLE BORDER='1' CELLSPACING=0 CELLPADDING=2>";
//echo "<TABLE>";
				//   Build the table to display the players.
				//The table column display heading names are
				//stored in row-0 of the $eligiblelList array.
$i=0;
foreach ($eligibleList as $columnArray)
	{
	if ($i==0)
		{
		$tblRowStr = "<TR>";
		$tblRowStr .= "<TH align=left>" . $columnArray['ID'] . "</TH>{$CRLF}";
//		$tblRowStr .= "<TH align=left>" . $columnArray['seriesName'] . "</TH>{$CRLF}";
		$tblRowStr .= "<TH align=left>" . $columnArray['fullName'] . "</TH>{$CRLF}";
		$tblRowStr .= "<TH align=left>" . $columnArray['personPName'] . "</TH>{$CRLF}";
		$tblRowStr .= "<TH align=left>" . $columnArray['personID'] . "</TH>{$CRLF}";
		$tblRowStr .= "</TR>{$CRLF}";
		}
	else
		{
		$tblRowStr = "<TR>";
		$tblRowStr .= "<TD align=left>" . $columnArray['ID'] . "</TD>{$CRLF}";
//		$tblRowStr .= "<TD align=left>" . $columnArray['seriesName'] . "</TD>{$CRLF}";
		$tblRowStr .= "<TD align=left>" . $columnArray['fullName'] . "</TD>{$CRLF}";
		$tblRowStr .= "<TD align=left>" . $columnArray['personPName'] . "</TD>{$CRLF}";
		$tblRowStr .= "<TD align=left>" . $columnArray['personID'] . "</TD>{$CRLF}";
		$tblRowStr .= "</TR>{$CRLF}";
		}
	echo $tblRowStr;
	$i++;
	}
echo "</TABLE>";

echo  Tennis_BuildFooter('NORM', 'listSeriesRoster.php?ID=3');

?> 
