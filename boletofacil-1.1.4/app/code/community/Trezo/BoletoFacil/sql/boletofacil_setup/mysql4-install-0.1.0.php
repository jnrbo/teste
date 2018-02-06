<?php

// here are the table creation for this module e.g.:
$this->startSetup();
$this->run("
CREATE TABLE {$this->getTable('trezo_boleto')} (
  `boleto_id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) NOT NULL,
  `boleto_facil_code` int(11) NOT NULL,
  `data_vencimento` datetime NOT NULL,
  `url` TEXT NOT NULL,
  `payment_token` TEXT,
  `valor` float NOT NULL,
  `valor_pago` float,
  `status` varchar(20) NOT NULL default '0',
  `nome` varchar(100) NOT NULL,
  `cpf_cnpj` varchar(20) NOT NULL,
  `created_time` TIMESTAMP default CURRENT_TIMESTAMP,
  `updated_time` datetime NULL,
  PRIMARY KEY (`boleto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$this->endSetup();
