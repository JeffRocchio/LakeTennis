<?php
   /*
		06/07/2012:
			Added function Roster_BuildCellNoteTxt().
			Modified function RosterRoster_BuildCellDone().
			Modified function RosterRoster_BuildCellTBD().
			FOR PURPOSE OF --
					Enhanced the rsvp grid display so that events can
				be flagged to display any rsvp notes directly in the cells (vs
				having only a link to the note). Did this to accomodate our mixed 
				doubles where I use an 'event' for the "Potluck" signups. This way
				what folks have signed up for is displayed directly in the grid.
				This was accomplished by adding a new 'Display' code value for
				"Display Normally with Notes." (See CodeSet #06).
			
   	01/25/2012: Modified to add a function that displays the series
   description.
   
	   07/26/2008: Modified to fix the 'infinite loop' problem that occurred
	when we didn't have any Events or Persons linked to the series.
	*/



function Roster_DisplaySeriesDescription($inText, $display='Y')
	{
	global $CRLF;

	$outHTML = "";
	if ($display=='Y')
		{
		$outHTML = "<DIV>" . $inText . "<P>&nbsp;</P></DIV>";
		}
	
	return $outHTML;

} // end function



function Roster_TblOpen($format)
	{
	//   Open a table to hold the roster.
	global $CRLF;
	$outHTML = "<TABLE BORDER='0' CELLSPACING=0 CELLPADDING=0>";
	return $outHTML;

} // end function


function Roster_TblClose()
	{
	//   Close the Roster-grid table.
	global $CRLF;
	return "</TABLE>{$CRLF}{$CRLF}{$CRLF}";

} // end function


function Roster_BuildEvtLableCells(&$row, &$tblHdrArray, $format)
	{
	global $CRLF;

	switch ($format)
		{
		case 'REC':
			$nameLbl = 'Event';
			break;

		default:
			$nameLbl = 'Opponent';
		}
	$tblRowDef = "<TD STYLE='border-top: 0; border-right: thin solid black; padding-right: 2' valign='top' align='right'>";
	$tblHdrArray['date'][0] = $tblRowDef . "<P CLASS=evtDate>DATE:</P></TD>";
	$tblHdrArray['time'][0] = $tblRowDef . "<P CLASS=evtTime>TIME:</P></TD>";
	$tblHdrArray['name'][0] = $tblRowDef . "<P CLASS=evtOpponent>{$nameLbl}:</P></TD>";
	$tblHdrArray['venue'][0] = $tblRowDef . "<P CLASS=evtVenue>Venue:</P></TD>";
	$tblHdrArray['edit'][0] = $tblRowDef . "<P CLASS=evtVenue>&nbsp;</P></TD>";
	$tblHdrArray['reset'][0] = $tblRowDef . "<P CLASS=evtVenue>&nbsp;</P></TD>";
					//   The below entry will be used in later functions to
					//indicate that current user has Mgr or Admin rights
					//to at least one event on the page. Knowing this is
					//important so we can build a row in the table to hold
					//the 'Edit' links in the column header for those events
					//the user does have edit rights to.
	$tblHdrArray['evtMgr'][0] = FALSE;
	
	return;

} // end function


