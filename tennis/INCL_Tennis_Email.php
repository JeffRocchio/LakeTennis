 <?php
 
 /*
12/20/2015 ==
		Modified EMAIL_listAddresses() so it can take and return list of emails
	for two additional scope-specs: "RSVPALLPOTENTIAL" and "RSVPNORESPONSE."
	I did this for the Mixed-Up Doubles events as I otherwise didn't have a 
	good way to sent emails out, via my personal email account, to those
	sub-sets' of individuals.
 */
 
 
//require_once('Mail.php');
//    01/01/2009: When I moved to Fedora 10 I didn't install 'Mail.php' (Pear).
//I'm going to leave this commented-out and see what happens as I'm thinking
//I don't need it.	
//    11/11/2008: A2 "installed" the PHP Mail package and fixed the error. So
// I have re-enabled this include.
//    11/11/2008, I disabled this as the A2 servers
//suddenly could not find this file. This is part of the Pear mail package.
//I did try to reinstall the Pear Mail package, but it still wouldn't work.
//I submitted a ticket for this issue, greying this out until that ticket is
//resolved. Disabling it tho doesn't seem to generate any errors on the site.
//winder if I need it at all??

function EMAIL_dbUpdateNotify($object, $ID, $Note)
{
	$eol="\n";
	$headers = "";
	
//	$emailTOaddress="rsvp@laketennis.com";
//	$emailTOaddress="tennis@activeage.com";
	$emailTOaddress="jroc@activeage.com";
//	$emailTOaddress="rocchio@rocketmail.com";
	$emailFROMaddress="d529518@laketennis.com";
	$emailEnvlopSendr="-f{$emailFROMaddress}";
	
	$emailSubject = "TENNIS - Database Record Updated";


	# Common Headers
	$headers .= "From: {$emailFROMaddress}{$eol}";
	$headers .= "Reply-To: {$emailFROMaddress}{$eol}";
	$headers .= "Return-Path: {$emailFROMaddress}{$eol}";    // these two to set reply address
	$headers .= "Message-ID: <".$now." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
	$headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

	$msgTxt = "";
	$msgTxt .= "A record in the tennis database has been updated.{$eol}{$eol}";
	$msgTxt .= "ID: {$ID}{$eol}";
	$msgTxt .= "Object: {$object}{$eol}{$eol}";
	$msgTxt .= "------------------{$eol}";
	$msgTxt .= "RECORD AFTER SAVE:{$eol}";
	$msgTxt .= "{$Note}{$eol}{$eol}";
	
	$msg = $headers.$msgTxt;

	mail($emailTOaddress, $emailSubject, $msgTxt, $headers, $emailEnvlopSendr);
	return;

}

function EMAIL_ToAddress($To, $Subject, $Body)
{
	$eol="\n";
	$headers = "";

	
//	$emailTOaddress="rsvp@laketennis.com";
//	$emailTOaddress="tennis@activeage.com";
//	$emailTOaddress="rocchio@rocketmail.com";
	$emailTOaddress=$To;
	$emailFROMaddress="d529518@laketennis.com";
	$emailEnvlopSendr="-f{$emailFROMaddress}";
	
	# Common Headers
	$headers .= "From: {$emailFROMaddress}{$eol}";
	$headers .= "Reply-To: {$emailFROMaddress}{$eol}";
	$headers .= "Return-Path: {$emailFROMaddress}{$eol}";    // these two to set reply address
	$headers .= "Message-ID: <".time() . " TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
	$headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

	$msgTxt = "{$Body}{$eol}{$eol}";
	
	$msg = $headers.$msgTxt;

	mail($emailTOaddress, $Subject, $msgTxt, $headers, $emailEnvlopSendr);
	return;

}

