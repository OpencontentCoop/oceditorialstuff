DROP TABLE IF EXISTS `oceditorialstuffhistory`;
CREATE TABLE `oceditorialstuffhistory` (
  `id` int(11) NOT NULL,
  `handler` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `object_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `created_time` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `params_serialized` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `oceditorialstuffhistory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `handler` (`handler`),
  ADD KEY `object_id` (`object_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `oceditorialstuffhistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


DROP TABLE IF EXISTS `oceditorialstuffnotificationrule`;
CREATE TABLE `oceditorialstuffnotificationrule` (
  `id` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_bin NOT NULL,
  `post_id` int(11) NOT NULL,
  `use_digest` int(1) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `oceditorialstuffnotificationrule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `oceditorialstuffnotificationrule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;