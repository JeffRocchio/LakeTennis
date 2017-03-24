<?php
/*
	   This script is used to play with and test the database and
	   recordset object classes; including inheritance.
	   
------------------------------------------------------------------ */
session_start();
include_once('../INCL_Tennis_CONSTANTS.php');
include_once('../INCL_Tennis_Functions_Session.php');
include_once('../INCL_Tennis_DBconnect.php');
include_once('../INCL_Tennis_Functions.php');
include_once('../classdefs/error.class.php');
include_once('../classdefs/debug.class.php');
include_once('../classdefs/event.class.php');
include_once('../classdefs/rsvp.class.php');
include_once('../clsdef_mdl/database.class.php');
include_once('../clsdef_mdl/recordset.class.php');
include_once('../clsdef_mdl/event.class.php');
include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;


//----GLOBAL VARIABLES--------------------------------------------------->
$LineFeed = "<BR>";
$OpenPara = "<P>";
$ClosePara = "</P>";
$nbSpace = NBSP;
$indent = $nbSpace . $nbSpace . $nbSpace;
$objError->DEBUG = $DEBUG;
$objDebug->DEBUG = $DEBUG;
$errLastErrorKey = 0;
$errCount = 0;


//----DECLARE LOCAL VARIABLES-------------------------------------------->
$personID = $_SESSION['recID'];

					//   The current user's edit rights on the current series.
					//Initial default value is "Guest."
					//Note that declare of the rights[] array is for compatability
					//with the Roster_GetUserRights() function and is not
					//currently used in this script; this array approach is for
					//possible future flexibility.
$userPrivSeries = 'GST';
$rights = array('view'=>'GST','edit'=>'GST');



//=== BEGIN CODE ==============================================================>
//=============================================================================>


//----CONNECT TO MYSQL--------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----MAKE PAGE HEADER-------------------------------------------------------->
$tbar = "Testing database and recordset Class";
$pgL1 = "Testing database and recordset Class";
$pgL2 = "";
$pgL3 = "";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----LIST ALL CLASS METHODS/PARAMS-------------------------------------------->
//documentClass();


//==============================================================================
//----BEGIN PLAY--------------------------------------------------------------->
$result = false;


					//   TEST CASE #1: ----------------------------------------------
/*
echo "{$OpenPara}<b>----| BEGIN TEST 1 : Just create a recordset object.</b>";

$objRst = new recordset();
$recArray = array();

$result = $objRst->openQuery("qrySeriesEvts", "", "");
echo "{$OpenPara}{$result}{$ClosePara}";
$result = $objRst->getNextRecord($recArray);
$objDebug->displayDBRecord($recArray);
$result = $objRst->closeQuery();

echo "{$OpenPara}END TEST 1 |----{$ClosePara}";
*/

					//   TEST CASE #2: ----------------------------------------------

echo "{$OpenPara}<b>----| BEGIN TEST 2 : Open an event recordset using the eventInfo class.</b>:{$ClosePara}";

$seriesID = 5;
$eventID = 26;
$objEvtInfo = new event();
$objRst = new recordset();
$recArray = array();
$objEvtInfo->setParam('$infoSet', '4Series');
$objEvtInfo->setParam('$ID', 5);
$objEvtInfo->setParam('$subset', 'UPCOMING');

$objRst = $objEvtInfo->openRecordset();

//echo "{$OpenPara}objRst ID: {$objRst}{$ClosePara}";

$result = $objRst->getNextRecord($recArray);
$objDebug->displayDBRecord($recArray);
$result = $objRst->closeQuery();

echo "{$OpenPara}END TEST 2 |----{$ClosePara}";



					//   TEST CASE #3: ----------------------------------------------
/*
echo "{$OpenPara}<b>----| BEGIN TEST 3 : event class function openView(seriesID, subset='UPCOMING').</b>:{$ClosePara}";

//print_r(get_extension_funcs("mysql"));


$seriesID = 5;
$rsvpString = "";
$errCount = 0;

$result = $objEvent->openView($seriesID, 'UPCOMING');

echo "{$OpenPara}{$result}{$ClosePara}";

if($objError->getErrCount($reportedClass="UNREPORTED")>0)
	{
	echo "{$OpenPara}Listing Out All Registered Errors:{$ClosePara}";
	$errCount = $objError->ReportAllErrs(0, FALSE);
	}

echo "{$OpenPara}END TEST 2 |----{$ClosePara}";
*/


//----END PLAY----------------------------------------------------------------->
//==============================================================================

echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);


?> 

