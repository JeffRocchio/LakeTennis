<?php
/*
	   This script displays a series roster. Meaning, each
	event in the series across columns with each eligible
	person in the series down the rows, and their RSVP
	records in the cells.
	   The series ID is assumed to be passed in via the
	query string.
		02/06/2009: Added security levels per Ken Sussewell request to allow
	club admin to control who can see the grid. This is controlled by setting
	the newly added ViewLevel series field value (new code-set #13).
		07/26/2008: Fixed bug that caused page to hang if called when no
	people or events have been linked to the series.
	   12/29/2006: I modified this script so that it can distinguish
	between a "recreational" series vs a "league" series. Based on
	these two different series types some of the formatting
	is different. As a result the "listRecPlay.php" script is now
	obsolete.
	   01/21/2007: I modified this script to implement edit rights
	via the 'authorities' table.
	   05/26/2007: Added code to support giving out a direct-URL to
	this page and then setting the session ClubID based on the
	club the series is for.

------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_CONSTANTS.php');
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Roster.php');
Session_Initalize();


$DEBUG = FALSE;
//$DEBUG = TRUE;


//----GLOBAL VARIABLES--------------------------------------------------->
					//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES-------------------------------------------->
$debugHTML = "<p>";

$seriesID = 0;
$seriesLongName = "";
$seriesShtName = "";
$seriesDescription = "";
$clubID = 0;
$personID = $_SESSION['recID'];

					//   The current user's edit rights on the current series.
					//Initial default value is "Guest."
					//Note that declare of the rights[] array is for compatability
					//with the Roster_GetUserRights() function and is not
					//currently used in this script; this array approach is for
					//possible future flexibility.
$userPrivSeries = 'GST';
$rights = array('view'=>'GST','edit'=>'GST');

					//   This array is used to build the roster table's heading
					//row. It gets passed into several functions within the
					//INCL_Roster.PHP file to build, then output, the heading
					//rows.
$tblHdrArray = array();

					//   Used to control if phone numbers should appear in
					//the person-name left-hand column of the roster table.
$seriesPhoneOFF = 'N';

					//   Used to control if the series Description field should be
					//displayed above the grid. Default=Yes. May be passed in the
					//query string.
$showDescript = 'Y';

					//   EMPTY SERIES -----:
					//   Holds # of Events for series.
$emptyNumEvents = 0;
					//   Holds # of Persons participating in series.
$emptyNumPersons = 0;
					//   Flag to use if either no events or no persons, and thus
					//we need to display the emptyMessageTxt instead of the grid.
$emptyFlag = FALSE;
					//   Holds Message to display when Events or Persons = 0.
$emptyMessageTxt = "<P>Grid cannot be displayed because either no events or";
$emptyMessageTxt .= " no Persons have been added to this series yet.";
$emptyMessageTxt .= " (If you see see an Event heading, then the problem is";
$emptyMessageTxt .= " that no Persons have been added yet.)";
$emptyMessageTxt .= "<BR><BR><b>Contact the Series or Club Administrator.</b>";
$emptyMessageTxt .= " (If you <i>are</i> the administrator, click on the EDIT link";
$emptyMessageTxt .= " next to the Series Name to add Events and People.)";
$emptyMessageTxt .= "</P>";
					//   Holds Message to display when user does not have view
					//rights to this page.
$noViewMessageTxt = "<P>You do not have permission to view this page.";
$noViewMessageTxt .= " If you believe you are supposed to have the ability";
$noViewMessageTxt .= " to view this page, please contact your Series or";
$noViewMessageTxt .= " Club administrator.";
$noViewMessageTxt .= "</P>";


//=== BEGIN CODE ===========================================================>
//==========================================================================>


//----GET URL-QUERY-STRING-DATA--------------------------------------------->
if (!array_key_exists('VIEW', $_GET)) $seriesView = 'ALL';
else $seriesView = $_GET['VIEW'];
if (!array_key_exists('DES', $_GET)) $showDescript = 'Y';
else $showDescript = $_GET['DES'];
if (array_key_exists('ID', $_GET)) $seriesID = $_GET['ID'];
else
	{
	echo "<P>ERROR, No Series Selected.</P>";
	include './INCL_footer.php';
	exit;
	}
					//   If a phone-listing value is specified, set
					//the setting into the session variable.
					//   If no setting is specified in the URL
					//string, the system default, as set in the
					//Session_ValidateCredentials(), function will
					//be used.
if (array_key_exists('PHOFF', $_GET)) $seriesPhoneOFF = $_GET['PHOFF'];
if ($seriesPhoneOFF == 'Y') { $_SESSION['RSTR_PhListOff'] = TRUE; }
elseif ($seriesPhoneOFF == 'N') { $_SESSION['RSTR_PhListOff'] = FALSE; }


//----SET RETURN PAGE FOR EDITS--------------------------------------------->
$_SESSION['RtnPg'] = "listSeriesRoster.php?ID={$seriesID}&VIEW={$seriesView}";


//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----FETCH THE SERIES RECORD----------------------------------------->
if(!Tennis_GetSingleRecord($row, "series", $seriesID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
					//   Set series variables that we'll need.
$clubID = $row['ClubID'];
$ViewLevel = $row['ViewLevel'];
$seriesLongName = $row['LongName'];
$seriesShtName = $row['ShtName'];
$seriesDescription = $row['Description'];
$seriesEvtsIREmail = $row['EvtsIREmail'];
if($seriesEvtsIREmail<=0) $seriesEvtsIREmail=1;
					//   Get the series Type code, and other
					//variables and aliases that vary by type.
switch ($row['Type'])
	{
	case 53: //Recreational play.
		$formatType='REC';
		$makeResetLink = TRUE;
		break;

	case 54: //League play.
	default:
		$formatType='NORM';
		$makeResetLink = FALSE;
	}
					//   5/26/2007: Support for a direct-link to a
					//series vs coming into the page from the home
					//page. Maybe a bit of a kludge here,
					//but to support multiple "clubs" I need to
					//check to see what club the series is for, and
					//if not for the current club, then we need to
					//set a new clubID in the session variable.
if ($clubID != $_SESSION['clubID'])
	{
	$_SESSION['clubID'] = $clubID;
	}




//----GET USER RIGHTS--------------------------------------------------------->
$userPrivSeries = Roster_GetUserRights($seriesID, $ViewLevel, "listSeriesRoster", $rights);


//----MAKE PAGE HEADER-------------------------------------------------------->
$tbar = "Roster for {$seriesShtName}";
$pgL1 = "Roster for Series";
switch ($seriesView)
	{
	case 'FUT':
		$pgL2 = "Future Events Only";
		break;
	
	case 'DON':
		$pgL2 = "Completed Events Only";
		break;
	
	default:
		$pgL2 = "All Events";
	}
$pgL3 = "<a href='dispSeries.php?ID={$seriesID}'>{$seriesShtName}</a>";
if ($userPrivSeries=='MGR' or $userPrivSeries=='ADM')
	{
	$pgL3 .= "&nbsp;&nbsp;<span style='font-size: x-small'>(";
	$pgL3 .= "<a href='editSeries.php?ID={$seriesID}'>";
	$pgL3 .= "EDIT</a>";
	$pgL3 .= ")</span>";
	} // end if
echo Tennis_BuildHeader($formatType, $tbar, $pgL1, $pgL2, $pgL3);



					//   Now that we have a valid HTML page, output any accumulated
					//debugging info here.
$debugHTML .= "<br>userPrivSeries= {$userPrivSeries}";
$debugHTML .= "<BR>ViewLevel= {$ViewLevel}";
if($DEBUG) echo $debugHTML;

	
//----ASSESS VIEW RIGHTS TO DETERMINE WHAT TO SHOW---------------------------->
					//   Determine if the user has view rights to this page.
					//If not, inform them of this fact and end the script.
if ($userPrivSeries=='NON')
	{
	echo $noViewMessageTxt;
	$tmp = Tennis_dbGetNameCode($ViewLevel, FALSE);;
	echo "<p>(View Level for this Page: <b>{$tmp}</b>)</p>";
	echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);
	exit;
	}



					//   User does have view rights -- BUILD THE GRID....
//==============================================================================	
// In this area put in code to display the series description field.

					//		First, display the series description, if the series 
					//is set to do that.
echo Roster_DisplaySeriesDescription($seriesDescription, $showDescript);

//==============================================================================	


//----BUILD THE GRID --------------------------------------------------------->
if (!$qryResult = Tennis_SeriesEventsOpen($seriesID, $seriesView))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	
	
	
					//   Test to see if we have any events for this series. If
					//not, then output a message explaining why we can't display
					//the grid - then end.
$emptyNumEvents = mysql_num_rows($qryResult);
if($DEBUG) echo "<P>Number of Events: {$emptyNumEvents}</P>";
if ($emptyNumEvents <= 0)
	{
						//   No Event records. Set the empty Flag and don't attempt
						//to build the grid.
	$emptyFlag = TRUE;
	}
else
	{
	
						//   Get the 1st record from the open query.
	$row = mysql_fetch_array($qryResult);
	if($DEBUG) echo "<P>row[1st record] =: {$row}</P>";

						//   Build the display table heading row into
						//an arrary.
	Roster_BuildEvtLableCells($row, $tblHdrArray, $formatType);
	$iCols = 0;
						//   Only build the event heading cells if there are events
						//in this series to build them for.
	do
		{
		Roster_BuildEvtCells($row, $tblHdrArray, $formatType, $makeResetLink, $userPrivSeries);
		$iCols++;
	
		} while ($row = mysql_fetch_array($qryResult));
						//   Open a table and output the table heading rows
						//that were built into an array using the above
						//do-while loop.
	echo Roster_TblOpen($formatType);
	echo Roster_TblHeadOutput($tblHdrArray, $formatType, $makeResetLink);
	
						//   Output the body of the table - the Person and RSVP
						//cells. Do this inside an IF statement so that we are
						//testing for the existence of Person and Event records
						//and don't get caught in an infinite loop.
	if(!Roster_TblBodyOutput($seriesID, $seriesView, $userPrivSeries))
		{
		$emptyFlag = TRUE;
		}
							//   Close out the display table-grid.
	echo Roster_TblClose();
	} // End the IF statement that controls for existence of events.

							//   If we don't have at least 1 Event record and
							//1 Person record, display a notification.
if ($emptyFlag)
	{
	echo $emptyMessageTxt;
	}



//----MAKE TABLE COLOR-CODING KEY--------------------------------------------->
if(!$emptyFlag)
	{
	$tblRowStr = "<P STYLE='margin-top: 20px; margin-bottom: 0'>Color Scheme:{$CRLF}";
	$tblRowStr .= "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
	$tblRowStr .= "<TABLE>";
	$tblRowStr .= "<TR>";
	$tblRowStr .= "<TD CLASS=rosterCellClear STYLE='border-top: 0; border-right: 0' COLSPAN='5'>";
	$tblRowStr .= "<P CLASS=rosterPhone>Whole Column Color-Coding for Completed Events:</P></TD>";
	$tblRowStr .= "</TR>";
	$tblRowStr .= "<TR>";
	$tblRowStr .= "<TD CLASS=rosterDoneWin><P CLASS=rosterPhone>Team WON Match</P></TD>";
	$tblRowStr .= "<TD CLASS=rosterDoneLoss><P CLASS=rosterPhone>Team LOST Match</P></TD>";
	$tblRowStr .= "<TD CLASS=rosterDone COLSPAN='3'><P CLASS=rosterPhone>Match Completed, Result UNKOWN</P></TD>";
	$tblRowStr .= "<TR>";
	$tblRowStr .= "<TR>";
	$tblRowStr .= "<TD CLASS=rosterCellClear STYLE='border-top: 0; border-right: 0' COLSPAN='5' >";
	$tblRowStr .= "<P CLASS=rosterPhone>Person Color-Coding for Future Matches:</P></TD>";
	$tblRowStr .= "</TR>";
	$tblRowStr .= "<TD CLASS=rosterCellPlay><P CLASS=rosterPhone>Scheduled to Play</P></TD>";
	$tblRowStr .= "<TD CLASS=rosterCellAvail><P CLASS=rosterPhone>Available to Play</P></TD>";
	$tblRowStr .= "<TD CLASS=rosterCellNota><P CLASS=rosterPhone>Not Available</P></TD>";
	$tblRowStr .= "<TD CLASS=rosterCellTent><P CLASS=rosterPhone>Tentative or Late</P></TD>";
	$tblRowStr .= "<TD CLASS=rosterUnknown><P CLASS=rosterPhone>Availability Unknown</P></TD>";
	$tblRowStr .= "</TR>";
	$tblRowStr .= "</TABLE>";
	$tblRowStr .= "</P>";
	echo $tblRowStr;
	}


//----LINKS FOR ALTERNATIVE VIEWS -------------------------------------------->
if(!$emptyFlag)
	{
	echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>Alternative Views:<BR>{$CRLF}";
	echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
	echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$seriesID}&VIEW=ALL\">All Events</A><BR>{$CRLF}";
	echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$seriesID}&VIEW=FUT\">Future Events Only</A><BR>{$CRLF}";
	echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$seriesID}&VIEW=DON\">Completed Events Only</A><BR>{$CRLF}";
	echo "*&nbsp;<A HREF=\"mobile/mlistSeriesRoster.php?ID={$seriesID}\">Mobile Phone View</A><BR>{$CRLF}";
	//added 7/30:
	if ($_SESSION['RSTR_PhListOff'] == FALSE)
		{
		echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$seriesID}&VIEW={$seriesView}&PHOFF=Y\">Phone Numbers Off</A><BR>{$CRLF}";
		}
	else
		{
		echo "*&nbsp;<A HREF=\"listSeriesRoster.php?ID={$seriesID}&VIEW={$seriesView}&PHOFF=N\">Phone Numbers On</A><BR>{$CRLF}";
		}
	// 7/30 end
	}



//----LINKS FOR NAVIGATION TO OTHER PLACES------------------------------------>
echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>GO TO:</P>{$CRLF}";
echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
echo "*&nbsp;<A HREF=\"dispMetricTable.php?ID={$seriesID}\">Metric Table for Series</A><BR>{$CRLF}";
//added 1/20/2007, updated 8/16/2008:
switch ($formatType)
	{
	case 'NORM':
		echo "*&nbsp;";
		echo "<A HREF=\"listMatch_text.php?NUM={$seriesEvtsIREmail}&SID={$seriesID}\">";
		echo "Make Confirm Email</A>{$CRLF}";
		break;
		
	case 'REC':
		echo "*&nbsp;";
		echo "<A HREF=\"listPractice_text.php?NUM={$seriesEvtsIREmail}&SID={$seriesID}\">";
		echo "Make Confirm Email</A>{$CRLF}";
		break;
		
	default:
		echo "*&nbsp;<A HREF=\"listMatch_text.php?ID={$seriesID}\">Make Confirm Email</A>";
		
	}
// 1/20/2007 end
echo "<BR>*&nbsp;<A HREF=\"listEmails.php?OBJ=SERIES&ID={$seriesID}\">Make Series Email Address List</A><BR>{$CRLF}";
echo "*&nbsp;<A HREF=\"listPerson_PhoneList.php\">Club Phone List</A><BR>{$CRLF}";
echo "*&nbsp;<A HREF=\"listSeries.php\">List of All Club Series</A><BR>{$CRLF}";



//----LINKS FOR THE ADMIN/CLUB-MANAGER/SERIES-MANAER------------------------->
if ($userPrivSeries=='MGR' or $userPrivSeries=='ADM')
	{
	echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>Administrative Functions:</P>{$CRLF}";
	echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
	echo "*&nbsp;<A HREF=\"editSeries.php?ID={$seriesID}\">Edit Series</A>{$CRLF}";
	if ($formatType=='REC')
		{
		echo "<BR>*&nbsp;<A HREF=\"editEvent_RollDates.php?SID={$seriesID}&NUM={$seriesEvtsIREmail}\">Roll 1st Event(s) to End</A>{$CRLF}";
		}
		echo "</P>{$CRLF}";
	}

echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);

?> 