function Roster_BuildEvtCells(&$row, &$tblHdrArray, $format, $resetLink, $userPrivSeries)
	{
	global $CRLF;

	$startDate = substr ($row['evtStart'], 5, 5);
	$startDate = substr_replace($startDate, "/", 2, 1);
	$startTime = Tennis_DisplayTime($row['evtStart'], TRUE);
	$recID = $row['evtID'];
	switch ($row['evtResultCode'])
		{
		case 36: //'WIN':
			$tdStyle = "rosterDoneWin";
			break;
			
		case 37: //'LOSS':
			$tdStyle = "rosterDoneLoss";
			break;

		case 38: //Other
			$tdStyle = "rosterDone";
			break;

		default:
			$tdStyle = "rosterCellClear";
			
		}
	$tblRowDef = "<TD CLASS='{$tdStyle}' STYLE='border-top: 0; padding-top: 0; padding-bottom: 0' align='center' valign='top'>";
	$tmp = $tblRowDef;
	$tmp .=  "<P CLASS=evtDate>";
	$tmp .= "<a href='dispEvent.php?ID={$recID}'>{$startDate}</a>";
	$tmp .= "</P></TD>";
	$tblHdrArray['date'][] = $tmp;
	$tblHdrArray['time'][] = $tblRowDef . "<P CLASS=evtTime>{$startTime}</P></TD>";
	$tblHdrArray['name'][] = $tblRowDef . "<P CLASS=evtOpponent>" . $row['evtName'] . "</P></TD>";
	$tblHdrArray['venue'][] = $tblRowDef . "<P CLASS=evtVenue>" . $row['venueShtName'] . "</P></TD>";
	if ($userPrivSeries=='MGR' or $userPrivSeries=='ADM' or $row['userPrivEvt']==47 or $row['userPrivEvt']==48)
		{
		$tblHdrArray['edit'][] = $tblRowDef;
		$tblHdrArray['edit'][] .= "<P CLASS=cellEditLink>";
		$tblHdrArray['edit'][] .= "<a href='editLineup.php?ID={$recID}'>LINEUP</a> // ";
		$tblHdrArray['edit'][] .= "<a href='editEvent.php?ID={$recID}'>EDIT</a>";
		$tblHdrArray['edit'][] .= "</P></TD>";
		if ($resetLink == TRUE)
			{
			$tblHdrArray['reset'][] = $tblRowDef . "<P CLASS=cellEditLink><a href='editResetRSVPs.php?ID={$recID}'>RESET</a></P></TD>";
			}
		$tblHdrArray['evtMgr'][0] = TRUE;
		}

	return;

} // end function


function Roster_TblHeadOutput(&$tblHdrArray, $format, $resetLink)
	{
	/*
	   Output the table heading rows that were built into an array
	using the Roster_BuildEvtCells() function.
	
	ASSUMES:
			1) A Table has been 'opened.' Meaning that the <TABLE...>
		HTML code has been issued.
			2) The $tblHdrArray[][] has been populated with the
		appropriate values for each [row][column] of the table's
		header.
	*/
	global $CRLF;
	$outHTML = "";

	$outHTML = "<THEAD>{$CRLF}";
	foreach ($tblHdrArray as $key => $hdrRowArray)
		{
		$outHTML .= "<TR>";
		foreach($hdrRowArray as $hdrRowCell)
			{
			if ($key != 'edit' && $key != 'reset' && $key != 'evtMgr')
				{
				$outHTML .= $hdrRowCell;
				}
			else
				{
				if ($tblHdrArray['evtMgr'][0]==TRUE)
					{
					if ($key == 'edit')
						{
						$outHTML .= $hdrRowCell;
						}
					elseif ($key=='reset' && $resetLink==TRUE)
						{
						$outHTML .= $hdrRowCell;
						}
					}
				}
			}
		$outHTML .= "</TR>{$CRLF}";
		}
	$outHTML .= "</THEAD>{$CRLF}{$CRLF}";
	return $outHTML;

} // end function


