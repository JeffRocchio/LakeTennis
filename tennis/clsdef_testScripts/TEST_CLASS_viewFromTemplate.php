<?php
/*
	Test cases for associated class def code.
	
	NOTES:
		1. Each test case *must* be defined using a seperate function whose 
			name is of the form: "TstCase##", where "##' represents the
			sequence number of the test case.
================================================================================
==============================================================================*/
session_start();
include_once('../INCL_Tennis_CONSTANTS.php');
include_once('../INCL_Tennis_Functions_Session.php');
include_once('../INCL_Tennis_DBconnect.php');
include_once('../INCL_Tennis_Functions.php');
include_once('../INCL_Tennis_Functions_ADMIN_v2.php');
include_once('../classdefs/error.class.php');
include_once('../classdefs/debug.class.php');
include_once('../clsdef_mdl/database.class.php');
include_once('../clsdef_mdl/recordset.class.php');
include_once('../clsdef_mdl/series.class.php');
include_once('../clsdef_mdl/event.class.php');
include_once('../clsdef_mdl/rsvp.class.php');
include_once('../clsdef_mdl/link.class.php');
include_once('../clsdef_mdl/txtBlock.class.php');
include_once('../clsdef_view/eventViewChunks.class.php');
include_once('../clsdef_view/linkViews.class.php');
include_once('../clsdef_ctrl/eventViewRequests.class.php');
include_once('../clsdef_ctrl/txtBlockViewRequests.class.php');
include_once('../clsdef_ctrl/viewFromTemplate.class.php');

include_once('../INCL_Tennis_GLOBALS.php');
Session_Initalize();


$DEBUG = FALSE;
$DEBUG = TRUE;

$testSetName = "viewFromTemplate Class";


					//Data we may use across all the test cases.
$clubID = 2;
$seriesID = 5;


//==============================================================================
//   TEST CASE EXECUTION ENGINE
include_once('../clsdef_testScripts/TEST_CLASS_INCLUDE_BaseexeEngine.php');
//==============================================================================


