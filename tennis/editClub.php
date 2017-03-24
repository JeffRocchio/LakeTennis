<?php
/*
	This script allows editing of an existing
	club record.
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

global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";



//----DECLARE LOCAL VARIABLES------------------------------------------->
$clubID=$_SESSION['clubID'];
$tblName = 'club';
$row = '';



//----GET URL QUERY-STRING DATA----------------------------------------->
$recID = $_GET['ID'];
if (!$recID)
	{
	echo "<P>ERROR, No Club Selected.</P>";
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
	


//----FETCH THE RECORD TO EDIT------------------------------------------>
if(!Tennis_GetSingleRecord($row, $tblName, $recID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include './INCL_footer.php';
	exit;
	}



//----GET USER EDIT RIGHTS---------------------------------------------->
				//   Let's check to be sure that the user wanting to edit
				//the club record is currently logged into this clubID and
				//not a different club (on which they may have admin rights).
				//   NOTE that in the nested IF statement, SESSION['clbmgr']=TRUE
				//really means Club ADM level rights. If this session value is
				//FALSE for the current user then the below code will look up
				//the user's authority on the club, which may be at the (lower)
				//'MGR' level of rights (or of course, no rights at all).
$userPriv='GST';
if ($_SESSION['admin']==True) { $userPriv='ADM'; } // Superuser.
else
	if ($clubID==$recID)
		if ($_SESSION['clbmgr']==True) { $userPriv='ADM'; }
		else
			{
			$userPriv=Session_GetAuthority(55, $recID);
			}



//----MAKE PAGE HEADER--------------------------------------------------->
$tbar = "Edit Club {$row['ClubName']}";
$pgL1 = "Edit Record";
$pgL2 = "CLUB";
$pgL3 = $row['ClubName'];
echo Tennis_BuildHeader('ADMIN', $tbar, $pgL1, $pgL2, $pgL3);



//----EVENT-TABLE EDIT SECTION OF THE PAGE------------------------------->
				//   Create a form to enter the data into.
				//Also need to create two hidden fields to hold
				//the database and table name to pass to the
				//page we're going to post the data to.
echo "<form method='post' action='editGeneric_post.php'>";

echo "<input type=hidden name=meta_RTNPG value={$rtnpg}>";

echo "<input type=hidden name=meta_TBL value={$tblName}>";

echo "<input type=hidden name=ID value={$row['ID']}>";

echo "<table border='1' CELLPADDING='3' rules='rows'>";

				//   Record ID.
$rowHTML = "<TR class=deTblRow>{$CRLF}";
$rowHTML .= "<TD class=deTblCellLabel>{$CRLF}";
$rowHTML .= "<P class=deFieldName>ID</P>";
$rowHTML .= "</TD>{$CRLF}";
$rowHTML .= "<TD class=deTblCellInput><P class=deFieldInput>";
$rowHTML .= $row['ID'];
$rowHTML .= "</P></TD></TR>";
echo $rowHTML;

				//   Club Name.
$fldLabel = "Club Name";
$fldHelp = "Enter a brief name for the club.";
$rowHTML = ADMIN_GenFieldText($fldLabel, $fldHelp, 'ClubName', 100, 65, $row['ClubName'], 'ADM', $userPriv);
echo $rowHTML;

				//   Lobby Show?
$fldLabel = "Display In Lobby?";
$fldHelp = "Be sure 'Yes' is selected to have your club listed on the";
$fldHelp .= " site's main entry, or lobby page. ";
$fldHelp .= "(This is where all clubs are shown so users can select which";
$fldHelp .= " club to go into.)";
$rowHTML= ADMIN_GenFieldYN($fldLabel, $fldHelp, 'LobbyShow', $row['LobbyShow'], 'ADM', $userPriv);
echo $rowHTML;


				//   Lobby Blurb.
$fldLabel = "Lobby Blurb";
$fldHelp = "Enter a brief description for the club";
$fldHelp .= " to appear on the site's main home page where all";
$fldHelp .= " the clubs are listed. ";
$fldHelp .= "This should help users determine which club is yours.";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'LobbyBlurb', 5, 65, $row['LobbyBlurb'], 'MGR', $userPriv);
echo $rowHTML;

				//   Club Description.
$fldLabel = "Description";
$fldHelp = "Enter a detailed description or notes for the club. ";
$fldHelp .= "This will appear at the top of your club's home page. ";
$fldHelp .= "(You may use HTML tags, which will be enclosed within an HTML";
$fldHelp .= " division).";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'Descript', 15, 65, $row['Descript'], 'MGR', $userPriv);
echo $rowHTML;

				//   Club Home Page Footer.
$fldLabel = "Home Page Footer";
$fldHelp = "Enter information you'd like to appear at the bottom";
$fldHelp .= " of your club's home page. ";
$fldHelp .= "(You may use HTML tags, which will be enclosed within an HTML";
$fldHelp .= " division).";
$rowHTML = ADMIN_GenFieldNote($fldLabel, $fldHelp, 'HomePgFoot', 5, 65, $row['HomePgFoot'], 'MGR', $userPriv);
echo $rowHTML;

				//   Club Active?
$fldLabel = "Club Active?";
$fldHelp = "Be sure 'Yes' is selected so that your club is active on the";
$fldHelp .= " site. ";
$fldHelp .= "Setting your club to inactive ('No') will eventually cause the club to";
$fldHelp .= " be removed.";
$rowHTML= ADMIN_GenFieldYN($fldLabel, $fldHelp, 'Active', $row['Active'], 'ADM', $userPriv);
echo $rowHTML;





echo "<tr>{$CRLF}<td colspan='2'><p align='left'><input type='submit' value='Save record'>";

echo "</P>";
echo "</td>{$CRLF}</tr>{$CRLF}";

echo "</table>{$CRLF}";

echo "</form>{$CRLF}";


//----BOTTOM OF PAGE ACTION-LINKS---------------------------------------->
$rowHTML = "<P><A HREF='{$rtnpg}'>RETURN</A>";

				//   If current user has rights to manage this club, give them
				//some additional options.
/*
if ($userPriv=='MGR' or $userPriv=='ADM' or $userPriv=='SADM')
	{
	$rowHTML .= "&nbsp;&nbsp;&nbsp;&nbsp";
	$rowHTML .= "<A HREF='editEvent_Delete.php?EID={$recID}'>";
	$rowHTML .= "DELETE Event</A>";
	}

$rowHTML .= "</P>{$CRLF}";
echo $rowHTML;
*/

//----CLOSE OUT THE PAGE------------------------------------------------->
echo  Tennis_BuildFooter('ADMIN', "editClub.php?ID={$recID}");


?> 