function Roster_TblBodyOutput($seriesID, $seriesView, $userPrivSeries)
	{
	/*
	   This function gets from the database all the individual
	RSVP records and builds them into a table body section that
	comprises the roster-grid.
	
	   07/26/2008: However, if there are no RSVP records then there are no Person
	records or Event records mapped to this series yet. In which case we
	get caught in a infinite loop. To prevent this situation we do a test of
	the number of records returned in the query. If it's zero, then we
	return this function as 'FALSE' so that this error can be handled in the
	display page.
	*/

	global $CRLF;
	$outHTML = "";

	$DEBUG = FALSE;
	//$DEBUG = TRUE;


	if (!$qryResult = Tennis_SeriesRosterOpen($seriesID, $seriesView))
		{
		echo "<P>{$GLOBALS['lstErrMsg']}</P>";
		include './INCL_footer.php';
		exit;
		}
	$emptyNumPersons = mysql_num_rows($qryResult);
	if($DEBUG) echo "<P># Persons: {$emptyNumPersons}</P>";
	if ($emptyNumPersons <= 0)
		{
		return FALSE;
		}

						//   Open the table body.
	echo "<TBODY>{$CRLF}";


						//   Fetch 1st row of the query and enter into
						//a loop to iterate over all rows of the
						//query and build the roster-grid body.
	$row = mysql_fetch_array($qryResult);
	do
		{
		$playerID = $row['prsnID'];
						//   Open the table-row.
		$outHTML = "<TR>{$CRLF}";
						//   Build the person name cell.
		$outHTML .= Roster_BuildCellName($row);
		
						//   Now build the event cells stretching to the right.
		while ($playerID == $row['prsnID'])
			{
			switch ($row['evtResultCode'])
				{
				case 36: //'WIN':
					$outHTML .= Roster_BuildCellDone($row, '1LINE', True, 'WIN', $userPrivSeries);
					break;
					
				case 37: //'LOSS':
					$outHTML .= Roster_BuildCellDone($row, '1LINE', True, 'LOSS', $userPrivSeries);
					break;
	
				case 38: //Other:
					$outHTML .= Roster_BuildCellDone($row, '1LINE', True, 'OTHER', $userPrivSeries);
					break;
	
				default:
					$outHTML .= Roster_BuildCellTBD($row, '1LINE', True, $userPrivSeries);
				}
			$row = mysql_fetch_array($qryResult);
			
			} // end while
		
		$outHTML .= "</TR>{$CRLF}";
		echo $outHTML;
		} while ($row); // end do

	echo "</TBODY>{$CRLF}";

	return TRUE;

} // End Function



function Roster_BuildCellEvt(&$row, &$tblHdrArray, $format, $resetLink)
	{
	//THIS FUNCTION IS OBSOLETE.

	global $CRLF;
	$outHTML = "";

	$startDate = substr ($row['evtStart'], 5, 5);
	$startDate = substr_replace($startDate, "/", 2, 1);
	$startTime = substr ($row['evtStart'], 11, 5);
	$recID = $row['evtID'];
	switch ($row['evtResultCode'])
		{
		case 36: //'WIN':
			$tdStyle = "rosterDoneWin";
			break;
			
		case 37: //'LOSS':
			$tdStyle = "rosterDoneLoss";
			break;

		case 38: //Other
			$tdStyle = "rosterDone";
			break;

		default:
			$tdStyle = "rosterCellClear";
			
		}
	$outHTML .= "<TD CLASS='{$tdStyle}' align='center' valign='top'>";
	$outHTML .= "<P CLASS=evtDate>";
	$outHTML .= "<a href='dispEvent.php?ID={$recID}'>{$startDate}</a>";
	$outHTML .= "</P>";
	$outHTML .= "<P CLASS=evtTime>{$startTime}</P>";
	$outHTML .= "<P CLASS=evtOpponent>" . $row['evtName'] . "</P>";
	$outHTML .= "<P CLASS=evtVenue>" . $row['venueShtName'] . "</P>";
	if ($_SESSION['evtmgr'] == TRUE or $row['userPrivEvt']==47 or $row['userPrivEvt']==48)
		{
		$outHTML .= "<P CLASS=cellEditLink><a href='editEvent.php?ID={$recID}'>EDIT</a></P>";
		if ($resetLink)
			{
			$outHTML .= "<P CLASS=cellEditLink><a href='editResetRSVPs.php?ID={$recID}'>RESET</a></P>";
			}
		}
	$outHTML .= "</TD>{$CRLF}";

	return $outHTML;

} // end function



