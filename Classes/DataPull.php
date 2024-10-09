<?php

namespace Data;

class DataPull
{
	public static $consentFields = [
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
	
	public static $otherFields = [
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
	
	public const recordIdConst = "redcap_record_id";
	
	private $module;
	
	private $project_id;
	private $dataCache = false;
	
	
	public function __construct($project_id)
	{
		$this->project_id = $project_id;
		$this->module = new EmergeR4Portal();
	}
	
	public function getRecordIdField() {
		return $this->module->getProject($this->project_id)->getRecordIdField();
	}
	
	public function getData()
	{
		if ($this->dataCache === false) {
			$this->dataCache = \REDCap::getData([
				"project_id" => $this->project_id,
				"fields" => array_merge([$this->getRecordIdField()], self::$consentFields, self::$otherFields),
				"return_format" => "json-array"
			]);
			
			foreach($this->dataCache as &$dataRow) {
				$dataRow[self::recordIdConst] = $dataRow[$this->getRecordIdField()];
			}
		}
		
		return $this->dataCache;
	}
}