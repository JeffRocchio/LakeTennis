<?php
/*
	This code represents a test case execution 'engine.'
	
	This file gets included into a test case file for a specific class def.
	
	The file for a specific class def contains:
		1. The necessary class def include statements for the test cases.
		2. Any global variables that test case file needs.
		3. A set of functions that each define one test case to run.
	
	NOTES:
		1. Each test case *must* be defined using a seperate function whose 
			name is of the form: "TstCase##", where "##' represents the
			sequence number of the test case.
================================================================================
==============================================================================*/

//----USEFUL GLOBAL VARIABLES-------------------------------------------------->
$LineFeed = "<BR>";
$OpenPara = "<P>";
$ClosePara = "</P>";
$nbSpace = NBSP;
$indent = $nbSpace . $nbSpace . $nbSpace;
$personID = $_SESSION['recID'];
				//		Used to manage which set of test cases to run (via the
				//URL query string). Default is to run ALL the test cases,
				//which is why $runAllTestCases is set to TRUE. If we are going
				//to run only a sub-set of cases, then $testCasesToRun will be
				//an array which defines the sub-set.
$testCasesToRun = NULL;
$runAllTestCases = TRUE;
				//   For error object, to be able to mark where an error was
				//declared.
$thisFunction = __FUNCTION__;
$currCodeLine = __LINE__;
				//		Strings for building messages to display.
$dispMessage = "";
$debugMessage = "";


//----Set Debug State on the Global Objects------------------------------------>
$objError->DEBUG = FALSE;
$objDebug->DEBUG = $DEBUG;



//----Set Default Edit Rights ------------------------------------------------->
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


//----DETERMINE WHICH TEST CASES TO RUN---------------------------------------->
					//		Permits us to run a sub-set of the test cases by 
					//passing in a set of comma-delimited text case numbers 
					//via the URL query string.
if (array_key_exists('TC', $_GET))
	{
	$testCases = $_GET['TC'];
	if ($testCasesToRun = explode(",", $testCases)) $runAllTestCases = FALSE;
	else
		{
		dm("*** ERROR IN QUERY STRING ***");
		exit;
		}
	}
					//		If running ALL test cases, then populate the 
					//$testCasesToRun array with all cases. Do this by inspecting
					//all user defined functions and counting the cases where the
					//function name indicates that it is a test case function. 
else
	{
	$i = 0;
	$arrDefinedFunctions = get_defined_functions();
	foreach($arrDefinedFunctions['user'] as $key => $value)
		{
		//dm("Function Name: {$value}");
		if (strtolower(substr($value,0,7))=='tstcase')
			{
			$testCasesToRun[$i] = substr($value,7,2);
			$i++;
			}
		}
	}

$numCases = count($testCasesToRun);


//----CONNECT TO MYSQL--------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----MAKE PAGE HEADER-------------------------------------------------------->
$tbar = $testSetName;
$pgL1 = "Number of Test Cases We Are Running: {$numCases}";
$pgL2 = "";
$pgL3 = "TESTING: {$testSetName}";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----LIST ALL CLASS METHODS/PARAMS-------------------------------------------->
//documentClass();


//==============================================================================
//----EXECUTE TEST CASES------------------------------------------------------->
					//**DO NOT MESS WITH THIS CODE **
					//		The test cases themselves are in seperate functions
					//which are placed at the end of the 'TEST_CLASS_xxx.php' file.
foreach($testCasesToRun as $listIndex => $tcNumber)
	{
	$tcNumAsString = sprintf('%02u', $tcNumber);
	//dm("tcNumAsString: {$tcNumAsString}");
	$tcFunctionName = "TstCase" . $tcNumAsString;
	$tcFunctionName($tcNumAsString);
	}
//----END TEST CASES AND CLOSE OUT PAGE---------------------------------------->
//==============================================================================

echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);




//====UTILITY FUNCTIONS=========================================================
//==============================================================================
function dm($displayText, $newPara=TRUE)
					//Display a message on the screen.
	{
	global $CRLF;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;
	global $indent;

	if($newPara) $textToDisplay = $OpenPara . $displayText . $ClosePara;
	else $textToDisplay = $displayText;
	echo $textToDisplay;
	return;
	}


function dmtcenex($caseNumber, $caseName, $start=TRUE)
					//Display indicators for start of a new test case and
					//end of test case.
	{
	if ($start) dm("<b>----| BEGIN TEST {$caseNumber} :</b> {$caseName}");
	else dm("----| <i>END TEST {$caseNumber}</i><BR />&nbsp;<HR>");
	return;
	}



?> 
