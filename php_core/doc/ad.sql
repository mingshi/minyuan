--格式说明 

--@author 添加人姓名
--@date 日期
--@desc 描述
--@todo dev
--@todo test
--@todo product  每个环境部署完代码后，删除相应的todo行
------------------------------------------------------

--@author wanggang   
--@date 2014-03-05
--@desc 描述
------------------------------------------------------
use ad_core;

create table order_bid_log (
    id int not null auto_increment primary key,
    order_id int not null,
    date date not null,
    price decimal(10,2) not null,
    last_update_time timestamp not null default current_timestamp on update current_timestamp,
    unique key(order_id, date)
) engine=innodb default charset utf8;
