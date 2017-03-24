 <?php
/*
	=======================
	CLASS: txtBlockViewRequests.
	=======================

	PURPOSE: To provide methods for responding to requests for views related to
	text blocks.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:
	
			1) .

	03/24/2012:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class txtBlockViewRequests
{

		
	//---GET/SET Functions-------------------------------------------------------


	//---------------------------------------------------------------------------
	public function getBlockText($blockID, &$returnString, $lfBefore, $lfAfter, $useOnce=FALSE, $mustBeActive=TRUE)
	{
	/*	PURPOSE: Get the text of the specified text block.

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
		
		TAKES --:
		
			1) Block ID.
			2) Pointer to string where the result will be returned.
			3) $lfBefore: The number of line-feeds to insert before the text block
				if we have an active block. IF INACTIVE then we insert none.
			4)	$lfAfter: Same as $lfBefore, except for at the end of the block.
			5)	$useOnce: If set to TRUE then we will return the text and
				immediately set the text block into INACTIVE status.
			6) $mustBeActive: If TRUE then the text block must be 'active';
				meaning that it's status is set to 'Active' and that now() falls
				within the block's effective start and end dates.
				
		RETURNS --:
			
		   1) The string to display from the requested text block.
		   	1.1)	The returned string may be empty if the parameter
		   			"$mustBeActive" is =TRUE and the text block is not
		   			currently active (as defined by a call to the
		   			determineIfActive() function). This is NOT a failure
		   			condition.
		   2) RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
	global $objError;
	global $objDebug;
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

					//		Initilization ---------------------------------------------
	$returnString = "";
	$active  = TRUE;

					//		Scratch variables.
	$dbmsRow = array();

					//		Logic------------------------------------------------------
	$txtBlock = new txtBlock();
	$dbmsRow = $txtBlock->fetch_Record_byID($blockID);
	$active  = TRUE;
	if($mustBeActive) $active = $txtBlock->determineIfActive($dbmsRow);
	if($active)
		{
		$returnString = str_repeat("<BR />",$lfBefore);
		$returnString .= $dbmsRow['BlockText'];
		$returnString .= str_repeat("<BR />",$lfAfter);
		}
	if($useOnce)
		{
		$txtBlock->update_BlockStatus($blockID, FALSE);
		}
	
	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
	return $returnString;

	} // END METHOD



} // END CLASS event


?>
