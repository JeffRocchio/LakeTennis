<?php
/*
	   For Mobile Phones.
		
		This script presents an event page under the presumption
	that the event is for a party. Meaning that folks are asked to
	bring stuff.
		This was created in Nov 0f 2014 for use with our annual
	Mixed-Up Doubles Social.
	   11/26/2014: Initial Creation.

---------------------------------------------------------------------------- */
session_start();
include_once('../INCL_Tennis_Functions_Session.php');
include_once('../INCL_Tennis_DBconnect.php');
include_once('../INCL_Tennis_Functions.php');
include_once('../INCL_Roster.php');
Session_Initalize();


$DEBUG = FALSE;
//$DEBUG = TRUE;

//----GLOBALS ---------------------------------------------------------------->
$CRLF = "\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

$RsvpEditPage = "meditRSVPPrsnMixedDbls.php";


//----DECLARE LOCAL VARIABLES------------------------------------------------->

				//   Declare arrays to hold the detail rsvp
				//recordset and the associated master records
				//for the event and the series.
$rowRsvp = array();
$rowEvent = array();
$rowSeries = array();

				//   Holds the recordIDs.
$recID = 0;
$eventID = 0;
$seriesID = 0;
				//   For testing function returns for errors.
$bSuccess = TRUE;


//----CONNECT TO MYSQL ------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}

//----OBTAIN QUERY STRING VARIABLES ------------------------------------------>
				//   Get the event ID from the query string.
	if ($_GET['ID'] > 0)
		{
		$recID = $_GET['ID'];
		}
	else
		{
		echo "<P>ERROR, No Event Selected.</P>";
		include './INCL_footer.php';
		exit;
		}

	$eventID = $recID;
	
//----GET MASTER DATA RECORDS ------------------------------------------------>
	$bSuccess = local_Data_GetMasters($eventID, $rowEvent, $rowSeries);
	if(!$bSuccess)
		{
		echo "<P>{$lstErrMsg}</P>";
		include './tennis/INCL_footer.php';
		exit;
		}
	$seriesID = $rowSeries['ID'];


//----BUILD PAGE HEADER ------------------------------------------------------>
				//   Output page header.

	local_OutputPageHeader($eventID, $rowEvent, $rowSeries);


//----BUILD PAGE SECTION: EVENT DETAILS -------------------------------------->


	local_GenSection_EventDescription($eventID, $rowEvent, $rowSeries);


//----BUILD PAGE SECTION: ATTENDING LIST ------------------------------------->


	$out = "<div>{$CRLF}";
	$out .= "<i>To update your rsvp, or what you are bringing, click on your name below.</i><BR /><BR />{$CRLF}";
	$out .= "</div>{$CRLF}";
	echo $out;
	local_GenSection_Attending($eventID, $rowEvent, $rowSeries);


//----BUILD PAGE SECTION: NO-RESPONSE/UNDERTAIN LIST ------------------------->


	local_GenSection_Undecided($eventID, $rowEvent, $rowSeries);


//----BUILD PAGE SECTION: NOT ATTENDING LIST --------------------------------->

	local_GenSection_NotAttending($eventID, $rowEvent, $rowSeries);


//----BUILD BOTTOM OF PAGE --------------------------------------------------->
				//   Make page-bottom links.
$out = "<DIV><BR />";
				//   If a valid group member is logged in, then make
				//some "action" links.
if ($_SESSION['recID'] > 0)
	{
	$out .= "Actions:{$CRLF}";
	$out .= "<BR />&nbsp;&nbsp;&nbsp;* <A HREF=\"/tennis/emailPracticeRSVP.php";
	$out .= "?SID={$seriesID}&NUM={$rowSeries['EvtsIREmail']}\">";
	$out .= "Email RSVP To Yourself</A>{$CRLF}";
	$out .= "<BR>&nbsp;&nbsp;&nbsp;* ";
	$out .= "<A HREF=\"/tennis/listSeries_Emails.php?ID={$seriesID}\">";
	$out .= "Make Email Address List</A>{$CRLF}";
	$out .= "<BR />";
	}
				//   Make general-purpose links.