//====TEST CASE FUNCTIONS=======================================================
//==============================================================================
function TstCase01($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Gen the RSVP List";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	$emailBodyView = "";

	$emailBodyTemplate = "";
	$emailBodyTemplate .= "RSVPs for Recreational Play this Week:<BR />";
	$emailBodyTemplate .= "<BR />|%DCbegin rsvpstat 5 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />|%DCbegin links S 2 DCend%|<BR />";
	$emailBodyTemplate .= "<BR />|%DCbegin sigseriesadmin 2 DCend%|";

	dm("Template Before Replacements:<BR />----------<BR />{$emailBodyTemplate}<BR />----------");

	$emailBody = new viewFromTemplate();

	$emailBody->set_template($emailBodyTemplate);
	$emailBody->makeViewFromTemplate();
	$emailBodyView = $emailBody->get_viewCreated();

	dm("Email After Replacements:<BR />----------<BR />{$emailBodyView}<BR />----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}


function TstCase02($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Generate Links From Template - In Plain Text Form";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	$emailBodyView = "";

	$emailBodyTemplate = "";
	$emailBodyTemplate .= "Should See Links for Club, Series, Event and none:";
	$emailBodyTemplate .= "<BR /><BR />Links for Club ---";
	$emailBodyTemplate .= "<BR />|%DCbegin links C 2 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />Links for Series ---";
	$emailBodyTemplate .= "<BR />|%DCbegin links S 5 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />Links for Event ---";
	$emailBodyTemplate .= "<BR />|%DCbegin links E 27 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />Invalid Link Specifier ---";
	$emailBodyTemplate .= "<BR />|%DCbegin links x 0 DCend%|";

	dm("Template Before Replacements:<BR />----------<BR />{$emailBodyTemplate}<BR />----------");

	$emailBody = new viewFromTemplate();
	$emailBody->set_viewFormat('TEXT');

	$emailBody->set_template($emailBodyTemplate);
	$emailBody->makeViewFromTemplate();
	$emailBodyView = $emailBody->get_viewCreated();

	dm("Email After Replacements:<BR />----------<BR />{$emailBodyView}<BR />----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}


function TstCase03($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Generate Links From Template - In HTML Form";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	$emailBodyView = "";

	$emailBodyTemplate = "";
	$emailBodyTemplate .= "Should See Links for Club, Series, Event and none:";
	$emailBodyTemplate .= "<BR /><BR />Links for Club ---";
	$emailBodyTemplate .= "<BR />|%DCbegin links C 2 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />Links for Series ---";
	$emailBodyTemplate .= "<BR />|%DCbegin links S 5 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />Links for Event ---";
	$emailBodyTemplate .= "<BR />|%DCbegin links E 27 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />Invalid Link Specifier ---";
	$emailBodyTemplate .= "<BR />|%DCbegin links x 0 DCend%|";

	dm("Template Before Replacements:<BR />----------<BR />{$emailBodyTemplate}<BR />----------");

	$emailBody = new viewFromTemplate();
	$emailBody->set_viewFormat('HTML');

	$emailBody->set_template($emailBodyTemplate);
	$emailBody->makeViewFromTemplate();
	$emailBodyView = $emailBody->get_viewCreated();

	dm("Email After Replacements:<BR />----------<BR />{$emailBodyView}<BR />----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



function TstCase04($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Generate Email with a Text Block in the Template";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
//	$objDebug->DEBUG = TRUE;

	$emailBodyView = "";

	$emailBodyTemplate = "";
	$emailBodyTemplate = "";
	$emailBodyTemplate .= "RSVPs for Recreational Play this Week:<BR />";
	$emailBodyTemplate .= "|%DCbegin textblock 1 1 1 FALSE DCend%|";
//	$emailBodyTemplate .= "|%DCbegin textblock 1 1 1 DCend%|";
	$emailBodyTemplate .= "<BR />|%DCbegin rsvpstat 5 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />|%DCbegin links S 2 DCend%|<BR />";
	$emailBodyTemplate .= "<BR />|%DCbegin sigseriesadmin 2 DCend%|";

	dm("Template Before Replacements:<BR />----------<BR />{$emailBodyTemplate}<BR />----------");

	$emailBody = new viewFromTemplate();
	$emailBody->set_viewFormat('HTML');

	$emailBody->set_template($emailBodyTemplate);
	$emailBody->makeViewFromTemplate();
	$emailBodyView = $emailBody->get_viewCreated();

	dm("Email After Replacements:<BR />----------<BR />{$emailBodyView}<BR />----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}



function TstCase05($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Generate Upcoming Events List";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;

	$emailBodyView = "";

	$emailBodyTemplate = "";
	$emailBodyTemplate .= "Please RSVP for Recreational Play this Week:<BR />";
	$emailBodyTemplate .= "<BR />|%DCbegin upcomingevents 5 DCend%|";

	dm("Template Before Replacements:<BR />----------<BR />{$emailBodyTemplate}<BR />----------");

	$emailBody = new viewFromTemplate();
	$emailBody->set_viewFormat('HTML');

	$emailBody->set_template($emailBodyTemplate);
	$emailBody->makeViewFromTemplate();
	$emailBodyView = $emailBody->get_viewCreated();

	dm("Email After Replacements:<BR />----------<BR />{$emailBodyView}<BR />----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}


function TstCase06($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Generate rsvp Update URL within Template";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	$objDebug->DEBUG = TRUE;

	$emailBodyView = "";

	$emailBodyTemplate = "";
	$emailBodyTemplate .= "Please RSVP for Recreational Play this Week:<BR />";
	$emailBodyTemplate .= "<BR />|%DCbegin ITERATE_rsvpUpdateURL 5 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />|%DCbegin links S 2 DCend%|<BR />";
	$emailBodyTemplate .= "<BR />|%DCbegin sigseriesadmin 2 DCend%|";

	dm("Template Before Replacements:<BR />----------<BR />{$emailBodyTemplate}<BR />----------");

	$emailBody = new viewFromTemplate();
	$emailBody->set_viewFormat('HTML');

	$emailBody->set_template($emailBodyTemplate);
	$emailBody->makeViewFromTemplate();
	$emailBodyView = $emailBody->get_viewCreated();

	dm("Email After Replacements:<BR />----------<BR />{$emailBodyView}<BR />----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}


function TstCase07($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Generate Full RSVP Update Request Email Body";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	//$objDebug->DEBUG = TRUE;

	$emailBodyView = "";

	$emailBodyTemplate = "";
	$emailBodyTemplate .= "Using the below link, please declare your RSVPs for ";
	$emailBodyTemplate .= " this week's upcoming Recreational Play:<BR />";
	$emailBodyTemplate .= "<BR />(<i>NOTE: This is an automated message. Please use";
	$emailBodyTemplate .= " the supplied link to update your playing intentions.";
	$emailBodyTemplate .= " Replies to this email will not been seen.</i>)<BR />";
	$emailBodyTemplate .= "<BR /> > |%DCbegin ITERATE_rsvpUpdateURL 5 DCend%| < <BR />";
	$emailBodyTemplate .= "<BR /><BR /><B>Recreational Play Schedule This Week:</B>";
	$emailBodyTemplate .= "<BR /><BR />|%DCbegin upcomingevents 5 DCend%|";
	$emailBodyTemplate .= "<BR /><BR />Useful Links:";
	$emailBodyTemplate .= "<BR /><BR />|%DCbegin links S 2 DCend%|<BR />";
	$emailBodyTemplate .= "<BR />|%DCbegin sigseriesadmin 2 DCend%|";

	dm("Template Before Replacements:<BR />----------<BR />{$emailBodyTemplate}<BR />----------");

	$emailBody = new viewFromTemplate();
	$emailBody->set_viewFormat('HTML');

	$emailBody->set_template($emailBodyTemplate);
	$emailBody->makeViewFromTemplate();
	$emailBodyView = $emailBody->get_viewCreated();

	dm("Email After Replacements:<BR />----------<BR />{$emailBodyView}<BR />----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}


function TstCase08($caseNumber)
	{
	global $objError;
	global $objDebug;

	$caseName = "Generate Full RSVP Update Request Email Body with Bringing Text";
	dmtcenex($caseNumber, $caseName, TRUE);

	//---Test Script Code Begins Here -------------------------------------------	
	$objDebug->DEBUG = FALSE;
	//$objDebug->DEBUG = TRUE;

	$emailBodyView = "";
	$emailBodyTemplate = "";
	$emailBodyTemplate .= "<BR />";
	$emailBodyTemplate .= "------------------------<BR />
MIXED-UP DOUBLES TENNIS SOCIAL<BR />
------------------------<BR />
<b>DATE</b>: January 1st (Thursday)<BR />
<BR />
<b>TIME</b>: 9:30am - 1:00pm<BR />
<BR />
<b>LOCATION</b>: North Meck Park, Huntersville<BR />
<BR />";
	$emailBodyTemplate .= "<BR />";
	$emailBodyTemplate .= "Here is the latest update for our New Year's morning social.<BR />";
	$emailBodyTemplate .= "<BR />";
	$emailBodyTemplate .= "<b>Here's What Folks have signed up to bring</b>:";
	$emailBodyTemplate .= "<BR />|%DCbegin rsvpbringing 538 DCend%|<BR />";
	$emailBodyTemplate .= "<BR />";
	$emailBodyTemplate .= "<b>Here is the current list of who-all coming</b>:<BR />";
	$emailBodyTemplate .= "<BR />|%DCbegin rsvpstatoneevent 538 NameLastFirst NA DCend%|";
	$emailBodyTemplate .= "<BR /><BR /><A HREF=\"http://laketennis.com/tennis/mobile/meventPage.php?ID=538\">Event Page</A><BR />";
	$emailBodyTemplate .= "<BR />|%DCbegin sigseriesadmin 56 DCend%|";

	dm("Template Before Replacements:<BR />----------<BR />{$emailBodyTemplate}<BR />----------");

	$emailBody = new viewFromTemplate();

	$emailBody->set_template($emailBodyTemplate);
	$emailBody->makeViewFromTemplate();
	$emailBodyView = $emailBody->get_viewCreated();

	dm("Email After Replacements:<BR />----------<BR />{$emailBodyView}<BR />----------");

	
	//---Test Script Code Ends Here ------------==-------------------------------	
	dmtcenex($caseNumber, $caseName, FALSE);
	return;
	}


?> 
