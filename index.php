<?
session_start();
set_include_path("./tennis");
include_once('INCL_Tennis_Functions_Session.php');
include_once('INCL_Tennis_DBconnect.php');
include_once('INCL_Tennis_Functions.php');
include_once('INCL_Tennis_Functions_Metrics.php');
include_once('INCL_Tennis_Functions_QUERIES.php');
Session_Initalize();


//$DEBUG = TRUE;
$DEBUG = FALSE;


global $CRLF;

//----DECLARE GLOBAL VARIABLES------------------------------------------------>
				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";

?>

<html>
<head>
	<title>Tennis Clubs</title>	
<?
/*
   Redirect to the current Holbrook homepage. Eventually I need to use this
page to display a list of all clubs and allow the user to choose one to go
into.
   */

?>
	<LINK REL=StyleSheet HREF="main.css" TYPE="text/css">
	<LINK REL=StyleSheet HREF="metric.css" TYPE="text/css">
	<LINK REL=StyleSheet HREF="handheld.css" media="handheld" TYPE="text/css">
	
<STYLE TYPE="text/css">

  #thispg_announce
	  	{
		font-size: large;
		padding-left: .5em;
		height: auto
	}

	#thispg_maincontent
	  	{
		padding-left: .5em;
		clear: both
	}


	TH.ClubListHeadID
	  	{
		background-color: #BBBBBB;
		width: 1%
	}

	TH.ClubListHeadNAME
	  	{
		background-color: #BBBBBB;
		width: 30%
	}

	TH.ClubListHeadBLURB
	  	{
		background-color: #BBBBBB;
	}


</STYLE>


</head>


<BODY CLASS='bdyNormal'>

<H1 CLASS='fntNormal'>Tennis Clubs</H1>



<?
//----DECLARE LOCAL VARIABLES------------------------------------------------->

$tblName = 'qryClubDisp';
				//   Declare array to hold the detail display
				//record.
$row = array();

				//   Return Page. Have to build a string which defines the 
				//return page from the root due to the index page in the
				//HTML root directory and not in the /tennis directory like all
				//the other pages are.
$server = $_SERVER['HTTP_HOST'];
$rtnpg = "http://{$server}/ClubHome.php";
$_SESSION['RtnPg'] = $rtnpg;


//----CONNECT TO MYSQL-------------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include "INCL_footer.php";
	exit;
	}



//----OPEN CLUB TABLE, PULLING UERS RIGHTS WITH IT--------------------------->
if(!$qryResult = Tennis_OpenViewGenericAuth($tblName, "", "ORDER BY {$tblName}.ClubName", 55))
	{
	echo "<P>{$lstErrMsg}</P>";
	include "INCL_footer.php";
	exit;
	}
	

?>
	




<DIV id="thispg_announce">

	<p><font color="#306030">
		Welcome to <strong>LakeTennis.COM</strong>. This site is home to several tennis groups 
		and is oriented specifically to help groups and clubs organize 
		tennis league play.
	</font>
	</p>

	<p><font color="#306030">
		Please select your group or club from the below list. Or simply login
		at the bottom of the page to be taken to your specific club.
	</font>
	</p>


</DIV>


<DIV id="thispg_maincontent">

		<hr noshade="noshade" size="5">

		<h2 CLASS='fntNormal'>Select Your Club</h2>

<?
//----BUILD THE LIST---------------------------------------------------------->
				//   Display the in standard
				//record-detail-display format.
$out = "{$CRLF}{$CRLF}<TABLE CLASS='ddTable' CELLSPACING='2' CELLPADDING='2'>{$CRLF}";

				//   Header Row.
$out .= "<THEAD>{$CRLF}";
$out .= "<TR CLASS='ddTblRow'>{$CRLF}";
$out .= "<TH CLASS='ClubListHeadID'><P CLASS='ddSectionTitle'>ID</P></TD>{$CRLF}";
$out .= "<TH CLASS='ClubListHeadNAME'><P CLASS='ddSectionTitle'>Club Name</P></TD>{$CRLF}";
$out .= "<TH CLASS='ClubListHeadBLURB'><P CLASS='ddSectionTitle'>Brief Description</P></TD>{$CRLF}";
$out .= "</TR></THEAD>{$CRLF}";
echo $out;
				
				//   Build table body.
$out = "<TBODY>{$CRLF}";
echo $out;
while ($row = mysql_fetch_array($qryResult))
	{
	if ($row['LobbyShow'] == 1 AND $row['Active'] ==1)
		{
		$out = "<TR CLASS='ddTblRow'>{$CRLF}";
					//   Record ID.
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>{$row['ID']}</P></TD>{$CRLF}";
					//   Club Name.
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
		$out .= "<A HREF='../ClubHome.php?ID={$row['ID']}'>{$row['ClubName']}</A></P></TD>{$CRLF}";
					//   Lobby Blurb.
		$out .= "<TD CLASS='ddTblCellDisplay'><P CLASS='ddFieldData'>";
		$out .= "{$row['LobbyBlurb']}</P></TD>{$CRLF}";
					//   Close out the row.
		$out .= "</TR>{$CRLF}{$CRLF}";
		echo $out;
		}
	}
				// Close out the table body.
$out = "</TBODY>{$CRLF}{$CRLF}";
				// Close out the table.
$out = "</TABLE>{$CRLF}{$CRLF}";
echo $out;

$out = "</DIV>{$CRLF}{$CRLF}";
echo $out;

				//   Show contact info.
$out = "<BR>&nbsp;<BR>";
$out .= "<p><font size='-1'>";
$out .= "<strong>Contact: </strong>";
$out .= "<a href='mailto:tennis@activeage.com'>Jeffrey Rocchio</a>";
$out .= " (tennis@activeage.com)";
$out .= "</font></p>";
echo $out;


echo  Tennis_BuildFooter('NORM', $rtnpg);
?> 

