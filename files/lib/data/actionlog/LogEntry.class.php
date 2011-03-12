<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents an actionlog entry.
 * 
 * @author		Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		info.codingcorner.wcf.lib.actionlog
 * @subpackage	lib.data.project
 * @category	Community Framework
 */
class LogEntry extends DatabaseObject {
	protected $object;
	
	/**
	 * Creates a LogEntry object.
	 * 
	 * @param	integer	$entryID
	 * @param	array	$row
	 */
	public function __construct($entryID, $row = null) {
		if ($entryID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_actionlog_entry
				WHERE	entryID = ".intval($entryID);
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
	}
	
	/**
	 * @see	DatabaseObject::handleData()
	 */
	public function handleData($data) {
		parent::handleData($data);
		
		if ($this->data['additionalData'] !== null) $this->data['additionalData'] = unserialize($this->data['additionalData']);
		else $this->data['additionalData'] = array();
	}
	
	public function setObject($object) {
		$this->object = $object;
	}
	
	public function getObject() {
		return $this->object;
	}
	
	public function __toString() {
		return WCF::getLanguage()->getDynamicVariable('wcf.actionlog.entry.'.$this->loggableName.'.'.$this->logEvent, array('entry' => $this, 'object' => $this->getObject(), 'additionalData' => $this->additionalData));
	}
}
?>