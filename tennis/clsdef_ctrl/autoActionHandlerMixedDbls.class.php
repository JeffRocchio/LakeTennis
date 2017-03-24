 <?php
/*
	=======================
	CLASS: autoActionHandlerMixedDbls.
	=======================

	PURPOSE: This a f'd up kludge to deal with the Mixed doubles RSVP notices.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) Created specifically for the New Year's mixed-up doubles social
			   in 2014.
		

	12/07/2014:	copied the autoActionHandler class to make one specific for
					mixed doubles.
		
*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================



class autoActionHandlerMixedDbls
{

	protected $actionRecArray = array();
	
	protected $autoAction;
	protected $rstActions;

					//		Used to evaluate if a requested action should be
					//triggered based on the current date/day/time of the run.
	protected $currDayOfWeek; //Sunday = 0.
	protected $currHour; //Hour as integer. 0=Midnight, 23=11pm.
	protected $currMonth; //1-12.
	protected $currDayOfMonth; //1-31.

	//---GET/SET Functions-------------------------------------------------------
	public function set_actionRecArray($value) {
	$this->actionRecArray = $value; return $this->actionRecArray; }

	public function get_actionRecArray() {
	return $this->actionRecArray; }

	//---------------------------------------------------------------------------
	public function __construct()
	{
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
					//		Scratch variables.

					//		Logic------------------------------------------------------
	$this->autoAction = new autoAction();
	$this->rstActions = new simulatedRecordset();
	$this->autoAction->setQrySpec_id(0);
	$this->autoAction->setQrySpec_infoSet('NOTICES');
	$this->autoAction->setQrySpec_subset('');
	$this->rstActions = $this->autoAction->openRecordset();
	$this->currDayOfWeek = idate('w',time()); //Sunday = 0.
	$this->currHour = idate('H',time()); //Hour as integer. 0=Midnight, 23=11pm.
	$this->currMonth = idate('m',time()); //1-12.
	$this->currDayOfMonth = idate('d',time()); //1-31.

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");

	} // END METHOD




	//---------------------------------------------------------------------------
	public function handleManualRequest(array $actionData)
	{
	/*	PURPOSE: Process a manually specified and initiate request (presumbably
		initiated by a user via a web-form PHP page).
		
		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) $actionData: An array that contains the data and parameters needed
				to process the request. This array needs to match up to what the
				autoAction class and simulatedRecordset class would normally 
				provide when processing automated actions via CRON.
				Key-Values in this array that must be set are:
					$actionData['AutoActClassID'] (e.g., AACT_SENDRSVPREQUEST)
					$actionData['ClubID']
					$actionData['ActTitle'] (Can be any string value)
					$actionData['TrggrObjType'] (e.g., OBJSERIES)
					$actionData['TrggrObjID'] (e.g., the series ID)
					$actionData['ToGroup'] (e.g., 30; // Send email to all members of the series.)
					$actionData['ToAddresses'] (e.g., rocchio@rocketmail.com, etc)
					$actionData['EmailEncodeFormat'] (e.g., "HTML")
					$actionData['EmailSubject'] (Can be any text)
					$actionData['EmailBodyTmplate'] (The template text that forms the body)
					$actionData['ForEventTypes'] (e.g., "05,06,07,09"; //Recreational events.)
					$actionData['ForEventStatus'] (e.g., "34"; //Result Code "TBD")

				
		RETURNS --:
			
		   1.1)	RTN_SUCCESS if success, 
		   1.2)	RTN_FAILURE if error.
	
		NOTES --:

				1) Created this on 12/06/2014 specifically for the Mixed-Up Doubles
			Social events so that I could get a request for RSVPs out to the group
			without having to set up a repeating event in the CRON autoAction
			class.
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------

					//		Scratch variables.
	$triggerEvalResult = TRUE;
	$actionResult = FALSE;
	$returnResult = RTN_FAILURE;
	$seekResult = RTN_FAILURE;
	$debugText = "";

					//		Logic------------------------------------------------------


					//   Load $this->actionRecArray.
	$this->set_actionRecArray($actionData);
	
	if ($objDebug->DEBUG)
		{
		$debugText = "actionData array values:<BR />";
		$debugText .= $objDebug->displayDBRecord($this->actionRecArray, FALSE);
		$objDebug->writeDebug($debugText);
		}

	switch ($this->actionRecArray['AutoActClassID'])
		{
		case AACT_ROLLDATES: //Codeset 14, CodeID 61
			$returnResult = RTN_SUCCESS;
			break;

		case AACT_SENDRSVPREQUEST: //Codeset 14, CodeID 62
			$returnResult = RTN_SUCCESS;
			$returnResult = $this->handle_RsvpUpdateRequest();
			break;

		case AACT_SENDRSVPSTAT: //Codeset 14, CodeID 63
			$returnResult = $this->handle_RsvpStatusNotice();
			break;

		default:
			$returnResult = RTN_NOACTION;
			# code...
		}
					
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnResult;

	} // END METHOD



	//===========================================================================
	//	INTERNAL PRIVATE FUNCTIONS
	//===========================================================================

	//---------------------------------------------------------------------------
	private function handle_RsvpStatusNotice()
	{
	/*	PURPOSE: Handle the processing for a notice to go out showing the
		current rsvp status for a series or event. BUT NOTE: Currently we are
		only handling status notices for a series. Still need to add logic for
		handling an event.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
			D) Assumes all required specs have already been into 
				the object-instance variables.
		
		TAKES --:
		
			1) .
				
		RETURNS --:
			
		   1.1) RTN_SUCCESS. OR
		   1.2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
	$result = FALSE;
	$drivingID = $this->actionRecArray['TrggrObjID'];

					//		Scratch variables.
	$debugText = "";
	$returnString = "";
	$ntceBody = "";
	
	$viewFromTemplate = new viewFromTemplate();
	$emailNotice = new emailNotice();

					//		Logic------------------------------------------------------

					//		1: Set Notification Subject Text.
	$debugText = "handle_RsvpStatus():: "; 
	$debugText .= "Step 1, Set Notification Subject Text"; 
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
	$emailNotice->set_Subject($this->actionRecArray['EmailSubject']);

					//		2: Set the TO address list.
	$debugText = "handle_RsvpStatus():: "; 
	$debugText .= "Step 2, Set TO List."; 
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
					//		Decode the 'ToGroup' value to determine what set of folks
					//to send the notice to.
					//		While in beta test, send notices to a specific test list
					//of addresses. So to accomplish this the 'ToGroup' param in
					//simulatedRecordset is set to the value 10, and an add'l
					//parameter is added to specify the list of email addresses
					//to send to.
	switch ($this->actionRecArray['ToGroup'])
		{
		case 10:
			$addresses = $this->actionRecArray['ToAddresses'];
			$emailNotice->appendToList($addresses, "TO");
			break;
		case 30:
			$emailNotice->genToList(OBJSERIES, $drivingID , "ALL", "TO");
			break;
		default:
			$addresses = "jroc@activeage.com";
			$emailNotice->appendToList($addresses, "TO");
		}

					//		3: Using emailbodytemplate, create the notification
					//body text.
	$debugText = "handle_RsvpStatus():: "; 
	$debugText .= "Step 3, Create Notification Body Text."; 
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

	$debugText = "<BR />Pass-1, Plain Text --> EmailBodyTmplate:<BR />";
	$debugText .= $this->actionRecArray['EmailBodyTmplate']; 
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
	$viewFromTemplate->set_template($this->actionRecArray['EmailBodyTmplate']);
	$viewFromTemplate->set_viewFormat('TEXT');
	$result = $viewFromTemplate->makeViewFromTemplate();
	$ntceBody = $viewFromTemplate->get_viewCreated();
	$emailNotice->appendBody($ntceBody, "TEXT");

	$debugText = "<BR />Pass-2, HTML Version --> EmailBodyTmplate:<BR />";
	$debugText .= $this->actionRecArray['EmailBodyTmplate']; 
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
	$viewFromTemplate->set_viewFormat('HTML');
	$result = $viewFromTemplate->makeViewFromTemplate();
	$ntceBody = $viewFromTemplate->get_viewCreated();
	$ntceBody = "<HTML><BODY>" . $ntceBody . "</HTML></BODY>";
	$emailNotice->appendBody($ntceBody, "HTML");
					
					//		Display consolidated debug info.
	$debugText = "Notice Data--: <BR />";
	$debugText .= "...<i>Subject</i>: ";
	$debugText .= $emailNotice->get_Subject();
	$debugText .= "<BR />...<i>TO</i>: ";
	$debugText .= $emailNotice->get_AddressList("TO");
	$debugText .= "<BR />...<i>CC</i>: ";
	$debugText .= "<BR />...<i>FROM</i>: ";
	$debugText .= $emailNotice->get_from();
	$debugText .= "<BR />...<i>BODY IN HTML FORMAT</i> --:<BR />";
	$debugText .= $emailNotice->get_Body("HTML");
	$debugText .= "<BR /><BR />...<i>BODY IN TEXT FORMAT</i> --:<BR />";
	$debugText .= "<TEXTAREA ROWS='10' COLS='100'>";
	$debugText .= $emailNotice->get_Body("TEXT");
	$debugText .= "</TEXTAREA>";
	$debugText .= "<BR />-----------------------------------------------";
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

					//		5: Send the email.
	$debugText = "handle_RsvpStatus():: "; 
	$debugText .= "Step 5, Send the email"; 
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
/*** RELEASE NOTE **************************************************************
	Turns out that either HTML or Plain Text works just fine on all
	devices. However, I can't get the multi-part/alternative version to work on
	the iPhone. The multi-part appears to work perfectly on all other email
	clients, including blackberry, webmail and my linux mail client.
*******************************************************************************/
	switch ($this->actionRecArray['EmailEncodeFormat'])
		{
		case 'HTML':
			$encTEXT = FALSE;
			$encHTML = TRUE;
			break;
		case 'TEXT':
			$encTEXT = TRUE;
			$encHTML = FALSE;
			break;
		default:
			$encHTML = TRUE;
			$encTEXT = TRUE;
			break;
		}
	$emailNotice->sendEmail($encHTML, $encTEXT);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	if(!$result) { return RTN_FAILURE; } else { return RTN_SUCCESS; }

	} // END METHOD





	private function handle_RsvpUpdateRequest()
	{
	/*	PURPOSE: Handle the processing for multiply emails to go out which
		request series participants to update their RSVP status.
		Each member has to get thier own unique email, which will contain
		a link that is specific to them. This individualized link will take
		them to a script that will (a) log them into the site and then (b)
		present them with the mobile view rsvp page where they can update
		their rsvps for all upcoming events.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
			D) Assumes all required specs have already been into 
				the object-instance variables.
		
		TAKES --:
		
			1) .
				
		RETURNS --:
			
		   1.1) RTN_SUCCESS. OR
		   1.2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Scratch variables.
	$debugText = "";
	$returnString = "";
	$ntceSubjectLine = "";
	$ntceBodyBaseTxt = "";
	$ntceBodyFinalTxt = "";
	$ntceBodyBaseHtm = "";
	$ntceBodyFinalHtm = "";
	$ntceTOline = "";
	$participantList = array();
	$indivMember = array();
	$memNameMark = "";
	$memName = "";
	$result = FALSE;
	$drivingID = 0;
	$hashKeyMark = "";
	
	$URLtokenToReplace = "|URLstring|";
	$URLpath = "";
	$queryString = "";
	$URLhtml = "";
	$rsvpEditPage = "tennis/editRSVPviaEmailMixedDbls.php";
	$runEnv = array();
	$host = "";
	
	$keyString = "";
	$viewFromTemplate = new viewFromTemplate();
	$emailNotice = new emailNotice();
	$rsvpUpdateFunctions = new rsvpUpdateViaEmailLink();


					//		Initilization ---------------------------------------------
	$result = FALSE;
	$drivingID = $this->actionRecArray['TrggrObjID'];
	$hashKeyMark = $viewFromTemplate->get_hashKeyMark();
	$memNameMark = $viewFromTemplate->get_memberNameMark();

	$debugText .= "...hashKeyMark: " . $hashKeyMark;
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

					//		Logic------------------------------------------------------

					//		1: Set Subject Text.
	$debugText = "Step 1, Set Subject Text"; 
	$ntceSubjectLine = $this->actionRecArray['EmailSubject'];
	$debugText .= "<BR />...Email Subject Line: " . $ntceSubjectLine;
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

					//		2: participant list, with email addresses.
	$debugText = "Step 2, Get Participant List with Emails."; 
					//		Decode the 'ToGroup' value to determine what set of folks
					//to send the notice to.
	$emptyRecordset = false;
	switch ($this->actionRecArray['ToGroup']) {
		case 10: // This is invalid in this context.
			$addresses = $this->actionRecArray['ToAddresses'];
			$emailNotice->appendToList($addresses, $participantList);
			break;
		case 30: //30-series is for series based lists.
			$emailNotice->genToArray(OBJSERIES, $drivingID , "ALL", $participantList);
			break;
		case 40: //Let's make the 40-series for Events.
			$emailNotice->genToArray(OBJEVENT, $drivingID , "ALL", $participantList);
			break;
		case 41: //Those who have not yet RSVP'd to the $drivingID event.
			$emailNotice->genToArray(OBJEVENT, $drivingID , "NORSVP", $participantList);
			break;
		case 42: //Those who have RSVP'd as Tentative to the $drivingID event.
			$emailNotice->genToArray(OBJEVENT, $drivingID , "TENT", $participantList);
			break;
		case 43: //Those who are either no RSVP yet OR Tentative.
			$emailNotice->genToArray(OBJEVENT, $drivingID , "NORSVP+TENT", $participantList);
			break;
		default:
			$addresses = "jroc@activeage.com";
			$emailNotice->appendToList($addresses, $participantList);
	}
	
						//   Now determine if we have any addresses in the requested set to send to.
		if (!is_array($participantList) OR count($participantList) == 0) $emptyRecordset = true;
	
	if ($objDebug->DEBUG) {
		$objDebug->writeDebug($debugText);
		if ($emptyRecordset) {
			$objDebug->writeDebug("Specified sub-set to send to is empty.");
		}
		else {
			$tmp = count($participantList);
			$objDebug->writeDebug("Emails to Send: {$tmp}");
			$i = 1;
			foreach($participantList as $key => $value) {
				$debugText = $objDebug->displayDBRecord($participantList[$i], FALSE);
				$objDebug->writeDebug($debugText);
				$i++;
			}
		}
	}

					//		3: Using emailbodytemplate, create the 'base' email
					//body text.
	$debugText = "Step 3, Create Body Text Base."; 
	$debugText .= "<BR />... ...hashKeyMark: " . $hashKeyMark;
	$debugText .= "<BR />...EmailBodyTmplate:<BR />";
	$debugText .= "<BR />... ..." . $this->actionRecArray['EmailBodyTmplate']; 
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

	$debugText = "<BR /><BR />...HTML Version.";
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);
	$ntceBodyBaseHtm = $this->actionRecArray['EmailBodyTmplate'];

					//   In the template text there is a token that represents the
					//URL to the rsvp edit page the link needs to take you to.
					//This token needs to now be replaced with the proper URL,
					//based on the server we are running on. The resulting URL
					//string will, however, have two other tokens within it, one
					//for the user/object/ID 'x' hashkey in the queryString and
					//one that represents the user's name. These two tokens get
					//replaced as each individual email is generated and sent out.
	$runEnv = Session_ServerHost();
	$host = $runEnv['Host']; //Will already be prepended with "http://"
	$URLpath = $host . "/" . $rsvpEditPage;
	$queryString = "x={$hashKeyMark}";
	$URLhtml = "<A HREF=\"{$URLpath}?{$queryString}\">";
	$URLhtml .= "CLICK to Update RSVPs for {$memNameMark}";
	$URLhtml .= "</A>";
	$tempTxt = str_replace($URLtokenToReplace, $URLhtml, $ntceBodyBaseHtm);
	$ntceBodyBaseHtm = $tempTxt;

	$debugText = "<BR /><BR />...<i>BODY BASE IN HTML FORMAT</i> --:<BR /><BR />";
	$debugText .= $ntceBodyBaseHtm;
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

	$ntceBodyBaseHtm = "<HTML><BODY>" . $ntceBodyBaseHtm . "</HTML></BODY>";
	

					//		4: Set the email format.
	$debugText = "Step 4, Set the email format"; 
	switch ($this->actionRecArray['EmailEncodeFormat'])
		{
		case 'HTML':
			$encTEXT = FALSE;
			$encHTML = TRUE;
			break;
		case 'TEXT':
			$encTEXT = TRUE;
			$encHTML = FALSE;
			break;
		default:
			$encHTML = TRUE;
			$encTEXT = TRUE;
			break;
		}
	$debugText .= "...Email Format Settings:"; 
	$debugText .= "<BR />... ...encHTML:" . $encHTML; 
	$debugText .= "<BR />... ...encHTML:" . $encTEXT; 
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);


					//		5: Iterate over the $participantList array to send 
					//the emails.
	if (!$emptyRecordset) {
		$debugText = "Step 5, Iterate over array participantList and send the emails"; 
		$debugText .= "<BR /><BR />...Sending Emails - seriedID | memID# | Email Count | Emails || Email Body>";
		if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

		$i = 1;
		foreach($participantList as $key => $value) {
			$debugText = "... ..." . $drivingID;
			$debugText .= " | " . $participantList[$i]['UserRecID'];
			$debugText .= " | " . $participantList[$i]['NumEmailsForTo'];
			$debugText .= " | " . $participantList[$i]['EmailsForTo'];
			$debugText .= "<BR />";
			if ($objDebug->DEBUG) $objDebug->writeDebug($debugText); $debugText="";
			if ($participantList[$i]['NumEmailsForTo'] > 0) {
				$emailNotice->resetObject();
				$memID = (string)$participantList[$i]['UserRecID'];
				$result = Tennis_GetSingleRecord($indivMember, 'person', $memID);
				$memName = $indivMember['FName'] . " " . $indivMember['LName'];
				$keyString = $rsvpUpdateFunctions->loginKey_Create($memID, OBJEVENT, $drivingID);
				$tempTxt = str_replace($hashKeyMark, $keyString, $ntceBodyBaseTxt);
				$tempTxt = str_replace($memNameMark, $memName, $tempTxt);
				$tempHtm = str_replace($hashKeyMark, $keyString, $ntceBodyBaseHtm);
				$tempHtm = str_replace($memNameMark, $memName, $tempHtm);
				$emailNotice->set_Subject($ntceSubjectLine);
				$emailNotice->appendToList($participantList[$i]['EmailsForTo'], "TO");
				$emailNotice->appendBody($tempTxt, "TEXT");
				$emailNotice->appendBody($tempHtm, "HTML");
				$emailNotice->sendEmail($encHTML, $encTEXT);
				$debugText .= "<BR />HTML Form of Email Message: <BR />{$tempHtm}";
				$debugText .= "<BR />";
				if ($objDebug->DEBUG) $objDebug->writeDebug($debugText); $debugText="";
			}
			$i++;
		}
	}
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	$result = TRUE;
	if(!$result) { return RTN_FAILURE; } else { return RTN_SUCCESS; }

	} // END METHOD


} // END CLASS




?>
