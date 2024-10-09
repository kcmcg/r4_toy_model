<?php

namespace Data;

class DataAnalysis
{
	public static $ageBrackets = [
		"3-13" => [3, 12],
		"13-17" => [13, 17],
		"18-30" => [18, 30],
		"31-44" => [31, 44],
		"45-59" => [45, 59],
		"60-75" => [60, 75]
	];
	public static $houseSizeBrackets = [
		"1" => [1, 1],
		"2" => [2, 2],
		"3-4" => [3, 4],
		"5-6" => [5, 6],
		"7-9" => [7, 9],
		"10+" => [10, 100]
	];
	
	public static function getConsentedRecords($recordData) {
		 $consentedRecords = self::mapFieldByRecord($recordData, DataPull::$consentFields, ["2"],false);
		 $withdrawnRecords = self::mapFieldByRecord($recordData, "participant_withdrawal",["1"],false);
		 
		 foreach($withdrawnRecords as $recordId => $withdrawn) {
			unset($consentedRecords[$recordId]);
		 }
		 return $consentedRecords;
	}
	
	public static function getAdultRecords($recordData) {
		return self::mapFieldByRecordInRange($recordData,"age",126,18);
	}
	
	public static function getPediatricRecords($recordData) {
		return self::mapFieldByRecordInRange($recordData,"age",18,0);
	}
	
	public static function mapFieldByRecord($recordData, $fieldNames, $validValues = [], $returnValues = true) {
		$returnData = [];
		
		foreach($fieldNames as $fieldName) {
			## Reduce down to rows with actual data
			$thisRecordData = self::filterDataByField($recordData,$fieldName);
			foreach($thisRecordData as $thisRow) {
				## Skip values not in the validValues list if provided
				if(!empty($validValues) && !in_array($thisRow[$fieldName],$validValues)) {
					continue;
				}
				
				if($returnValues) {
					$returnData[$thisRow["redcap_record_id"]] = $thisRow[$fieldName];
				}
				else {
					$returnData[$thisRow["redcap_record_id"]] = 1;
				}
			}
		}
		return $returnData;
	}
	
	public static function mapFieldByRecordInRange($recordData, $fieldName, $upperCutoff, $minimumValue) {
		$returnData = [];
		
		$recordData = self::filterDataByField($recordData,$fieldName);
		foreach($recordData as $thisRow) {
			if($thisRow[$fieldName] >= $minimumValue && $thisRow[$fieldName] < $upperCutoff) {
				$returnData[$thisRow["redcap_record_id"]] = 1;
			}
		}
		return $returnData;
	}
	
	## Take a json-array set of record data and filter down to rows with a particular field set
	public static function filterDataByField($recordData, $fieldName) {
		return array_filter($recordData, function($thisRow) use ($fieldName) {
			if($thisRow[$fieldName] !== NULL && $thisRow[$fieldName] !== "") {
				return true;
			}
			return false;
		});
	}
	
	## Compare an array of data with record ID keys to a json-array format record data and only return matches
	public static function filterDataByArray($recordData, $filterRecords) {
		return array_filter($recordData,function($thisRow) use ($filterRecords) {
			return array_key_exists($thisRow["redcap_record_id"],$filterRecords);
		});
	}
}