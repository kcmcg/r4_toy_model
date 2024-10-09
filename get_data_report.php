<?php
use \Data\DataPull;
use \Data\DataAnalysis;

$dataPull = new DataPull($project_id);
$recordData = $dataPull->getData();

$consentedRecords = [];
$withdrawnRecords = [];
$adultRecords = [];
$metreeRecords = [];
$giraRecords = [];

foreach($recordData as $dataRow) {
	$recordId = $dataRow[$dataPull->getRecordIdField()];
	
	foreach(DataPull::$consentFields as $thisField) {
		if(isset($dataRow[$thisField]) && $dataRow[$thisField] == "2") {
			$consentedRecords[$recordId] = 1;
		}
	}
	
	if(isset($dataRow["participant_withdrawal"]) && $dataRow["participant_withdrawal"] == "1") {
		$withdrawnRecords[$recordId] = 1;
	}
	
	if(isset($dataRow["age"]) && $dataRow["age"] > 0) {
		$adultRecords[$recordId] = 1;
	}
	
	if(isset($dataRow["metree_results_processed_date"]) && $dataRow["metree_results_processed_date"] !== "") {
		$metreeRecords[$recordId] = 1;
	}
	
	if(isset($dataRow["date_gira_ehr_upload"]) && $dataRow["date_gira_ehr_upload"] !== "") {
		$giraRecords[$recordId] = 1;
	}
}


$genderOptions = $module->getChoiceLabels("gender_identity",$project_id);
$insuranceOptions = $module->getChoiceLabels("covered_by_health_insurance",$project_id);
$educationOptions = $module->getChoiceLabels("highest_grade_level",$project_id);

$houseCounts = [];
$ageCounts = [];
$insuranceCounts = [];
$educationCounts = [];
$genderCounts = [];

foreach($ageBrackets as $label => $range) {
	$ageCounts[$label] = 0;
}
foreach($houseSizeBrackets as $label => $range) {
	$houseSizeCounts[$label] = 0;
}
foreach($insuranceOptions as $value => $label) {
	$insuranceCounts[$label] = 0;
}
foreach($educationOptions as $value => $label) {
	$educationCounts[$label] = 0;
}

foreach($recordData as $dataRow) {
	$recordId = $dataRow[$recordIdField];
	
	if(array_key_exists($recordId, $consentedRecords) && !array_key_exists($recordId, $withdrawnRecords)) {
		if(isset($dataRow["age"]) && $dataRow["age"] != "") {
			foreach($ageBrackets as $label => $range) {
				if($range[0] <= $dataRow["age"] && $range[1] >= $dataRow["age"]) {
					$ageCounts[$label]++;
				}
			}
		}
		
		$gender = false;
		if(isset($dataRow["gender_identity"]) && $dataRow["gender_identity"] != "") {
			$gender = $dataRow["gender_identity"];
		}
		if(isset($dataRow["gender_identity_child"]) && $dataRow["gender_identity_child"] != "") {
			$gender = $dataRow["gender_identity_child"];
		}
		
		if($gender !== false) {
			foreach($genderOptions as $rawValue => $thisLabel) {
				if($gender == $rawValue) {
					$genderCounts[$thisLabel]++;
				}
			}
		}
		
		if(array_key_exists($recordId, $adultRecords)) {
			if(isset($dataRow["how_many_people_are_curren"]) && $dataRow["how_many_people_are_curren"] != "") {
				foreach($houseSizeBrackets as $thisLabel => $range) {
					if($range[0] <= $dataRow["how_many_people_are_curren"] &&
							$range[1] >= $dataRow["how_many_people_are_curren"]) {
						$houseCounts[$thisLabel]++;
					}
				}
			}
			
			if(isset($dataRow["covered_by_health_insurance"]) && $dataRow["covered_by_health_insurance"] != "") {
				foreach($insuranceOptions as $rawValue => $label) {
					if($dataRow["covered_by_health_insurance"] == $rawValue) {
						$insuranceCounts[$label]++;
					}
				}
			}
			
			if(isset($dataRow["highest_grade_level"]) && $dataRow["highest_grade_level"] != "") {
				foreach($educationOptions as $rawValue => $label) {
					if($dataRow["highest_grade_level"] == $rawValue) {
						$educationCounts[$label]++;
					}
				}
			}
		}
	}
}

$reportData = [
	"adultCount" => count($adultRecords),
	"pediatricCount" => count($consentedRecords) - count($adultRecords) - count($withdrawnRecords),
	"householdSizeCounts" => $houseCounts,
	"ageCounts" => $ageCounts,
	"insuranceCounts" => $insuranceCounts,
	"educationCounts" => $educationCounts,
	"genderCounts" => $genderCounts
];