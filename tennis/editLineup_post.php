<?php
/*
	
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();


//$DEBUG = FALSE;
$DEBUG = TRUE;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

$tblName = "RSVP";

				//   Connect to mysql
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
		}
		

if ($_POST['meta_POST'] == 'TRUE')
				//   We're saving the records, not displaying the form.
	{
				//   Output page header stuff.
				//Feb-10-2008: Obsolete, using the ADMIN_Post_HeaderOK function.
/*
	$tbar = "Post Lineup";
	$pgL1 = "Edit Records";
	$pgL2 = "Lineup";
	$pgL3 = "Save Lineup Changes";
	echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);
*/	
				//   Save the updates to the DB.
	$recordsUpdated = Tennis_dbLineupUpdate();
	if ($recordsUpdated==0)
		{
		$message = "{$GLOBALS['lstErrMsg']}";
		}

	$message = "{$recordsUpdated} Records Updated.";
	$rtnpg = "editLineup.php?ID={$_POST['ID']}";
//	$rtnpg = $_SESSION['RtnPg'];
	}

else
	{
	$message = "Post not set = TRUE.";
	} //end if

				//   Output a page header, status
				//message and link to get back.
echo ADMIN_Post_HeaderOK($tblName, $rtnpg, $message);

echo  Tennis_BuildFooter('ADMIN', $rtnpg);

?> 
