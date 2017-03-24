<?php

function Tennis_DBConnect()
	{
	/*
		This function connects to the mysql database.
	
	ASSUMES:
		1) Existence of the global error variables.
	
	TAKES:
		1) Nothing.
		
	RETURNS:
		1) The database object.

		   
		   
	VARIABLES USED IN FUNCTION --------------------------------------------

	$CRLF
		:: AS String.
		:: Contains a carriage-return / Line-Feed string.


	*/

		
	$DEBUG = FALSE;
	//$DEBUG = TRUE;
	
	$CRLF = "\n";
	
	$GLOBALS['lstErrExist'] = FALSE;
	$GLOBALS['lstErrMsg'] = "";



				//   Connect to mysql
//---> On A2 Server: $link = mysql_connect('localhost', 'd529518_php', 'php');
//--->Correct new way to do it: $link = mysqli_connect('localhost', 'php', 'php');
	$link = mysql_connect('localhost', 'd529518_php', 'php'); // <<-- on A2
//	$link = mysql_connect('localhost', 'php', 'php'); //<<-- on local.
	if (!$link)	
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = 'Could not connect. Mysql error #: ' . mysql_error();
		return;
		}
	if ($DEBUG)
		{
		echo '<p>Connected successfully</p>';
		}
	
					
				//   Select the requested database. If we can't select the
				//requested db, then return an error.
//---> On A2 Server: $db_selected = mysql_select_db('d529518_tennis', $link);
//---> On A2 Server, using new mysqli: $db_selected = mysqli_select_db($link, 'd529518_tennis');
	$db_selected = mysql_select_db('d529518_tennis', $link); // <<-- on A2.
//	$db_selected = mysql_select_db('tennis', $link); // <<-- On Local.
//--->Correct new way to do it: $db_selected = mysqli_select_db($link, 'tennis');
	if (!$db_selected)
		{
		$GLOBALS['lstErrExist'] = TRUE;
		$GLOBALS['lstErrMsg'] = "Can't use tennis. Mysql error: " . mysql_error();
		return;
		}

	return $link;
		
}


?>