function EMAIL_ToMember($ID, $Subject, $Body)
{
	$eol="\n";
	
//	$emailTOaddress="rsvp@laketennis.com";
//	$emailTOaddress="tennis@activeage.com";
	$emailTOaddress="jroc@activeage.com";
//	$emailTOaddress="rocchio@rocketmail.com";
	$emailFROMaddress="d529518@laketennis.com";
	$emailEnvlopSendr="-f{$emailFROMaddress}";
	
	# Common Headers
	$headers .= "From: {$emailFROMaddress}{$eol}";
	$headers .= "Reply-To: {$emailFROMaddress}{$eol}";
	$headers .= "Return-Path: {$emailFROMaddress}{$eol}";    // these two to set reply address
	$headers .= "Message-ID: <".time()." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
	$headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

	$msgTxt = "{$Body}{$eol}{$eol}";
	
	$msg = $headers.$msgTxt;

	mail($emailTOaddress, $Subject, $msgTxt, $headers, $emailEnvlopSendr);
	return;

}

function XX_EMAIL_dbUpdateNotify($object, $ID, $Note)
{
	//For the life of me, I can't get this to work.
	
	$eol="\n";
	$emailaddress="rsvp@laketennis.com";
	$emailsubject = "TENNIS - Database Record Updated";
	
	# Common Headers
	$headers .= 'From: Jeff <d529518@laketennis.com>'.$eol;
	$headers .= 'Reply-To: Jeff <d529518@laketennis.com>'.$eol;
	$headers .= 'Return-Path: Jeff <d529518@laketennis.com>'.$eol;    // these two to set reply address
	$headers .= "Message-ID: <".time()." TheSystem@".$_SERVER['SERVER_NAME'].">".$eol;
	$headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters
	# Boundry for marking the split & Multitype Headers
	$mime_boundary = "---NEXTPART__";
	$mime_boundary .= md5(time());
	$headers .= 'MIME-Version: 1.0'.$eol;
	$headers .= "Content-Type: multipart/alternative; boundary=\"".$mime_boundary."\"".$eol;

	$msg = "";
	$body = "";
	
	# Text Version
	$msg .= $mime_boundary.$eol;
	$msg .= "Content-Type: text/plain; charset=\"us-ascii\"".$eol;
	$msg .= "Content-Transfer-Encoding: quoted-printable".$eol;
	$msg .= "A record in the tennis database has been updated.{$eol}";
	$msg .= "ID: {$ID}{$eol}";
	$msg .= "Object: {$object}{$eol}";
	$msg .= "------------------{$eol}";
	$msg .= "NOTE:{$eol}";
	$msg .= "{$Note}{$eol}{$eol}";
	$msg .= "--".$mime_boundary.$eol;
	
	# HTML Version
	$msg .= $mime_boundary.$eol;
	$msg .= "Content-Type: text/html; charset=\"us-ascii\"".$eol;
	$msg .= "Content-Transfer-Encoding: quoted-printable".$eol;
	$body .= "<HTML><BODY><P>Tennis DB Record Updated.</P>{$eol}";
	$body .= "<P>ID: {$ID}</P>{$eol}";
	$body .= "<P>Object: {$object}</P>{$eol}";
	$body .= "<P>NOTE: {$Note}</P>{$eol}";
	$body .= "</body></html>{$eol}";
	$msg .= $body.$eol.$eol;
	
	# Finished
	$msg .= "--".$mime_boundary."--".$eol.$eol;  // finish with two eol's for better security. see Injection.
	
	mail($emailaddress, $emailsubject, $msg, $headers, "-fd529518@laketennis.com");
//	mail("jroc@activeage.com", $emailsubject, $msg, $headers);
	mail("rocchio@rocketmail.com", $emailsubject, $msg, $headers, "-fd529518@laketennis.com");
//	mail("tennis@activeage.com", $emailsubject, $msg, $headers);
	
	return;

}

