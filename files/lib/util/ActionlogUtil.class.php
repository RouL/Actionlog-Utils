<?php

/**
 * Util for loggin actions.
 * 
 * @author		Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license		GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl.html>
 * @package		info.codingcorner.wcf.lib.actionlog
 * @subpackage	lib.util
 * @category	Community Framework
 */

class ActionlogUtil {
	/**
	 * Holds loggables data before they're instantiated.
	 * 
	 * @var	array<array>
	 */
	protected static $loggablesData = null;
	
	/**
	 * Holds all loggables.
	 * 
	 * @var	array<Loggable>
	 */
	protected static $loggables = array();
	
	/**
	 * Writes an entry to the given actionlog
	 * 
	 * @param	string	$log
	 * @param	integer	$loggableName
	 * @param	integer	$objectID
	 * @param	string	$logEvent
	 * @param	string	$reason
	 * @param	integer	$userID
	 * @param	string	$ipAddress
	 */
	public static function log($log, $loggableName, $objectID, $logEvent = null, $reason = null, $userID = null, $username = null, $ipAddress = null, $additionalData = array()) {
		// use default event 'changed' if null
		if ($logEvent == null) $logEvent = 'changed';
		
		// get user id from active user if null
		if ($userID == null) $userID = WCF::getUser()->userID;
		
		// get username from active user if null and user is not a guest
		if ($username == null && $userID > 0) $username = WCF::getUser()->username;
		
		// get IP from session if null
		if ($ipAddress == null) $ipAddress = WCF::getSession()->ipAddress;
		
		// get loggable
		$loggable = self::getLoggable($loggableName);
		
		$sql = "INSERT INTO wcf".WCF_N."_actionlog_entry (
				log,
				userID,
				".(($username !== null) ? ("username,") : (""))."
				loggableID,
				objectID,
				logEvent,
				".(($reason !== null) ? ("reason,") : (""))."
				logTime,
				ipAddress
				".((count($additionalData) > 0) ? (",additionalData") : (""))."
			)
			VALUES (
				'".escapeString($log)."',
				".intval($userID).",
				".(($username !== null) ? ("'".escapeString($username)."',") : (""))."
				".intval($loggable->loggableID).",
				".intval($objectID).",
				'".escapeString($logEvent)."',
				".(($reason !== null) ? ("'".escapeString($reason)."',") : (""))."
				".intval(TIME_NOW).",
				'".escapeString($ipAddress)."'
				".((count($additionalData) > 0) ? (",'".serialize($additionalData)."'") : (""))."
			)";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Get the loggable with the given name.
	 * 
	 * @param	string	$loggableName
	 * 
	 * @return	Loggable
	 * @throws	SystemException
	 */
	public static function getLoggable($loggableName) {
		if (self::$loggablesData == null) {
			self::loadLoggables();
		}
		
		if (!isset(self::$loggables[$loggableName])) {
			if (!isset(self::$loggablesData[$loggableName])) {
				throw new SystemException("Unable to find loggable '".$loggableName."'");
			}
			$loggableData = self::$loggablesData[$loggableName];
			
			// calculate class path
			$path = '';
			if (empty($loggableData['packageDir'])) {
				$path = WCF_DIR;
			}
			else {
				$path = FileUtil::getRealPath(WCF_DIR.$loggableData['packageDir']);
			}
			
			// include class file
			if (!file_exists($path.$loggableData['classPath'])) {
				throw new SystemException("Unable to find class file '".$path.$loggableData['classPath']."'");
			}
			require_once($path.$loggableData['classPath']);
			
			// create instance
			$className = StringUtil::getClassName($loggableData['classPath']);
			if (!class_exists($className)) {
				throw new SystemException("Unable to find class '".$className."'");
			}
			self::$loggables[$loggableName] = new $className(null, $loggableData);
			
			// save memory ;)
			unset(self::$loggablesData[$loggableName]);
		}
		
		return self::$loggables[$loggableName];
	}
	
	/**
	 * Loads loggables cache.
	 */
	protected static function loadLoggables() {
		WCF::getCache()->addResource('loggables-'.PACKAGE_ID, WCF_DIR.'cache/cache.loggables-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderLoggable.class.php');
		self::$loggablesData = WCF::getCache()->get('loggables-'.PACKAGE_ID, 'loggables');
	}
}