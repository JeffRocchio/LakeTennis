 <?php
/*
	=======================
	CLASS: viewFromTemplate.
	=======================

	PURPOSE: Abstracts a view "template" concept. Provide methods for 
	parsing an input template, in the form of a string, into an output view.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) Created specifically for the automated events system. To take
		in a string that contains 'templates' embedded within it and output
		an email body used to send rsvp requests and status.
		
			2)	This class uses regular expressions to parse the templates.

	02/05/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================



class viewFromTemplate
{

	protected $template = "";

					//		Used to set the display format for the view. Can be
					//either HTML or TEXT. TEXT=make a view suitable to be run
					//through the Html2text class for display as plain-text (as,
					//for example, in an email).
	protected $viewFormat = "HTML";
	
	protected $viewCreated = "";
	
					//		Strings which demark the start/end of an embedded
					//dynamic content section within the template.
	private $tpMarkStart = "|%DCbegin";
	private $tpMarkEnd = "DCend%|";

					//		String which demarks where a member ID# (the db record#,
					//not the text user ID) is to go within the rsvpUpdatURL 
					//produced dynamic template. See function EXP_rsvpUpdateURL()
	private $memberIDMark = "|memRecID|";
					//		String which demarks where a member name is to go within 
					//a person-specific dynamic template. 
					//See function EXP_rsvpUpdateURL()
	private $memberNameMark = "|memName|";
					//		String which demarks where a hash-key is to go within 
					//the rsvpUpdatURL produced dynamic template. 
					//See function EXP_rsvpUpdateURL()
	private $hashKeyMark = "|hashkey|";
	
					//		This array contains the regular expressions to match on.
					//Meaning that they define the available set of dynamic content
					//that can be included in a template.
	private $regexArr = array(
			'#\|%DCbegin (rsvpstat) (\b[0-9]{1,4}\b) DCend%\|#',
			
			'#\|%DCbegin (rsvpstatoneevent) (\b[0-9]{1,4}\b) (\bNamePublic|NameLastFirst|NameFirstLast\b) (\bNA\b) DCend%\|#', 
					//2nd field is event ID. 3rd field specifies a name listing order. 4th field is a format specifier, tho not yet implemented.
					
			'#\|%DCbegin (rsvpbring) (\b[0-9]{1,4}\b) DCend%\|#',
			'#\|%DCbegin (rsvpbringing) (\b[0-9]{1,4}\b) DCend%\|#',
			'#\|%DCbegin (links) (\b[a-z,A-Z]\b) (\b[0-9]{1,4}\b) DCend%\|#',
			'#\|%DCbegin (textblock) (\b[0-9]{1,4}\b) (\b[0-9]{1}\b) (\b[0-9]{1}\b) (\btrue|TRUE|false|FALSE\b) DCend%\|#',
			'#\|%DCbegin (upcomingevents) (\b[0-9]{1,4}\b) DCend%\|#',
			'#\|%DCbegin (ITERATE_rsvpUpdateURL) (\b[0-9]{1,4}\b) DCend%\|#',
			'#\|%DCbegin (sigseriesadmin) (\b[0-9]{1,4}\b) DCend%\|#'
			);


	//---GET/SET Functions-------------------------------------------------------
	public function set_template($value) {
	$this->template = $value; return $this->template; }

	public function get_template() {
	return $this->template; }

	public function set_viewFormat($value) {
	$this->viewFormat = $value; return $this->viewFormat; }

	public function get_viewCreated() {
	return $this->viewCreated; }

	public function get_memberIDMark() {
	return $this->memberIDMark; }

	public function get_hashKeyMark() {
	return $this->hashKeyMark; }

	public function get_memberNameMark() {
	return $this->memberNameMark; }

	//---------------------------------------------------------------------------
	public function makeViewFromTemplate()
	{
	/*	PURPOSE: Take in a template that represents a view (e.g., the body
		of an email notification) and generate from the template the
		actual view.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C)	The template string has been set into this classes' properties.
		
		TAKES --:
		
			1) Template string must have been set into the object prior to
				call to this function.
			2) Pointer to string where the result will be returned.
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
		   2) The $returnString will contain an HTML formatted view.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$numReplacements = 0;
	$replacedString = "";

					//		Logic------------------------------------------------------
					
					//		It's simple - just let PHP parse the template and
					//use a callback function to replace/expand each dynamic
					//content section into it's current displayable sub-string.
					//		NOTE the use of array(&$this, 'replaceTemplates') to
					//specify the callback function. Has to be done this way
					//because we are calling into a function within this class.
					//See: https://bugs.php.net/bug.php?id=11085&edit=1
	$replacedString = preg_replace_callback(
					$this->regexArr, 
					array(&$this, 'replaceTemplates'), 
					$this->template, 
					-1, 
					$numReplacements
					);
	$this->viewCreated = $replacedString;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD




	//===========================================================================
	//	INTERNAL PRIVATE FUNCTIONS
	//===========================================================================

	//---------------------------------------------------------------------------
	private function replaceTemplates($matches)
	{
		/*	PURPOSE: This is the call-back function for the regular expression
			parsing function preg_replace_callback(). It serves to extract each
			dynamic content section from the input string and replace it with
			the expanded displayable current actual content for that section.

			ASSUMES:
				A) Global error object has been declared.
		
			TAKES --:
		
				1) .
				
			RETURNS --:
			
				1) A string that is used to replace the specified dynamic content
					section with the current display string.
	
			NOTES --:

					1) This function (a) identifies the incoming dynamic content
				section and then (b) calls a different function that will then
				generate the actual display content applicable to that section.
	
		*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$replaceWith = "";
	$debugTxt = "";

					//		Logic------------------------------------------------------
					
	if ($objDebug->DEBUG)
		{
		$debugTxt .= "The <i>matches</i> Array as Passed In:<BR />";
		foreach ($matches as $key => $value)
			{
			$debugTxt .= "...KEY:{$key} -> VALUE: {$value}<BR />";
			}
		$objDebug->writeDebug($debugTxt);
		}

	switch ($matches[1])
		{
		case 'rsvpstat':
			$replaceWith = "";
			$this->EXP_rsvpstat($replaceWith, $matches[2]);
			break;

		case 'rsvpstatoneevent':
			$replaceWith = "";
			$this->EXP_rsvpstatoneevent($replaceWith, $matches[2], $matches[3], $matches[4]);
			break;

		case 'rsvpbringing':
			$replaceWith = "";
			$this->EXP_rsvpBringing($replaceWith, $matches[2]);
			break;

		case 'links':
			$replaceWith = "";
			$this->EXP_links($replaceWith,$matches[2],$matches[3]);
			break;

		case 'textblock':
			$replaceWith = "";
			$this->EXP_textblock($replaceWith,$matches[2],$matches[3],$matches[4],$matches[5]);
			break;

		case 'upcomingevents':
			$replaceWith = "";
			$this->EXP_upcomingEvents($replaceWith,$matches[2]);
			break;


		case 'ITERATE_rsvpUpdateURL':
			$replaceWith = "";
			$this->EXP_rsvpUpdateURL($replaceWith,$matches[2]);
			break;

		case 'sigseriesadmin':
			$replaceWith = "";
			$this->EXP_sigseriesadmin($replaceWith,$matches[2]);
			break;

		default:
			$replaceWith = "";
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $replaceWith;
	} // END FUNCTION



	//---------------------------------------------------------------------------
	private function EXP_rsvpstat(&$replaceWith, $seriesID)
	{
	/*	PURPOSE: Expand/replace a dynamic content section.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Pointer to string variable which will contain the output from
				this function.
			2) ID# of series to get RSVPs for.
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$rsvpView = new eventViewRequests;
	$returnString = "";


					//		Logic------------------------------------------------------
	$replaceWith = "RSVP Status: No Status Available.";
	$rsvpView->getPlayingStatus4Series($seriesID, $returnString,'UPCOMING');
	$replaceWith = $returnString;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD




	//---------------------------------------------------------------------------
	private function EXP_rsvpstatoneevent(&$replaceWith, $eventID, $order, $format)
	{
	/*	PURPOSE: Expand/replace a dynamic content section.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Pointer to string variable which will contain the output from
				this function.
			2) ID# of event to get RSVPs for.
			3) A listing order specifier. Used to, for example, specify that the
				list of rsvp names be shown in last-name / first-name order. Or
				some other valid order. Valid order strings are determined by the
				switch statement in eventViewChunks->getRSVPstatString().
			4) A format specifier (not yet implemented tho, so it will just contain
				'NA' until it is implemented).
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$rsvpView = new eventViewRequests;
	$returnString = "";


					//		Logic------------------------------------------------------
	$replaceWith = "RSVP Status: No Status Available.";
	$rsvpView->getPlayingStatus4Event($eventID, $returnString, $order, $format);
	$replaceWith = $returnString;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD




	//---------------------------------------------------------------------------
	private function EXP_rsvpBringing(&$replaceWith, $eventID)
	{
	/*	PURPOSE: Expand/replace a dynamic content section. In this case, a
					section that will list out everything folks are bringing to an
					event.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Pointer to string variable which will contain the output from
				this function.
			2) ID# of event to get RSVPs for.
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$rsvpView = new eventViewRequests;
	$returnString = "";


					//		Logic------------------------------------------------------
	$replaceWith = "&nbsp;&nbsp;&nbsp;*&nbsp;Nobody is bringing anything at this time.";
	$rsvpView->getBringingList4Event($eventID, $returnString);
	if (strlen($returnString) > 0) {
		$replaceWith = $returnString;
	}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD




	//---------------------------------------------------------------------------
	private function EXP_links(&$replaceWith, $linkSet, $ID)
	{
	/*	PURPOSE: Expand/replace a dynamic content section.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Pointer to string variable which will contain the output from
				this function.
			2) $linkSet: What set of links to generate.
			3) $ID: Appropriate ID# that matches the $linkSet (e.g., SeriesID).
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
	
		NOTES --:

				1) Remember, when we are running in CRON, we are not running 
			on the web server. So pre-defined variables coming from the web 
			server are not available.

	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
	$linkSetUPPER = strtoupper($linkSet);
	$RunningInCron = !isset($_SERVER['HTTP_HOST']);
	if ($RunningInCron)
		{
		$serverTxt = "http://laketennis.com";
		}
	else
		{
		$serverTxt = "http://" . $_SERVER['HTTP_HOST'];
		}

					//		Scratch variables.
	$returnString = "";
	$urlList = array();
	$linkObject = new link();
	$linkViews = new linkViews();


					//		Logic------------------------------------------------------
	$replaceWith = "";
	switch ($linkSetUPPER)
		{
		case 'C':	//Links for a club.
			$returnString = "Links for Club ID {$ID}.";
			$returnString .= " This function is not yet implemented.";
			break;

		case 'S':	//Links for a Series, do NOT include any Admin links.
			$urlList = $linkObject->getURLs4SeriesAsArray($ID);
			if ($objDebug->DEBUG) $objDebug->writeDebug("Link Format: {$this->viewFormat}");
			if($this->viewFormat == 'HTML')
				{
				$returnString = $linkViews->makeList4Html($urlList, 0);
				}
			else
				{
				$returnString = $linkViews->makeList4PlainText($urlList, 0);
				}
			break;

		case 'E':	//Links for an event.
			$returnString = "Request is for Links for Event ID {$ID}.";
			$returnString .= " This function is not yet implemented.";
			break;

		default:	//Warning Condition.
			$returnString = "Invalid Link-Set Specified in the Template's Parameters.";
		}
	$replaceWith = $returnString;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD



	//---------------------------------------------------------------------------
	public function EXP_textblock(&$replaceWith, $ID, $lfBefore, $lfAfter, $useOnce=FALSE)
	{
	/*	PURPOSE: Expand/replace a dynamic content section.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:		
			1) Pointer to string variable which will contain the output from
				this function.
			2)	$ID: Record # of the text block.
			3) $lfBefore: The number of line-feeds to insert before the text block
				if we have an active block. IF INACTIVE then we insert none.
			4)	$lfAfter: Same as $lfBefore, except for at the end of the block.
			5)	$useOnce: If TRUE, then we want to use the txtBlock only one time.
				So when we are done mark it "Inactive."
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
	$replaceWith = "";
	$txtBlockView = new txtBlockViewRequests();

					//		Scratch variables.
	$returnString = "";

					//		Logic------------------------------------------------------
	$txtBlockView->getBlockText($ID, $returnString, $lfBefore, $lfAfter, $useOnce, $mustBeActive=TRUE);
	$replaceWith = $returnString;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD



	//---------------------------------------------------------------------------
	private function EXP_upcomingEvents(&$replaceWith, $seriesID)
	{
	/*	PURPOSE: Expand/replace a dynamic content section. In this case
					create text that shows info on the upcoming events for
					a series.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Pointer to string variable which will contain the output from
				this function.
			2) ID# of series to get RSVPs for.
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$rsvpView = new eventViewRequests;
	$returnString = "";


					//		Logic------------------------------------------------------
	$replaceWith = "There are no current and active events.";
	$rsvpView->getEventHeaders4Series($seriesID, $returnString,'UPCOMING');
	$replaceWith = $returnString;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD




	//---------------------------------------------------------------------------
	private function EXP_rsvpUpdateURL(&$replaceWith, $seriesID)
	{
	/*	PURPOSE: Expand/replace a dynamic content section. In this case we
					are generating a sort of sub-template. We are creating a
					URL to a page where the email receipent can update their
					rsvp status. This will be used to send out a set of 
					individualized email notices that request each user to
					click the link and post their rsvp updates. So each email
					sent will have an individualized URL (the query string part
					of the URL) that the script will use to generate the edit
					form for that specific user. SO, the output of this
					function is that URL string, but with a sub-string embedded
					within it that the higher-level controller which is generating
					each individual email will use to replace that sub-string
					marker with the individual member's query string parameter
					(e.g., the member's ID record #).

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Pointer to string variable which will contain the output from
				this function.
			2) ID# of series to get RSVPs for.
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	$rsvpView = new eventViewRequests;
	$returnString = "";
	$runEnv = array();
	$rowSeries = array();
	$rsvpEditPage = "";


					//		Logic------------------------------------------------------

	$replaceWith = "There are no current and active events.";
					//		Be sure to use session function to get host name because
					//it accounts for case where we are running in cron, and so
					//the php environment in that case doesn't know the host name.
	$runEnv = Session_ServerHost();
	$host = $runEnv['Host']; //Will already be prepended with "http://"
	$URLpath = $host . "/" . SCRPT_RSVPLOGINUPDATE;
	$queryString = "x={$this->hashKeyMark}";
	$URLhtml = "<A HREF=\"{$URLpath}?{$queryString}\">";
	$URLhtml .= "CLICK to Update RSVPs for {$this->memberNameMark}";
	$URLhtml .= "</A>";
	$replaceWith = $URLhtml;

	$debugText = "STRING VALUES:<BR .>";
	$debugText .= "...host: {$host}<BR />";
	$debugText .= "...URLpath: {$URLpath}<BR />";
	$debugText .= "...queryString: {$queryString}<BR />";
	$debugText .= "...URLhtml: " . $URLhtml;
	if ($objDebug->DEBUG) $objDebug->writeDebug($debugText);

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD




	//---------------------------------------------------------------------------
	private function EXP_sigseriesadmin(&$replaceWith, $ID)
	{
	/*	PURPOSE: Expand/replace a dynamic content section.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Pointer to string variable which will contain the output from
				this function.
			2)	$ID: Record ID of the txtBlock record to insert.
				
		RETURNS --:
			
		   1) RTN_SUCCESS if success, RTN_FAILURE if error.
	
		NOTES --:

				1) The $useOnce function is not yet implemented.
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");


					//		Initilization ---------------------------------------------
					//		Scratch variables.
	


					//		Logic------------------------------------------------------
	$replaceWith = "Series Admin Signature.";

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return RTN_SUCCESS;

	} // END METHOD



} // END CLASS event


?>
