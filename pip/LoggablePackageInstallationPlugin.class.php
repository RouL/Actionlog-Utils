<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes loggables.
 * 
 * @author		Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		info.codingcorner.wcf.lib.actionlog
 * @subpackage	lib.acp.package.plugin
 * @category	Community Framework
 */
class LoggablePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'loggable';
	public $tableName = 'actionlog_loggable';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		if (!$xml = $this->getXML()) {
			return;
		}
		
		// Create an array with the data blocks (import or delete) from the xml file.
		$xml = $xml->getElementTree('data');
		
		// Loop through the array and install or uninstall loggable items.
		foreach ($xml['children'] as $key => $block) {
			if (count($block['children'])) {
				// Handle the import instructions
				if ($block['name'] == 'import') {
					// Loop through items and create or update them.
					foreach ($block['children'] as $loggable) {
						// Extract item properties.
						foreach ($loggable['children'] as $child) {
							if (!isset($child['cdata'])) continue;
							$loggable[$child['name']] = $child['cdata'];
						}
					
						// check required attributes
						if (!isset($loggable['attrs']['name'])) {
							throw new SystemException("Required 'name' attribute for 'loggable'-tag is missing.");
						}
						
						// default values
						$loggableName = $classPath = '';
						
						// get values
						$loggableName = $loggable['attrs']['name'];
						if (isset($loggable['classpath'])) $classPath = $loggable['classpath'];
						
						// check if the loggable exist already and was installed by this package
						$sql = "SELECT	loggableID
							FROM 	wcf".WCF_N."_".$this->tableName."
							WHERE 	loggableName = '".escapeString($loggableName)."'
							AND	packageID = ".$this->installation->getPackageID();
						$row = WCF::getDB()->getFirstRow($sql);
						if (empty($row['loggableID'])) {
							$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."
										(packageID, loggableName, classPath)
								VALUES		(".$this->installation->getPackageID().",
										'".escapeString($loggableName)."',
										'".escapeString($classPath)."')";
							WCF::getDB()->sendQuery($sql);
						}
						else {
							$sql = "UPDATE  wcf".WCF_N."_".$this->tableName."
								SET	classPath = '".escapeString($classPath)."'
								WHERE	loggableID = ".$row['loggableID'];
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
				// Handle the delete instructions.
				else if ($block['name'] == 'delete') {
					if ($this->installation->getAction() == 'update') {
						// Loop through items and delete them.
						$itemNames = '';
						foreach ($block['children'] as $loggable) {
							// check required attributes
							if (!isset($loggable['attrs']['name'])) {
								throw new SystemException("Required 'name' attribute for 'loggable'-tag is missing.");
							}
							// Create a string with all item names which should be deleted (comma seperated).
							if (!empty($itemNames)) $itemNames .= ',';
							$itemNames .= "'".escapeString($loggable['attrs']['name'])."'";
						}
						// Delete items.
						if (!empty($itemNames)) {
							$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
								WHERE		loggableName IN (".$itemNames.")
								AND 		packageID = ".$this->installation->getPackageID();
							WCF::getDB()->sendQuery($sql);
						}
					}
				}
			}
		}
	}
}
?>