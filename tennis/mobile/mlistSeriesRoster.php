<?php
/*
	   For Mobile Phones.
		
		This script displays the names of each person who is
	participating in a given series along with a link to a page that
	allows you to edit their availability for each event.
	   12/29/2007: Initial Creation.

---------------------------------------------------------------------------- */
session_start();
include_once('../INCL_Tennis_Functions_Session.php');
include_once('../INCL_Tennis_DBconnect.php');
include_once('../INCL_Tennis_Functions.php');
include_once('../INCL_Roster.php');
Session_Initalize();


//$DEBUG = TRUE;
$DEBUG = FALSE;

//----GLOBALS ---------------------------------------------------------------->
$CRLF = "\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------------->

				//   Declare array to hold the detail display
				//record.
$row = array();
$recID = array();

//----CONNECT TO MYSQL ------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

				//   Determine which series to list for.
if ($_GET['ID'] > 0)
	{
	$recID = $_GET['ID'];
	}
else
	{
	echo "<P>ERROR, No Series Selected.</P>";
	include './INCL_footer.php';
	exit;
	}

				//   Open query with the series info
				//and all the eligible participants.
$where = "WHERE (ID={$recID})";
$sort = "ORDER BY prsnFName, prsnLName";
if(!$qryResult = Tennis_OpenViewGeneric('qrySeriesEligible', $where, $sort))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './tennis/INCL_footer.php';
	exit;
	}
$row = mysql_fetch_array($qryResult);

					//   Get the series Type code, and other series variables
					//that we'll need.
switch ($row['seriesTypeCode'])
	{
	case 53: //Recreational play.
		$formatType='REC';
		break;

	case 54: //League play.
	default:
		$formatType='NORM';
	}
$seriesEvtsIREmail = $row['seriesEvtsIREmail'];
if($seriesEvtsIREmail<=0) $seriesEvtsIREmail=1;
$seriesID = $recID;

	
				//   Output page header stuff.
$tbar = "mTennis - Series Roster";
$pgL1 = "List Series Participants";
$pgL2 = "SERIES: {$row['seriesName']}";
$pgL3 = "";
echo Tennis_BuildHeader('MOBILE', $tbar, $pgL1, $pgL2, $pgL3);

$out = "<div>Click on Participants to See and Edit their availability for any
events in the series.<BR />&nbsp;</div>{$CRLF}{$CRLF}";
echo $out;

echo "<div>";
do
	{
	if ($_SESSION['member'] == TRUE)
		{
		$tmp = $row['prsnFullName'];
		}
	else
		{
		$tmp = $row['prsnPName'];
		}
	$prsnID = $row['prsnID'];
	$out = "<A HREF=\"meditRSVPPrsn.php?SID={$recID}&PID={$prsnID}\">{$tmp}</A>";
	$out .= "<BR />{$CRLF}";
	echo $out;
	}
while ($row = mysql_fetch_array($qryResult));
echo "</div>{$CRLF}{$CRLF}";


				//   Make page-bottom links.
$out = "<DIV><BR />";
				//   If a valid group member is logged in, then make
				//some "action" links.
if ($_SESSION['recID'] > 0)
	{
	$out .= "Actions:{$CRLF}";
	$out .= "<BR />&nbsp;&nbsp;&nbsp;* <A HREF=\"/tennis/emailPracticeRSVP.php";
	$out .= "?SID={$seriesID}&NUM={$seriesEvtsIREmail}\">";
	$out .= "Email RSVP To Yourself</A>{$CRLF}";
	$out .= "<BR>&nbsp;&nbsp;&nbsp;* ";
	$out .= "<A HREF=\"/tennis/listSeries_Emails.php?ID={$seriesID}\">";
	$out .= "Make Email Address List</A>{$CRLF}";
	$out .= "<BR />";
	}
				//   Make general-purpose links.
$out .= "Useful Links:{$CRLF}";
$out .= "<BR />&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='/tennis/listSeriesRoster.php";
$out .= "?ID={$seriesID}'>Full RSVP Grid</A>{$CRLF}";
$out .= "<BR />&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='/tennis/dispSeries.php";
$out .= "?ID={$seriesID}'>Series Notes</A>{$CRLF}";
switch ($formatType)
	{
	case 'REC':
		$out .= "<BR />&nbsp;&nbsp;&nbsp;* ";
		$out .= "<A HREF=\"/tennis/listPractice_text.php";
		$out .= "?NUM={$seriesEvtsIREmail}&SID={$seriesID}\">";
		$out .= "Make Confirm Email</A>{$CRLF}";
		break;
		
	case 'NORM':
	default:
		$out .= "<BR />&nbsp;&nbsp;&nbsp;* ";
		$out .= "<A HREF=\"/tennis/listMatch_text.php";
		$out .= "?NUM={$seriesEvtsIREmail}&SID={$seriesID}\">";
		$out .= "Make Confirm Email</A>{$CRLF}";
		
	}
$hreftxt = "http://laketennis.com";
if ($_SESSION['clubID'] > 0)
	{
	$hreftxt = "http://laketennis.com/ClubHome.php?ID={$_SESSION['clubID']}";
	}
$out .= "<BR />&nbsp;&nbsp;&nbsp;* <A HREF='{$hreftxt}'>";
$out .= "Club Home Page</A>{$CRLF}";
$out .= "</div>{$CRLF}";
echo $out;

$_SESSION['RtnPg'] = "/tennis/mobile/mlistSeriesRoster.php?ID={$seriesID}";
echo  Tennis_BuildFooter('NORM', $_SESSION['RtnPg']);

?> 
