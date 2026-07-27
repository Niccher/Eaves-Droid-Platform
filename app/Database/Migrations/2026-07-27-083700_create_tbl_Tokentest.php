<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTokentest extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_Tokentest` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `token_submitted` varchar(100) NOT NULL,
  `token_senttime` varchar(20) NOT NULL,
  `token_received` varchar(20) NOT NULL,
  `token_ip` varchar(20) NOT NULL,
  `token_format` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_Tokentest`');
    }
}
