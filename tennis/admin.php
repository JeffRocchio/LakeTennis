<?php
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();
$_SESSION['RtnPg'] = "http://" . $_SERVER['HTTP_HOST'] . "/tennis/admin.php";
$rtnpg = Session_SetReturnPage();


//$DEBUG = TRUE;
$DEBUG = FALSE;

$CRLF = "\n";


//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID=$_SESSION['clubID'];

$baseURL = "http://" . $_SERVER['HTTP_HOST'];


//----GET USER EDIT RIGHTS---------------------------------------------->
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='SADM'; }
elseif ($_SESSION['clbmgr']==True) { $userPriv='ADM'; }



//----ENSURE USER RIGHTS ARE OK TO PROCEED------------------------------->
if(($userPriv<>'ADM') AND ($userPriv<>'SADM'))
	{
	echo "<P>You are Not Authorized to View This Page</P>";
	echo "<P>Your User Rights are: {$userPriv}</P>";
	include './INCL_footer.php';
	exit;
	}



//=== BUILD PAGE ============================================================>

//----MAKE PAGE HEADER------------------------------------------------------->
$tbar = "Tennis Admin Page";
$pgL1 = "";
$pgL2 = "";
$pgL3 = "Tennis Admin Page";
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);


echo "<p>This page provides access to a variety of management functions for";
echo " your tennis site.</p>";


echo "<p><b>LIST:</b></p>";

if ($userPriv=='SADM')
	{
		echo "<p style='margin-left: 15px'><A HREF=\"../mysqlmaint/listDBs.php\">List Databases in MySql</A></p>";
		echo "<p style='margin-left: 15px'><A HREF=\"listTextBlock.php\">List Text Blocks</A></p>";
	}

echo "<p style='margin-left: 15px'><A HREF=\"listClub.php\">List All Clubs</A></p>";

echo "<p style='margin-left: 15px'><A HREF=\"listSeries.php\">List All Series For Your Club</A></p>";

echo "<p style='margin-left: 15px'><A HREF=\"listPerson.php\">List All Persons For All Clubs</A></p>";

echo "<p style='margin-left: 15px'><A HREF=\"listVenue.php\">List All Venues</A> (Venues are used by all clubs)</p>";


echo "<p><b>EDIT:</b></p>";

echo "<p style='margin-left: 15px'><A HREF=\"editClub.php?ID={$clubID}\">Edit Your Club's Record</A></p>";


echo "<p><b>ADD:</b></p>";

if ($userPriv=='SADM')
	{
	echo "<p style='margin-left: 15px'><A HREF=\"addClub.php\">Add New Club</A></p>";
	echo "<p style='margin-left: 15px'><A HREF=\"addPerson.php\">Add New Person</A>";
	echo "&nbsp;&nbsp;&nbsp;(Non Super Admins get <A HREF=\"{$baseURL}/tennis/faq/faq_admin_AddClubMember.php\">FAQ Page</A>)</p>";
	echo "<p style='margin-left: 15px'><A HREF=\"addTextBlock.php\">Add New Text Block</A></p>";

	}
else
	{

	echo "<p style='margin-left: 15px'><A HREF=\"{$baseURL}/tennis/faq/faq_admin_AddClubMember.php\">Add New Person</A></p>";
	}
echo "<p style='margin-left: 15px'><A HREF=\"addVenue.php\">Add New Venue</A></p>";

echo "<p style='margin-left: 15px'><A HREF=\"addSeries.php\">Add New Series</A></p>";


echo "<p><b>OTHER:</b></p>";
	
echo "<p style='margin-left: 15px'><A HREF=\"login.php\">Login As Different User</A></p>";

if ($userPriv=='SADM')
	{
		echo "<p style='margin-left: 15px'><A HREF=\"playEmail001.php\">Send Test Email</A></p>";
		echo "<p style='margin-left: 15px'><A HREF=\"playEmail002.php\">Send Test Email - With Form</A></p>";
	
		echo "<p style='margin-left: 15px'><A HREF=\"news.xml\">RSS Feed?</A></p>";
	}
	
	
echo  Tennis_BuildFooter('ADMIN', $rtnpg);

?>
