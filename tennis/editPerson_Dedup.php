<?php
/*
	This script allows the super-admin to de-duplicate a person record.
	That is, to replace an existing person record with a different one.
	
	The following tables have foreign keys to the person table and so need 
	to be updated as part of this process:
   		-	authority (Person)
   		-	eligible (Person)
   		-	rsvp (Person)
   		-	value (Person)

==============================================================================*/
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$rtnpg = Session_SetReturnPage();


$DEBUG = FALSE;
$DEBUG = TRUE;


//----Declare Globals-----------------------------------------------------------
global $CRLF;
global $lstErrExist;
global $lstErrMsg;
global $debugNote;


//----Declare Locals------------------------------------------------------------

				//   Table we are fetching data from.
$tblGet = "person";

				//   Tables to use and replace ID in:
$tblPut = array();
$tblPut['1']['table'] = "ClubMember";
$tblPut['1']['IDField'] = "Person";
$tblPut['2']['table'] = "eligible";
$tblPut['2']['IDField'] = "Person";
$tblPut['3']['table'] = "rsvp";
$tblPut['3']['IDField'] = "Person";
$tblPut['4']['table'] = "value";
$tblPut['4']['IDField'] = "Person";
$tblPut['5']['table'] = "authority";
$tblPut['5']['IDField'] = "Person";
				//   The two person record IDs we are operating on. These come
				//in from the form fields.
$IDKeep = "";
$IDRemove = "";

				//   Pointer to the MySql DB Object.
$link;
				//   Server string so we can make URLs that are valid
				//for whatever server (local or INET) we are running on.
$server = "http://".$_SERVER['HTTP_HOST'];
$clubhome = $server . "/ClubHome.php";

				//   Holds the database query result resource.
$qryResult = "";

				//   Used to fetch the number of records the database
				//query returned or acted upon.
$numRecords = "0";

				//   Holds the action to take on this page. Passed in via
				//the URL's query-string parameters.
$formAction = "FORM";

				//   To hold debug message header.
$debugHeader = "<p>****DEBUG MESSAGE****</P>";

				//   To hold debug output.
$debugMessage = "";

				//   To hold display output.
$out = "";

				//   To hold the current user's identifying info.
$UserID = "";
$pass = "";
$personRecID = "";

				//   To hold clubID.
$clubID = "";

$row = array();




//----GET USER EDIT RIGHTS---------------------------------------------->
$userPrivEvt='GST';
if ($_SESSION['admin']==True) { $userPrivEvt='ADM'; }

//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if($userPrivEvt<>'ADM')
	{
	echo "<P>You are Not Authorized to View This Page</P>";
	echo "<P>Your User Rights on this Page Are: {$userPrivEvt}</P>";
	include './INCL_footer.php';
	exit;
	}


//----Determine What Action To Take-------------------------------------------->
if (array_key_exists('POST', $_GET))
	{
	$formAction = $_GET['POST'];
	$IDKeep = $_GET['IDKeep'];
	$IDRemove = $_GET['IDRemove'];
	}
elseif (array_key_exists('meta_POST', $_POST))
	{
	$formAction = $_POST['meta_POST'];
	$IDKeep = $_POST['IDKeep'];
	$IDRemove = $_POST['IDRemove'];
	}
else
	{
	$formAction = "FORM";
	}



//----Connect to mysql----------------------------------------------------------
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
		


