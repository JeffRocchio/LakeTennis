<?php
/*

12/20/2015:
	   Updated to be able to generate a list of emails based on
	RSVP status: All who are Available, Tentative or Not Yet RSVP'd

03/16/2008:
	   This script lists email addresses based on parameters passed
	into it.
	   - Need to finish code for each object-type.
		- Need to add in the links to other places code.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_CONSTANTS.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Email.php');
Session_Initalize();



$DEBUG = FALSE;
//$DEBUG = TRUE;


//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID=$_SESSION['clubID'];

				//   Object Type and ID to list emails for. E.g., the whole
				//club, a series or an Event.
$Obj = "CLUB";
$recID = 0;

$row = array();

				//   Defines the scope of emails to list - the subset.
$Scope = "";
				//   Defines the format in which to list them.
$Format = "";


//----GET URL QUERY-STRING DATA----------------------------------------->
if (array_key_exists('OBJ', $_GET)) $Obj = $_GET['OBJ'];
if (!array_key_exists('ID', $_GET))
	{
	echo "<P>ERROR, No ID Selected.</P>";
	include './INCL_footer.php';
	exit;
	}
$recID = $_GET['ID'];

if (array_key_exists('SCOPE', $_GET)) $Scope = $_GET['SCOPE'];
if (array_key_exists('FORMAT', $_GET)) $Format = $_GET['FORMAT'];


//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----SET VARIABLES RELATED TO OBJECT TYPE------------------------------->
switch ($Obj)
	{
	case "CLUB":
		$ObjDescript = "Club Members";
		$Object = OBJCLUB;
		$tblName = "club";
		$ObjNameField = "ClubName";
		$ScopeDescript = "Who Are Active Members";
		break;
		
	case "SERIES":
		$ObjDescript = "Series";
		$Object = OBJSERIES;
		$tblName = "series";
		$ObjNameField = "ShtName";
		$ScopeDescript = "Who Are Series Participants";
		break;
		
	case "EVENT":
		$ObjDescript = "Event";
		$Object = OBJEVENT;
		$tblName = "Event";
		$ObjNameField = "Name";
		switch ($Scope)
			{
			case 'AVAIL': // Not scheduled to play, but AVAIL.
				$ScopeDescript = "Available to Play or Sub";
				break;
			case 'PLAY': // Scheduled to play.
				$ScopeDescript = "Scheduled to Play";
				break;
			case 'NOTPLAY': // Everyone not scheduled to play, dispite AVAIL.
				$ScopeDescript = "Not Scheduled to Play";
				break;
			case 'RSVPALLPOTENTIAL': // Everyone who is, or might, play based on RSVP claims.
				$ScopeDescript = "All Who Are or Might Participate";
				break;
			case 'RSVPNORESPONSE': // Everyone who has not yet RSVP'd or is tentative.
				$ScopeDescript = "All Who Have Not Yet Posted Their RSVP or is Tentative";
				break;
		default:
				$ScopeDescript = "Who Are Series Participants";
			}
		break;
		
	case "PERSON":
		$ObjDescript = "Specific Person";
		$Object = OBJPERSON;
		$tblName = "person";
		$ObjNameField = "";
		break;
		
	default;
		$pgL3 = "ERROR - Don't Know What to List";

	}


//----MAKE PAGE HEADER--------------------------------------------------->
$testResult = Tennis_GetSingleRecord($row, $tblName, $recID);
if (!$testResult)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


$tbar = "Tennis Email List";
$pgL1 = "Email List";
$pgL2 = "For {$ObjDescript}";
$pgL3 = $row[$ObjNameField];
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----SET UP PAGE FOR DISPLAY------------------------------------------->
if ($_SESSION['member'] != TRUE)
		{
		$out = "<P>You Are Not Authorized to View this Page.</P>{$CRLF}";
		$out .= "<P>IF you are a club member, please login.</P>{$CRLF}";
		echo $out;
		echo  Tennis_BuildFooter('NORM', "listEmails.php?OBJ={$Obj}&ID={$recID}");
		exit;
		}

$out = "<DIV>";
if (strlen($ScopeDescript) > 0)
	{
	$out .= "Email List For Those {$ScopeDescript}:<BR />";
	}
$out .= "----------<BR />{$CRLF}";
echo $out;

$out = EMAIL_listAddresses($Object, $recID, $Scope, $Format);
echo $out;

$out = "<BR />----------<BR />{$CRLF}";
$out .= "(Cut/paste the above list into your email TO field)</DIV>{$CRLF}";
echo $out;


//----LINKS TO OTHER PLACES---------------------------------------------->
		//   Make links to navigate to other places.
		//These links need to be based on the object type and ID
		//for which we've made the email list.
		//   So we'll have to use a switch structure here.
/*
CLUB:
echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>GO TO:</P>{$CRLF}";
echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
echo "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='listPerson_PhoneList.php'>Club Phone List</A><BR>{$CRLF}";
echo "</P>{$CRLF}";

SERIES:
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='listRecPlay.php?ID={$recID}'>Full RSVP Grid</A><BR>{$CRLF}";
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='dispSeries.php?ID={$recID}'>Series Notes</A><BR>{$CRLF}";
$hreftxt = "http://laketennis.com";
if ($_SESSION['clubID'] <> 1)
	{
	$hreftxt = "http://laketennis.com/ClubHome.php?ID={$_SESSION['clubID']}";
	}
$out .= "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='{$hreftxt}'>Club Home Page</A><BR>{$CRLF}";
$out .= "</P>{$CRLF}";

*/

echo  Tennis_BuildFooter('NORM', "listEmails.php?OBJ={$Obj}&ID={$recID}");

?> 
