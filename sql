CREATE DATABASE minyuan;

use minyuan;

CREATE TABLE `minyuan` (
    id int NOT NULL auto_increment primary key,
    mobile char(11) NOT NULL DEFAULT '0' COMMENT '用户手机号',
    order_name varchar(255) NOT NULL DEFAULT '' COMMENT '订单名称',
    status varchar(50) NOT NULL DEFAULT '正在制作' COMMENT '订单状态',
    KEY(mobile)
) engine=innodb default charset utf8;

CREATE TABLE `user` (
    id int NOT NULL auto_increment primary key,
    username varchar(50) NOT NULL DEFAULT '',
    passwd char(32) NOT NULL DEFAULT '',
    is_admin tinyint(1) NOT NULL DEFAULT '0',
    UNIQUE KEY(username)
) engine=innodb default charset utf8;

alter table minyuan add `uniq` char(32) NOT NULL DEFAULT '';
alter table minyuan add `uid` int(11) NOT NULL DEFAULT '0';
alter table minyuan add `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;
alter table minyuan add UNIQUE KEY `uniq` (`uniq`);

alter table minyuan add `number` int(11) NOT NULL DEFAULT '1';
alter table minyuan add `order_date` varchar(50) NOT NULL DEFAULT '';
alter table minyuan add `order_no` varchar(50) NOT NULL DEFAULT '';

alter table minyuan add UNIQUE KEY `order_no` (`order_no`);
