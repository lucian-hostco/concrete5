<?
defined('C5_EXECUTE') or die("Access Denied.");
class WorkflowType extends Object {

	public function getWorkflowTypeID() {return $this->wftID;}
	public function getWorkflowTypeHandle() {return $this->wftHandle;}
	public function getWorkflowTypeName() {return $this->wftName;}
	
	public static function getByID($wftID) {
		$db = Loader::db();
		$row = $db->GetRow('select wftID, pkgID, wftHandle, wftName from WorkflowTypes where wftID = ?', array($wftID));
		if ($row['wftHandle']) {
			$class = Loader::helper('text')->camelcase($row['wftHandle']) . 'WorkflowType';
			$file = Loader::helper('concrete/path')->getPath(DIRNAME_MODELS . '/' . DIRNAME_WORKFLOW . '/' . DIRNAME_SYSTEM_TYPES . '/' . $row['wftHandle'] . '.php', $row['pkgID']);
			require_once($file);
			$wt = new $class();
			$wt->setPropertiesFromArray($row);
			return $wt;
		}
	}

	protected function loadController() { 
		$txt = Loader::helper('text');
		$className = $txt->camelcase($this->wftHandle) . 'WorkflowTypeController';
		$file = Loader::helper('concrete/path')->getPath(DIRNAME_MODELS . '/' . DIRNAME_WORKFLOW . '/' . DIRNAME_SYSTEM_TYPES . '/' . $this->wftHandle . '/' . FILENAME_CONTROLLER, $this->pkgID);
		if ($file) { 
			require_once($file);
			$this->controller = new $className($this);
		}
	}
	
	
	public function __destruct() {
		unset($this->controller);
	}
	
	public static function getList() {
		$db = Loader::db();
		$list = array();
		$r = $db->Execute('select wftID from WorkflowTypes order by wftID asc');

		while ($row = $r->FetchRow()) {
			$list[] = WorkflowType::getByID($row['wftID']);
		}
		
		$r->Close();
		return $list;
	}
	
	public static function exportList($xml) {
		$wtypes = WorkflowType::getList();
		$db = Loader::db();
		$axml = $xml->addChild('workflowtypes');
		foreach($wtypes as $wt) {
			$wtype = $axml->addChild('workflowtype');
			$wtype->addAttribute('handle', $wt->getWorkflowTypeHandle());
			$wtype->addAttribute('name', $wt->getWorkflowTypeName());
			$wtype->addAttribute('package', $wt->getPackageHandle());
		}
	}
	
	public function delete() {
		$db = Loader::db();
		if (method_exists($this->controller, 'deleteType')) {
			$this->controller->deleteType();
		}
		
		$db->Execute("delete from WorkflowTypes where wftID = ?", array($this->wftID));
	}
	
	public static function getListByPackage($pkg) {
		$db = Loader::db();
		$list = array();
		$r = $db->Execute('select wftID from WorkflowTypes where pkgID = ? order by wftID asc', array($pkg->getPackageID()));
		while ($row = $r->FetchRow()) {
			$list[] = WorkflowType::getByID($row['wftID']);
		}
		$r->Close();
		return $list;
	}	
	
	protected function getAssignmentClass() {
 		$class = str_replace('BasicWorkflowType', 'BasicWorkflowAssignment', get_class($this));
 		if (!class_exists($class)) {
 			$class = 'BasicWorkflowAssignment';
 			require_once(Loader::helper('concrete/path')->getPath(DIRNAME_MODELS . '/' . DIRNAME_WORKFLOW . '/' . DIRNAME_WORKFLOW_ASSIGNMENTS . '/' . $this->getWorkflowTypeHandle() . '.php', $this->getPackageHandle()));
 		}
 		return $class;
 	}
	
	protected function buildAssignmentFilterString($filterEntities) { 
		$peIDs = '';
		$filters = array();
		if (count($filterEntities) > 0) {
			foreach($filterEntities as $ent) {
				$filters[] = $ent->getAccessEntityID();
			}
			$peIDs .= 'and peID in (' . implode($filters, ',') . ')';
		}
		return $peIDs;
	}

	public function getPackageID() { return $this->pkgID;}
	public function getPackageHandle() {
		return PackageList::getHandle($this->pkgID);
	}
	
	public static function getByHandle($wftHandle) {
		$db = Loader::db();
		$wftID = $db->GetOne('select wftID from WorkflowTypes where wftHandle = ?', array($wftHandle));
		if ($wftID > 0) {
			return self::getByHandle($wftID);
		}
	}
	
	public static function add($wftHandle, $wftName, $pkg = false) {
		$pkgID = 0;
		if (is_object($pkg)) {
			$pkgID = $pkg->getPackageID();
		}
		$db = Loader::db();
		$db->Execute('insert into WorkflowTypes (wftHandle, wftName, pkgID) values (?, ?, ?)', array($wftHandle, $wftName, $pkgID));
		$id = $db->Insert_ID();
		$est = WorkflowType::getByID($id);
		return $est;
	}
	
}