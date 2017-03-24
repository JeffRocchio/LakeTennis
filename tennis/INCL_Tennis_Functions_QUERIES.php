<?php
/*
	This file is used to simulate queries in the database.
	
	I don't want to build queries in the actual database
	just yet, so I'm using this fake-out for now.
	
	When building a query it is important to have a field
	named 'ID.' This ID field needs to be thought of as the
	primary-key for the virtual-table the query represents.
	Several functions depend on having such a field exist
	in the query. See, e.g., Tennis_OpenViewGenericAuth().
*/


function query_qryGetQuery($qryKey)
{

	switch ($qryKey)
		{
		case 'qryEventDisp':
			$qry = "(SELECT ";
			$qry .= "Event.ID AS ID, ";
			$qry .= "Event.Purpose AS purposeID, ";
			$qry .= "Event.Series AS seriesID, ";
			$qry .= "Event.Venue AS venueID, ";
			$qry .= "Event.Name AS evtName, ";
			$qry .= "Event.Start AS evtStart, ";
			$qry .= "DAYOFWEEK(Event.Start) AS evtDayofWeek, ";
			$qry .= "Event.End AS evtEnd, ";
			$qry .= "Event.MakeUp AS evtMakeUp, ";
			$qry .= "Event.Results AS evtResults, ";
			$qry .= "Event.Notes AS evtNotes, ";
			$qry .= "Code_1.LongName AS purposeName, ";
			$qry .= "Code_2.LongName AS resultLgName, ";
			$qry .= "series.LongName AS seriesName, ";
			$qry .= "series.ClubID AS seriesClubID, ";
			$qry .= "series.ViewLevel AS seriesViewLevel, ";
			$qry .= "venue.ShtName AS venueShtName, ";
			$qry .= "venue.LongName AS venueName, ";
			$qry .= "venue.Location AS venueLoc, ";
			$qry .= "venue.Description AS venueDesc, ";
			$qry .= "venue.URL AS venueURL, ";
			$qry .= "venue.Notes AS venueNotes ";
			$qry .= "FROM Event, ";
			$qry .= "Code Code_1, ";
			$qry .= "Code Code_2, ";
			$qry .= "venue, ";
			$qry .= "series ";
			$qry .= "WHERE (Code_1.ID=Event.Purpose AND ";
			$qry .= "Code_2.ID=Event.ResultCode AND ";
			$qry .= "series.ID=Event.Series AND ";
			$qry .= "venue.ID=Event.Venue)) AS qryEventDisp";
			break;

		case 'qryPersonDisp':
			$qry = "(SELECT ";
			$qry .= "person.ID AS ID, ";
			$qry .= "person.ClubID AS ClubID, ";
			$qry .= "person.UserID AS UserID, ";
			$qry .= "person.FName AS FName, ";
			$qry .= "person.LName AS LName, ";
			$qry .= "CONCAT(person.FName,' ',person.LName) AS FullName, ";
			$qry .= "person.PName AS PName, ";
			$qry .= "person.Gender AS prsnGender, ";
			$qry .= "person.HighPriv AS HighPriv, ";
			$qry .= "person.Email1 AS Email1, ";
			$qry .= "person.Email2 AS Email2, ";
			$qry .= "person.Email3 AS Email3, ";
			$qry .= "person.Email1Active AS Email1Active, ";
			$qry .= "person.Email2Active AS Email2Active, ";
			$qry .= "person.Email3Active AS Email3Active, ";
			$qry .= "person.PhoneH AS PhoneH, ";
			$qry .= "person.PhoneC AS PhoneC, ";
			$qry .= "person.PhoneW AS PhoneW, ";
			$qry .= "person.USTANum AS USTANum, ";
			$qry .= "person.Note AS Note, ";
			$qry .= "person.Currency AS Currency, ";
			$qry .= "Code.LongName AS CurrencyLName, ";
			$qry .= "Code.ShtName AS CurrencySName ";
			$qry .= "FROM person, ";
			$qry .= "Code ";
			$qry .= "WHERE (Code.ID=person.Currency)";
			$qry .= " ) AS qryPersonDisp";
			break;
			
			
		case 'qryClubMembers':
			$qry = "(SELECT ";
			$qry .= "ClubMember.ID AS ID, ";
			$qry .= "ClubMember.Active AS Active, ";
			$qry .= "ClubMember.Note AS ClubNote, ";
			$qry .= "club.ID AS clubID, "; 
			$qry .= "club.ClubName AS clubName, ";
			$qry .= "club.Active AS clubActive, ";
			$qry .= "person.ID AS prsnID, "; 
			$qry .= "person.UserID AS UserID, ";
			$qry .= "person.HighPriv AS HighPriv, ";
			$qry .= "person.PName AS prsnPName, ";
			$qry .= "person.FName AS prsnFName, "; 
			$qry .= "person.LName AS prsnLName, "; 
			$qry .= "CONCAT(person.FName,' ',person.LName) AS FullName, ";
			$qry .= "person.Gender AS prsnGender, ";
			$qry .= "person.Email1 AS Email1, ";
			$qry .= "person.Email2 AS Email2, ";
			$qry .= "person.Email3 AS Email3, ";
			$qry .= "person.Email1Active AS Email1Active, ";
			$qry .= "person.Email2Active AS Email2Active, ";
			$qry .= "person.Email3Active AS Email3Active, ";
			$qry .= "person.PhoneH AS PhoneH, ";
			$qry .= "person.PhoneW AS PhoneW, ";
			$qry .= "person.PhoneC AS PhoneC, ";
			$qry .= "person.USTANum AS USTANum, ";
			$qry .= "person.Currency AS PrsnCurrency, ";
			$qry .= "person.Note AS PrsnNote ";
			$qry .= "FROM club,"; 
			$qry .= "person,"; 
			$qry .= "ClubMember ";
			$qry .= "WHERE (ClubMember.Person=person.ID AND ClubMember.Club=club.ID) ";
			$qry .= "ORDER BY prsnLName, prsnFName"; 
			$qry .= " ) AS qryClubMembers";
			break;



		case 'qrySeriesEvts':
			$qry = "(SELECT ";
			$qry .= "Event.ID AS evtID, ";
			$qry .= "venue.ID AS venueID, ";
			$qry .= "series.ID AS ID, ";
			$qry .= "Event.Name AS evtName, ";
			$qry .= "Event.Start AS evtStart, ";
			$qry .= "DAYOFWEEK(Event.Start) AS evtDayofWeek, ";
			$qry .= "Event.End AS evtEnd, ";
			$qry .= "Code.ShtName AS evtPurpose, ";
			$qry .= "Event.MakeUp AS evtMakeup, ";
			$qry .= "Event.ResultCode AS evtResultCode, ";
			$qry .= "Code_3.ShtName AS evtResultShtName, ";
			$qry .= "venue.ShtName AS venueShtName, ";
			$qry .= "venue.LongName AS venueName, ";
			$qry .= "series.ShtName AS seriesShtName, ";
			$qry .= "series.Type AS seriesTypeCode, ";
			$qry .= "series.ClubID AS ClubID ";
			$qry .= "FROM Code, ";
			$qry .= "Code Code_3, ";
			$qry .= "series, ";
			$qry .= "Event, ";
			$qry .= "venue ";
			$qry .= "WHERE (Code.ID=Event.Purpose AND ";
			$qry .= "series.ID=Event.Series AND ";
			$qry .= "venue.ID=Event.Venue) AND ";
			$qry .= "Code_3.ID=Event.ResultCode ";
			$qry .= "ORDER BY Event.Start) ";
			$qry .= "AS qrySeriesEvts";
			break;
			
		case 'qrySeriesRsvps':
			$qry = "(SELECT ";
			$qry .= "series.ID AS ID, ";
			$qry .= "series.ShtName AS seriesShtName, ";
			$qry .= "Event.Name AS evtName, ";
			$qry .= "Event.Start AS evtStart, ";
			$qry .= "Event.End AS evtEnd, ";
			$qry .= "Event.ID AS evtID, ";
			$qry .= "Event.ResultCode AS evtResultCode, ";
			$qry .= "Event.Display AS evtDisplay, ";
			$qry .= "rsvp.ID AS rsvpID, ";
			$qry .= "rsvp.ClaimCode AS rsvpClaimCode, ";
			$qry .= "rsvp.Position AS rsvpPositionCode, ";
			$qry .= "rsvp.Role AS rsvpRoleCode, ";
			$qry .= "rsvp.BringingTxt AS rsvpBringingTxt, ";
			$qry .= "rsvp.Note AS rsvpNote, ";
			$qry .= "rsvp.TSfsa AS rsvpTSfsa, ";
			$qry .= "rsvp.TSlru AS rsvpTSlru, ";
			$qry .= "person.ID AS prsnID, ";
			$qry .= "person.PName AS prsnPName, ";
			$qry .= "CONCAT(person.FName,' ',person.LName) AS prsnFullName, ";
			$qry .= "person.LName AS prsnLName, ";
			$qry .= "person.FName AS prsnFName, ";
			$qry .= "person.Gender AS prsnGender, ";
			$qry .= "person.PhoneH AS prsnPhoneH, ";
			$qry .= "person.PhoneC AS prsnPhoneC, ";
			$qry .= "person.PhoneW AS prsnPhoneW, ";
			$qry .= "Code.ShtName AS rsvpClaim, ";
			$qry .= "Code_1.ShtName AS rsvpPosition, ";
			$qry .= "Code_1.Sort AS rsvpPositionSort, ";
			$qry .= "Code_2.ShtName AS rsvpRole, ";
			$qry .= "Code_3.ShtName AS evtResultShtName ";
			$qry .= "FROM Code, ";
			$qry .= "rsvp, ";
			$qry .= "Event, ";
			$qry .= "Code Code_1, ";
			$qry .= "Code Code_2, ";
			$qry .= "Code Code_3, ";
			$qry .= "series, ";
			$qry .= "person ";
			$qry .= "WHERE (Code.ID=rsvp.ClaimCode AND ";
			$qry .= "Event.ID=rsvp.Event AND ";
			$qry .= "Code_1.ID=rsvp.Position AND ";
			$qry .= "Code_2.ID=rsvp.Role AND ";
			$qry .= "series.ID=Event.Series AND ";
			$qry .= "Code_3.ID=Event.ResultCode AND ";
			$qry .= "person.ID=rsvp.Person)) ";
			$qry .= "AS qrySeriesRsvps";
			break;

		case 'qryRsvp':
			$qry = "(SELECT ";
			$qry .= "rsvp.ID AS ID, ";
			$qry .= "rsvp.ClaimCode AS rsvpClaimCode, ";
			$qry .= "rsvp.Position AS rsvpPositionCode, ";
			$qry .= "rsvp.Role AS rsvpRoleCode, ";
			$qry .= "rsvp.BringingTxt AS rsvpBringingTxt, ";
			$qry .= "rsvp.Note AS rsvpNote, ";
			$qry .= "rsvp.TSfsa AS rsvpTSfsa, ";
			$qry .= "rsvp.TSlru AS rsvpTSlru, ";
			$qry .= "Code.ShtName AS rsvpClaim, ";
			$qry .= "Code_1.ShtName AS rsvpPosition, ";
			$qry .= "Code_2.ShtName AS rsvpRole, ";
			$qry .= "Event.Name AS evtName, ";
			$qry .= "Event.Start AS evtStart, ";
			$qry .= "Event.End AS evtEnd, ";
			$qry .= "Event.ID AS evtID, ";
			$qry .= "Event.Purpose AS evtPurposeCd, ";
			$qry .= "person.ID AS prsnID, ";
			$qry .= "person.PName AS prsnPName, ";
			$qry .= "CONCAT(person.FName,' ',person.LName) AS prsnFullName, ";
			$qry .= "person.LName AS prsnLName, ";
			$qry .= "person.FName AS prsnFName, ";
			$qry .= "person.Gender AS prsnGender, ";
			$qry .= "person.PhoneH AS prsnPhoneH, ";
			$qry .= "person.PhoneC AS prsnPhoneC, ";
			$qry .= "person.PhoneW AS prsnPhoneW, ";
			$qry .= "series.ShtName AS seriesShtName, ";
			$qry .= "series.ID AS seriesID, ";
			$qry .= "series.ViewLevel AS seriesViewLevel ";
			$qry .= "FROM Code, ";
			$qry .= "rsvp, ";
			$qry .= "Event, ";
			$qry .= "Code Code_1, ";
			$qry .= "Code Code_2, ";
			$qry .= "series, ";
			$qry .= "person ";
			$qry .= "WHERE (Code.ID=rsvp.ClaimCode AND ";
			$qry .= "Event.ID=rsvp.Event AND ";
			$qry .= "Code_1.ID=rsvp.Position AND ";
			$qry .= "Code_2.ID=rsvp.Role AND ";
			$qry .= "series.ID=Event.Series AND ";
			$qry .= "person.ID=rsvp.Person)) ";
			$qry .= "AS qryRsvp";
			break;
			
		  /* Same as qryRsvp but adds in the fields for the Bringing table. Once I am done cleaning up after the Mixed-Doubles custom scripts I can moved this query.*/
		case 'qryRsvpBringing':
			$qry = "(SELECT ";
			$qry .= "rsvp.ID AS ID, ";
			$qry .= "rsvp.ClaimCode AS rsvpClaimCode, ";
			$qry .= "rsvp.Position AS rsvpPositionCode, ";
			$qry .= "rsvp.BringingPreDef AS rsvpBringingPreDef, ";
			$qry .= "rsvp.BringingTxt AS rsvpBringingTxt, ";
			$qry .= "rsvp.Role AS rsvpRoleCode, ";
			$qry .= "rsvp.Note AS rsvpNote, ";
			$qry .= "rsvp.TSfsa AS rsvpTSfsa, ";
			$qry .= "rsvp.TSlru AS rsvpTSlru, ";
			$qry .= "Code.ShtName AS rsvpClaim, ";
			$qry .= "Code_1.ShtName AS rsvpPosition, ";
			$qry .= "Code_2.ShtName AS rsvpRole, ";
			$qry .= "Bringing.ItemBringing AS bringItem, ";
			$qry .= "Event.Name AS evtName, ";
			$qry .= "Event.Start AS evtStart, ";
			$qry .= "Event.End AS evtEnd, ";
			$qry .= "Event.ID AS evtID, ";
			$qry .= "Event.Purpose AS evtPurposeCd, ";
			$qry .= "person.ID AS prsnID, ";
			$qry .= "person.PName AS prsnPName, ";
			$qry .= "CONCAT(person.FName,' ',person.LName) AS prsnFullName, ";
			$qry .= "person.LName AS prsnLName, ";
			$qry .= "person.FName AS prsnFName, ";
			$qry .= "person.Gender AS prsnGender, ";
			$qry .= "person.PhoneH AS prsnPhoneH, ";
			$qry .= "person.PhoneC AS prsnPhoneC, ";
			$qry .= "person.PhoneW AS prsnPhoneW, ";
			$qry .= "series.ShtName AS seriesShtName, ";
			$qry .= "series.ID AS seriesID, ";
			$qry .= "series.ViewLevel AS seriesViewLevel ";
			$qry .= "FROM Code, ";
			$qry .= "rsvp, ";
			$qry .= "Event, ";
			$qry .= "Code Code_1, ";
			$qry .= "Code Code_2, ";
			$qry .= "Bringing, ";
			$qry .= "series, ";
			$qry .= "person ";
			$qry .= "WHERE (Code.ID=rsvp.ClaimCode AND ";
			$qry .= "Event.ID=rsvp.Event AND ";
			$qry .= "Code_1.ID=rsvp.Position AND ";
			$qry .= "Code_2.ID=rsvp.Role AND ";
			$qry .= "Bringing.ID=rsvp.BringingPreDef AND ";
			$qry .= "series.ID=Event.Series AND ";
			$qry .= "person.ID=rsvp.Person)) ";
			$qry .= "AS qryRsvp";
			break;
			
		case 'qryRsvpPerson':
			$qry = "(SELECT ";
			$qry .= "series.ID AS ID, ";
			$qry .= "Event.ID AS evtID, ";
			$qry .= "rsvp.ID AS rsvpID, ";
			$qry .= "rsvp.ClaimCode AS rsvpClaimCode, ";
			$qry .= "rsvp.Position AS rsvpPositionCode, ";
			$qry .= "rsvp.Role AS rsvpRoleCode, ";
			$qry .= "rsvp.TSfsa AS rsvpTSfsa, ";
			$qry .= "rsvp.TSlru AS rsvpTSlru, ";
			$qry .= "person.ID AS prsnID, ";
			$qry .= "person.PName AS prsnPName, ";
			$qry .= "CONCAT(person.FName,' ',person.LName) AS prsnFullName, ";
			$qry .= "person.LName AS prsnLName, ";
			$qry .= "person.FName AS prsnFName, ";
			$qry .= "person.Gender AS prsnGender, ";
			$qry .= "person.Email1 AS Email1, ";
			$qry .= "person.Email2 AS Email2, ";
			$qry .= "person.Email3 AS Email3, ";
			$qry .= "person.Email1Active AS Email1Active, ";
			$qry .= "person.Email2Active AS Email2Active, ";
			$qry .= "person.Email3Active AS Email3Active ";
			$qry .= "FROM ";
			$qry .= "rsvp, ";
			$qry .= "Event, ";
			$qry .= "series, ";
			$qry .= "person ";
			$qry .= "WHERE (";
			$qry .= "Event.ID=rsvp.Event AND ";
			$qry .= "series.ID=Event.Series AND ";
			$qry .= "person.ID=rsvp.Person))";
			$qry .= "AS qryRsvpPerson";
			break;

	
		case 'qrySeriesEligible':
			$qry = "(SELECT ";
			$qry .= "eligible.ID AS eligID, ";
			$qry .= "series.ShtName AS seriesShtName, ";
			$qry .= "series.LongName AS seriesName, ";
			$qry .= "series.Type AS seriesTypeCode, ";
			$qry .= "series.EvtsIREmail AS seriesEvtsIREmail, ";
			$qry .= "person.ID AS prsnID, ";
			$qry .= "CONCAT(person.FName,' ',person.LName) AS prsnFullName, ";
			$qry .= "person.PName AS prsnPName, ";
			$qry .= "person.FName AS prsnFName, ";
			$qry .= "person.LName AS prsnLName, ";
			$qry .= "person.Gender AS prsnGender, ";
			$qry .= "person.Email1 AS Email1, ";
			$qry .= "person.Email2 AS Email2, ";
			$qry .= "person.Email3 AS Email3, ";
			$qry .= "person.Email1Active AS Email1Active, ";
			$qry .= "person.Email2Active AS Email2Active, ";
			$qry .= "person.Email3Active AS Email3Active, ";
			$qry .= "series.ID AS ID ";
			$qry .= "FROM series, ";
			$qry .= "person, ";
			$qry .= "eligible ";
			$qry .= "WHERE (eligible.Person=person.ID AND eligible.Series=series.ID) ";
			$qry .= "ORDER BY prsnLName, prsnFName) ";
			$qry .= "AS qrySeriesEligible";
			break;

		case 'qrySeriesMetrics':
			$qry = "(SELECT ";
			$qry .= "metric.ID AS metricID, ";
			$qry .= "metric.Name AS metricName, ";
			$qry .= "metric.Series AS SeriesID, ";
			$qry .= "metric.ValType AS metricValType, ";
			$qry .= "metric.Display AS metricDisplayCode, ";
			$qry .= "metric.Description AS metricDescription, ";
			$qry .= "metric.Announcement AS metricAnnouncement, ";
			$qry .= "Code.ShtName AS metricDisplaySht, ";
			$qry .= "Code.LongName AS metricDisplayLong ";
			$qry .= "FROM metric, ";
			$qry .= "Code ";
			$qry .= "WHERE (Code.ID=metric.Display) ";
			$qry .= "ORDER BY metricName) ";
			$qry .= "AS qrySeriesMetrics";
			break;

		case 'qryMetricDisp':
			$qry = "(SELECT ";
			$qry .= "metric.ID AS ID, ";
			$qry .= "metric.Sort AS metricSort, ";
			$qry .= "metric.Name AS metricName, ";
			$qry .= "metric.ShtName AS metricShtName, ";
			$qry .= "metric.Series AS seriesID, ";
			$qry .= "metric.ValType AS metricValTypeCode, ";
			$qry .= "metric.SortDesc AS metricSortDesc, ";
			$qry .= "metric.Display AS metricDisplayCode, ";
			$qry .= "metric.Description AS metricDiscription, ";
			$qry .= "metric.Announcement AS metricAnnouncement, ";
			$qry .= "Code_1.LongName AS metricValType, ";
			$qry .= "Code_2.LongName AS metricDisplay, ";
			$qry .= "series.LongName AS seriesName ";
			$qry .= "FROM metric, ";
			$qry .= "Code Code_1, ";
			$qry .= "Code Code_2, ";
			$qry .= "series ";
			$qry .= "WHERE (Code_1.ID=metric.ValType AND ";
			$qry .= "Code_2.ID=metric.Display AND ";
			$qry .= "series.ID=metric.Series)";
			$qry .= ") AS qryMetricDisp";
			break;

		case 'qryValueDisp':
			$qry = "(SELECT ";
			$qry .= "value.*, ";
			$qry .= "person.ID AS personID, ";
			$qry .= "person.FName AS prsnFName, ";
			$qry .= "person.LName AS prsnLName, ";
			$qry .= "CONCAT(person.FName,' ',person.LName) AS prsnFullName, ";
			$qry .= "person.PName AS prsnPName, ";
			$qry .= "metric.ID AS metricID, ";
			$qry .= "metric.ValType AS metricValType, ";
			$qry .= "metric.SortDesc AS metricSortDesc ";
			$qry .= "FROM value, ";
			$qry .= "person, ";
			$qry .= "metric ";
			$qry .= "WHERE (metric.ID=value.metric AND ";
			$qry .= "person.ID=value.Person)) AS qryValueDisp";
			break;

		case 'qryClubDisp':
			$qry = "(SELECT * ";
			$qry .= "FROM club ";
			$qry .= "ORDER BY club.ClubName) AS qryClubDisp";
			break;

		case 'qryLBVclub':
			$qry = "(SELECT ";
			$qry .= "club.ID AS ID, ";
			$qry .= "club.ClubName AS description ";
			$qry .= "FROM club ";
			$qry .= "ORDER BY description) ";
			$qry .= "AS qryLBVclub";
			break;

		case 'qryLBVEvent':
			$qry = "(SELECT ";
			$qry .= "Event.ID AS ID, ";
			$qry .= "Event.Name AS description, ";
			$qry .= "series.ID AS filterID ";
			$qry .= "FROM Event, ";
			$qry .= "series ";
			$qry .= "ORDER BY Event.Start) ";
			$qry .= "AS qryLBVEvent";
			break;

		case 'qryLBVperson':
			$qry = "(SELECT ";
			$qry .= "person.ID AS ID, ";
			$qry .= "CONCAT(person.FName, ' ',person.LName) AS description, ";
			$qry .= "FROM person ";
			$qry .= "ORDER BY description)";
			$qry .= "AS qryLBVperson";
			break;

		case 'qryLBVeligible':
			$qry = "(SELECT ";
			$qry .= "person.ID AS ID, ";
			$qry .= "CONCAT(person.FName, ' ',person.LName) AS description, ";
			$qry .= "series.ID AS filterID ";
			$qry .= "FROM series, ";
			$qry .= "person, ";
			$qry .= "eligible ";
			$qry .= "WHERE (eligible.Person=person.ID ";
			$qry .= "AND series.ID=eligible.Series) ";
			$qry .= "ORDER BY description) ";
			$qry .= "AS qryLBVEligPeople";
			break;

		case 'qryLBVseries':
			$qry = "(SELECT ";
			$qry .= "series.ID AS ID, ";
			$qry .= "series.LongName AS description ";
			$qry .= "FROM series ";
			$qry .= "ORDER BY series.Sort) ";
			$qry .= "AS qryLBVseries";
			break;

		case 'qryLBVvenue':
			$qry = "(SELECT ";
			$qry .= "venue.ID AS ID, ";
			$qry .= "venue.LongName AS description ";
			$qry .= "FROM venue ";
			$qry .= "ORDER BY venue.Sort) ";
			$qry .= "AS qryLBVvenue";
			break;

		default:
			$qry = $qryKey;
		}

	return $qry;

}










?>
