 <?php
/*
	=======================
	CLASS: link.
	=======================

	PURPOSE: Abstracts URL links as if they were a db table.
	The idea here is to provide services to get data on links that we can
	use to create various view-chunks of links.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
	02/12/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class link
{

		//		The array which will contain the list of links.
	protected $urlList = array();
	protected $urlListIndex = -1;
		

	//---GET/SET Functions-------------------------------------------------------
	public function get_urlList() {
	return $this->urlList; }


	//---------------------------------------------------------------------------
	public function getURLs4SeriesAsArray($seriesID)
	{
	/*	PURPOSE: Return an array that contains a list of URL links relevant for
		use in the context of providing 'useful links' at the club level.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Series ID.
				
		RETURNS --:
			
		   1) A 2D Array. 
		   		(a)	Each row represents a URL or link. Each row then has a 
		   				set of columns:
		   		(b)	Col-1: ['Title'] = Title for link in plain-text.
		   		(c)	Col-2: ['URL'] = URL for the link.
		   		(d)	Col-3: ['HTML'] = HTML formatted display. Title in an
		   				<A HREF...> tag.
		   		(e)	Col-4: ['Admin'] = 0 if URL is considered for use by
		   				any regular user. =1 if for Series Admin users.
		   				=2 if for Club Admin users.
		   2) If some error has occurred, the row with key 'ERROR' will contain
		   	the value "RTN_FAILURE".
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$this->urlList['ERROR']['Title'] = "SUCCESS";
	$this->urlList['ERROR']['URL'] = "";
	$this->urlList['ERROR']['HTML'] = "";
	$this->urlList['ERROR']['Admin'] = RTN_SUCCESS;

					//		Scratch variables.
	$Result = FALSE;
	$dbRow = array();
	$runEnv = array();
	$series = new series();
	$server = "";
	$clubID = 0;
	$title = "";
	$url = "";
	$debugText = "";

					//		Logic------------------------------------------------------

					//		1: We need the web-server host URL path.
	$runEnv = Session_ServerHost();
	$server = $runEnv['Host'];

					//		2: We need the clubID# so that we can make the 'home'
					//link. Even for a list of series links, we do want to
					//include a link to the group home page.
	$dbRow = $series->getRecord4ID($seriesID);
	$clubID = $dbRow['ClubID'];

					//		2a: Got the clubID, now make the link to the home
					//page.
	$title = "HOME";
	$url = "{$server}/ClubHome.php?ID={$clubID}";
	$this->setArrayItem($title, $url, 0);
	if ($objDebug->DEBUG)
		{
		$debugText = "Home Page Entry in Links Array:<BR />";
		$debugText .= $objDebug->displayDBRecord($this->urlList[0], FALSE);
		$objDebug->writeDebug($debugText);
		}

	$title = "Schedule GRID";
	$url = "{$server}/tennis/listSeriesRoster.php?ID={$seriesID}";
	$this->setArrayItem($title, $url, 0);

	$title = "Series Details and Notes";
	$url = "{$server}/tennis/dispSeries.php?ID={$seriesID}";
	$this->setArrayItem($title, $url, 0);

	$title = "Mobile Phone View";
	$url = "{$server}/tennis/mobile/mlistSeriesRoster.php";
	$url .= "?ID={$seriesID}";
	$this->setArrayItem($title, $url, 0);

	$title = "Series Email Address List";
	$url = "{$server}/tennis/listEmails.php";
	$url .= "?OBJ=SERIES&ID={$seriesID}";
	$this->setArrayItem($title, $url, 0);
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $this->urlList;

	} // END METHOD





	//---------------------------------------------------------------------------
	private function setArrayItem($title, $url, $admin=0)
	{
	/*	PURPOSE: Sets one item of the array that contains the links.

		ASSUMES:
			A)	Nothing.
		
		TAKES --:
		
			1) The values to be set.
				
		RETURNS --:
			
		   1) Nothing. But classes' array property has been set with the
		   	new item.
	*/
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
					//		Scratch variables.

					//		Logic------------------------------------------------------
	$this->urlListIndex++;
	$this->urlList[$this->urlListIndex]['Title'] = $title;
	$this->urlList[$this->urlListIndex]['URL'] = $url;
	$this->urlList[$this->urlListIndex]['HTML'] = "<A HREF='{$url}'>{$title}</A>";
	$this->urlList[$this->urlListIndex]['Admin'] = $admin;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return;

	} // END METHOD



} // END CLASS event


?>
