drop table if exists `test_table`;

create table `test_table`
(
    `id`   int          not null auto_increment,
    `name` varchar(100) not null,
    primary key (`id`)
);

insert into `test_table` (`name`)
values ('John'),
       ('Peter');
