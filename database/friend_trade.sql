CREATE DATABASE IF NOT EXISTS `czz` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `czz`;

CREATE TABLE IF NOT EXISTS `friend_user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL COMMENT '平台用户ID',
  `nickname` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '头像',
  `coins` INT NOT NULL DEFAULT 1000 COMMENT '金币',
  `worth` INT NOT NULL DEFAULT 500 COMMENT '身价',
  `level` INT NOT NULL DEFAULT 1 COMMENT '等级',
  `owner_user_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前经纪人用户ID，0表示自由',
  `partner_count` INT NOT NULL DEFAULT 0 COMMENT '拥有搭档数量',
  `total_earned` INT NOT NULL DEFAULT 0 COMMENT '累计收益',
  `total_spent` INT NOT NULL DEFAULT 0 COMMENT '累计支出',
  `sign_days` INT NOT NULL DEFAULT 0 COMMENT '连续签到天数',
  `last_sign_date` DATE DEFAULT NULL COMMENT '最后签到日期',
  `last_growth_date` DATE DEFAULT NULL COMMENT '最后身价自然增长日期',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1正常 0禁用',
  `create_time` INT UNSIGNED NOT NULL DEFAULT 0,
  `update_time` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_id` (`user_id`),
  KEY `idx_worth` (`worth`),
  KEY `idx_coins` (`coins`),
  KEY `idx_owner` (`owner_user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='好友签约游戏用户表';

CREATE TABLE IF NOT EXISTS `friend_relation` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_user_id` INT UNSIGNED NOT NULL COMMENT '经纪人用户ID',
  `partner_user_id` INT UNSIGNED NOT NULL COMMENT '搭档用户ID',
  `buy_price` INT NOT NULL DEFAULT 0 COMMENT '签约价格',
  `current_worth` INT NOT NULL DEFAULT 0 COMMENT '签约后身价',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1有效 0已解除',
  `create_time` INT UNSIGNED NOT NULL DEFAULT 0,
  `update_time` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_owner` (`owner_user_id`),
  KEY `idx_partner` (`partner_user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_partner_status` (`partner_user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='好友签约关系表';

CREATE TABLE IF NOT EXISTS `friend_trade_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `buyer_user_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '买家/新经纪人',
  `seller_user_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '原经纪人',
  `partner_user_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '被签约用户',
  `trade_type` TINYINT NOT NULL DEFAULT 1 COMMENT '1签约 2转签 3解约',
  `price` INT NOT NULL DEFAULT 0 COMMENT '成交价格',
  `seller_income` INT NOT NULL DEFAULT 0 COMMENT '原经纪人收益',
  `system_fee` INT NOT NULL DEFAULT 0 COMMENT '系统回收',
  `old_worth` INT NOT NULL DEFAULT 0 COMMENT '原身价',
  `new_worth` INT NOT NULL DEFAULT 0 COMMENT '新身价',
  `create_time` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_buyer` (`buyer_user_id`),
  KEY `idx_seller` (`seller_user_id`),
  KEY `idx_partner` (`partner_user_id`),
  KEY `idx_type` (`trade_type`),
  KEY `idx_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='好友签约交易记录表';

CREATE TABLE IF NOT EXISTS `friend_work_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_user_id` INT UNSIGNED NOT NULL COMMENT '经纪人用户ID',
  `partner_user_id` INT UNSIGNED NOT NULL COMMENT '搭档用户ID',
  `income` INT NOT NULL DEFAULT 0 COMMENT '派单收益',
  `work_date` DATE NOT NULL COMMENT '派单日期',
  `create_time` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_partner_date` (`partner_user_id`, `work_date`),
  KEY `idx_owner_date` (`owner_user_id`, `work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='好友派单记录表';

CREATE TABLE IF NOT EXISTS `friend_coin_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
  `change_amount` INT NOT NULL DEFAULT 0 COMMENT '变动金币，可正可负',
  `before_amount` INT NOT NULL DEFAULT 0 COMMENT '变动前金币',
  `after_amount` INT NOT NULL DEFAULT 0 COMMENT '变动后金币',
  `scene` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '场景 sign/buy/sell/release/work/system',
  `remark` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_user_time` (`user_id`, `create_time`),
  KEY `idx_scene` (`scene`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='好友签约金币流水表';

CREATE TABLE IF NOT EXISTS `friend_notice` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL COMMENT '接收用户ID',
  `type` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '通知类型',
  `title` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '标题',
  `content` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '内容',
  `is_read` TINYINT NOT NULL DEFAULT 0 COMMENT '0未读 1已读',
  `create_time` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`, `is_read`),
  KEY `idx_type` (`type`),
  KEY `idx_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='好友签约游戏通知表';

CREATE TABLE IF NOT EXISTS `friend_config` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `config_key` VARCHAR(100) NOT NULL DEFAULT '',
  `config_value` TEXT,
  `remark` VARCHAR(255) NOT NULL DEFAULT '',
  `update_time` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='好友签约游戏配置表';

INSERT INTO `friend_config` (`config_key`, `config_value`, `remark`, `update_time`) VALUES
('init_coins', '1000', '初始金币', UNIX_TIMESTAMP()),
('init_worth', '500', '初始身价', UNIX_TIMESTAMP()),
('sign_base_reward', '500', '签到基础奖励', UNIX_TIMESTAMP()),
('sign_extra_per_day', '50', '连续签到每日额外奖励', UNIX_TIMESTAMP()),
('sign_extra_max', '500', '连续签到最高额外奖励', UNIX_TIMESTAMP()),
('worth_up_rate', '0.2', '签约后身价上涨比例', UNIX_TIMESTAMP()),
('worth_up_min', '100', '签约后最低身价上涨值', UNIX_TIMESTAMP()),
('daily_growth_rate', '0.02', '每日身价自然增长比例', UNIX_TIMESTAMP()),
('daily_growth_min', '20', '每日身价最低自然增长值', UNIX_TIMESTAMP()),
('release_rate', '0.5', '解约费用比例', UNIX_TIMESTAMP()),
('seller_income_rate', '0.9', '原经纪人转让收益比例', UNIX_TIMESTAMP()),
('work_income_rate', '0.05', '派单收益比例', UNIX_TIMESTAMP()),
('work_income_min', '50', '派单最低收益', UNIX_TIMESTAMP()),
('work_income_max', '2000', '派单最高收益', UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
`config_value` = VALUES(`config_value`),
`remark` = VALUES(`remark`),
`update_time` = VALUES(`update_time`);
