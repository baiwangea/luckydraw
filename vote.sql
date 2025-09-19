/*
 Navicat Premium Dump SQL

 Source Server         : Dnmp-local-5
 Source Server Type    : MySQL
 Source Server Version : 50741 (5.7.41-log)
 Source Host           : localhost:3306
 Source Schema         : luckydraw

 Target Server Type    : MySQL
 Target Server Version : 50741 (5.7.41-log)
 File Encoding         : 65001

 Date: 19/09/2025 10:34:25
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ld_vote
-- ----------------------------
DROP TABLE IF EXISTS `ld_vote`;
CREATE TABLE `ld_vote` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(100) NOT NULL DEFAULT '' COMMENT '用户名',
  `photo` varchar(256) NOT NULL DEFAULT '' COMMENT '图片',
  `ballot` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '票数',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of ld_vote
-- ----------------------------
BEGIN;
INSERT INTO `ld_vote` (`id`, `username`, `photo`, `ballot`, `status`, `create_time`, `update_time`) VALUES (1, 'James L. Whitaker', '', 1, 1, 1755428210, 1758249155);
INSERT INTO `ld_vote` (`id`, `username`, `photo`, `ballot`, `status`, `create_time`, `update_time`) VALUES (2, 'Marcus D. Klein', '', 0, 1, 1755428210, 1755428210);
COMMIT;

-- ----------------------------
-- Table structure for ld_vote_record
-- ----------------------------
DROP TABLE IF EXISTS `ld_vote_record`;
CREATE TABLE `ld_vote_record` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `vid` int(11) NOT NULL COMMENT '投票相关ID',
  `ballot` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '票数',
  `ip` varchar(45) NOT NULL COMMENT '投票者IP（支持IPv6）',
  `user_agent` text COMMENT '浏览器User-Agent',
  `fingerprint` varchar(64) DEFAULT NULL COMMENT '浏览器指纹（可选）',
  `cookie_id` varchar(64) DEFAULT NULL COMMENT '前端生成的UUID（存入cookie）',
  `voted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_poll_ip` (`vid`,`ip`),
  KEY `idx_poll_cookie` (`vid`,`cookie_id`),
  KEY `idx_poll_fingerprint` (`vid`,`fingerprint`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of ld_vote_record
-- ----------------------------
BEGIN;
INSERT INTO `ld_vote_record` (`id`, `vid`, `ballot`, `ip`, `user_agent`, `fingerprint`, `cookie_id`, `voted_at`) VALUES (1, 1, 1, '192.168.65.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '', '6', '2025-09-19 10:32:35');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
