<?php
/*
	   This script is used to test/debug the ERROR object.
	   
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_CONSTANTS.php');
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./classdefs/error.class.php');
Session_Initalize();


$DEBUG = FALSE;
//$DEBUG = TRUE;


//----GLOBAL VARIABLES--------------------------------------------------->
$LineFeed = "<BR>";
$OpenPara = "<P>";
$ClosePara = "</P>";
$nbSpace = NBSP;

$objError = new ErrorList();
$objError->DEBUG = $DEBUG;
$errLastErrorKey = 0;
$errCount = 0;


//----DECLARE LOCAL VARIABLES-------------------------------------------->
				//   Holds the current function name and program line#.
				//For use in error reporting.
$thisFunction = __FUNCTION__;
$currCodeLine = __LINE__;


				//   Holds counter and index values for what errors to simulate.
				//Default is 1 error, error ID #1.
				//These values can also be passed in via query string.
$errorCount = 1;
$errorNum = array($errorCount=>1);


$clubID = 1;
$seriesID = 1;
$seriesLongName = "";
$seriesShtName = "";
$personID = $_SESSION['recID'];

					//   The current user's edit rights on the current series.
					//Initial default value is "Guest."
					//Note that declare of the rights[] array is for compatability
					//with the Roster_GetUserRights() function and is not
					//currently used in this script; this array approach is for
					//possible future flexibility.
$userPrivSeries = 'GST';
$rights = array('view'=>'GST','edit'=>'GST');

$dispMessage = "";
$debugMessage = "";


//=== BEGIN CODE ==============================================================>
//=============================================================================>


//----GET URL-QUERY-STRING-DATA------------------------------------------------>
if (array_key_exists('ERRS', $_GET))
	{
	$errorCount = 1;
	$arrkey = "NUM" . $errorCount;
	while (array_key_exists($arrkey, $_GET));
		{
		$errorNum[$errorCount] = $_GET[$arrkey];
		$errorCount++;
		$arrkey = "NUM" . $errorCount;
		}
	if ($errorNum[1] == 0) $errorNum[1]=1;
	}


//----CONNECT TO MYSQL--------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}


//----MAKE PAGE HEADER-------------------------------------------------------->
$tbar = "Testing Error Object";
$pgL1 = "Testing Error Object";
$pgL2 = "";
$pgL3 = "";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----LIST ALL CLASS METHODS/PARAMS-------------------------------------------->
documentClass();


//----SIMULATE ERRORS---------------------------------------------------------->

foreach ($errorNum as $ErrToSim)
	{
	switch ($ErrToSim)
		{
		case 2:   //Get a series and create a fake error.
			if(!Tennis_GetSingleRecord($row, "series", $seriesID))
				{
				echo "<P>{$lstErrMsg}</P>";
				include './INCL_footer.php';
				exit;
				}
			$clubID = $row['ClubID'];
			$seriesLongName = $row['LongName'];
			$seriesShtName = $row['ShtName'];
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
			break;

		case 3:   //Fake some error with USER RIGHTS.
			$userPrivSeries = Roster_GetUserRights($seriesID, $ViewLevel, "listSeriesRoster", $rights);
			break;
		
		default :   //The default error, (Error ID #1)
			echo "{$OpenPara}<b>In Test Case</b>: default.{$ClosePara}";
			$errLastErrorKey = $objError->RegisterErr(
				ERRSEV_NOTICE, 
				ERRCLASS_OTHER, 
				__FUNCTION__, 
				__LINE__, 
				"Testing Error Register", 
				False);
			echo "{$OpenPara}<b>Registered Error #</b>: $errLastErrorKey [Should be #1 and reported]{$ClosePara}";
			$objError->ReportByKey($errLastErrorKey);
			$errLastErrorKey = $objError->RegisterErr(
				ERRSEV_WARNING, 
				ERRCLASS_DBOPEN, 
				__FUNCTION__, 
				__LINE__, 
				"2ND Test Error.", 
				False);
			echo "{$OpenPara}<b>Registered Error #</b>: {$errLastErrorKey} [Should be #2 and not reported]{$ClosePara}";
			echo "{$OpenPara}<b>Calling:</b> objError->ReportAllErrs(0, FALSE) [Should list 1 error, error #1]{$ClosePara}";
			$errCount = $objError->ReportAllErrs(0, FALSE);
			echo "{$OpenPara}<b>Errors Reported:</b> {$errCount}{$ClosePara}";
			echo "{$OpenPara}<b>Calling:</b> objError->ReportAllErrs(0, TRUE) [Should list 2 errors, #1 and #2]{$ClosePara}";
			$errCount = $objError->ReportAllErrs(0, TRUE);
			echo "{$OpenPara}<b>Errors Reported:</b> {$errCount}{$ClosePara}";

		} // End Switch.
		
	} // End For-Each.




echo  Tennis_BuildFooter("NORM", $_SESSION['RtnPg']);




function documentClass()
{
	/*	PURPOSE: Document the class's Methods.
	
		RETURNS: A list outputted to the screen.
	*/
	
	global $objError;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;

	$clsMethodsList = array();
	$clsMethodDoc = "";
	$dispText = "";
	
	
	$clsMethodsList = get_class_methods('ErrorList');
	$clsReflector = new ReflectionClass('ErrorList');

	echo "{$OpenPara}<b>List of All Class Methods ---:</b>: " . $LineFeed;
	foreach ($clsMethodsList as $methodName)
		{
		$method = $clsReflector->getMethod($methodName);
		$parameters = $method->getParameters();
		$dispText = $methodName . "(";
		$dispText .= formatParameters($parameters);
		$dispText .= ")";
		echo $dispText . $LineFeed;
		}
	echo $LineFeed . $LineFeed;

}

function formatParameters($params) 
	{
	global $objError;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;


	$args = array();
	$arg = "";

    foreach($params as $param)
    	{
		$arg = '';
		if($param->isPassedByReference())
			{
			$arg .= '&';
			}
		if($param->isOptional())
			{
			$arg .= '[' . $param->getname();
			if($param->isDefaultValueAvailable())
				{
				$arg .= ' = ';
				$default = $param->getDefaultValue();
				if (is_bool($default))
					{
					if ((boolean)$default) { $arg.='TRUE'; } else { $arg.='FALSE'; }
					}
				elseif(empty($default)) $arg .= '""';
				else $arg .= $default;
				}
			$arg .= ']';
			}
			else
			{
			$arg .= $param->getName();
			}
		$args[] = $arg;
		}
	return implode(', ', $args);
	}


?> 