$out .= "Useful Links:{$CRLF}";
$out .= "<BR />&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='/tennis/dispSeries.php";
$out .= "?ID={$seriesID}'>Series Notes</A>{$CRLF}";
$formatType = 'REC'; // Kludging this. Should be set based on the event's purpose code.
switch ($formatType)
	{
	case 'REC':
		$out .= "<BR />&nbsp;&nbsp;&nbsp;* ";
		$out .= "<A HREF=\"/tennis/listPractice_text.php";
		$out .= "?NUM={$rowSeries['EvtsIREmail']}&SID={$seriesID}\">";
		$out .= "Make Confirm Email</A>{$CRLF}";
		break;
		
	case 'NORM':
	default:
		$out .= "<BR />&nbsp;&nbsp;&nbsp;* ";
		$out .= "<A HREF=\"/tennis/listMatch_text.php";
		$out .= "?NUM={$rowSeries['EvtsIREmail']}&SID={$seriesID}\">";
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

$_SESSION['RtnPg'] = "/tennis/mobile/meventPage.php?ID={$eventID}";
echo  Tennis_BuildFooter('NORM', $_SESSION['RtnPg']);


/*====Local Functions =========================================================
*/


function local_Data_GetMasters($eventID, &$rowEvent, &$rowSeries)
  {
    /*
	    Open the detail rsvp recordset and fetch the related master records
	for the Event and associated Series.
    */
    
	global $lstErrMsg;

	$seriesID = 0;
					//   Get the associated event record.
	Tennis_GetSingleRecord($rowEvent, "Event", $eventID);
					
					//   Get the associated series record.
	$seriesID = $rowEvent['Series'];				
	Tennis_GetSingleRecord($rowSeries, "series", $seriesID);
					
	return TRUE;
  
} //END FUNCTION


function local_Data_getRSVPSet($eventID, $subset)
	{

	global $CRLF;

	switch ($subset)
		{
		case 'ATTENDING': // Available, Confirmed or Late
			$selCrit = "(rsvpClaimCode=15 OR rsvpClaimCode=13 OR rsvpClaimCode=16)"; // ="Available"
			break;
		
		case 'NOTATTENDING':
			$selCrit = "(rsvpClaimCode=11)"; // ="Available"
			break;
		
		default: // No Response or Tentative
			$selCrit = "(rsvpClaimCode=10 OR rsvpClaimCode=14)";
		}
	
	if(!$qryResult = Tennis_OpenViewGeneric('qryRsvpBringing', "WHERE (evtID={$eventID} AND {$selCrit})", "ORDER BY prsnLName, prsnFName"))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	
	return $qryResult;
}




function local_OutputPageHeader($eventID, &$rowEvent, &$rowSeries)
  {
    /*
	    Generate and output the page header.
    */
    
	global $CRLF;
	global $lstErrMsg;

	$tbar = "mTennis - Event Participation";
	$pgL1 = "Tennis Event";
	$pgL2 = "SERIES: {$rowSeries['LongName']}";
	$pgL3 = "EVENT: {$rowEvent['Name']}";
	echo Tennis_BuildHeader('MOBILE', $tbar, $pgL1, $pgL2, $pgL3);

	return TRUE;
  
} //END FUNCTION



function local_GenSection_EventDescription($eventID, &$rowEvent, &$rowSeries)
  {
    /*
		Generate and output the section of the page that shows the
	event details.
    */
    
	global $CRLF;
	global $lstErrMsg;

		//   We are going to pull the event description from the series.
		//This is because this version of this page was built to support
		//the Mixed-Up Doubles Tennis Social so I am scrambling to put
		//this together real quick.
	$descText = $rowSeries['Description'];	
	$out = Roster_DisplaySeriesDescription($descText);
	echo $out;

	return TRUE;
  
} //END FUNCTION



function local_GenSection_Attending($eventID, &$rowEvent, &$rowSeries)
  {
    /*
		Generate and output the section of the page that 
	Lists all those planning on attending.
    */
    
	global $CRLF;
	global $lstErrMsg;
	global $RsvpEditPage;

	
	
		//   Open the relevant rsvp recordset.
	$qryResult = local_Data_getRSVPSet($eventID, "ATTENDING");
	if(!$qryResult)
		{
		echo "<P>{$lstErrMsg}</P>";
		include './tennis/INCL_footer.php';
		exit;
		}
	$rowsReturned = mysql_num_rows($qryResult);
	$out = "<div>";
	$out .= "<b>ATTENDING</b>";
	if ($rowsReturned > 0)
		{
		$out .= "<TABLE BORDER='1' CELLSPACING=0 CELLPADDING=3px>";
		$out .= "<THEAD>";
		$out .= "<TD><b>Name</b></TD><TD><b>Bringing</b></TD>";
		$out .= "</THEAD>";
		echo $out;
		$recCountMale = 0;
		$recCountFemale = 0;
		$rowRsvp = mysql_fetch_array($qryResult);
		do
			{
			$prsnID = $rowRsvp['prsnID'];
			switch ($rowRsvp['prsnGender']) { case "M":$recCountMale += 1; break; case "F":$recCountFemale += 1; break; }
			if ($_SESSION['member'] == TRUE)
				{
				if ($_SESSION['recID'] == $prsnID) // Highlight current user's row.
					{
					$tmp = "<b>{$rowRsvp['prsnFullName']}</b>";
					$tmp .= " <FONT style=\"font-size:smaller\">(edit rsvp)</FONT>";
					}
				else
					{
					$tmp = $rowRsvp['prsnFullName'];
					}
				}
			else
				{
				$tmp = $rowRsvp['prsnPName'];
				}
			$out = "<TR><TD>";
			$out .= "<A HREF=\"{$RsvpEditPage}?ID={$rowRsvp['ID']}&PID={$rowRsvp['prsnID']}\">{$tmp}</A>";
			$out .= "</TD>";
			if (is_null($rowRsvp['rsvpBringingTxt']))
				{ 
				$tmp="&nbsp;";
				}
			else
				{
				$tmp = $rowRsvp['rsvpBringingTxt'];
				}
			$out .= "<TD>{$tmp}";
			$out .= "</TD>";
			$out .= "</TR>{$CRLF}";
			echo $out;
			}
		while ($rowRsvp = mysql_fetch_array($qryResult));

		$recCountTotal = $recCountMale+$recCountFemale;
		echo "</TABLE>{$CRLF}";
		echo "<P>";
		echo "Head Count:";
		echo " {$recCountTotal}";
		echo " (M:{$recCountMale} F:{$recCountFemale})";
		echo "</P>{$CRLF}";
		}
	else
		{
		$out .= "<P>Nobody rsvp'd as attending yet.</P>{$CRLF}";
		echo $out;
		}
	echo "</div>{$CRLF}{$CRLF}";

	return TRUE;
  
} //END FUNCTION



function local_GenSection_Undecided($eventID, &$rowEvent, &$rowSeries)
  {
    /*
		Generate and output the section of the page that 
	Lists all those who have not yet responded or have marked
	themselves as tentative.
    */
    
	global $CRLF;
	global $lstErrMsg;
	global $RsvpEditPage;

	
	
		//   Open the relevant rsvp recordset.
	$qryResult = local_Data_getRSVPSet($eventID, "NORSVP");
	if(!$qryResult)
		{
		echo "<P>{$lstErrMsg}</P>";
		include './tennis/INCL_footer.php';
		exit;
		}
	$rowsReturned = mysql_num_rows($qryResult);
	$out = "<div>";
	$out .= "<b>NOT YET RESPONDED OR STILL UNDECIDED</b>";
	if ($rowsReturned > 0)
		{
		$out .= "<TABLE BORDER='1' CELLSPACING=0 CELLPADDING=3px>";
		$out .= "<THEAD>";
		$out .= "<TD><b>Name</b></TD><TD><b>Status</b></TD>";
		$out .= "</THEAD>";
		echo $out;
		$recCountMale = 0;
		$rowRsvp = mysql_fetch_array($qryResult);
		do
			{
			$prsnID = $rowRsvp['prsnID'];
			$recCountMale += 1;
			if ($_SESSION['member'] == TRUE)
				{
				if ($_SESSION['recID'] == $prsnID) // Highlight current user's row.
					{
					$tmp = "<b>{$rowRsvp['prsnFullName']}</b>";
					$tmp .= " <FONT style=\"font-size:smaller\">(edit rsvp)</FONT>";
					}
				else
					{
					$tmp = $rowRsvp['prsnFullName'];
					}
				}
			else
				{
				$tmp = $rowRsvp['prsnPName'];
				}
			$prsnID = $rowRsvp['prsnID'];
			$out = "<TR><TD>";
			$out .= "<A HREF=\"{$RsvpEditPage}?ID={$rowRsvp['ID']}&PID={$rowRsvp['prsnID']}\">{$tmp}</A>";
			$out .= "</TD>";
			switch ($rowRsvp['rsvpClaimCode'])
				{ 
				case 10:
					$tmp="No RSVP Yet";
					break;
				case 14:
					$tmp="Undecided";
					break;
				default: 
					$tmp="UNKNOWN";
				}
			$out .= "<TD>{$tmp}";
			$out .= "</TD>";
			$out .= "</TR>{$CRLF}";
			echo $out;
			}
		while ($rowRsvp = mysql_fetch_array($qryResult));

		echo "</TABLE>{$CRLF}";
		}
	else
		{
		$out .= "<P>Everyone has rsvp'd.</P>{$CRLF}";
		echo $out;
		}
	echo "<BR /></div>{$CRLF}{$CRLF}";

	return TRUE;
  
} //END FUNCTION



function local_GenSection_NotAttending($eventID, &$rowEvent, &$rowSeries)
  {
    /*
		Generate and output the section of the page that 
	Lists all those who are not coming.
    */
    
	global $CRLF;
	global $lstErrMsg;
	global $RsvpEditPage;

	
	
		//   Open the relevant rsvp recordset.
	$qryResult = local_Data_getRSVPSet($eventID, "NOTATTENDING");
	if(!$qryResult)
		{
		echo "<P>{$lstErrMsg}</P>";
		include './tennis/INCL_footer.php';
		exit;
		}
	$rowsReturned = mysql_num_rows($qryResult);
	$out = "<div>";
	$out .= "<b>NOT ATTENDING</b>";
	if ($rowsReturned > 0)
		{
		$out .= "<TABLE BORDER='1' CELLSPACING=0 CELLPADDING=3px>";
		$out .= "<THEAD>";
		$out .= "<TD><b>Name</b></TD><TD><b>Notes</b></TD>";
		$out .= "</THEAD>";
		echo $out;
		$recCountMale = 0;
		$rowRsvp = mysql_fetch_array($qryResult);
		do
			{
			$prsnID = $rowRsvp['prsnID'];
			$recCountMale += 1;
			if ($_SESSION['member'] == TRUE)
				{
				if ($_SESSION['recID'] == $prsnID) // Highlight current user's row.
					{
					$tmp = "<b>{$rowRsvp['prsnFullName']}</b>";
					$tmp .= " <FONT style=\"font-size:smaller\">(edit rsvp)</FONT>";
					}
				else
					{
					$tmp = $rowRsvp['prsnFullName'];
					}
				}
			else
				{
				$tmp = $rowRsvp['prsnPName'];
				}
			$prsnID = $rowRsvp['prsnID'];
			$out = "<TR><TD>";
			$out .= "<A HREF=\"{$RsvpEditPage}?ID={$rowRsvp['ID']}&PID={$rowRsvp['prsnID']}\">{$tmp}</A>";
			$out .= "</TD>";
			if (is_null($rowRsvp['rsvpNote']))
				{ 
				$tmp="&nbsp;";
				}
			elseif (strlen($rowRsvp['rsvpNote']) < 3)
				{ 
				$tmp="&nbsp;";
				}
			else
				{
				$tmp = $rowRsvp['rsvpNote'];
				}
			$out .= "<TD>{$tmp}";
			$out .= "</TD>";
			$out .= "</TR>{$CRLF}";
			echo $out;
			}
		while ($rowRsvp = mysql_fetch_array($qryResult));

		echo "</TABLE>{$CRLF}";
		}
	else
		{
		$out .= "<P>Nobody is not coming.</P>{$CRLF}";
		echo $out;
		}
	echo "</div>{$CRLF}{$CRLF}";

	return TRUE;
  
} //END FUNCTION




?> 