function Roster_BuildCellName(&$row)
	{
	global $CRLF;
	$outHTML = "";

	if ($_SESSION['member'] == TRUE)
		{
		$pName = "<A HREF='dispPerson.php?ID={$row['prsnID']}&FORMAT=FULL'>{$row['prsnFullName']}</A>";
		$pPhH = "h:{$row['prsnPhoneH']}";
		$pPhC = "c:{$row['prsnPhoneC']}";
		$pPhW = "w:{$row['prsnPhoneW']}";
		$outHTML = "<TD class='rosterLable'><P CLASS='rosterFullName'>{$pName}</P>{$CRLF}";
		if (!($_SESSION['RSTR_PhListOff'] == TRUE))
			{
			$outHTML .= "<P CLASS='rosterPhone'>$pPhH</P>";
			$outHTML .= "<P CLASS='rosterPhone'>$pPhC</P>";
			$outHTML .= "<P CLASS='rosterPhone'>$pPhW</P>";
			}
		$outHTML .= "</TD>{$CRLF}";
		}
	else
		{
		$outHTML = "<TD class='rosterLable'><P CLASS='rosterPublicName'>{$row['prsnPName']}</P></TD>{$CRLF}";
		}
		
	return $outHTML;

} // end function



function Roster_BuildCellTBD(&$row, $format, $noteLink, $userPrivSeries)
	{
	global $CRLF;
	$outHTML = "";
	$cellNoteTxt = "";
	
	switch ($format)
		{
		case '1LINE':
			$position = Roster_BuildCellPositionTxt($row, '1LINE');
			switch($position)
				{
				case 'P':
					$tdStyle = "rosterCellPlay";
					$pgStyle = "rosterGShort";
					break;
				
				case 'S1':
				case 'S2':
				case 'D1':
				case 'D2':
				case 'D3':
					$tdStyle = "rosterCellPIDd";
					$pgStyle = "rosterGShort";
					break;
				
				case 'AVAIL':
				case 'CNFRM':
					$tdStyle = "rosterCellAvail";
					$pgStyle = "rosterGMed";
					break;
				
				case 'TENT':
				case 'LATE':
					$tdStyle = "rosterCellTent";
					$pgStyle = "rosterGMed";
					break;
				
				case 'NOTAV':
					$tdStyle = "rosterCellNota";
					$pgStyle = "rosterGMed";
					break;
				
				case 'BACKU':
					$tdStyle = "rosterCellBkup";
					$pgStyle = "rosterGMed";
					$cell = 'BUP';
					break;
				
				default:
					$tdStyle = "rosterUnknown";
					$pgStyle = "rosterGMed";
				} // end switch
						//   Cell background color overrides. So that
						//we represent the availability status by using
						//the cell's background color, no matter what the
						//playing-position cell text is.
			if ($row['rsvpClaim'] == 'NOTAV') $tdStyle = "rosterCellNota";
			if ($row['rsvpClaim'] == 'TENT') $tdStyle = "rosterCellTent";
			if ($row['rsvpClaim'] == 'LATE') $tdStyle = "rosterCellTent";
			if ($row['rsvpClaim'] == 'NORES') $tdStyle = "rosterUnknown";
			$role = Roster_BuildRole($row, '1LINE');
			if ($noteLink) $position = Roster_BuildCellNotelink($row, $position);
						//   Now build the output display, accounting for case where
						//we will display the rsvp note text vs the playing position
						//or attendance claim code.
			$outHTML .= "<TD CLASS='{$tdStyle}'><P CLASS='{$pgStyle}'>";
			$outHTML .= $position;
			if (($row['evtDisplay']==EVDISP_NORMALWITHNOTES) && (strlen($row['rsvpNote']) > 3))
				{
				$outHTML .= "</P><P CLASS='rosterGLong'>";
				$outHTML .= $row['rsvpNote'];
				}
			$outHTML .= "{$role}</P>{$CRLF}";
			break;
		
		default:
			$position = Roster_BuildCellPositionTxt($row, 'SEP');
			$role = Roster_BuildRole($row, 'SEP');
			if ($noteLink) $position = Roster_BuildCellNotelink($row, $position);
			$outHTML .= "<TD CLASS='rosterCellClear'>";
			$outHTML .= $role;
			$outHTML .= "<P CLASS='rosterGShort'>";
			$outHTML .= $position;
			$outHTML .= "{$role}</P>{$CRLF}";
			switch($row['rsvpClaim'])
				{
				case 'NOTAV':
					$color = "red";
					break;
				
				case 'TENT':
				case 'LATE':
					$color = "yellow";
					break;
				
				default:
					$color = "green";
				} // end switch
			$outHTML .= "<P CLASS='rosterAvail' STYLE='color: {$color}'>";
			$outHTML .= "[{$row['rsvpClaim']}]";
			$outHTML .= "</P>{$CRLF}";

		}
	
	if ($userPrivSeries=='MGR' or $userPrivSeries=='ADM' or $_SESSION['recID']==$row['prsnID'] or $row['userPrivEvt']==47 or $row['userPrivEvt']==48)
		{
		$outHTML .= "<P CLASS=cellEditLink><a href='editRSVP.php?ID={$row['rsvpID']}'>EDIT</a></P>";
		}
	$outHTML .= "</TD>";
	
	return $outHTML;

} // end function