function EMAIL_listAddresses($Object, $ID, $Scope, $Format)
{
	/*
	This function generates a list of email addresses.

	ASSUMES:
		1)	Mysql connection is currently open.
	
	TAKES:
		1)	$Object: CLUB, SERIES, EVENT.
		2)	$ID: The ID for the $Object.
		3)	$Scope: The sub-set of persons for whom to generate the emails
			for given the $Object and $ID. (E.g., for an event we might want
			email list only for those person's who are scheduled to play in
			the match.)
		4)	$Format: TBD. (E.g., might be used to control if seperators should
			be semi-colon vs comma vs line-breaks, etc.)
		
	RETURNS:
		1)	A string containing the email address list. Assumption is that
			this string will either be displayed onto a web page or inserted
			into an email 'TO' field.

	*/
	
	//----Local Variables ---
	
				//   Contains the list of email addresses. This is what we build
				//the list into.
	$list = "";


	//----Code ---

	switch ($Object)
		{
		case OBJCLUB:
			$tblName = 'qryPersonDisp';
			$where="WHERE Currency=39 AND ClubID={$ID}";
			$orderby="ORDER BY FullName";
			$testNameString = "FullName";
			break;

		case OBJSERIES:
			$tblName = 'qrySeriesEligible';
			$where="WHERE (ID={$ID})";
			$orderby="ORDER BY prsnFullName";
			$testNameString = "prsnFullName";
			//$list = "IN ObjSeries:<BR />";
			break;
		
		case OBJEVENT:
			$tblName = 'qryRsvpPerson';
				switch ($Scope)
					{
					case 'AVAIL': // Not scheduled to play, but AVAIL.
						$selCrit = " AND ";
						$selCrit .= "(rsvpClaimCode=15 OR rsvpClaimCode=13 OR rsvpClaimCode=16) AND ";
						$selCrit .= "(rsvpPositionCode=28 OR rsvpPositionCode=30 ";
						$selCrit .= "OR rsvpPositionCode=27)";
						break;
		
					case 'PLAY': // Scheduled to play.
						$selCrit = " AND ";
						$selCrit .= "(rsvpPositionCode<>28 AND rsvpPositionCode<>30)";
						//$selCrit .= "AND rsvpPositionCode<>27)";
						break;
				
					case 'NOTPLAY': // Everyone not scheduled to play, dispite AVAIL.
						$selCrit = " AND ";
						$selCrit .= "(rsvpPositionCode=28 OR rsvpPositionCode=30 ";
						$selCrit .= "OR rsvpPositionCode=27)";
						break;
						
					case 'RSVPALLPOTENTIAL': // Everyone who is, or might, still play base only on RSVP claims.
						$selCrit = " AND ";
						$selCrit .= "(rsvpClaimCode=13 OR rsvpClaimCode=14 OR rsvpClaimCode=15 OR rsvpClaimCode=16 OR rsvpClaimCode=10)";
						break;
						
					case 'RSVPNORESPONSE': // Everyone who has not yet RSVP'd or is tentative.
						$selCrit = " AND ";
						$selCrit .= "(rsvpClaimCode=10 OR rsvpClaimCode=14)";
						break;
		
				default:
					$selCrit = ""; // All members of the series.
					}
	
			$where="WHERE (evtID={$ID}{$selCrit})";
			//$where="WHERE (evtID={$ID})";
			$orderby="ORDER BY prsnFullName";
			$testNameString = "prsnFullName";
			break;
		
		case OBJPERSON:
			$tblName = 'qryPersonDisp';
			$where="WHERE Currency=39 AND ClubID={$ID}";
			$orderby="ORDER BY FullName";
			break;
		
		default:
			$list = "EMPTY: Invalid Selection.";
	
		}

					//   Generate the list.	
	$row = array();
		if(!$qryResult = Tennis_OpenViewGeneric($tblName, $where, $orderby))
		{
		echo "<P>{$lstErrMsg}</P>";
		include './INCL_footer.php';
		exit;
		}
	$row = mysql_fetch_array($qryResult);
	if (strlen($row[$testNameString]) > 0)
		{
		do
			{
			if ($row['Email1Active'] == 1)
				{
				if (strlen($row['Email1']) > 3) $list .= "{$row['Email1']}, ";
				}
			if ($row['Email2Active'] == 1)
				{
				if (strlen($row['Email2']) > 3) $list .= "{$row['Email2']}, ";
				}
			if ($row['Email3Active'] == 1)
				{
				if (strlen($row['Email3']) > 3) $list .= "{$row['Email3']}, ";
				}
			}
		while ($row = mysql_fetch_array($qryResult));
		$len = strlen($list);
		if ($len > 3)
			{
			$len = strlen($list);
			$last = strrpos($list, ',');
			$list = substr($list, 0, $last);
			}
		else
			{
			$list .= "EMPTY: No Active Email Addresses for Selection.";
			}
		}

	
		return $list;

	
} // END EMAIL_listAddresses.
?>
