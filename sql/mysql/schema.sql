DROP TABLE IF EXISTS `oceditorialstuffhistory`;
CREATE TABLE `oceditorialstuffhistory` (
  `handler` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `object_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `created_time` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `params_serialized` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;