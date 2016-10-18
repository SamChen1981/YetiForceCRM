<?php

/**
 * Action to clipboard
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_ClipBoard_Action extends Vtiger_BasicAjax_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
		$this->exposeMethod('addAllRecords');
		$this->exposeMethod('addRecord');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function addAllRecords(Vtiger_Request $request)
	{
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$count = $coordinatesModel->saveAllRecordsToCache($request->get('srcModule'));
		$response = new Vtiger_Response();
		$response->setResult(['count' => $count]);
		$response->emit();
	}

	public function delete(Vtiger_Request $request)
	{
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->deleteCache($request->get('srcModule'));
		$response = new Vtiger_Response();
		$response->setResult(0);
		$response->emit();
	}

	public function save(Vtiger_Request $request)
	{
		$srcModuleName = $request->get('srcModule');
		$records = $request->get('recordIds');
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->deleteCache($srcModuleName);
		$coordinatesModel->saveCache($srcModuleName, $records);
		$response = new Vtiger_Response();
		$response->setResult(count($records));
		$response->emit();
	}

	public function addRecord(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$srcModuleName = $request->get('srcModuleName');
		$coordinatesModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinatesModel->addCache($srcModuleName, $record);
		$moduleModel = Vtiger_Module_Model::getInstance($srcModuleName);
		$coordinatesModel->set('srcModuleModel', $moduleModel);
		$coordinates = $coordinatesModel->readCoordinatesByRecords([$record]);
		if(empty($coordinates)) {
			$coordinates = vtranslate('ERR_ADDRESS_NOT_FOUND', 'OpenStreetMap');
		}
		$response = new Vtiger_Response();
		$response->setResult($coordinates);
		$response->emit();
	}
}