function Roster_BuildCellDone(&$row, $format, $noteLink, $result, $userPrivSeries)
	{
	global $CRLF;
	$outHTML = "";
	
	switch ($format)
		{
		case '1LINE':
			$position = Roster_BuildCellPositionTxt($row, '1LINE');
			switch($position)
				{
				case 'P':
				case 'S1':
				case 'S2':
				case 'D1':
				case 'D2':
				case 'D3':
					break;
				
				default:
					$position = "&nbsp;";
				}
			$tdStyle = "rosterDone";
			if ($result == 'WIN') $tdStyle = "rosterDoneWin";
			if ($result == 'LOSS') $tdStyle = "rosterDoneLoss";
			$pgStyle = "rosterGShort";
			if ($noteLink) $position = Roster_BuildCellNotelink($row, $position);
						//   Now build the output display, accounting for case where
						//we will display the rsvp note text vs the playing position
						//or attendance claim code.
			$outHTML .= "<TD CLASS='{$tdStyle}'><P CLASS='{$pgStyle}'>";
			$outHTML .= $position;
			if (($row['evtDisplay']==EVDISP_NORMALWITHNOTES) && (strlen($row['rsvpNote']) > 3))
				{
				$outHTML .= "</P><P CLASS='rosterGLong'>";
				$outHTML .= $row['rsvpNote'];
				}
			$outHTML .= "</P>{$CRLF}";
			break;
		
		default:
			$position = Roster_BuildCellPositionTxt($row, 'SEP');
			$role = Roster_BuildRole($row, 'SEP');
			if ($noteLink) $position = Roster_BuildCellNotelink($row, $position);
			$tdStyle = "rosterDone";
			if ($result == 'WIN') $tdStyle = "rosterDoneWin";
			if ($result == 'LOSS') $tdStyle = "rosterDoneLoss";
			$outHTML .= "<TD CLASS='{$tdStyle}'>";
			$outHTML .= $role;
			$outHTML .= "<P CLASS='rosterGShort'>";
			$outHTML .= $position;
			$outHTML .= "{$role}</P>{$CRLF}";
			switch($row['rsvpClaim'])
				{
				case 'NOTAV':
					$color = "red";
					break;
				
				case 'TENT':
				case 'LATE':
					$color = "yellow";
					break;
				
				default:
					$color = "green";
				} // end switch
			$outHTML .= "<P CLASS='rosterAvail' STYLE='color: {$color}'>";
			$outHTML .= "[{$row['rsvpClaim']}]";
			$outHTML .= "</P>{$CRLF}";

		}
	
	if ($userPrivSeries=='MGR' or $userPrivSeries=='ADM' or $_SESSION['recID']==$row['prsnID'] or $row['userPrivEvt']==47 or $row['userPrivEvt']==48)
		{
		$outHTML .= "<P CLASS=cellEditLink><a href='editRSVP.php?ID={$row['rsvpID']}'>EDIT</a></P>";
		}
	$outHTML .= "</TD>";
	
	return $outHTML;

} // end function



function Roster_BuildCellPositionTxt(&$row, $format)
	{
	global $CRLF;
	$txt = "";

	switch ($format)
		{
		case '1LINE':
			$txt = $row['rsvpPosition'];
			if (($row['rsvpPosition'] == 'NP') OR ($row['rsvpPosition'] == 'TBD'))
				{
				$txt = $row['rsvpClaim'];
				}
			break;
		
		default:
			$txt = $row['rsvpPosition'];
			
		}

	return $txt;

} // end function