//----Take the Action-----------------------------------------------------------
switch ($formAction)
	{
				//   Post the update to replace the person record.
	case "POST":
		$tbar = "Replace Person Record";
		$pgL1 = "Edit Records";
		$pgL2 = "Repace a Person Record with Another";
		$pgL3 = "Running Merge Queries";
		echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
		$debugMessage .= "<P>In POST section of script.<BR />";
		$debugMessage .= "<P>IDKeep= {$IDKeep}<BR />";
		$debugMessage .= "<P>IDRemove= {$IDRemove}<BR />";
		$debugMessage .= "</P>";
		local_PrintDebugMsg();
		local_DoReplace($tblGet, $tblPut);
		local_PrintDebugMsg();
		local_SetRetiredInactive($tblGet);
		local_PrintDebugMsg();
		echo "<h2>Merged person records</h2>";
		echo "<P><a href='{$_POST['meta_RTNPG']}'>OK</a>&nbsp;&nbsp;</P>";
		echo "<P><a href='editPerson_Dedup.php'>Merge Another Pair</a></P>";
		break;

				//   Create Data-Entry Form.
	case "FORM":
		$tbar = "Replace Person Record";
		$pgL1 = "Edit Records";
		$pgL2 = "Repace a Person Record with Another";
		$pgL3 = "Enter Record Numbers to Merge";
		echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
		echo "<form method='post' action='editPerson_Dedup.php'>";
		echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";
		echo "<input type=hidden name=meta_POST value=POST>";
		echo "<table border='1' CELLPADDING='3' rules='rows'>";
	
					//   Get Surviving Person ID.
		$fldLabel = "Surviving Person ID";
		$fldHelp = "Enter the record number of the person record that is to survive.";
		$fldSpecStr = "<INPUT TYPE=text NAME=IDKeep ";
		$fldSpecStr .= "SIZE=4 MAXLENGTH=6 ";
		$fldSpecStr .= "VALUE=''>";
		$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
		echo $rowHTML;

					//   Get Retiring Person ID.
		$fldLabel = "Person ID to Remove";
		$fldHelp = "Enter the record number of the person record that is to be removed.";
		$fldSpecStr = "<INPUT TYPE=text NAME=IDRemove ";
		$fldSpecStr .= "SIZE=4 MAXLENGTH=6 ";
		$fldSpecStr .= "VALUE=''>";
		$rowHTML = Tennis_GenDataEntryField($fldSpecStr, $fldLabel, $fldHelp);
		echo $rowHTML;

		echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Merge Records'>";
		echo "</td>{$CRLF}</tr>{$CRLF}";
		echo "</table>{$CRLF}";
		echo "</form>{$CRLF}";


	} //end switch

echo  Tennis_BuildFooter('ADMIN', $rtnpg);



//====LOCAL FUNCTIONS===========================================================

function local_DoReplace($tblGet, &$tblPutArray)
{

	global $CRLF;
	global $DEBUG;
	global $debugMessage;
	global $IDKeep;
	global $IDRemove;
	
	$qry = "";

	$debugMessage .= "<P>In local_DoReplace()</P>";	
	foreach ($tblPutArray as $key => $value)
		{
		$debugMessage .= "<P>Key: {$key}; Value: {$value['table']}; {$value['IDField']}<br />{$CRLF}";
				//   Build replace query for table.
		$qry = "UPDATE {$value['table']} ";
		$qry .= "SET {$value['IDField']}={$IDKeep} ";
		$qry .= "WHERE ({$value['table']}.{$value['IDField']}={$IDRemove});";
		if ($DEBUG)
			{
			$debugMessage .= "<p>UPDATE QUERY:<BR />";
			$debugMessage .= "{$qry}</p>";
			}
				//   Run the query.
//		$qryResult = TRUE;
		$qryResult = mysql_query($qry);
		if (!$qryResult)
			{
			$GLOBALS['lstErrExist'] = TRUE;
			$GLOBALS['lstErrMsg'] = "ERROR";
			$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
			$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $qry;
			break;
			}
		}
	$debugMessage .= "<P>Exiting local_DoReplace()</P>";	
	return $qryResult;
	
	} // local_DoReplace()




function local_SetRetiredInactive($tblGet)
{
	global $CRLF;
	global $DEBUG;
	global $debugMessage;
	global $IDKeep;
	global $IDRemove;
	
	$qry = "";

	$debugMessage .= "<P>In local_SetRetiredInactive()</P>";	

				//   Set person record inactive on site.
	$qry = "UPDATE {$tblGet} ";
	$qry .= "SET Currency=40 ";
	$qry .= "WHERE ({$tblGet}.ID={$IDRemove});";
	if ($DEBUG)
		{
		$debugMessage .= "<p>UPDATE QUERY:<BR />";
		$debugMessage .= "{$qry}</p>";
		}
				//   Run the query.
//	$qryResult = TRUE;
	$qryResult = mysql_query($qry);
	if (!$qryResult)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "ERROR";
		$GLOBALS['lstErrMsg'] .= '<BR>Invalid query: ' . mysql_error();
		$GLOBALS['lstErrMsg'] .= '<BR>Query Sent: ' . $qry;
		}

	$debugMessage .= "<P>Exiting local_DoReplace()</P>";	
	return $qryResult;
	} // local_SetRetiredInactive()


function local_PrintDebugMsg()
{

	global $debugHeader;
	global $debugMessage;
	global $DEBUG;

	if ($DEBUG)
		{
		echo $debugHeader;
		echo $debugMessage;
		echo "<HR>";
		$debugMessage = "";
		}
	
	return;

	} // local_SetRetiredInactive()






?> 
