 <?php
/*
	Include file for functions specific to the autoAction system.

*/


//		12/04/2011: Initial creation.




class AUTO_AutoAction
{
	/*	This class represents the two tables: AutoAction and AutoParams.
	*/

					// Properties
	public $DEBUG = FALSE;
	public $DebugTxtAvail = FALSE;
	public $DebugTxt = "";
	public $dbmsResource;
			
					// Methods

	public function test()
		{
		$this->DebugTxt = "In the AUTO_AutoAction Object. Value of DEBUG is: ";
		$this->DebugTxtAvail = TRUE;
		if($this->DEBUG) { $this->DebugTxt .= "TRUE"; } else { $this->DebugTxt .= "FALSE"; } 
		}


} // END CLASS AUTO_AutoAction








function AUTO_tbdA()
{
/*
	This function
	
	TAKES:
	
	ASSUMES:
	
	RETURNS:

*/

	$DEBUG = FALSE;
	$DEBUG = TRUE;
	
	global $CRLF;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;

	$thisFunction = __FUNCTION__;

	$statusMessage = "";

	if ($DEBUG) { echo "{$OpenPara}Entering Function: {$thisFunction}{$ClosePara}"; }


				//   (1) .
	if ($DEBUG) { echo "{$OpenPara}Begin Step 1.{$ClosePara}"; }



	return;
}

function AUTO_tbd()
{
/*
	This function
	
	TAKES:
	
	ASSUMES:
	
	RETURNS:

*/

	$DEBUG = FALSE;
	$DEBUG = TRUE;
	
	global $CRLF;
	global $LineFeed;
	global $OpenPara;
	global $ClosePara;
	global $nbSpace;

	$thisFunction = __FUNCTION__;

	$statusMessage = "";

	if ($DEBUG) { echo "{$OpenPara}Entering Function: {$thisFunction}{$ClosePara}"; }


				//   (1) .
	if ($DEBUG) { echo "{$OpenPara}Begin Step 1.{$ClosePara}"; }



	return;
}
?>
