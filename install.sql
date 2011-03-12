-- actionlog entries
DROP TABLE IF EXISTS wcf1_actionlog_entry;
CREATE TABLE wcf1_actionlog_entry (
  entryID int(10) NOT NULL AUTO_INCREMENT,
  log varchar(255) NOT NULL,
  userID int(10) NOT NULL,
  username varchar(255),
  loggableID int(10) NOT NULL,
  objectID int(10) NOT NULL,
  logEvent varchar(255) NOT NULL,
  reason mediumtext,
  logTime int(10) NOT NULL,
  ipAddress varchar(15) NOT NULL,
  additionalData text,
  PRIMARY KEY (entryID),
  KEY log (log),
  KEY logTime (log,logTime),
  KEY logObject (log,loggableID,objectID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- loggables
DROP TABLE IF EXISTS wcf1_actionlog_loggable;
CREATE TABLE wcf1_actionlog_loggable (
  loggableID int(10) NOT NULL AUTO_INCREMENT,
  loggableName varchar(255) NOT NULL,
  classPath varchar(255) NOT NULL,
  packageID int(10) NOT NULL,
  PRIMARY KEY (loggableID),
  UNIQUE KEY loggableName (loggableName,packageID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
