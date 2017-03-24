<?php
/*
	This script builds the weekly RSVP email message for
	practice sessions, and sends the email to the person
	running the script.
	
	01/12/2008 Version 1.0 (This needs enhance and clean-up!)
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
include_once('./INCL_Tennis_Email.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();



//$DEBUG = TRUE;
$DEBUG = FALSE;

//----GLOBAL VARIABLES-------------------------------------------------------->
$CRLF = "\n";
$emCRLF = "\r\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


				
				
//----LOCAL VARIABLES--------------------------------------------------------->
$tblName = 'qryRsvp';

$row = array();
$recID = array();

$emBody;
$emSubject = "TENNIS RSVPs";


//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}




//----BUILD PAGE POST--------------------------------------------------------->
if ((array_key_exists('meta_POST', $_POST)) AND ($_POST['meta_POST'] == 'TRUE'))
	{
				//   Retreive Series ID.
	$seriesID = $_POST['meta_SERIES'];
					
				//   Output a page header and 'OK' link
				//for pages where we have successfully
				//posted data to the DB.
	$out = "<html><head>";
	$out .= "<title>Send RSVP Email</title>";
	//	$out .= "<meta http-equiv='REFRESH' content='0;url={$rtnPage}'>";
	$out .= "</head>";
	$out .= "<body>";
	$out .= "<h2>Send RSVP Email</h2>";

			//   Send the email.
	if ($_SESSION['recID'] > 0)
		{
		$Subject = "TENNIS - RSVPs";
		$Body = $_POST['emailBody'];
		$out .= "<P>Email Sent To:<BR />{$CRLF}";
		if (isset($_POST['emailAddr']))
			{
			foreach ($_POST['emailAddr'] as $toAddr)
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
		}
	else
		{
		$out .= "<P>You are not authorized to send email. If you are";
		$out .= "a member, please login and try again.</P>{$CRLF}";
		}

	$out .= "<P>";
	$out .= "<a href=\"{$rtnpg}\">OK</a>{$CRLF}";
	$out .= "&nbsp;&nbsp;";
	$out .= "</P>{$CRLF}{$CRLF}";
	
	echo $out;

	$out = local_MakeLinks("FORM");
	echo $out;
	
		//echo ADMIN_Post_HeaderOK("", $rtnpg, $message);
		
	} // End script for posting the data.


//----BUILD PAGE DATA-ENTRY-------------------------------------------------->
else
	{
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
	elseif ($_GET['NUM'] > 0)
		{
		$NumEvts = $_GET['NUM'];
		$seriesID = $_GET['SID'];
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
		}
				//   Output page header stuff.
	$tbar = "Tennis RSVP Status";
	$pgL1 = "RSVP Status";
	$pgL2 = "";
	$pgL3 = "RSVPs to Email";
	echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


				//   Create a form so we can create a 'SEND' button.
	$out = "<form method='post' action='emailPracticeRSVP.php?POST=T'>{$CRLF}";
	$out .= "<input type=hidden name=meta_RTNPG value={$rtnpg}>{$CRLF}";
	$out .= "<input type=hidden name=meta_ADDPG value=''>{$CRLF}";
	$out .= "<input type=hidden name=meta_POST value=TRUE>{$CRLF}";

	$out .= "<DIV>Email to be Sent:</DIV>{$CRLF}";
	$out .= "<HR />{$CRLF}";
	$out .= "<P>RSVPs for tennis this week ---</P>{$CRLF}";
	$emBody = "RSVPs for tennis this week ---{$emCRLF}{$emCRLF}";
	echo $out;
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
		$dispVenue = $row['venueShtName'];
		$dispEvtName = $row['evtName'];
		$dispTitle = "<A HREF='dispEvent.php?ID={$curEvtID}'>{$dispEvtName}</A>, {$dispDate} // {$dispTime} at {$dispVenue}:";
					//   Remove the &nbsp; HTML character from the time string.
		$tmp = substr ($dispTime, 0, 5);
		$tmp .= " ";
		$tmp .= substr ($dispTime, 11, 2);
		$emBody .= "{$dispEvtName}, {$tmp} at {$dispVenue}:{$emCRLF}";
		local_ListNames($dispTitle, $curEvtID, $emBody, FALSE);
		}

		
	$emBody .= local_MakeLinks("EMAIL");
	
					//   Here we have the choice to put the email text into a
					//form field so the form's user can make edits to it before
					//sending it off; or else we just display on the page what the
					//email will look like when we send it without allowing the
					//user the option to edit it.
					//   For now, I'm choosing to use the form-field version.
					//To change this be sure to also change the last
					//value in the local_ListNames function to TRUE.
	$out = "<P>{$CRLF}";
	$out .= "<TEXTAREA NAME=emailBody ROWS=20 COLS=60>{$CRLF}";
	$out .= $emBody;
//	$out .= "test{$emCRLF}Line2";
	$out .= "</TEXTAREA>{$CRLF}{$CRLF}";
//	$out = "<P>{$CRLF}<input type=hidden name=emailBody value=\"{$emBody}\">{$CRLF}{$CRLF}";

					//   Make fields so user can select which email
					//addresss to send to. But only make the fields if a
					//valid user is logged in.
	if ($_SESSION['recID'] > 0)
		{
		$out .= "<HR />{$CRLF}";
		$out .= local_makeEmailSelect();
					//   Make the form's Save button.
		$out .= "<input type='submit' value='SEND'>{$CRLF}";
		}
	else
		{
		$out .= "{$CRLF}<input type=hidden name=emailNumber value=\"0\">{$CRLF}{$CRLF}";
		$out .= "You must be a group member to use this page.";
		$out .= " Please login.{$CRLF}";
		}

					//   Save the series ID.	
	$out .= "{$CRLF}<input type=hidden name=meta_SERIES value=\"{$seriesID}\">{$CRLF}{$CRLF}";
					//   Close out the form.
	$out .= "</form></P>{$CRLF}{$CRLF}";
	echo $out;

	$out = local_MakeLinks("FORM");
	echo $out;
	echo  Tennis_BuildFooter('NORM', "emailPracticeRSVP.php?{$_SERVER['QUERY_STRING']}");
	} // End script for page/form display.
	


$_SESSION['RtnPg'] = "/tennis/listSeriesRoster.php?ID={$seriesID}";



//---FUNCTIONS ----------------------------------------------------------------

function local_ListNames($title, $eventID, &$emBody, $pgDisp)
	{
	
	//   $pgDisp: IF TRUE then write the text of what the email will contain
	//to the screen as well as building it into the email body. IF FALSE then
	//do not write it to the screen because presumbably we're putting the
	//email text into an editable text field so the user can modify the email
	//text before it gets sent off.
	
	GLOBAL $CRLF;
	GLOBAL $emCRLF;
	
	$numResponses = 0;
	$out = "<P>$title<BR>{$CRLF}";
	if ($_SESSION['member'])
		{
		$keyPrsnName = 'prsnFullName';
		}
		else
		{
		$keyPrsnName = 'prsnPName';
		}
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
//	echo "<P>In Function: {$emBody}</P>";

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


function local_makeEmailSelect()
	{
	
	GLOBAL $CRLF;
	
	$person = array();
	$recID = $_SESSION['recID'];
	$html = "";	

	Tennis_GetSingleRecord($person, "qryPersonDisp", $recID);
	
	$html .= "<DIV>{$CRLF}";
	$html .= "Select which email addresses to send to:{$CRLF}";
	$html .= "<BR /><BR />{$CRLF}{$CRLF}";
	for ($k=1; $k<=3; $k++)
		{
		$fld = "Email{$k}";
		$fldVal = "{$person[$fld]}";
		if (strlen($fldVal) > 3)
			{
			$html .= "<input type=checkbox name=emailAddr[{$k}]";
			$html .= " ID=\"{$k}\"value=\"{$fldVal}\"";
			if ($person["Email{$k}Active"] == 1) $html .= " CHECKED";
			$html .= ">{$CRLF}";
			$html .= "<LABEL FOR={$k}>{$fldVal}</LABEL><BR />{$CRLF}";
			$html .= "{$CRLF}";
			}
		}
	$html .= "<BR />{$CRLF}</DIV>{$CRLF}";
	return $html;

}

function local_MakeLinks($format)
	{
	global $CRLF;
	global $emCRLF;
	global $seriesID;
	
	$serverTxt = "http://" . $_SERVER['HTTP_HOST'];
	$homePg = $serverTxt . "/ClubHome.php?ID={$_SESSION['clubID']}";

	switch ($format)
		{
		case 'EMAIL':
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
