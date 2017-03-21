<?
session_start();
set_include_path("./tennis");
include_once('INCL_Tennis_Functions_Session.php');
include_once('INCL_Tennis_DBconnect.php');
include_once('INCL_Tennis_Functions.php');
include_once('INCL_Tennis_Functions_Metrics.php');
include_once('INCL_Tennis_Functions_QUERIES.php');
Session_Initalize();






$DEBUG = FALSE;
//$DEBUG = TRUE;


//----DECLARE GLOBAL VARIABLES------------------------------------------------>

global $CRLF;

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


//----DECLARE LOCAL VARIABLES------------------------------------------------->
$tblName = 'qryClubDisp';
$row = array();
$server = $_SERVER['HTTP_HOST'];
$clubID = 0;


//----GET QUERY STRING DATA--------------------------------------------------->
if (array_key_exists('ID', $_GET)) $clubID = $_GET['ID'];


//----SET CLUB ID------------------------------------------------------------->
if (!$clubID) { $clubID = $_SESSION['clubID']; }
if (!$clubID)
	{
	echo "<P>ERROR, Not Signed Into A Valid Club.</P>";
	include "INCL_footer.php";
	exit(0);
	}
$_SESSION['clubID'] = $clubID;


//----GET-SET RETURN PAGE----------------------------------------------->
//$rtnpg = "http://{$server}/ClubHome.php?ID={$_SESSION['clubID']}";
$rtnpg = "http://{$server}/ClubHome.php";
$_SESSION['RtnPg'] = $rtnpg;


//----CONNECT TO MYSQL-------------------------------------------------->
$link = Tennis_DBConnect();
if ($lstErrExist == TRUE)
	{
	echo "<P>{$lstErrMsg}</P>";
	include "INCL_footer.php";
	exit(0);
	}



//----OPEN CLUB TABLE, PULLING UERS RIGHTS WITH IT----------------------->
if(!Tennis_GetSingleRecord($row, $tblName, $clubID))
	{
	echo "<P>{$lstErrMsg}</P>";
	include "INCL_footer.php";
	exit(0);
	}


//----MAKE PAGE HEADER--------------------------------------------------->
$out = "<HTML><HEAD>{$CRLF}";
$out .= "<TITLE>{$row['ClubName']}</TITLE>{$CRLF}";
$out .= "<LINK REL=StyleSheet HREF='main.css' TYPE='text/css'>{$CRLF}";
$out .= "<LINK REL=StyleSheet HREF='metric.css' TYPE='text/css'>{$CRLF}";

?>

<STYLE TYPE="text/css">
  #thispg_Bar
  	{
		font-size: small;
		background: #AAAA44;
		width: 20em;
		float: right;
		height: auto;
		padding-left: .1em;
		margin-left: 1em;
		border: solid
	}

  #thispg_announce
  	{
		padding-left: .5em;
		height: auto
	}

  #thispg_maincontent
  	{
		padding-left: .5em;
		clear: both
	}

  #thispg_footer
  	{
		padding-left: .5em;
		height: auto
	}

</STYLE>


</head>


<BODY CLASS=bdyNormal>

<?
//----MAKE CLUB HOME PAGE------------------------------------------------>

$out .= "<H1 CLASS=fntNormal>{$row['ClubName']}</H1>";
$out .= "<DIV id='thispg_announce'>";

$out .= "<P>{$CRLF}";
$out .= $row['Descript'];
$out .= $CRLF;
$out .= "</P>{$CRLF}";
$out .= "</DIV>";
echo $out;
$out = "";


?>

<DIV id="thispg_maincontent">

		<hr noshade="noshade" size="5">

		<h2 CLASS=fntNormal>Useful Links</h2>


		<table id="Table3" border="0" cellpadding="3" cellspacing="1" width="100%">

				<tbody><tr>
					<td align="right" valign="bottom" width="65">
						</td>

					<td align="left" bgcolor="#dcdcdc" valign="bottom" width="40%"><font size="+1"> <strong>Link</strong>
						</font>
						</td>

					<td align="left" bgcolor="#dcdcdc" valign="bottom"><font size="+1"> <strong>Description</strong>
						</font>
						</td>
				</tr>


				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="./tennis/listPerson_PhoneList.php">
						Club Phone List</a>
						</td>

					<td align="left" valign="top"><strong>Club Phone List.</strong><br>
						This link will show a page that lists all our club members and their phone numbers.
						You have to be a club member to view this list.<br>
						</td>
				</tr>
				
				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="./tennis/listSeries.php">
						League History</a>
						</td>

					<td align="left" valign="top"><strong>League History.</strong><br>
						This link will display a list of all current and prior series and leagues
						we have participated in, with links to their roster-grids.<br>
						</td>
				</tr>



