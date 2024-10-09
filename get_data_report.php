<?php
$consentFields = [
	"vumc_consent_part_2_complete",
	"cchmc_consent_part_2_complete",
	"chop_consent_part_2_complete",
	"columbia_consent_part_2_complete",
	"mgb_consent_part_2_complete",
	"mphc_consent_part_2_complete",
	"uab_consent_part_2_complete",
	"uw_consent_part_2_complete",
	"mt_sinai_consent_part_2_complete",
	"nu_consent_part_2_complete",
	"pdf_file"
];

$otherFields = [
	"site_id",
	"crsp_sample_id",
	"participant_lab_id",
	"gira_report_generated_date",
	"prescreening_survey_complete",
	"age",
	"sex_at_birth",
	"race_at_enrollment",
	"hispanic",
	"gender_identity",
	"gender_identity_child",
	"how_you_think_of_yourself",
	"how_many_people_are_curren",
	"annual_household_income",
	"highest_grade_level",
	"covered_by_health_insurance",
	"participant_withdrawal",
	"zip",
	"consent_date",
	"full_gira_generated",
	"nothighrisk_return_modality",
	"date_gira_disclosed",
	"date_gira_ehr_upload",
	"adult_fhh_rescue_complete",
	"pediatric_fhh_rescue_complete"
];

$recordIdField = $module->getProject($project_id)->getRecordIdField();

$recordData = \REDCap::getData([
	"project_id" => $project_id,
	"fields" => array_merge([$recordIdField],$consentFields,$otherFields),
	"return_format" => "json-array"
]);

$consentedRecords = [];
$withdrawnRecords = [];
$adultRecords = [];
$metreeRecords = [];
$giraRecords = [];

foreach($recordData as $dataRow) {
	$recordId = $dataRow[$recordIdField];
	
	foreach($consentFields as $thisField) {
		if(isset($dataRow[$thisField]) && $dataRow[$thisField] == "2") {
			$consentedRecords[$recordId] = 1;
		}
	}
	
	if(isset($dataRow["participant_withdrawal"]) && $dataRow["participant_withdrawal"] == "1") {
		$withdrawnRecords[$recordId] = 1;
	}
	
	if(isset($dataRow["age"]) && $dataRow["age"] > 18) {
		$adultRecords[$recordId] = 1;
	}
	
	if(isset($dataRow["metree_results_processed_date"]) && $dataRow["metree_results_processed_date"] !== "") {
		$metreeRecords[$recordId] = 1;
	}
	
	if(isset($dataRow["date_gira_ehr_upload"]) && $dataRow["date_gira_ehr_upload"] !== "") {
		$giraRecords[$recordId] = 1;
	}
}

$ageBrackets = [
	"3-13" => [3,12],
	"13-17" => [13,17],
	"18-30" => [18,30],
	"31-44" => [31,44],
	"45-59" => [45,59],
	"60-75" => [60,75]
];
$houseSizeBrackets = [
	"1" => [1,1],
	"2" => [2,2],
	"3-4" => [3,4],
	"5-6" => [5,6],
	"7-9" => [7,9],
	"10+" => [10,100]
];

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
foreach($genderOptions as $value => $label) {
	$genderCounts[$label] = 0;
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
	"giraCount" => count($giraRecords),
	"metreeCount" => count($metreeRecords),
	"householdSizeCounts" => $houseCounts,
	"ageCounts" => $ageCounts,
	"insuranceCounts" => $insuranceCounts,
	"educationCounts" => $educationCounts,
	"genderCounts" => $genderCounts,
];