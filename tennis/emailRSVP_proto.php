<?php
/*
	This is my first attempt to build a script that I can have
	the server execute automatically in order to send out the
	weekly RSVP results/reminder email.
	
	
	10/18/2011 Version 0.1 --
		NOTE: Cannot figure out how to run the script in CRON with
	querystring params. So I have hard-coded the parms.

------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
include_once('./INCL_Tennis_Email.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();



$DEBUG = FALSE;
//$DEBUG = TRUE;

//----GLOBAL VARIABLES-------------------------------------------------------->
$CRLF = "\n";
$emCRLF = "\r\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

$seriesID = 0;

			//   Remember, we are running in CRON, so we don't have a
			//user logged into a club. So we can't get the ClubID from the
			//SESSION variable, we have to get it from one of the event/series
			//queries we'll be using later.
$clubID = 0;



				
				
//----LOCAL VARIABLES--------------------------------------------------------->
$tblName = 'qryRsvp';
$NumEvts = 0;

$row = array();
$recID = array();

$freeText = array();

$emBody;
$emSubject = "TENNIS RSVPs (no reply)";
$emTo = array();

$out = "";


//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



//----GET EVENT INFO----------------------------------------------------------->
						//   Determine which event records to list.
						//Either the IDs were passed in via query
						//sting in the URL, or else the query string
						//only specified how many of the next scheduled
						//events to list for.
if (array_key_exists('ID', $_GET))
	{
	$recID[1] = $_GET['ID'];
	$byRecID = True;
	if ($recID[1] < 1)
		{
		echo "<P>ERROR, No Event Selected.</P>";
		include './INCL_footer.php';
		exit;
		}
						//   Two other events we can build the list for.
	if ($_GET['ID2'] > 0) $recID[2] = $_GET['ID2'];
	if ($_GET['ID3'] > 0) $recID[3] = $_GET['ID3'];
	}
elseif (array_key_exists('NUM', $_GET))
	{
	$NumEvts = $_GET['NUM'];
	$seriesID = $_GET['SID'];
	}
else
	{
	$NumEvts = 2;
	$seriesID = 1;
	}
		
if ($NumEvts > 0)
	{
	if (!$qryResult = Tennis_SeriesEventsOpen($seriesID, 'FUT'))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	for ($i=1; $i<=$NumEvts; $i++)
		{
		$row = mysql_fetch_array($qryResult);
		$recID[$i] = $row['evtID'];
		}
	$clubID = $row['ClubID'];
	}


//----OUTPUT PAGE HEADER------------------------------------------------------->
$tbar = "Tennis RSVP Email Auto Send";
$pgL1 = "RSVP Status";
$pgL2 = "";
$pgL3 = "Sending RSVP Emails";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);

$out .= "<DIV>Email to be Sent:</DIV>{$CRLF}";
$out .= "<HR />{$CRLF}";
echo $out;
$out = "";


//----BUILD EMAIL BODY--------------------------------------------------------->
					//   Set the email preamble text.
$emBody = "";
$freeText[0] = "** This is the weekly RSVP reminder for tennis.";
$freeText[1] = "** Please do not reply to this email as this is ";
$freeText[2] = "** an automated message.";
$freeText[3] = "";
$freeText[4] = "RSVPs for tennis this week ---";
$freeText[5] = "";
foreach ($freeText as $textLine)
	{
	$out .= $textLine . "<BR />";
	$emBody .= "{$textLine}{$emCRLF}";
	}
echo $out;
$out = "";
foreach ($recID as $curEvtID)
	{
					//   Get Event Record(s).
	if(!Tennis_GetSingleRecord($row, 'qryEventDisp', $curEvtID))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
					//   Get the Series ID so we can use it later.
	$seriesID = $row['seriesID'];
					//   Build the list - in plain-text form.
	$dispDate = Tennis_DisplayDate($row['evtStart']);
	$dispTime = Tennis_DisplayTime($row['evtStart'], True);
	$dispVenue = $row['venueName'];
	$dispEvtName = $row['evtName'];
	$dispTitle = "<A HREF='dispEvent.php?ID={$curEvtID}'>{$dispEvtName}</A>, {$dispDate} // {$dispTime} at {$dispVenue}:";
					//   Remove the &nbsp; HTML character from the time string.
	$tmp = substr ($dispTime, 0, 5);
	$tmp .= " ";
	$tmp .= substr ($dispTime, 11, 2);
	$emBody .= "{$dispEvtName}, {$tmp} at {$dispVenue}:{$emCRLF}";
	local_ListNames($dispTitle, $curEvtID, $emBody, TRUE);
	}
$emBody .= local_MakeLinks("EMAIL");
$out .= local_MakeLinks("FORM");


$freeText[0] = "";
$freeText[1] = "";
$freeText[2] = "(This email sent on behalf of Jeffrey Rocchio ";
$freeText[3] = "by the laketennis server.)";
$freeText[4] = "";
$freeText[5] = "";
foreach ($freeText as $textLine)
	{
	$out .= $textLine . "<BR />";
	$emBody .= "{$textLine}{$emCRLF}";
	}
$out .= "<HR />{$CRLF}";
echo $out;
$out = "";



//----BUILD EMAIL TO LIST------------------------------------------------------>

$emTo[0] = "rocchio@rocketmail.com";
$emTo[1] = "jroc@activeage.com";

//----SEND THE EMAIL----------------------------------------------------------->
$Subject = $emSubject;
$Body = $emBody;

$out .= "<P><BR />Email Sent To:<BR />{$CRLF}";
if (isset($emTo))
	{
	foreach ($emTo as $toAddr)
		{
		EMAIL_ToAddress($toAddr, $Subject, $Body);
		$out .= "&nbsp;&nbsp;&nbsp;* {$toAddr}<BR />{$CRLF}";
		}
	}
else
	{
	$out .= "No Email Addresses Selected. No mail sent.{$CRLF}";
	}
$out .= "</P>{$CRLF}";
$out .= "<P>";
$out .= "<a href=\"{$rtnpg}\">OK</a>{$CRLF}";
$out .= "&nbsp;&nbsp;";
$out .= "</P>{$CRLF}{$CRLF}";

echo $out;
$out = "";




echo  Tennis_BuildFooter('NORM', "emailRSVP_proto.php?{$_SERVER['QUERY_STRING']}");

$_SESSION['RtnPg'] = "/tennis/listSeriesRoster.php?ID={$seriesID}";



//---FUNCTIONS ----------------------------------------------------------------

function local_ListNames($title, $eventID, &$emBody, $pgDisp)
	{
	
	//   $pgDisp: IF TRUE then write the text of what the email will contain
	//to the screen as well as building it into the email body. IF FALSE then
	//do not write it to the screen.
	
	GLOBAL $DEBUG;
	GLOBAL $CRLF;
	GLOBAL $emCRLF;
	
	$numResponses = 0;
	$out = "<P>$title<BR>{$CRLF}";
	$keyPrsnName = 'prsnFullName';
	$qryResult = local_getRSVPSet($eventID, 'PLAYING');
	$row = mysql_fetch_array($qryResult);
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;{$row[$keyPrsnName]}<BR>{$CRLF}";
			$emBody .= "   * {$row[$keyPrsnName]}{$emCRLF}";
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}
	
	$qryResult = local_getRSVPSet($eventID, 'LATE');
	$row = mysql_fetch_array($qryResult);
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;will be late> {$row[$keyPrsnName]}<BR>{$CRLF}";
			$emBody .= "   * will be late> {$row[$keyPrsnName]}{$emCRLF}";
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}
	
	$qryResult = local_getRSVPSet($eventID, 'TENT');
	$row = mysql_fetch_array($qryResult);
	if (strlen($row['prsnPName']) > 0)
		{
		do
			{
			$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;tentative> {$row[$keyPrsnName]}<BR>{$CRLF}";
			$emBody .= "   * tentative> {$row[$keyPrsnName]}{$emCRLF}";
			$numResponses ++;
			}
		while ($row = mysql_fetch_array($qryResult));
		}


	if ($numResponses == 0)
		{
		$out .= "&nbsp;&nbsp;&nbsp;*** NO RESPONSES. Where IS everybody? ***{$CRLF}";
		$emBody .= "*** NO RESPONSES. Where IS everybody? ***{$emCRLF}";
		}
	
	$out .= "</P>{$CRLF}{$CRLF}";
	$emBody .= "{$emCRLF}";
	if ($pgDisp) echo $out;
	if($DEBUG)
		{
		echo "<P>In Function local_ListNames, Contents of emBody --:<BR />";
		echo "{$emBody}</P>";
		}

}


function local_getRSVPSet($eventID, $subset)
	{
	switch ($subset)
		{
		case 'TENT':
			$selCrit = "rsvpClaimCode=14"; // ="Tentative"
			break;
		
		case 'LATE':
			$selCrit = "rsvpClaimCode=13"; // ="Late"
			break;
		
		default:
//			$selCrit = "rsvpPositionCode=29 AND rsvpClaimCode<>13 AND rsvpClaimCode<>14"; // ="Playing"
			$selCrit = "rsvpClaimCode=15 OR rsvpClaimCode=16"; // ="Available" or "Confirmed"
		}
	
	if(!$qryResult = Tennis_OpenViewGeneric('qrySeriesRsvps', "WHERE (evtID={$eventID} AND ({$selCrit}))", "ORDER BY prsnPName"))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	
	return $qryResult;
}


function local_MakeLinks($format)
	{
	global $CRLF;
	global $emCRLF;
	global $seriesID;
	global $clubID;
	
	$serverTxt = "http://" . $_SERVER['HTTP_HOST'];
	$homePg = $serverTxt . "/ClubHome.php?ID={$_SESSION['clubID']}";

	switch ($format)
		{
		case 'EMAIL':
					//   Remember, when we are running in CRON, we are not running on the
					//web server. So pre-defined variables coming from the web server are
					//not available.
			$serverTxt = "http://laketennis.com";
			$homePg = $serverTxt . "/ClubHome.php?ID={$clubID}";
			$htmltxt = "{$emCRLF}Useful Links:{$emCRLF}{$emCRLF}";
			$htmltxt .= "   * HOME: ";
			$htmltxt .= "[{$homePg}]{$emCRLF}{$emCRLF}";
			$htmltxt .= "   * Full RSVP Grid: ";
			$htmltxt .= "[{$serverTxt}/tennis/listSeriesRoster.php?ID={$seriesID}]{$emCRLF}{$emCRLF}";
			$htmltxt .= "   * Mobile Phone View: ";
			$htmltxt .= "[{$serverTxt}/tennis/mobile/mlistSeriesRoster.php?ID={$seriesID}]{$emCRLF}";
			break;
		
		default:
		case 'FORM':
			$htmltxt = "<P><BR><BR>Useful Links:<BR>{$CRLF}";
			
			$htmltxt .= "&nbsp;&nbsp;&nbsp;*&nbsp;";
			$htmltxt .= "<A HREF=\"{$serverTxt}/tennis/listSeriesRoster.php?ID={$seriesID}\">";
			$htmltxt .= "Full RSVP Grid</A><BR>{$CRLF}";
			
			$htmltxt .= "&nbsp;&nbsp;&nbsp;*&nbsp;";
			$htmltxt .= "<A HREF=\"{$serverTxt}/tennis/dispSeries.php?ID={$seriesID}\">";
			$htmltxt .= "Recreational Play Notes</A><BR>{$CRLF}";
			
			$htmltxt .= "&nbsp;&nbsp;&nbsp;*&nbsp;";
			$htmltxt .= "<A HREF=\"{$serverTxt}/tennis/mobile/mlistSeriesRoster.php?ID={$seriesID}\">";
			$htmltxt .= "Mobile Phone View</A><BR>{$CRLF}";
			
			$htmltxt .= "<BR>&nbsp;&nbsp;&nbsp;*&nbsp;";
			$htmltxt .= "<A HREF=\"{$serverTxt}/tennis/listEmails.php?OBJ=SERIES&ID={$seriesID}\">";
			$htmltxt .= "Make Email Address List</A><BR>{$CRLF}";
			
			$htmltxt .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF=\"{$homePg}\">";
			$htmltxt .= "Club Home Page</A><BR>{$CRLF}";
			
			$htmltxt .= "</P>{$CRLF}";
			break;
		}

	return $htmltxt;
}

?> 
