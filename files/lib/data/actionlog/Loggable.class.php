<?php

/**
 * All loggables should implement this interface.
 * 
 * @author		Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		info.codingcorner.wcf.lib.actionlog
 * @subpackage	lib.data.project
 * @category	Community Framework
 */
interface Loggable {
	/**
	 * Gets objects by given object ids.
	 * 
	 * @param	array	$objectIDs
	 * 
	 * @return	array<Loggable>
	 */
	public function getObjectsByIDs($objectIDs);
}

?>