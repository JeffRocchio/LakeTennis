<?php
/*
	This script lists all the email addresses for all persons
	in the club who are currently active. The email list
	is in a form suitable for cut/paste into an email
	message.

	08-028-2009:
	   1) Fevised the $tblName and $where variables to make this script 
	   	work properly in the new multi-club data schema model.
------------------------------------------------------------------ */
session_start();
include_once('./INCL_Tennis_Functions_Session.php');
include_once('./INCL_Tennis_DBconnect.php');
include_once('./INCL_Tennis_Functions.php');
include_once('./INCL_Tennis_Functions_ADMIN_v2.php');
Session_Initalize();



$DEBUG = FALSE;
//$DEBUG = TRUE;


$CRLF = "\n";

//----DECLARE GLOBAL VARIABLES------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID=$_SESSION['clubID'];
$tblName = 'qryClubMembers';
$row = array();
				//   Where and Sort clauses for the person view on this
				//list.
$where="WHERE Active=1 AND clubID={$clubID}";
$orderby="ORDER BY prsnFName, prsnLName";


//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "Tennis Email List";
$pgL1 = "List Records";
$pgL2 = "Active Club Members";
$pgL3 = "Email List";
echo Tennis_BuildHeader('NORM', $tbar, $pgL1, $pgL2, $pgL3);


//----OPEN THE TABLE TO LIST-------------------------------------------->
if(!$qryResult = Tennis_OpenViewGeneric($tblName, $where, $orderby))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}
$row = mysql_fetch_array($qryResult);
	
if ($_SESSION['member'] != TRUE)
		{
		$out = "<P>You Are Not Authorized to View this Page.</P>{$CRLF}";
		$out .= "<P>IF you are a club member, please login.</P>{$CRLF}";
		echo $out;
		echo  Tennis_BuildFooter('NORM', "listPerson_Emails.php");
		exit;
		}

$out = "<P>Email address list for all currently active club members. ";
$out .= "You should be able to cut/paste the below list into your email TO field:</p>{$CRLF}";
$out .= "<P>----------</p>{$CRLF}";
echo $out;
$out = "";
if (strlen($row['FullName']) > 0)
	{
	$out = "<P>";
	do
		{
		if ($row['Email1Active'] == 1)
			{
			if (strlen($row['Email1']) > 3) $out .= "{$row['Email1']}, ";
			}
		if ($row['Email2Active'] == 1)
			{
			if (strlen($row['Email2']) > 3) $out .= "{$row['Email2']}, ";
			}
		if ($row['Email3Active'] == 1)
			{
			if (strlen($row['Email3']) > 3) $out .= "{$row['Email3']}, ";
			}
		}
	while ($row = mysql_fetch_array($qryResult));
	$len = strlen($out);
	if ($len > 3)
		{
		$len = strlen($out);
		$last = strrpos($out, ',');
		$out = substr($out, 0, $last);
		}
	else
		{
		$out = "<P>There are no currently active club members.";
		}
	$out .= "</P>";
	}

echo $out;

$out = "<P>----------</p>{$CRLF}";
echo $out;

					//   Make links to navigate to other places. 02/03/2007.
echo "<P STYLE='margin-top: 20px; margin-bottom: 0'>GO TO:</P>{$CRLF}";
echo "<P STYLE='margin-left: 10px; margin-top: 0; margin-bottom: 0; font-size: small'>{$CRLF}";
echo "&nbsp;&nbsp;&nbsp;*&nbsp;<A HREF='listPerson_PhoneList.php'>Club Phone List</A><BR>{$CRLF}";
echo "</P>{$CRLF}";


echo  Tennis_BuildFooter('NORM', "listPerson_Emails.php");

?> 