<?
/*
//====BUILD A LIST OF ALL ACTIVE SERIES FOR CLUB====================>
// The database needs to be modified so there is an indicator for the
//series on if to display it here or not.
//OR better yet, implement a list of links kind of like sharepoint so
//that this list can be whatever the club owner wants it to be
				
				//   Open series table.
if(!$qryResult = Tennis_OpenViewGeneric("series", "WHERE series.ClubID={$clubID}", "ORDER BY series.Sort, series.ShtName"))
	{
	echo "<P>{$lstErrMsg}</P>";
	include 'tennis/INCL_footer.php';
	exit;
	}
				//   Build the list.
while ($row = mysql_fetch_array($qryResult))
	{
	$out = "<tr>{$CRLF}";

	$out .= "<td align='right' valign='top' width='65'>{$CRLF}";
	$out .= "&nbsp;{$CRLF}";
	$out .= "</td>{$CRLF}";

	$out .= "<td align='left' valign='top' width='40%'>{$CRLF}";
	$out .= "<a href='./tennis/listSeriesRoster.php?ID={$row['ID']}'>{$row['ShtName']}</a><BR>{$CRLF}";
	$out .= "</td>{$CRLF}";

	$out .= "<td align='left' valign='top'>{$CRLF}";
	$out .= "<STRONG>{$row['LongName']}</STRONG><BR>{$CRLF}";
	$out .= "{$row['Description']}<BR>{$CRLF}";
	$out .= "</td>{$CRLF}";
	
	$out .= "</TR>{$CRLF}{$CRLF}";
	
	echo $out;
	}

//if (strlen($out) > 3) echo $out;

//====END BUILD SERIES LIST=========================================<
*/
?>


				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top">
						<a href="http://national.usta.com/">Tennis Link</a>
						</td>

					<td align="left" valign="top"><strong>Tennis Link.</strong><br>
						The "My Teams" feature allows you to quickly track the activity of your 
						favorite teams without having to enter the team number every time you visit the 
						site. Add as many teams as you wish. Click on Help for instructions.<br>
						</td>
				</tr>

				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://nctennis.com/">North Carolina USTA Page</a>
						</td>

					<td align="left" valign="top"><strong>NC Tennis Web site.</strong><br>
						Home page for North Carolina chapter of the USTA.<br>
						</td>
				</tr>

				<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://www.lakenormantennis.org">Lake Norman Tennis Home</a>
						</td>

					<td align="left" valign="top"><strong>Lake Norman Tennis Association.</strong><br>
						Home page for Lake Norman tennis. Ladders, tournaments, etc.<br>
						</td>
				</tr>

			<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://www.usta.com/USTA/Global/Active/Custom%20Pages/Leagues/1237_NTRP.aspx">
						USTA Rating Guidelines and Process</a>
						</td>

					<td align="left" valign="top"><strong>General Characteristics of
						Various NTRP Playing Levels.</strong><br>
						The page on USTA site that contain the description of the
						ratings, guidelines for how to rate yourself and the process
						for self-rating.<br>
						</td>

			</tr>

			<tr>
					<td align="right" valign="top" width="65">
						&nbsp;
						</td>

					<td align="left" valign="top" width="40%">
						<a href="http://www.usta.com/Membership/Default.aspx">
						Become USTA Member</a>
						</td>

					<td align="left" valign="top"><strong>How do I get a USTA Number?</strong><br>
						The page on USTA site that allows you to sign up to become a USTA member.
						You must be a USTA member to register to play on a team.
						</td>
			</tr>

		</tbody></table>

		<hr noshade="noshade" size="5">

</DIV>

<?

//----MAKE FOOTER ------------------------------------------------------------>

$out = "<DIV id='thispg_footer'>{$CRLF}";
$out .= $row['HomePgFoot'];
$out .= $CRLF;
if($DEBUG)
	{
	$out .= "<BR>";
	$out .= "Server: http://" .$_SERVER['HTTP_HOST'];
	$out .= "<BR>";
	$out .= "Page: " .$_SERVER['PHP_SELF'];
	$out .= "<BR>";
	$out .= "value of rtnpg = {$rtnpg}";
	}
$out .= "</DIV>{$CRLF}";
echo $out;


echo  Tennis_BuildFooter('NORM', $rtnpg);
?>
