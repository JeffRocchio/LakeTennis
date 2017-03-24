<?php
/*
	This script allows the admin to reset all RSVP records
	for a given event.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();


//$DEBUG = TRUE;
$DEBUG = FALSE;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
		}
		

if ($_POST['meta_POST'] == 'TRUE')
				//   We've confirmed the reset, do it.
	{
				//   Output page header stuff.
	$tbar = "Reset RSVP Records";
	$pgL1 = "Reset RSVP Records";
	$pgL2 = "";
	$pgL3 = "Records Have Been Reset";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);

	if(!Tennis_ResetRSVPs($_POST['ID'], $_POST['ClaimCode']))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	echo "<P>Click OK to continue.</P>";
	echo "<P STYLE='font-size: large'>";
	echo "<A HREF='{$_SESSION['RtnPg']}'>OK</A></P>";
	}

else
	{
	$tblName = 'Event';
	array($row);
				
				
				//   Get the page to return to
				//after the data has posted.
	$rtnpg = $_GET['RTNPG'];
	if (strlen($rtnpg) == 0) $rtnpg = $_SESSION['RtnPg'];
	if (strlen($rtnpg) == 0) $rtnpg = "../index.php";
	if (strlen($_SESSION['RtnPg']) == 0) $_SESSION['RtnPg'] = $rtnpg;
					
				//   Get the record ID info.
	$recID = $_GET['ID'];
	if (!$recID)
		{
		echo "<P>ERROR, No Event Selected.</P>";
		include './INCL_footer.php';
		exit;
		}
	
				//   Fetch the event we're resetting.
	if(!Tennis_GetSingleRecord($row, $tblName, $recID))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}

				//   Output page header stuff.
	$tbar = "Reset RSVP Records";
	$pgL1 = "Reset RSVP Records";
	$pgL2 = "";
	$pgL3 = "For Event: {$row['ID']} - {$row['Name']}";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);

				//   Create a form so we can make a button.
	echo "<form method='post' action='editResetRSVPs.php'>";
	
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";
	
	echo "<input type=hidden name=meta_POST value=TRUE>";
	
	echo "<input type=hidden name=meta_TBL value={$tblName}>";
	
	echo "<input type=hidden name=ID value={$row['ID']}>";
	
	echo "<table border='1' CELLPADDING='3' rules='rows'>";
	
				//   Display Record ID.
	$fldLabel = "Event ID";
	$fldHelp = "Display Only.";
	$fldSpecStr = $row['ID'];
	$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
	echo $rowHTML;
	
				//   Event name.
	$fldLabel = "Event Name";
	$fldHelp = "Display Only.";
	$fldSpecStr = $row['Name'];
	$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
	echo $rowHTML;

				//   Event Start.
	$fldLabel = "Event Start";
	$fldHelp = "Display Only.";
	$fldSpecStr = $row['Start'];
	$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
	echo $rowHTML;

				//   Notes.
	$fldLabel = "Event General Notes";
	$fldHelp = "Display Only.";
	$fldSpecStr = $row['Notes'];
	$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
	echo $rowHTML;

				//   Claim Code to reset to.
	$fldLabel = "Claim Code";
	$fldHelp = "Select the appropriate claim code to reset all records to.";
	$fldSpecStr = Tennis_GenLBoxCodeSet('ClaimCode', 3, 10);
	$rowHTML = Tennis_GenDataEntryField(&$fldSpecStr, &$fldLabel, &$fldHelp);
	echo $rowHTML;


	echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='RESET rsvp records'>";
	echo "</td>{$CRLF}</tr>{$CRLF}";
	
	echo "</table>{$CRLF}";
	
	echo "</form>{$CRLF}";


	} //end if

echo  Tennis_BuildFooter('ADMIN', $rtnpg);


?> 
