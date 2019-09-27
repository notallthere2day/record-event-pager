<?php
namespace NorwichCTU\record_event_pager;

use \REDCap;

class record_event_pager extends \ExternalModules\AbstractExternalModule {
	public function __construct() {
		parent::__construct();
		// Other code to run when object is instantiated
	}
	
	public function redcap_data_entry_form( int $project_id, string $record = NULL, string $instrument, int $event_id, int $group_id = NULL, int $repeat_instance ) {
		
		$showEventPager = $this->getProjectSetting("show-event-pager");
		$showRecordPager = $this->getProjectSetting("show-record-pager");
		//**** Common code
		
		$downarrow_image = APP_PATH_IMAGES . "arrow_state_grey_expanded.png";
		global $Proj;
		
		$this_user=strtolower(USERID);
		$rights = REDCap::getUserRights($this_user);
			//print_array($rights);
		$group_id = $rights[$this_user]['group_id'];
		$groupName= REDCap::getGroupNames(false, $group_id);
		$formRights=$rights[$this_user]['forms'];
		//print_array($formRights);

		if($showEventPager){
			//**** Event Pager code
			
			//get list of events, and the first instrument at each:
			
			$eventNames=REDCap::getEventNames();
			$allEventsInstruments=($Proj->eventsForms);
			//$instrumentEventIDs=array();
			$firstInstruments=array();
			
			//populate list of the first instrument in each event that the current user has access to.
			foreach ($allEventsInstruments AS $eventIDNo=>$instruments){
				
								
				$userHasRights=false;
				$currentInstrument="";
				
				//loop through all the instruments at this event, and check the user's permissions. 
				//Add the first instrument that they have permission on to the list
				//If they do not have permission on any instruments at this event, move to the next event.
				$arrayLength = count($instruments);
        
				$i = 0;
				
				while ($i < $arrayLength && !$userHasRights)
					{
						$currentInstrument=$instruments[$i];
						$userHasRights=$formRights[$currentInstrument];
						if($userHasRights){
							$firstInstruments[$eventIDNo]=$currentInstrument;
						}
						$i++;
					}
				
				
				//populate list of all the events the current instrument appears at
				//currently unused, but may be used in next version
				// if (in_array($instrument, array_values($instruments))){
					// array_push($instrumentEventIDs,$eventIDNo);
				// }
			}
			
			//print_array($allEventsInstruments);
			//print_array($instrumentEventIDs);
			//print_array($firstInstruments);
			
			//get array of event IDs
			$allEventIDs=array_keys($firstInstruments);
			//get array of instrument names and labels
			$instrumentNames=RedCap::getInstrumentNames();
			
			//get array key and value of current, next and previous Events
			$thisEventKey = array_search ($event_id, $allEventIDs);
			$thisEventName=$eventNames[$event_id];
			//next event
			$nextEventKey=$thisEventKey+1;
			$nextEventID=$allEventIDs[$nextEventKey];
			$nextEventName=$eventNames[$nextEventID];
			$nextEventFirstInst=$firstInstruments[$nextEventID];
			$nextEventLink="index.php?pid=$project_id&id=$record&event_id=$nextEventID&page=$nextEventFirstInst";
			//previous event
			$prevEventKey=$thisEventKey-1;
			$prevEventID=$allEventIDs[$prevEventKey];
			$prevEventName=$eventNames[$prevEventID];
			$prevEventFirstInst=$firstInstruments[$prevEventID];
			
			$prevEventLink="index.php?pid=$project_id&id=$record&event_id=$prevEventID&page=$prevEventFirstInst";
			
			//display control on page
			echo "<div style='padding:5px; max-width: 800px; background-color: #f5f5f5;  border: 1px solid #dddddd;' >";
			echo "<span style='display: flex; align-items: center;  justify-content: space-between; '>";
			
			//previous event button
			if (isset($prevEventID)){
				echo "<a href='$prevEventLink' class='btn btn-defaultrc btn-sm' title='Click to go to the first form in the previous event.'>< Previous Event ($prevEventName)</a>";
			}else{
				echo " No previous events ";
				}
				
			//current event name
			echo "<span>";
			echo "<span style='padding:5px; margin: 5px; vertical-align: middle;'  > This event: $thisEventName</span> ";
				
			//Show all records button
			echo "&nbsp<span class='btn btn-defaultrc btn-sm' ' title='Show all events.' onclick='toggleEventMenu();' >Show all<img src='$downarrow_image'/></span>&nbsp";
			
			echo "</span>";
				//next event button
			if (isset($nextEventID)){
				echo "<a href='$nextEventLink' class='btn btn-defaultrc btn-sm' style='float: right;' title='Click to go to the first form in the next event.'>Next Event ($nextEventName) > </a>";
			}else{echo " No further events ";}
			echo "</span>";
			
			//create menu of all available events in order
			echo "<div id='allEventMenu' style='display:none;  max-height: 200px; overflow: auto;'>";
			echo "All events for this project. Clicking a button will take you to the first instrument in the event.<br/>";
			
			//loop through first instrument, creating menu of buttons
			foreach ($firstInstruments AS $eventIDNo=>$unique_event_name){
				$firstInstrument=$firstInstruments[$eventIDNo];
				$instrumentLabel=$instrumentNames[$firstInstrument];
				//print button with name of event and link to page
				$eventLink="index.php?pid=$project_id&id=$record&event_id=$eventIDNo&page=$firstInstrument";
				$eventName=$eventNames[$eventIDNo];
				//$eventName="testing";
				if($eventIDNo==$event_id){
					echo "<a href='$eventLink' class='btn btn-defaultrc btn-sm' style='background-color:#aaa;' title='$instrumentLabel'>$eventName</a>";
				}else{
					echo "<a href='$eventLink' class='btn btn-defaultrc btn-sm' title='$instrumentLabel'>$eventName</a>";
				}
				
			}
				
			//close all events div	
			echo "</div>";
			//close event pager div
			echo "</div>";
				
		}	
		
		if($showRecordPager){
			//**** Record Pager Code
			//get list of record IDs for the current user's DAG- If user not in DAG, should display all record IDs
			

			
			//print_array($groupName);
			$record_id_field = REDCap::getRecordIdField();
			$records=REDCap::getData('array',null, $record_id_field, null, $group_id);
			$recordIDs=array_keys($records);

			//var_dump ($sampleIDs);
			//get array key and value of current, next and previous records
			$thisRecordKey = array_search ($record, $recordIDs);
			//echo "<br/>This record Key:$thisREcordKey";
			$nextRecordKey=$thisRecordKey+1;
			//echo "<br/>Next record Key:$nextRecordKey";
			$nextRecord=$recordIDs[$nextRecordKey];
			//echo "<br/>Next record :$nextRecord";
			$nextRecordLink="index.php?pid=$project_id&id=$nextRecord&event_id=$event_id&page=$instrument";
			//echo $nextRecordLink;
			$prevRecordKey=$thisRecordKey-1;
			//echo "<br/>Prev record Key :$prevRecordKey";
			$prevRecord=$recordIDs[$prevRecordKey];
			//echo "<br/>Prev record :$prevRecord";
			//var_dump ($prevRecord);
			$prevRecordLink="index.php?pid=$project_id&id=$prevRecord&event_id=$event_id&page=$instrument";
			
			//display control on page
			echo "<div style='padding:5px; max-width: 800px; background-color: #f5f5f5;    border: 1px solid #dddddd; text-align:center;  '>";
			echo "<span style='display: flex; align-items: center;  justify-content: space-between; '>";
			//Previous record button
			if (isset($prevRecord)){
				echo "<a href='$prevRecordLink' class='btn btn-defaultrc btn-sm'> <  Previous Record ($prevRecord)</a>";
			}else{echo " No previous records ";}
			
			echo "<span>";
			//current record button
			echo "<span style='padding:5px; margin: 5px; '> This record: $record </span> ";
			
			//Show all records button
			echo "&nbsp<span class='btn btn-defaultrc btn-sm' title='Show all records.' onclick='toggleRecordMenu();' >Show all<img src='$downarrow_image'/></span>&nbsp";
			
			echo "</span>";
			
			//next record button
			if (isset($nextRecord)){
				echo "<a href='$nextRecordLink' class='btn btn-defaultrc btn-sm' style='float: right;'  >Next record ($nextRecord) > </a>";
			}else{echo " No further records ";}
			
			
			echo "</span>";
			echo "<div id='allRecordMenu' style='display:none; max-height: 200px; overflow: auto;'>";
			if($group_id==""){
				echo "All records in this project:<br/>";
			}else{
				echo "All records for $groupName:<br/>";
			}
			
			// all records menu
			foreach ($recordIDs AS $record_id){
					//echo $eventIDNo;
					$recordLink="index.php?pid=$project_id&id=$record_id&event_id=$event_id&page=$instrument";
					//print button with record ID and link to record
					//change colour of current record
					if($record_id==$record){
						echo "<a href='$recordLink' class='btn btn-defaultrc btn-sm' style='background-color:#aaa;'>$record_id</a>";
					}
					else{
						echo "<a href='$recordLink' class='btn btn-defaultrc btn-sm' >$record_id</a>";
					}
				}
			echo "</div>";
			echo "</div>";
		
		}
		
		if($showRecordPager==false && $showEventPager==false){
			echo "<span style='color:#329c75;'>Record and event pagers are both disabled. </span>";
		}
		
		//Javascript for menu toggling
		echo" <script>
					function toggleRecordMenu() {
					$('#allRecordMenu').slideToggle();
					}
					function toggleEventMenu() {
					$('#allEventMenu').slideToggle();
					}
					</script>";
					


		}
		
	}

	
