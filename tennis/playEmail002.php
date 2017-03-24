<html>
 <head>
  <title>PHP Send Email Test</title>
 </head>
 <body>
 
 
 <?php
//include('Mail.php');
require_once('Mail.php');


$eol="\n";
$emailaddress="rsvp@laketennis.com,rocchio@rocketmail.com,jroc@activeage.com";
//$emailaddress="rocchio@rocketmail.com";
$emailsubject = "test from PHP - text + HTML";

# Common Headers
$headers .= 'From: Jeff <d529518@laketennis.com>'.$eol;
$headers .= 'Reply-To: Jeff <d529518@laketennis.com>'.$eol;
$headers .= 'Return-Path: Jeff <d529518@laketennis.com>'.$eol;    // these two to set reply address
$headers .= "Message-ID: <".$now." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
$headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

# Boundry for marking the split & Multitype Headers
$mime_boundary=md5(time());
$headers .= 'MIME-Version: 1.0'.$eol;
$headers .= "Content-Type: multipart/alternative; boundary=\"".$mime_boundary."\"".$eol;
$msg = "";


$body = "<html>
<body>{$eol}";

$body .= "<p>Test Email Send from PHP<BR><b>This is PHP v5 and Apache v2.</b></p>{$eol}";
$body .= "<form method='post' action='editRSVP.php?ID=6&POST=T'>{$eol}";
$body .= "<input type=hidden name=meta_RTNPG value=listSeriesRoster.php?ID=6>{$eol}";
$body .= "<input type=hidden name=meta_ADDPG value=''>{$eol}";
$body .= "<input type=hidden name=meta_POST value=TRUE>{$eol}";
$body .= "<input type=hidden name=meta_TBL value=rsvp>{$eol}";
$body .= "<input type=hidden name=meta_EVTPURP value=17}>{$eol}";
$body .= "<input type=hidden name=ID value=692}>{$eol}";
$body .= "<table border='1' CELLPADDING='3' rules='rows'>{$eol}";
$body .= "<TR>
<TD>
<P>Availability</P></TD>
<TD><P><SELECT name=ClaimCode><OPTION SELECTED value =\"10\">No Response</OPTION>{$eol}
<OPTION value =\"11\">Not Available</OPTION>{$eol}
<OPTION value =\"13\">Late</OPTION>{$eol}
<OPTION value =\"14\">Tentative</OPTION>{$eol}
<OPTION value =\"15\">Available</OPTION>{$eol}
<OPTION value =\"16\">Confirmed</OPTION>{$eol}
</SELECT>{$eol}
</P>{$eol}
<P>Person's availability for this event.</P>{$eol}
</TD>
</TR>{$eol}
</body></html>{$eol}";

# Non-Multipart version for old email readers.
$msg .= "{$eol}This is a multipart mime message{$eol}{$eol}";

# Text Version
$msg .= "--".$mime_boundary.$eol;
$msg .= "Content-Type: text/plain; charset=iso-8859-1".$eol;
$msg .= "Content-Transfer-Encoding: 8bit{$eol}{$eol}";
$msg .= "This is a multi-part message in MIME format.".$eol;
$msg .= "If you are reading this, you are reading the text only portion".$eol;
$msg .= "+ + Text Only Email from Jeff Rocchio".$eol.$eol;

# HTML Version
$msg .= "--".$mime_boundary.$eol;
$msg .= "Content-Type: text/html; charset=iso-8859-1{$eol}";
$msg .= "Content-Transfer-Encoding: 8bit{$eol}{$eol}";
$msg .= $body.$eol.$eol;

# Finished
$msg .= "--".$mime_boundary."--".$eol.$eol;  // finish with two eol's for better security. see Injection.

mail($emailaddress, $emailsubject, $msg, $headers);

echo "<p>Sent test message via php mail()...</p>";

echo "<p>message sent:</P>";
echo "<P>{$msg}</p>";


?>
</body>
</html>
