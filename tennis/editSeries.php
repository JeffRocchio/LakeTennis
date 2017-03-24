<?php
/*
	This script allows the admin to edit an existing series record.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();
$_SESSION['RtnPg'] = $rtnpg;


//$DEBUG = TRUE;
$DEBUG = FALSE;

global $CRLF;


//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";



//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID=$_SESSION['clubID'];
$tblName = 'series';
$row = '';



//----GET URL QUERY-STRING DATA----------------------------------------->
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No Series Selected.</P>";
	include './INCL_footer.php';
	exit;
	}

//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	
//----FETCH THE RECORD TO EDIT------------------------------------------>
if(!Tennis_GetSingleRecord($row, $tblName, $recID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
	

//----GET USER EDIT RIGHTS---------------------------------------------->
				//   Levels of rights on this page:
				//     1) SADMIN. System admin. Can do anything.
				//     2) OADMIN. Object admin. A person with ADM rights to
				//        the series.
				//     3) MANAGER. A person with MGR rights to the series.
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='SADM'; }
else
	{
	$tmp=Session_GetAuthority(55, $clubID);
	if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='ADM'; }
	else { $userPriv=Session_GetAuthority(42, $recID); }
	}




//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "Edit Series {$row['LongName']}";
$pgL1 = "Edit Series";
$pgL2 = "";
$pgL3 = $row['LongName'];
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);




//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPriv<>'SADM' and $userPriv<>'ADM' and $userPriv<>'MGR')
	{
	echo "<P>You are Not Authorized to View This Page</P>";
	if ($DEBUG) echo "<P>Your User Rights are: {$userPriv}</P>";
	include './INCL_footer.php';
	exit;
	}



//----SERIES-TABLE EDIT SECTION OF THE PAGE------------------------------>
				//   Create a form to enter the data into.
				//Also need to create two hidden fields to hold
				//the database and table name to pass to the
				//page we're going to post the data to.
echo "<form method='post' action='editGeneric_post.php'>";

echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";

echo "<input type=hidden name=meta_TBL value={$tblName}>";

echo "<input type=hidden name=ID value={$row['ID']}>";
echo "<input type=hidden name=meta_UserRecID value={$_SESSION['recID']}>";
echo "<input type=hidden name=meta_UserID value={$_SESSION['UserID']}>";

echo "<table border='1' CELLPADDING='3' rules='rows'>";

				//   Record ID.
$rowHTML = "<TR class=deTblRow>{$CRLF}";
$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName>ID</P>";
$rowHTML .= "</TD>{$CRLF}";
$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
$rowHTML .= $row['ID'];
$rowHTML .= "</P></TD></TR>";
echo $rowHTML;

				//   Club.
$fldLabel = "Club";
$fldHelp = "Club this series is for. ";
$fldHelp .= "(NOTE: Requires system administrator rights to change.)";
$rowHTML = ADMIN_GenFieldDropTbl($fldLabel, $fldHelp, 'ClubID', 'club', 0, $clubID, FALSE, 'SADM', $userPriv);
echo $rowHTML;

				//   Series Type drop-down.
$fldLabel = "Series Type";
$fldHelp = "Select a type for this series - how it will be used. E.g., ";
$fldHelp .= "Recreational Play or League Play.";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'Type', 12, $row['Type'], FALSE, 'ADM', $userPriv);
echo $rowHTML;

				//   Series View Level Permission drop-down (code-set 13).
$fldLabel = "Series View Level";
$fldHelp = "REQUIRED. Select which level of user you must be in order";
$fldHelp .= " to view the series details";
$fldHelp .= ", including the roster-grid.";
$rowHTML = ADMIN_GenFieldDropCode($fldLabel, $fldHelp, 'ViewLevel', 13, $row['ViewLevel'], FALSE, 'ADM', $userPriv);
echo $rowHTML;

				//   Series Sort.
$fldLabel = "Sort";
$fldHelp = "Defines an alternative sort order for listing series (5 characters).";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'Sort', 5, 5, $row['Sort'], 'ADM', $userPriv);
echo $rowHTML;

				//   Series Short Name.
$fldLabel = "Series Short Name";
$fldHelp = "REQUIRED. Short reference name for the series. (30 characters).";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'ShtName', 30, 30, $row['ShtName'], 'ADM', $userPriv);
echo $rowHTML;

				//   Series Long Name.
$fldLabel = "Series Long Name";
$fldHelp = "REQUIRED. Descriptive name for the series (150 characters).";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'LongName', 150, 65, $row['LongName'], 'ADM', $userPriv);
echo $rowHTML;

				//   Description.
$fldLabel = "Description";
$fldHelp = "Detailed description of the series.";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'Description', 5, 65, $row['Description'], 'MGR', $userPriv);
echo $rowHTML;

				//   URL.
$fldLabel = "Related URL";
$fldHelp = "Reference URL to a web-site associated with the series.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'URL', 255, 65, $row['URL'], 'MGR', $userPriv);
echo $rowHTML;

				//   Notes.
$fldLabel = "Notes";
$fldHelp = "If you wish you can record any general notes concerning ";
$fldHelp .= "this series in this field.";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'Notes', 5, 65, $row['Notes'], 'MGR', $userPriv);
echo $rowHTML;

				//   Events to Include In Reminder Email. This field contains the
				//number of events to include when generating reminder emails for
				//upcoming events in the series. Default is 1. A recreational
				//series where the group plays twice/week may wish to set this to 2.
$fldLabel = "Events in Reminder Emails";
$fldHelp = "The number of Events to include when user selects the";
$fldHelp .= " Make Confirm Email link at the bottom of the roster grid page";
$fldHelp .= " (1 digit number).";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'EvtsIREmail', 1, 1, $row['EvtsIREmail'], 'ADM', $userPriv);
echo $rowHTML;



echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Save record'>";
echo "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='{$rtnpg}'>RETURN</A>";
echo "</P>";
echo "</td>{$CRLF}</tr>{$CRLF}";
echo "</form>{$CRLF}";
echo "</table>{$CRLF}";


//----ELIGIBILITY SECTION OF THE PAGE------------------------------------>
				//   List of currently eligible and currently non-eligible
				//people, along with a link to change each person's 
				//eligibility status.
echo "<P>&nbsp;</P>{$CRLF}";
echo "<table border='1' CELLPADDING='3' rules='cols'>";

$fldLabel = "Current Eligibility For This Series";

$fldHelp = "Click appropriate links to REMOVE/ADD a person as eligible for this series.";
$fldHelp .= "<BR><b>NOTE:</b> Doing so will erase any changes you entered in the form above.";
$fldHelp .= " If appropriate, save that form prior to editing eligibility.";

$rowHTML = "<TR CLASS='deTblRow'>{$CRLF}";
$rowHTML .= "<TD CLASS='deTblCellSectiontitle' COLSPAN='2'>";
$rowHTML .= "<P CLASS='deSectionTitle'>{$fldLabel}</P>{$CRLF}";
$rowHTML .= "</TD></TR>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TR class=deTblRow>{$CRLF}";
$rowHTML .= "<TD class=deTblCellInput COLSPAN='2'>";
$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>";
$rowHTML .= "</TD></TR>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TR class=deTblRow>{$CRLF}";
echo $rowHTML;

$fldLabel = "Currently Eligibile";
$rowHTML = "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName align='left'>{$fldLabel}</P>";
$rowHTML .= "</TD>{$CRLF}";
echo $rowHTML;

$fldLabel = "NOT Currently Eligibile";
$rowHTML = "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName align='left'>{$fldLabel}</P>";
$rowHTML .= "</TD>{$CRLF}";
echo $rowHTML;

$rowHTML = "</TR>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TR class=deTblRow>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TD class=deTblCellInput>";
$rowHTML .= "<P class=deFieldInput>";
echo $rowHTML;
				//   Make list inside of the table cell.
local_listEligible($recID, $userPriv);
$rowHTML = "</TD>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TD class=deTblCellInput>";
$rowHTML .= "<P class=deFieldInput>";
echo $rowHTML;
				//   Make list inside of the table cell.
local_listNotEligible ($recID, $clubID, $userPriv);
$rowHTML = "</TD>{$CRLF}";
echo $rowHTML;

$rowHTML = "</TR>{$CRLF}";
echo $rowHTML;


echo "</table>{$CRLF}{$CRLF}{$CRLF}";


//----METRICS SECTION OF THE PAGE---------------------------------------->
				//   List of metrics defined for this series.
echo "<P>&nbsp;</P>{$CRLF}{$CRLF}";
echo "<table border='1' CELLPADDING='3' rules='cols'>{$CRLF}";

$fldLabel = "Current Metrics Defined For This Series";

$fldHelp = "Click appropriate links to HIDE/UNHIDE a metric for this series.";
$fldHelp .= "<BR><b>NOTE:</b> Doing so will erase any changes you entered in the form above.";
$fldHelp .= " If appropriate, save that form prior to editing eligibility.";

$rowHTML = "<TR CLASS='deTblRow'>{$CRLF}";
$rowHTML .= "<TD CLASS='deTblCellSectiontitle' COLSPAN='2'>";
$rowHTML .= "<P CLASS='deSectionTitle'>{$fldLabel}</P>{$CRLF}";
$rowHTML .= "</TD></TR>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TR class=deTblRow>{$CRLF}";
$rowHTML .= "<TD class=deTblCellInput COLSPAN='2'>";
$rowHTML .= "<P class=deFieldDscrpt>{$fldHelp}</P>";
$rowHTML .= "</TD></TR>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TR class=deTblRow>{$CRLF}";
echo $rowHTML;

$fldLabel = "Current Metrics Defined and Visible";
$rowHTML = "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName align='left'>{$fldLabel}</P>";
$rowHTML .= "</TD>{$CRLF}";
echo $rowHTML;

$fldLabel = "Metrics Made Invisible";
$rowHTML = "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName align='left'>{$fldLabel}</P>";
$rowHTML .= "</TD>{$CRLF}";
echo $rowHTML;

$rowHTML = "</TR>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TR class=deTblRow>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TD class=deTblCellInput>";
$rowHTML .= "<P class=deFieldInput>";
echo $rowHTML;
local_listMetrics($recID, "V", $userPriv);
$rowHTML = "</TD>{$CRLF}";
echo $rowHTML;

$rowHTML = "<TD class=deTblCellInput>";
$rowHTML .= "<P class=deFieldInput>";
echo $rowHTML;
local_listMetrics ($recID, "H", $userPriv);
$rowHTML = "</TD>{$CRLF}";
echo $rowHTML;

$rowHTML = "</TR>{$CRLF}";
echo $rowHTML;


echo "</table>{$CRLF}";

//<----END METRICS SECTION



				//   Build a link to return from whence we came.
$rowHTML = "<P><A HREF='{$rtnpg}'>RETURN</A>";
				


//----ADMINISTRATIVE OPTIONS--------------------------------------------->
				//   If current user has rights to manage this series, give them some
				//additional options.
if ($userPriv=='MGR' or $userPriv=='ADM' or $userPriv=='SADM')
	{
	$rowHTML .= "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='addEvent.php?RTNPG=editSeries.php&RTNID={$recID}&ID={$recID}'>";
	$rowHTML .= "ADD Event</A>";
	$rowHTML .= "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF='addMetric.php?RTNPG=editSeries.php&RTNID={$recID}&ID={$recID}'>";
	$rowHTML .= "ADD Metric</A>";
	}
if ($userPriv=='ADM' or $userPriv=='SADM')
	{
	$rowHTML .= "&nbsp;&nbsp;&nbsp;&nbsp;<A HREF=\"addClubMember.php\">";
	$rowHTML .= "ADD Person</A>";
	}
	$rowHTML .= "</P>{$CRLF}";
echo $rowHTML;

//<----END ADMIN OPTIONS SECTION


echo  Tennis_BuildFooter('ADMIN', "editSeries.php?ID={$recID}");




//=============================================================================

function local_listEligible ($recID, $userPriv)
	{
	
	global $CRLF;
	$rowHTML = "";

	if (!$qryResult = Tennis_EligibleForSeriesOpen($recID))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	while ($row = mysql_fetch_array($qryResult))
		{
	if ($userPriv=='MGR' or $userPriv=='ADM' or $userPriv=='SADM')
			{
			$rowHTML .= "<A HREF='editSeries_eligible_post.php?ID={$row['prsnID']}&SID={$recID}&ACT=R'>";
			$rowHTML .= "REMOVE</A>&nbsp;&nbsp;";
			}
		if ($_SESSION['member'] == TRUE)
			{
			$rowHTML .= "{$row['prsnFullName']}<BR>{$CRLF}";
			}
		else
			{
			$rowHTML .= "{$row['prsnPName']}<BR>{$CRLF}";
			}
		}
	echo $rowHTML;
	
}


function local_listNotEligible ($recID, $clubID, $userPriv)
	{
	
	global $CRLF;
	global $lstErrMsg;

	$rowHTML = "";

	if (!$qryResult = Tennis_NotEligibleForSeriesOpen($recID, $clubID))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	while ($row = mysql_fetch_array($qryResult))
		{
	if ($userPriv=='MGR' or $userPriv=='ADM' or $userPriv=='SADM')
			{
			$rowHTML .= "<A HREF='editSeries_eligible_post.php?ID={$row['prsnID']}&SID={$recID}&ACT=A'>";
			$rowHTML .= "ADD</A>&nbsp;&nbsp;";
			}
		if ($_SESSION['member'] == TRUE)
			{
			$rowHTML .= "{$row['prsnFullName']}<BR>{$CRLF}";
			}
		else
			{
			$rowHTML .= "{$row['prsnPName']}<BR>{$CRLF}";
			}
		}
	echo $rowHTML;

	
}

function local_listMetrics ($recID, $visibility, $userPriv)
	{
	
	global $CRLF;
	$rowHTML = "";

	if ($qryResult = Tennis_MetricsForSeriesOpen($recID, $visibility))
		{
		while ($row = mysql_fetch_array($qryResult))
			{
			if ($userPriv=='MGR' or $userPriv=='ADM' or $userPriv=='SADM')
				{
				if ($visibility == 'V')
					{
					$rowHTML .= "<A HREF='editSeries_metric_post.php?ID={$row['metricID']}&SID={$recID}&ACT=H'>";
					$rowHTML .= "HIDE</A>&nbsp;&nbsp;{$CRLF}";
					}
				else
					{
					$rowHTML .= "<A HREF='editSeries_metric_post.php?ID={$row['metricID']}&SID={$recID}&ACT=V'>";
					$rowHTML .= "UNHIDE</A>&nbsp;&nbsp;{$CRLF}";
					}
				}
			$rowHTML .= "<A HREF='dispMetric.php?ID={$row['metricID']}'>{$row['metricName']}</A><BR>{$CRLF}";
			}
		}
	else
		{
		$rowHTML = "No Metrics Defined in this Category.";
		}
	echo $rowHTML;
	
}



?> 
