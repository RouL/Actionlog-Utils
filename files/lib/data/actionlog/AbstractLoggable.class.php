<?php
// wcf imports
require_once(WCF_DIR.'lib/data/actionlog/Loggable.class.php');

/**
 * Loggable-implementation for projects. 
 * 
 * @author		Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		info.codingcorner.wcf.lib.actionlog
 * @subpackage	lib.data.project
 * @category	Community Framework
 */
abstract class AbstractLoggable extends DatabaseObject implements Loggable {
	/**
	 * Creates a Loggable object.
	 * 
	 * @param	integer	$loggableID
	 * @param	array	$row
	 */
	public function __construct($loggableID, $row = null) {
		if ($loggableID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_actionlog_loggable
				WHERE	loggableID = ".intval($loggableID);
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
	}
	
	/**
	 * @see Loggable::getObjectsByIDs()
	 */
	public function getObjectsByIDs($objectIDs) {
		throw new SystemException("Method getObjectsByIDs not implemented for loggable '".get_class($this)."'");
	}
}
?>