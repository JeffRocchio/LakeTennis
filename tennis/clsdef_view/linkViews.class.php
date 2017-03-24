 <?php
/*
	=======================
	CLASS: linkViews.
	=======================

	PURPOSE: Provide services to produce and format displayable view chunks 
	or snippits or blocks that are URL links.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) This class is intimately related to the link class. The link
		class provides the 'data' for the links. This class provides a
		variety of formatting services.
	
	02/12/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class linkViews
{

		
	//---GET/SET Functions-------------------------------------------------------
	public function setSpec_fldkeyName($value) {
	$this->fldkeyName = $value; return $this->fldkeyName; }




	//---------------------------------------------------------------------------
	public function makeList4PlainText(&$urlArrayList, $adminFlg=0)
	{
	/*	PURPOSE: Generate and return a displayable list of the URL links for
		use as 'plain text.' The text string returned will be tagged with
		HTML, but the title will not be in <A HREF=''...> form. For true
		plain text you'll need to run the returned string through 
		the Html2Text object.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Pointer to array of link items. This array is assumed to have
				been created by a call to link::getURLs...AsArray().
			2) Admin Flag. To control showing links meant for admin users.
				If the link's admin flag is less-than-or-equal-to the value
				passed in, then it will be shown. Otherwise, not. So, e.g., to
				show all links, including those meant for admins, pass in the
				value 2.
				
		RETURNS --:
			
		   1) A String suitable for display.
		   2) "" if an error has occurred.
	
	*/
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------

					//		Scratch variables.
	$returnString = "";

					//		Logic------------------------------------------------------
	foreach ($urlArrayList as $rowKey => $rowArray)
		{
		if(($rowArray['Admin']<=$adminFlg) && ($rowArray['Admin']>=0))
			{
			$returnString .= "&nbsp;&nbsp;&nbsp;* ";
			$returnString .= $rowArray['Title'];
			$returnString .= ":<BR />";
			$returnString .= "[";
			$returnString .= $rowArray['URL'];
			$returnString .= "]";
			$returnString .= "<BR /><BR />";
			}
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD




	//---------------------------------------------------------------------------
	public function makeList4Html(&$urlArrayList, $adminFlg=0)
	{
	/*	PURPOSE: Generate and return a displayable list of the URL links for
		use in an HTML display. Meaning that the link title will be
		displayed in <A HREF=''...> form.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Pointer to array of link items. This array is assumed to have
				been created by a call to link::getURLs...AsArray().
			2) Admin Flag. To control showing links meant for admin users.
				If the link's admin flag is less-than-or-equal-to the value
				passed in, then it will be shown. Otherwise, not. So, e.g., to
				show all links, including those meant for admins, pass in the
				value 2.
				
		RETURNS --:
			
		   1) A String suitable for display.
		   2) "" if an error has occurred.
	
	*/
	global $objError;
	global $objDebug;

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------

					//		Scratch variables.
	$returnString = "";

					//		Logic------------------------------------------------------
	foreach ($urlArrayList as $rowKey => $rowArray)
		{
		if(($rowArray['Admin']<=$adminFlg) && ($rowArray['Admin']>=0))
			{
			$returnString .= "&nbsp;&nbsp;&nbsp;* ";
			$returnString .= "<A HREF='{$rowArray['URL']}'>";
			$returnString .= $rowArray['Title'];
			$returnString .= "</A>";
			$returnString .= "<BR />";
			}
		}

	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD






} // END CLASS event

?>
