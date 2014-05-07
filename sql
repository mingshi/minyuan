CREATE DATABASE minyuan;

CREATE TABLE `minyuan` (
    id int NOT NULL auto_increment primary key,
    mobile int(11) NOT NULL DEFAULT '0' COMMENT '用户手机号',
    order_name varchar(255) NOT NULL DEFAULT '' COMMENT '订单名称',
    status varchar(50) NOT NULL DEFAULT '正在制作' COMMENT '订单状态',
    KEY(mobile)
) engine=innodb default charset utf8;
