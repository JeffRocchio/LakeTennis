<?php
/*
	This script handles requests to edit an existing RSVP record.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


//$DEBUG = TRUE;
$DEBUG = FALSE;

//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$tblName = 'rsvp';
$row = '';


//----GET URL QUERY-STRING DATA----------------------------------------->
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No Event Selected.</P>";
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


//----BUILD PAGE POST--------------------------------------------------->
				//   Are we collecting the data (via a form)
				//or posting data already collected?

if (array_key_exists('meta_POST', $_POST))
	{
	if ($_POST['meta_POST'] == 'TRUE')
					//   Posting data.
		{
		$evtprp = $_POST['meta_EVTPURP'];
		$rsvpClaim = $_POST['ClaimCode'];
					//   If other than a real match or formal practice, then we
					//need to make adjustments to the Position based on how
					//availability has been set.
		if ($evtprp <> 17 && $evtprp <> 5)
			{
			if ($rsvpClaim == 10 || $rsvpClaim == 11) $_POST['Position']=30; //No Response or Not Available.
			elseif ($rsvpClaim == 13) $_POST['Position']=28; //Late.
			elseif ($rsvpClaim == 14) $_POST['Position']=28; //Tentative.
			elseif ($rsvpClaim == 15) $_POST['Position']=29; //Available.
			elseif ($rsvpClaim == 16) $_POST['Position']=29; //Confirmed.
			}
		$message = Tennis_dbRecordUpdate($_POST, $tblName);

					//   Output a page header, status
					//message and link to get back.
		echo ADMIN_Post_HeaderOK($tblName, $rtnpg, $message);
		include './INCL_footer.php';
		}
	}

//----BUILD PAGE DATA-ENTRY--------------------------------------------->
else
	{
				//   Fetch the record to edit. NOTE: I
				//am getting a big joined record from
				//the qryRsvp query so that I
				//have the record IDs for the Event, the
				//series and such. So be careful about the
				//field names used for the form entry field
				//names (which have to match the 'raw' rsvp
				//table fields names when handed off to the
				//post script).
	if(!Tennis_GetSingleRecord($row, "qryRsvp", $recID))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	
//----GET USER EDIT RIGHTS---------------------------------------------->
	$userPriv='GST';
	if (($_SESSION['evtmgr']==True) OR ($_SESSION['clbmgr']==True)) { $userPriv='ADM'; }
	else
		{
		$tmp=Session_GetAuthority(42, $row['seriesID']);
		if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='ADM'; }
		else
			{
			$tmp=Session_GetAuthority(43, $row['evtID']);
			if ($tmp=='MGR' or $tmp=='ADM') { $userPriv='ADM'; }
			}
		}
		$slfStr = "&self={$row['prsnID']}";

				//   Make pretty date.
	$date = Tennis_DisplayDate($row['evtStart']);
	$time = Tennis_DisplayTime($row['evtStart'], TRUE);

				//   Output page header stuff.
	$tbar = "Edit RSVP";
	$pgL1 = "Edit RSVP";
	$pgL2 = "Person: {$row['prsnPName']} PRIV: {$userPriv}";
	$pgL3 = "Event: {$row['evtName']} on {$date} at {$time}";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);


				//   Create a form to enter the data into.
				//Also need to create two hidden fields to hold
				//the database and table name to pass to the
				//page we're going to post the data to.
	echo "<form method='post' action='editRSVP.php?ID={$recID}&POST=T'>";
	
	echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";
	
	echo "<input type=hidden name=meta_ADDPG value=''>";
	
	echo "<input type=hidden name=meta_POST value=TRUE>";
	
	echo "<input type=hidden name=meta_TBL value={$tblName}>";
	
	echo "<input type=hidden name=meta_EVTPURP value={$row['evtPurposeCd']}>";
	
	echo "<input type=hidden name=ID value={$row['ID']}>";
	
	echo "<table border='1' CELLPADDING='3' rules='rows'>";

				//   Display Record ID.
	$fldLabel = "RSVP ID";
	$fldHelp = "Display Only.";
	$fldSpecStr = $row['ID'];
	$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
	echo $rowHTML;

				//   Event drop-down.
	$fldLabel = "For Event";
	$fldHelp = "Select which event this RSVP is for.";
	$rowHTML = ADMIN_GenFieldDropTbl($fldLabel,$fldHelp,'Event','Event',$row['seriesID'],$row['evtID'],False,'XXX',$userPriv);
	echo $rowHTML;

				//   Person drop-down.
	$fldLabel = "For Person";
	$fldHelp = "Select which person this RSVP is for.";
	$rowHTML = ADMIN_GenFieldDropTbl($fldLabel,$fldHelp,'Person','eligible',$row['seriesID'],$row['prsnID'],False,'XXX',$userPriv);
	echo $rowHTML;

				//   Claim code drop-down.
	$fldLabel = "Availability";
	$fldHelp = "Person's availability for this event.";
	$rowHTML = ADMIN_GenFieldDropCode($fldLabel,$fldHelp,'ClaimCode',3,$row['rsvpClaimCode'],False,"MGR{$slfStr}",$userPriv);
	echo $rowHTML;

				//   Position drop-down.
	$fldLabel = "Assigned Position";
	$fldHelp = "What position is this person assigned to for this event?";
	$rowHTML = ADMIN_GenFieldDropCode($fldLabel,$fldHelp,'Position',5,$row['rsvpPositionCode'],False,'MGR',$userPriv);
	echo $rowHTML;

				//   Role drop-down.
	$fldLabel = "Assigned Role";
	$fldHelp = "What role is this person assigned to for this event?";
	$rowHTML = ADMIN_GenFieldDropCode($fldLabel,$fldHelp,'Role',4,$row['rsvpRoleCode'],False,'MGR',$userPriv);
	echo $rowHTML;

				//   Notes.
	$fldLabel = "General Notes";
	$fldHelp = "If you wish you can record any general notes concerning ";
	$fldHelp .= "this RSVP item in this field.";
	$rowHTML = ADMIN_GenFieldNote($fldLabel,$fldHelp,'Note',5,65,$row['rsvpNote'],"MGR{$slfStr}",$userPriv);
	echo $rowHTML;
	
	
	echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Save record'>";
	echo "</td>{$CRLF}</tr>{$CRLF}";
	
	echo "</table>{$CRLF}";
	
	echo "</form>{$CRLF}";
	
//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "editRSVP.php?ID={$recID}");

	}

?> 
