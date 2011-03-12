<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/actionlog/LogEntry.class.php');

/**
 * Represents a list of actionlog entries.
 * 
 * @author		Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		info.codingcorner.wcf.lib.actionlog
 * @subpackage	lib.data.project
 * @category	Community Framework
 */
class LogEntryList extends DatabaseObjectList {
	/**
	 * list of LogEntries
	 *
	 * @var array<LogEntry>
	 */
	public $entries = array();

	/**
	 * sql order by statement
	 *
	 * @var	string
	 */
	public $sqlOrderBy = 'actionlog_entry.logTime DESC';
	
	/**
	 * The log for which we need to get the entries.
	 * 
	 * @var	string
	 */
	public $log = '';
	
	/**
	 * Creates a new LogEntryList object.
	 * 
	 * @param	string	$log
	 */
	public function __construct($log = '') {
		$this->log = $log;
	}
	
	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_actionlog_entry actionlog_entry
			WHERE	log = '".escapeString($this->log)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		return $row['count'];
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		// get entries
		$sql = "SELECT		actionlog_entry.*, actionlog_loggable.loggableName".$this->sqlSelects."
			FROM		wcf".WCF_N."_actionlog_entry actionlog_entry
			LEFT JOIN	wcf".WCF_N."_actionlog_loggable actionlog_loggable
				ON		actionlog_entry.loggableID = actionlog_loggable.loggableID
			".$this->sqlJoins."
			WHERE		actionlog_entry.log = '".escapeString($this->log)."'
			".$this->sqlConditions."
			ORDER BY	".$this->sqlOrderBy."
			LIMIT		".intval($this->sqlOffset).",".intval($this->sqlLimit);
		$result = WCF::getDB()->sendQuery($sql);
		$loggableIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($loggableIDs[$row['loggableName']])) $loggableIDs[$row['loggableName']] = array();
			$loggableIDs[$row['loggableName']][$row['entryID']] = $row['objectID'];
			$this->entries[] = new LogEntry(null, $row);
		}
		
		$objects = array();
		foreach ($loggableIDs as $loggableName => $objectIDs) {
			$loggable = ActionlogUtil::getLoggable($loggableName);
			$objects[$loggableName] = $loggable->getObjectsByIDs($objectIDs);
		}
		
		foreach ($this->entries as $key => $entry) {
			$this->entries[$key]->setObject($objects[$entry->loggableName][$entry->objectID]);
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->entries;
	}
}
?>