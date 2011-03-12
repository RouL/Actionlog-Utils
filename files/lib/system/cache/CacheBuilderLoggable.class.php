<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the loggables.
 * 
 * @author		Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		info.codingcorner.wcf.lib.actionlog
 * @subpackage	lib.system.cache
 * @category	Community Framework
 */
class CacheBuilderLoggable implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array('loggables' => array());
		
		// get all loggables and filter them by priority
		$sql = "SELECT		loggable.loggableID, loggable.loggableName
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_actionlog_loggable loggable
			WHERE 		loggable.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$itemIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$itemIDs[$row['loggableName']] = $row['loggableID'];
		}
		
		if (count($itemIDs) > 0) {
			$sql = "SELECT		loggable.*, package.packageDir
				FROM		wcf".WCF_N."_actionlog_loggable loggable
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = loggable.packageID)
				WHERE 		loggable.loggableID IN (".implode(',', $itemIDs).")
				ORDER BY	loggable.loggableID";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$data['loggables'][$row['loggableName']] = $row;
			}
		}
		
		return $data;
	}
}
?>