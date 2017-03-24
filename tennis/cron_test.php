<?php

$DEBUG = FALSE;
//$DEBUG = TRUE;

//----GLOBAL VARIABLES-------------------------------------------------------->
$CRLF = "\n";
$emCRLF = "\r\n";

				//   Declare the global error variables.
$lstErrExist = FALSE;
$lstErrMsg = "";


				
		
//----LOCAL VARIABLES--------------------------------------------------------->
$tblName = 'qryRsvp';

$row = array();
$recID = array();

$freeText = array();

$emBody;
$emSubject = "TENNIS RSVPs (no reply)";
$emTo = array();

$out = "<HTML><Head><Title>CRON TEST</title></head>";
$out .= "<Body><P>CRON TEST</P></body>";
$out .= "</HTML>";
echo $out;

?> 
