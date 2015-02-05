CREATE TABLE `electric_meter` (
`id`  int(12) NOT NULL AUTO_INCREMENT ,
`date`  date NOT NULL ,
`time`  time NOT NULL ,
`value`  decimal(24,12) NOT NULL ,
`date_time`  datetime NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=latin1 COLLATE=latin1_swedish_ci
AUTO_INCREMENT=1
ROW_FORMAT=COMPACT
;

CREATE TABLE `electric_month` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`t1`  decimal(21,9) NULL DEFAULT NULL ,
`t2`  decimal(21,9) NULL DEFAULT NULL ,
`t3`  decimal(21,9) NULL DEFAULT NULL ,
`from`  datetime NULL DEFAULT NULL ,
`to`  datetime NULL DEFAULT NULL ,
`cur_t1`  decimal(21,9) NULL DEFAULT NULL ,
`cur_t2`  decimal(21,9) NULL DEFAULT NULL ,
`cur_t3`  decimal(21,9) NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=latin1 COLLATE=latin1_swedish_ci
AUTO_INCREMENT=1
ROW_FORMAT=COMPACT
;

CREATE TABLE `electric_tarifs` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`t1`  decimal(12,2) NULL DEFAULT NULL ,
`t2`  decimal(12,2) NULL DEFAULT NULL ,
`t3`  decimal(12,2) NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=latin1 COLLATE=latin1_swedish_ci
AUTO_INCREMENT=1
ROW_FORMAT=COMPACT
;
