create table if not exists `b_awz_pull_chanels` (
  `ID` varchar(32) NOT NULL,
  `USER` int(18) NOT NULL DEFAULT '0',
  `DATE_EXPIRED` datetime NOT NULL,
  `TYPE` varchar(16) NOT NULL DEFAULT 'public',
  UNIQUE KEY `ID` (`ID`),
  KEY `USER` (`USER`),
  KEY `DATE_EXPIRED` (`DATE_EXPIRED`)
);