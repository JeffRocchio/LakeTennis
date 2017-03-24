  <?php
/*
	=======================
	CLASS: recordset.
	=======================
	Include file that defines the recordset class as an extension of the
	class: database.

	PURPOSE: To provide an abstraction of an list of records in the MySQL 
	Database.

	POLICIES --:

			(a) Use the ERROR object for error handling. This object is
		declared in the INCL_GLOBALS include file, so should "automatically"
		be available for use in all main scripts and all classes and functions.

	NOTES --:

			1) Goal here is to provide a standard set of dbms functions for 
		opening a query, going to next record, etc.


	01/22/2011:	Initial creation as part of building the automated action
					system,

*/


//==============================================================================
//---CLASS DEFINITION
//==============================================================================

class recordset extends database
{

	//---------------------------------------------------------------------------
//	public function funcname()
//	{
	/*	PURPOSE: .

		ASSUMES:
			A)	Connection to DBMS is already open.
			B) Global error object has been declared.
			C) Global debug object has been declared.
		
		TAKES --:
		
				1) .
				
		RETURNS --:
			
		   1) RTN_SUCCESS if successful, RTN_FAILURE if an error has occurred.
	
		NOTES --:

				1) .
	
	*/
//	global $objError;
//	global $objDebug;
//	$errLastErrorKey = 0;
//	$qryResult = FALSE;
//	$queryName = "";
//	$debugTxt = "";


//	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="ENTRY");

//	if ($objDebug->DEBUG) $objDebug->writeDebug(__FUNCTION__, $type="EXIT");
//	return $qryResult;

//	} // END METHOD




} // END CLASS


?>
