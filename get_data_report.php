<?php
use \Data\DataPull;
use \Data\DataAnalysis;

$dataPull = new DataPull($project_id);
$recordData = $dataPull->getData();

$consentedRecords = DataAnalysis::getConsentedRecords($recordData);
$consentedData = DataAnalysis::filterDataByArray($recordData,$consentedRecords);

$adultRecords = DataAnalysis::getAdultRecords($consentedData);
$adultData = DataAnalysis::filterDataByArray($consentedData, $adultRecords);

$pediatricRecords = DataAnalysis::getPediatricRecords($consentedData);
$pediatricData = DataAnalysis::filterDataByArray($consentedData, $pediatricRecords);

$metreeRecords = DataAnalysis::mapFieldByRecord($consentedData,["metree_results_processed_date"]);
$giraRecords = DataAnalysis::mapFieldByRecord($consentedData, ["date_gira_ehr_upload"]);

$genderOptions = $module->getChoiceLabels("gender_identity",$project_id);
$insuranceOptions = $module->getChoiceLabels("covered_by_health_insurance",$project_id);
$educationOptions = $module->getChoiceLabels("highest_grade_level",$project_id);

$houseCounts = [];
$ageCounts = [];
$insuranceCounts = [];
$educationCounts = [];
$genderCounts = [];

foreach(DataAnalysis::$ageBrackets as $label => $range) {
	$recordInRange = DataAnalysis::mapFieldByRecordInRange($consentedData,"age", $range[0],$range[1]);
	$ageCounts[$label] = count($recordInRange);
}
foreach($genderOptions as $value => $label) {
	$recordInRange = DataAnalysis::mapFieldByRecord($consentedData,["gender_identity","gender_identity_child"],$value,false);
	$genderCounts[$label] = count($recordInRange);
}
foreach(DataAnalysis::$houseSizeBrackets as $label => $range) {
	$recordInRange = DataAnalysis::mapFieldByRecordInRange($adultData,"how_many_people_are_curren", $range[0],$range[1]);
	$houseSizeCounts[$label] = count($recordInRange);
}
foreach($insuranceOptions as $value => $label) {
	$recordInRange = DataAnalysis::mapFieldByRecordInRange($adultData,"covered_by_health_insurance", $value, false);
	$insuranceCounts[$label] = count($recordInRange);
}
foreach($educationOptions as $value => $label) {
	$recordInRange = DataAnalysis::mapFieldByRecordInRange($adultData,"highest_grade_level", $value, false);
	$educationCounts[$label] = count($recordInRange);
}

$reportData = [
	"adultCount" => count($adultRecords),
	"pediatricCount" => count($pediatricRecords),
	"giraCount" => count($giraRecords),
	"metreeCount" => count($metreeRecords),
	"householdSizeCounts" => $houseCounts,
	"ageCounts" => $ageCounts,
	"insuranceCounts" => $insuranceCounts,
	"educationCounts" => $educationCounts,
	"genderCounts" => $genderCounts,
];