<?php

/*
	Contains often-used constants.
	
*/

//Page URLs:
define("SCRPT_RSVPLOGINUPDATE", "tennis/editRSVPviaEmail.php");



define("LF", "\n");
define("NBSP", "&nbsp;");
$CRLF = "\n";

//OBJECT IDs:

define("OBJCLUB", 55);
define("OBJSERIES", 42);
define("OBJEVENT", 43);
define("OBJMETRIC", 44);
define("OBJRSVP", 45);
define("OBJPERSON", 56);
define("OBJAUTOACTION", 61);

//Standard Set of Function Return Status Codes:
define("RTN_FAILURE", 0);
define("RTN_SUCCESS", -1);
define("RTN_NOACTION", -2);
define("RTN_WARNING", -3);
define("RTN_EOF", -4);


//For email related functions:
define("EMAILCRLF", "\r\n");


//For use with the ERROR object:
define("ERRSEV_NOTICE", 1);
define("ERRSEV_WARNING", 2);
define("ERRSEV_ERROR", 3);
define("ERRSEV_FATAL", 4);
 
define("ERRCLASS_DBCNNCT", "001 Unable to Connect to Database.");
define("ERRCLASS_DBOPEN", "002 Unable to Open Requested View or Table.");
define("ERRCLASS_OBJDATA", "011 Object Property(s) Have Invalid Data for Requested Action.");
define("ERRCLASS_EMAILSEND", "021 Problem Attempting to Send Email.");
define("ERRCLASS_NOTAUTH", "035 User is not authorized for the requested action.");
define("ERRCLASS_OTHER", "901 Undefined Error Reported by PHP.");


//autoAction Types - this is CodeSet #14:

define("AACT_ROLLDATES", 61); //ROLLD
define("AACT_SENDRSVPREQUEST", 62); //RNOTE
define("AACT_SENDRSVPSTAT", 63); //SNOTE


//Event Types - this is CodeSet #02:
//Although, it appears that in most, or perhaps all cases,
//where I use this CodeSet, I am actually returning the 
//CodeSet's Short Name field value, and not the Code ID#.
//So this should be investigated to see if this is always 
//the case; and then I can decide what to do about the 
//use of these constants. I only added this constant 
//set on 2/20/2017 and have not actually used these
//anywhere as of yet.
define("EVTYPE_PRACTICE", 5);
define("EVTYPE_RECPLAY", 6);
define("EVTYPE_RECFULLCOURTS", 65);
define("EVTYPE_SOCIALPLAY", 7);
define("EVTYPE_MEETING", 8);
define("EVTYPE_PARTY", 9);
define("EVTYPE_MATCH", 17);


//RSVP ClaimCodes - this is CodeSet #03:
//Note that the last part of these match exactly the 
//'ShtName' fieLd in the Code table. E.g., 'NORES.'
define("RSVP_CLAIM_NORES", 10);
define("RSVP_CLAIM_NOTAV", 11);
define("RSVP_CLAIM_LATE", 13);
define("RSVP_CLAIM_TENT", 14);
define("RSVP_CLAIM_AVAIL", 15);
define("RSVP_CLAIM_CNFRM", 16);


//RSVP ClaimCodes - this is CodeSet #05:
//Note that the last part of these match exactly the 
//'ShtName' fieLd in the Code table. E.g., 'S1.'
define("RSVP_POS_S1", 22);
define("RSVP_POS_S2", 23);
define("RSVP_POS_D1", 24);
define("RSVP_POS_D2", 25);
define("RSVP_POS_D3", 26);
define("RSVP_POS_BACKU", 27);
define("RSVP_POS_TBD", 28);
define("RSVP_POS_P", 29);
define("RSVP_POS_NP", 30);


//EventDisplay Types - this is CodeSet #06:
define("EVDISP_NORMAL", 31);
define("EVDISP_NORMALWITHNOTES", 64);
define("EVDISP_ALWAYS", 33);
define("EVDISP_NEVER", 32);



?>