function Roster_BuildCellNotelink(&$row, $inText)
	{
	global $CRLF;

	$outHTML = $inText;
	if (strlen($row['rsvpNote']) > 0)
		{
		$tmp = substr($row['rsvpNote'], 0, 200);
		$outHTML = "<A HREF='./dispRSVP.php?ID={$row['rsvpID']}&FORMAT=NOTE' TITLE='{$tmp}'>{$inText}</A>";
		}
	
	return $outHTML;

} // end function



function Roster_BuildCellNoteTxt(&$row, $format)
	{
	global $CRLF;
	$txt = "";

	if (strlen($row['rsvpNote'])<3)
		{
		$outHTML = "";
		}
	else
		{
		$outHTML = "<SPAN STYLE='font-size: small'>{$row['rsvpNote']}</SPAN>";
		}

	return $outHTML;

} // end function



function Roster_BuildRole(&$row, $format)
	{
	global $CRLF;
	$outHTML = "";

	switch ($format)
		{
		case '1LINE':
			$role = "";
			if ($row['rsvpRole'] == 'CAPTN') $role = 'c';
			if ($row['rsvpRole'] == 'COCAP') $role = 'cc';
			if ($role <> '')
				{
				$outHTML = "<SPAN STYLE='vertical-align: 70%; color: red; font-size: small'>{$role}</SPAN>";
				}
		break;
		
		default:
			$role = $row['rsvpRole'];
			switch ($role)
				{
				case 'CAPTN':
				$color = "red";
				$role = "Captain";
				break;
				
				case 'COCAP':
				$color = "purple";
				$role = "Co-Captain";
				break;
				
				default:
				$color = "gray";
				
				}
			$outHTML = "<P CLASS='rosterRole' STYLE='color: {$color}'>{$role}</P>";
		}

	return $outHTML;

} // end function



function Roster_GetUserRights($seriesID, $viewLevel, $pageName, &$rights)
	{
	/*
	   This function determines what rights the user has on the series and page.
	   
	   TAKES:
				- Series ID.
	         - View Level for the page or script.
	         - Page or script to determine rights for (not currently needed
	      really, but I think as this approach evolves it will become
	      useful later).
	         - Reference to an array which holds the rights (at this time
	      there are two types of rights, view rights and edit rights.
	   
	   RETURNS:
	   	   - The user's view as a 3-char string.
	   	   - The rights array populated.
	   
	   ASSUMES:
	   
	*/
	$currUserID = $_SESSION['recID'];
	$userPrivSeries='GST';
	$userPrivSeries = Session_GetAuthority(42, $seriesID);
	if ($_SESSION['admin']==True) $userPrivSeries = 'ADM';
	if ($_SESSION['clbmgr']==True) $userPrivSeries = 'ADM';

						//   Adjust rights based on the view-level 
						//setting for the series.
	if ($userPrivSeries <> 'ADM' and $userPrivSeries<>'MGR')
		{
		switch ($viewLevel)
			{
			case 58: // Must be Club Member to view.
				if ($userPrivSeries=='GST') $userPrivSeries='NON';
				break;
	
			case 59: // Must be a series or team participant to view.
				$viewName = "qrySeriesEligible";
				$where = "WHERE (ID={$seriesID} AND prsnID={$currUserID})";
				$sort = "";
				$participant = FALSE;
				$qryResult = Tennis_OpenViewGeneric($viewName, $where, $sort);
				$NumRows = mysql_num_rows($qryResult);
				if ($NumRows == 1) $participant = TRUE;
				if ($participant == FALSE) $userPrivSeries='NON';
				break;
	
			case 60: // Must be ADM or MGR level to view
				if ($userPrivSeries<>'ADM' and $userPrivSeries<>'MGR') $userPrivSeries='NON';
				break;

			default:
			}
		}

	$rights['view'] = $userPrivSeries;
	$rights['edit'] = $userPrivSeries;
	return $userPrivSeries;

} // end function



?> 
