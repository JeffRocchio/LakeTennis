<?php
/*
	This script lists all the email address for all persons
	eligible to participate in a given series. The email list
	is in a form suitable for cut/paste into an email
	message.
	
	03/16/2008:	This script has been replaced by listEmails.php and
					should be retired.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();



//$DEBUG = TRUE;
$DEBUG = FALSE;


$CRLF = "\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


$tblName = 'qryRsvp';
				
				
				
				//   Declare array to hold the detail display
				//record.
array($row);
array($recID);


				//   Connect to mysql
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
if(!$qryResult = Tennis_OpenViewGeneric('qrySeriesEligible', "WHERE (ID={$recID})", ""))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
$row = mysql_fetch_array($qryResult);
	
				//   Output page header stuff.
$tbar = "Tennis Email List";
$pgL1 = "List Series Emails";
$pgL2 = "";
$pgL3 = "SERIES: {$row['seriesName']}";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);

$out = "<P>Email address list for all participants in the series.<BR><BR>";
$out .= "You should be able to cut/paste the below list into your email TO field:</p>{$CRLF}";
echo $out;

if ($_SESSION['member'])
	{
	if (strlen($row['prsnFullName']) > 0)
		{
		$out = "<P>";
		do
			{
			if ($row['Email1Active'] == 1)
				{
				if (strlen($row['Email1']) > 3) $out .= "{$row['Email1']}, ";
				}
			if ($row['Email2Active'] == 1)
				{
				if (strlen($row['Email2']) > 3) $out .= "{$row['Email2']}, ";
				}
			if ($row['Email3Active'] == 1)
				{
				if (strlen($row['Email3']) > 3) $out .= "{$row['Email3']}, ";
				}
			}
		while ($row = mysql_fetch_array($qryResult));
		$len = strlen($out);
		if ($len > 3)
			{
			$len = strlen($out);
			$last = strrpos($out, ',');
			$out = substr($out, 0, $last);
			}
		else
			{
			$out = "<P>There are no eligible participants for this series.";
			}
		$out .= "</P>";
		}
	}
else
	{
	$out = "<P>You are not authorized to view this page.</P>{$CRLF}";
	$out = "<P>If you are a member, please login to see this page.</P>{$CRLF}";
	}

echo $out;


$out = "<P><BR><BR>Useful Links:<BR>{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='listRecPlay.php?ID={$recID}'>Full RSVP Grid</A><BR>{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='dispSeries.php?ID={$recID}'>Series Notes</A><BR>{$CRLF}";
$hreftxt = "http://laketennis.com";
if ($_SESSION['clubID'] <> 1)
	{
	$hreftxt = "http://laketennis.com/ClubHome.php?ID={$_SESSION['clubID']}";
	}
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='{$hreftxt}'>Club Home Page</A><BR>{$CRLF}";
$out .= "</P>{$CRLF}";
echo $out;






echo  Tennis_BuildFooter('NORM', "listSeries_Emails.php?ID={$recID}");

?> 
