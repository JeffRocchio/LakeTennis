<?php
/*
/*
	===================
	CLASS: emailNotice
	===================

	PURPOSE: Provide common services for creating, addressing and sending email
	notices.
		
	DEPENDENCIES --:
	
			1: Global constant include file.
			2: ERROR Object.
			3: INCL_Tennis_Functions include file.
					for: Tennis_ContactListOpen($Object, $ID, $Scope)
			4: CLASS_html2text.php Object.
	 
	USAGE NOTES --:

			A.	Each run-time instance of this object handles one email notice.
		The email is built into the instance; and they you instruct the instance
		to 'send' the email out. After the email is sent, it could be reused to
		build and send another email. If this is done you should clear the
		object out prior to reusing it by calling ->resetObject().

			B.	You may create different HTML and Plain-Text versions of the
		email. So, for example, you may have a web-form embedded in the HTML
		version, but the plain-text section may simply refer readers to a URL.

			C.	As an alternative to 'B' above, you may have this object 
		generate a plain-text version of the email using the HTML version.
		This way you can still support all your user's different email clients 
		without having to explicitly build two different text stings. 
		Use the appropriate flag on the sendEmail() function. If, at that time, 
		the $this->textBody variable is empty, then we will generate a 
		plain-text version of the email from $this->htmlBody.

		   D.	Body text of the email is passed to the object in HTML
		format. Even when passing in the string to be used for the plain-text
		section of the email, that string must be passed in as an HTML formatted
		sting. This object will convert the HTML into plain text.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

			(b) I am still unsure how I want to deal with the issue of the display 
		in terms of web browser vs CRON vs Email - that is to say, do I want to 
		create a "Display" object at some point? Because of this, I would like 
		to adopt a policy whereby all output to the display is confined to one 
		private function within this object. This will permit a relatively 
		painless way to implement a Display object at some later point in time.
		
		   (c) Body text of the email is to be passed to this object in HTML
		format. Any plan-text version of it is to be derived internal to this
		object using the HTML string.


	DEVELOPMENT NOTES --:

			(1) Bear in mind that when running in CRON we are *not* running on the
		web server, so we don't have access to $_SERVER[] variables like
		$_SERVER['HTTP_HOST'], etc.
	
			(2) Also, when running in CRON we don't have a user logged in.
		So beware the use of $_SESSION[] variables.
		
	REVISIONS --:

		06/14/2012: Created the genToArray() function as part of building the
						rsvpRequestNotice autoAction handler.

		12/26/2011: Initial creation.
	
*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class emailNotice {

					//   Variables needed for getting data from the dbms.
	private $dbRow = array();
	private $dbRecsRead = 0;
	
				// Properties for building the email notice.
	protected $from = "notices@laketennis.com";
//	protected $from = "d529518@laketennis.com";
	protected $subject = "";
	protected $toList = "";
	protected $ccList = "";
	protected $bccList = "";
	protected $htmlBody = "";
	protected $textBody = "";
	protected $headers = "";
	protected $multiBoundary = "==Multipart_Boundary_x8745376";

					// Properties for control and admin stuff.
	protected $toListCount = 0;


	//---GET/SET Functions-------------------------------------------------------
	public function get_from() {
	return $this->from; }

	public function get_Subject() {
	return $this->subject; }

	public function set_Subject($subject) {
	$this->subject = substr($subject, 0, 250);
	return $this->subject; }

	public function get_AddressList($addrLine="TO") {
	$addrList = "";
	switch ($addrLine)
		{
		case "CC":
			$addrList = $this->ccList;
			break;
		case "BCC":
			$addrList = $this->bccList;
			break;
		default:
			$addrList = $this->toList;
		}
	return $addrList; }

	public function get_Body($bodyFormat="HTML") {
	$classHTML2Text = null;
	$bodyString = "";
	switch ($bodyFormat)
		{
		case "HTML":
			$bodyString = $this->htmlBody;
			break;
		default:
			if(strlen($this->textBody) == 0)
				{
				$classHTML2Text = new html2text($this->htmlBody);
				$bodyString = $classHTML2Text->get_text();
				}
			else
				{
				$bodyString = $this->textBody;
				}
		}
	return $bodyString; }



	//---------------------------------------------------------------------------
	public function appendToList($addresses, $addrLine="TO")
	{
	/*	PURPOSE: Add address entries to the requested email address 
		line (TO, CC, BCC).
	*/	
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
	$comma = "";
	if($this->toListCount > 0) $comma = ", ";

					//		Scratch variables.
					//		Logic------------------------------------------------------

					//   Add the address list into the right slot.
	switch ($addrLine)
		{
		case "CC":
			$this->ccList .= $comma . $addresses;
			break;
			
		case "BCC":
			$this->bccList .= $comma . $addresses;
			break;

		default:
			$this->toList .= $comma . $addresses;
			$this->toListCount++;
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return true;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function genToList($Object, $ID, $Scope, $addrLine="TO")
	{
	/*	PURPOSE: Generate formatted email address lists and add them
		to the requested email address line (TO, CC, BCC).
		
		NOTE: We are **adding** addresses to the requested line, not
		clearing the line out.

		ASSUMES --:
			a)	Mysql connection is currently open.

		TAKES --:
			a)	$Object: One of the Global Constants: OBJCLUB, OBJSERIES, OBJEVENT.
			b)	$ID: The ID for the $Object.
			c)	$Scope: The sub-set of persons for whom to generate the emails
				for given the $Object and $ID. (E.g., for an event we might want
				email list only for those person's who are scheduled to play in
				the match.) Must be one of the values recognized by the
				Tennis_ContactListOpen() function.
			d)	Address line to put the addresses in: "TO", "CC", "BCC".

		RETURNS --:
				a) TRUE if success, FALSE otherwise (error will have been
			registered).
				b) The object variable applicable to the $addrLine will have
			the requested address set appended to it (this function will not
			overwrite any existing addresses stored there; this allows you to
			call the method several times to add various subsets of members to
			any particular email address line.)
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$qryResult = 0;
	$row = array();
	$list = "";
	$listCount = 0;
	$debugText = "";


					//		Logic------------------------------------------------------

					//   Open a query into the members table that selects the
					//requested recordset.
					//   Then loop through the recordset and format the email
					//address list.
	if (!$qryResult = Tennis_ContactListOpen($Object, $ID, $Scope))
		{
					//put error handling here....
		$debugText = "** ERROR calling Tennis_ContactListOpen() **";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
		if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
		return true;
		}
	if ($row = mysql_fetch_array($qryResult))
		{
		do
			{
			if (($row['Email1Active'] == 1) and (strlen($row['Email1']) > 3))
				{
				$list .= "{$row['Email1']}, ";
				$listCount++;
				}
			if (($row['Email2Active'] == 1) and (strlen($row['Email2']) > 3))
				{
				$list .= "{$row['Email2']}, ";
				$listCount++;
				}
			if (($row['Email3Active'] == 1) and (strlen($row['Email3']) > 3))
				{
				$list .= "{$row['Email3']}, ";
				$listCount++;
				}
			}
		while ($row = mysql_fetch_array($qryResult));
		}
					//   Remove the trailing comma that
					//follows the last entry in the list.
	if ($listCount > 0)
		{
		$len = strlen($list);
		$last = strrpos($list, ',');
		$list = substr($list, 0, $last);
		}

					//   Add the address list into the right slot.
	switch ($addrLine)
		{
		case "CC":
			$this->ccList .= $list;
			break;
			
		case "BCC":
			$this->bccList .= $list;
			break;

		default:
			$this->toList .= $list;
			$this->toListCount += $listCount;
		}


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return true;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function genToArray($Object, $ID, $Scope, &$list, $addrSep=",")
	{
	/*	PURPOSE: Generate email address list and return it as an array.
		
		ASSUMES --:
			a)	Mysql connection is currently open.

		TAKES --:
			a)	$Object: One of the Global Constants: OBJCLUB, OBJSERIES, OBJEVENT.
			b)	$ID: The ID for the $Object.
			c)	$Scope: The sub-set of persons for whom to generate the emails
				for given the $Object and $ID. (E.g., for an event we might want
				email list only for those person's who are scheduled to play in
				the match.) Must be one of the values recognized by the
				Tennis_ContactListOpen() function.
			d) $ResultList: An pointer to array that will contain the resulting
				list of generated email addresses.
			e) OPTIONAL - $addrSep: Default=","  If you require semi-colons
				instead of commas to seperate your email address entries you
				may pass a semicolon in.

		RETURNS --:
				a) TRUE if success, FALSE otherwise (error will have been
			registered).
				b) Populates the passed-by-reference $list array with the list of 
			generated email addresses. The array will have columns: 
			UserRecID | prsnPName | prsnFName | prsnLName | Email1 | 
			Email1Active | Email2 | Email2Active | Email3 | Email3Active |
			EmailsForTo | NumEmailsForTo. 
				The 'EmailsForTo' column contains all active emails for
			the user in a form suitable for setting into an email TO (or cc or bcc)
			line to send one email to all of the user's active email addresses 
			on file.
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$qryResult = 0;
	$row = array();
	$rowCount = 0;
	$listCount = 0;
	$list = "";
	$debugText = "";


					//		Logic------------------------------------------------------

					//   Open a query into the members table that selects the
					//requested recordset.
					//   Then loop through the recordset and format the email
					//address list.
	if (!$qryResult = Tennis_ContactListOpen($Object, $ID, $Scope))
		{
					//put error handling here....
		$debugText = "** ERROR calling Tennis_ContactListOpen() **";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
		if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
		return FALSE;
		}
	if ($row = mysql_fetch_array($qryResult))
		{
		do
			{
			$rowCount++;
			$list[$rowCount]['UserRecID'] = $row['prsnID'];
			$list[$rowCount]['prsnPName'] = $row['prsnPName'];
			$list[$rowCount]['prsnFName'] = $row['prsnFName'];
			$list[$rowCount]['prsnLName'] = $row['prsnLName'];
			$list[$rowCount]['Email1'] = $row['Email1'];
			$list[$rowCount]['Email1Active'] = $row['Email1Active'];
			$list[$rowCount]['Email2'] = $row['Email2'];
			$list[$rowCount]['Email2Active'] = $row['Email2Active'];
			$list[$rowCount]['Email3'] = $row['Email3'];
			$list[$rowCount]['Email3Active'] = $row['Email3Active'];
			$list[$rowCount]['EmailsForTo'] = "";
			if (($row['Email1Active'] == 1) and (strlen($row['Email1']) > 3))
				{
				$list[$rowCount]['EmailsForTo'] .= "{$row['Email1']}{$addrSep} ";
				$listCount++;
				}
			if (($row['Email2Active'] == 1) and (strlen($row['Email2']) > 3))
				{
				$list[$rowCount]['EmailsForTo'] .= "{$row['Email2']}{$addrSep} ";
				$listCount++;
				}
			if (($row['Email3Active'] == 1) and (strlen($row['Email3']) > 3))
				{
				$list[$rowCount]['EmailsForTo'] .= "{$row['Email3']}{$addrSep} ";
				$listCount++;
				}
					//   Remove the trailing comma that
					//follows the last entry in the list.
			if ($listCount > 0)
				{
				$tmp = $list[$rowCount]['EmailsForTo'];
				$len = strlen($tmp);
				$last = strrpos($tmp, $addrSep);
				$tmp = substr($tmp, 0, $last);
				$list[$rowCount]['EmailsForTo'] = $tmp;
				}
			$list[$rowCount]['NumEmailsForTo'] = $listCount;
			$listCount = 0;
			}
		while ($row = mysql_fetch_array($qryResult));
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return TRUE;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function appendBody($bodyText, $section="HTML")
	{
	/*	PURPOSE: Load the body text into the email.
		
		NOTE-A: We are **appending** the text to any existing body text, not
		clearing the body and replacing it with this text. This allows you to
		call this function several times, as necessary, to build up the email's
		body.

		NOTE-B: Even if the section you are sending a string in for is the
		plain text section, you still have to format that string in HTML form.
		This function will automatically convert the HTML into plain text
		for you.
		
		ASSUMES --:
			a)	Mysql connection is currently open.

		TAKES --:
				a)	$htmlBody: An HTML tagged string that is the body of the email.
				b) $section: Used to designate if loading HTML version of the body
			or the plain-text version of the body. (See Note-B above!)

		RETURNS --:
				a) TRUE if success, FALSE otherwise (error will have been
			registered).
				b) The internal object variable $htmlBody or $textBody will
			contain any strings from prior calls along with this sting
			appended to it.

	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$classHTML2Text = null;

					//		Logic------------------------------------------------------
	switch ($section)
		{
		case "HTML":
			$this->htmlBody .= $bodyText;
			break;
			
		default:
			$classHTML2Text = new html2text($bodyText);
			$this->textBody .= $classHTML2Text->get_text();
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return true;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function sendEmail($FlagHtml=TRUE, $FlagText=TRUE)
	{
	/*	PURPOSE: Send the email notice.
		
		NOTE-A: The object instance will still exist in memory, even after a
		successful send. To re-use the instance for another email, call
		the resetObject() method first.
		
		NOTE-B: If you specify to send as Plain-Text, and there is no body
		text in the $textBody object variable, then the plain-text body string
		will be automatically derived from what is in the $htmlBody variable.

		ASSUMES --:
				a)	Sendmail is operational on the server and that PHP has access
			to it.
				b)	The various parts of the email (Subject, TO, Body) have been
			set into the object instance. Some degree of validation of this will
			be performed as part of this send function.

		TAKES --:
				a)	$FlagHtml: Pass TRUE if you want the email to be sent out in
			HTML format.
				b) $FlagText: Pass TRUE if you want the email to be sent out in
			plain text format. IF both flags are set TRUE then the email will
			be sent as a multi-part email with both HTML and Plain-Text sections.
			This is the default.

		RETURNS --:
				a) TRUE if success, FALSE otherwise (error will have been
			registered).
				b) If successful, the email will have been sent out.

	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
//	$eol = EMAILCRLF;
	$eol = PHP_EOL;
	$from = $this->from;
	$to = $this->toList;
	$cc = $this->ccList;
	$bcc = $this->bccList;
	$subject = $this->subject;

					//		Scratch variables.
	$classHTML2Text = null;
	$emailEnvlopSendr = "";
	$bodyString = "";
	$allHeaders = "";
	$errRecord = array();
	$emailSendResult = FALSE;
	$methodReturnValue = FALSE;
	$debugText = "";

					//   Array returned from the data-validation function. Used to
					//deal with any errors in the data that get detected prior to
					//attempting to build and send the email.
					//   $errList['ERRORS_TOTAL']
					//   $errList['ERRORS_WARNING']
					//   $errList['ERRORS_FATAL']
	$errDetectedList = array();

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");
	
					//		Logic------------------------------------------------------

					//   ---ONE: Check validity of the data - do we have what we
					//need to send the email?
					//   If FATAL errors are reported back, abort the send
					//and return with FALSE.
					//   If WARNING errors are reported back, determine if we
					//can proceed or not.
	$errDetectedList = $this->dataValidityErrors();
	if ($errDetectedList['ERRORS_WARNING'] > 0)
		{
		if ((array_key_exists('041', $errDetectedList)) and ($FlagHtml))
			{
			$errDetectedList['ERRORS_FATAL']++;
			$erNumEmailsForTorDetectedList['ERRORS_WARNING'] = $errDetectedList['ERRORS_WARNING'] - 1;
			$errorText= "";
			$errorText .= "042: HTML formatted email requested, but no";
			$errorText .= " HTML string has been provided.";
			$errDetectedList['042'] = $objError->RegisterErr(
				ERRSEV_ERROR, 
				ERRCLASS_OBJDATA, 
				__FUNCTION__, 
				__LINE__, 
				$errorText, 
				False);
			}
		}
	if ($errDetectedList['ERRORS_FATAL'] > 0)
		{
		return FALSE;
		exit;		
		}
		
		
					//   ---TWO: IF the plain text flag is set to TRUE, then
					//Go ahead and make sure the plain text object variable
					//is populated; converting the html version of the body
					//string if necessary.
	if ($FlagText)
		{
		if (strlen($this->textBody) == 0)
			{
			$debugText = "...No Pain-Text set, so using html converted to ";
			$debugText .= "plain text.";
			if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
			$classHTML2Text = new html2text($this->htmlBody);
			$this->textBody = $classHTML2Text->get_text();
			}
		}


					//   ---THREE: (a) Set header string based on 
					//multi-part or single-part email form.
					//   And (b) Set the body sting also based on multi
					//or single.
	if ($FlagHtml and $FlagText)
					//   Multi-part message.
		{
		if ($objDebug->DEBUG) $objDebug->writeDebug("...Multi Part Email.");
		$allHeaders = $this->makeEmailHeaders("MULTI");
		$bodyString = "--{$this->multiBoundary}{$eol}";
		$bodyString .= "Content-Type: text/plain; charset=\"iso-8859-1\"{$eol}";
		$bodyString .= "Content-Transfer-Encoding: 7bit{$eol}{$eol}";
		$bodyString .= $this->textBody;
		$bodyString .= $eol . $eol;
		$bodyString .= "--{$this->multiBoundary}{$eol}";
		$bodyString .= "Content-Type: text/html; charset=\"iso-8859-1\"{$eol}";
		$bodyString .= "Content-Transfer-Encoding: 7bit{$eol}{$eol}";
		$bodyString .= $this->htmlBody;
		$bodyString .= $eol . $eol;
		$bodyString .= "--{$this->multiBoundary}{$eol}";
		}
	elseif ($FlagHtml)
					//   Single-part message, HTML format.
		{
		$debugText = "...Single Part Email, HTML Format.";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
		$allHeaders = $this->makeEmailHeaders("HTML");
		$bodyString = $this->htmlBody;
		}
	else
					//   Single-part message, plain text format.
		{
		$debugText = "...Single Part Email, Pain-Text Format.";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
		$allHeaders = $this->makeEmailHeaders("TEXT");
		if ($objDebug->DEBUG) $objDebug->writeDebug("...allHeaders: {$allHeaders}");
		$bodyString = $this->textBody;
		}
		
					//   ---FOUR: Set this variable. I can't say I actually know
					//what this is for or does, but some documentation says that
					//it helps avoid spam filters. 
		$emailEnvlopSendr="-f{$from}";

					//   ---FIVE: Attempt to Send the Email, if an error is 
					//returned, register an error and return FALSE. 
					//Otherwise return TRUE.
	$emailSendResult = mail($to, $subject, $bodyString, $allHeaders, $emailEnvlopSendr);
	if ($emailSendResult)
		{
		if ($objDebug->DEBUG) $objDebug->writeDebug("...Mail Sent.");
		$methodReturnValue = TRUE;
		}
	else
		{
		if ($objDebug->DEBUG) $objDebug->writeDebug("...mail() failed.");
		$methodReturnValue = FALSE;
		$errorText= "";
		$errorText .= "090: Email could not be sent. PHP mail() function";
		$errorText .= " returned failure for some unknown reason.";
		$objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_EMAILSEND, 
			__FUNCTION__, 
			__LINE__, 
			$errorText, 
			False);
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $methodReturnValue;

	} // END METHOD




	//---------------------------------------------------------------------------
	public function resetObject()
	{
	/* 	PURPOSE: "Reset" the object so it can be used to build and send
			a new email.
	
			TAKES:
					a)	Nothing.

			RETURNS:
					a)	All the object's variables will be set to what they
				normally are when the object is first instantiated.
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Logic------------------------------------------------------
	$this->dbRow = NULL;
	$this->dbRecsRead = 0;
	$this->from = "notices@laketennis.com";
//	$this->from = "d529518@laketennis.com";
	$this->subject = "";
	$this->toList = "";
	$this->ccList = "";
	$this->bccList = "";
	$this->htmlBody = "";
	$this->textBody = "";
	$this->toListCount = 0;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return true;

	} // END METHOD



	//---------------------------------------------------------------------------
	private function dataValidityErrors()
	{
	/*	PURPOSE: Checks to see if we have all the info we need to form and
		send a valid email. Call this function prior to sending to be sure
		all is well.
	
		ASSUMES --:
				a) Nothing.

		TAKES --:
				a) Nothing.

		RETURNS --:
				a) An array containing the key#'s for any and all errors
			detected. The array element ['ERRORS_TOTAL'] will contain the number 
			of errors that were detected. It will be set to 0 if no errors were 
			detected. The array element ['ERRORS_WARNING'] will contain the number 
			of errors that detected that are considered warnings, not necessarily
			fatal. The array element ['ERRORS_FATAL'] will contain the number 
			of errors that were detected which are considered fatal. All other
			array elements will use a 3-character error ID# as their key.
				b) IF errors are detected, then they will have been registered
			in the ERROR object. The first 3 characters of the Error title
			will contain an error ID # which the calling function can use
			for error recovery.

	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
	$errList = array();
	$errList['ERRORS_TOTAL'] = 0;
	$errList['ERRORS_WARNING'] = 0;
	$errList['ERRORS_FATAL'] = 0;

					//		Scratch variables.
	$errorText= "";
	$debugText = "";
	
					//		Logic------------------------------------------------------

	if (strlen($this->from) < 6)
		{
		if ($objDebug->DEBUG) $objDebug->writeDebug("...ERROR: no this->from set.");
		$errList['ERRORS_TOTAL']++;
		$errList['ERRORS_FATAL']++;
		$errorText = "";
		$errorText .= "010: No FROM email address specified.";
		$errList['010'] = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_OBJDATA, 
			__FUNCTION__, 
			__LINE__, 
			$errorText, 
			False);
		}

	if (strlen($this->toList) < 6)
		{
		if ($objDebug->DEBUG) $objDebug->writeDebug("...ERROR: no this->toList set.");
		$errList['ERRORS_TOTAL']++;
		$errList['ERRORS_FATAL']++;
		$errorText = "";
		$errorText .= "020: No TO email addresses provided.";
		$errList['020'] = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_OBJDATA, 
			__FUNCTION__, 
			__LINE__, 
			$errorText, 
			False);
		}

	if (strlen($this->subject) < 3)
		{
		if ($objDebug->DEBUG) $objDebug->writeDebug("...ERROR: no this->subject set.");
		$errList['ERRORS_TOTAL']++;
		$errList['ERRORS_FATAL']++;
		$errorText = "";
		$errorText .= "030: No Subject provided.";
		$errList['030'] = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_OBJDATA, 
			__FUNCTION__, 
			__LINE__, 
			$errorText, 
			False);
		}

	if (strlen($this->htmlBody) < 3)
		{
		$debugText = "...WARNING: this->htmlBody is empty.";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
		$errList['ERRORS_TOTAL']++;
		$errList['ERRORS_WARNING']++;
		$errorText = "";
		$errorText .= "041: The HTML form of the body text is empty.";
		$errList['041'] = $objError->RegisterErr(
			ERRSEV_WARNING, 
			ERRCLASS_OBJDATA, 
			__FUNCTION__, 
			__LINE__, 
			$errorText, 
			False);
		}

	if ((strlen($this->htmlBody) < 3) and (strlen($this->textBody) < 3))
		{
		$debugText = "...ERROR: Both HTML and TEXT bodies are empty.";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
		$errList['ERRORS_TOTAL']++;
		$errList['ERRORS_FATAL']++;
		$errorText = "";
		$errorText .= "045: No body text provided in either HTML";
		$errorText .= " or Plain-Text forms.";
		$errList['045'] = $objError->RegisterErr(
			ERRSEV_ERROR, 
			ERRCLASS_OBJDATA, 
			__FUNCTION__, 
			__LINE__, 
			$errorText, 
			False);
		}


	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $errList;

	} // END METHOD



	//---------------------------------------------------------------------------
	private function makeEmailHeaders($emailStructure)
	{
	/*	PURPOSE: Creates the headers needed to send an email.
	
		ASSUMES --:
				a) All needed values in the object properties have been
			correctly set.

		TAKES --:
				a) $emailStructure: Tells this function what set of headers to
			build. Values: "HTML", "TEXT", "MULTI".
				a)	$FlagHtml: Pass TRUE if you want the email to be sent out in
			HTML format.
				b) $FlagText: Pass TRUE if you want the email to be sent out in
			plain text format. IF both flags are set TRUE then the email will
			be sent as a multi-part email with both HTML and Plain-Text sections.
			This is the default. The value for these flags should be the same
			as the values passed into the sendEmail() method.

		RETURNS --:
				a) The headers string. IF this returned string is empty, then
			an error has occurred (and been registered with ERROR object.
				b) The object property $headers will also contain a copy of
			the header string.

	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$eol = EMAILCRLF;
	$from = $this->from;

					//		Scratch variables.
	$debugText = "";

					//		Logic------------------------------------------------------

	$this->headers = "From: {$from}{$eol}Reply-To: {$from}{$eol}";
	$this->headers .= "X-Mailer: PHP v" . phpversion() . $eol;
	switch($emailStructure)
		{
		case "HTML":
			$this->headers .= "MIME-Version: 1.0{$eol}";
			$this->headers .= "Content-type: text/html; charset=\"iso-8859-1\"{$eol}";
			break;

		case "TEXT":
			$this->headers .= "Content-Type: text/plain; charset=\"iso-8859-1\"{$eol}";
			break;

		default:
			$this->headers .= "Content-Type: multipart/alternative; ";
			$this->headers .= "boundary=\"{$this->multiBoundary}\"{$eol}{$eol}";
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug("HEADER STRING: <BR>{$this->headers}");

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $this->headers;

	} // END METHOD


} // END CLASS emailNotice


?> 
