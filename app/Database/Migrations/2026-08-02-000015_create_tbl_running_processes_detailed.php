<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblRunningProcessesDetailed extends Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE `tbl_running_processes_detailed` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int unsigned DEFAULT NULL,
  `device_id` varchar(100) DEFAULT NULL,
  `pid` int DEFAULT NULL,
  `name` varchar(500) DEFAULT NULL,
  `ppid` int DEFAULT NULL,
  `uid` int DEFAULT NULL,
  `importance` int DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `tid` int DEFAULT NULL,
  `nice` int DEFAULT NULL,
  `threads` int DEFAULT NULL,
  `vsize_kb` bigint DEFAULT NULL,
  `vsize_peak_kb` bigint DEFAULT NULL,
  `rss_kb` bigint DEFAULT NULL,
  `pss_kb` bigint DEFAULT NULL,
  `uss_kb` bigint DEFAULT NULL,
  `swap_kb` bigint DEFAULT NULL,
  `cpu_time_ms` bigint DEFAULT NULL,
  `cpu_time_user_ms` bigint DEFAULT NULL,
  `cpu_time_system_ms` bigint DEFAULT NULL,
  `start_time` bigint DEFAULT NULL,
  `elapsed_time_ms` bigint DEFAULT NULL,
  `processor` int DEFAULT NULL,
  `cmdline` text,
  `gid` int DEFAULT NULL,
  `groups` text,
  `priority` int DEFAULT NULL,
  `fd_count` int DEFAULT NULL,
  `socket_count` int DEFAULT NULL,
  `wake_lock_count` int DEFAULT NULL,
  `oom_score` int DEFAULT NULL,
  `oom_score_adj` int DEFAULT NULL,
  `cgroup` varchar(500) DEFAULT NULL,
  `selinux_context` varchar(500) DEFAULT NULL,
  `capabilities_eff` varchar(100) DEFAULT NULL,
  `capabilities_prm` varchar(100) DEFAULT NULL,
  `capabilities_inh` varchar(100) DEFAULT NULL,
  `capabilities_bnd` varchar(100) DEFAULT NULL,
  `capabilities_amb` varchar(100) DEFAULT NULL,
  `seccomp_mode` int DEFAULT NULL,
  `env_vars` text,
  `signal_mask` varchar(255) DEFAULT NULL,
  `signal_pending` varchar(255) DEFAULT NULL,
  `signal_blocked` varchar(255) DEFAULT NULL,
  `signal_ignored` varchar(255) DEFAULT NULL,
  `signal_caught` varchar(255) DEFAULT NULL,
  `wake_channels` varchar(255) DEFAULT NULL,
  `timer_slack_ns` varchar(255) DEFAULT NULL,
  `namespace` varchar(255) DEFAULT NULL,
  `open_files` text,
  `memory_maps` text,
  `stack_trace` text,
  `cputime_clock_id` int DEFAULT NULL,
  `cpu_percent` double DEFAULT NULL,
  `extracted_at` bigint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_running_process_detailed_per_device` (`owner_id`,`device_id`,`pid`),
  KEY `owner_id` (`owner_id`),
  KEY `device_id` (`device_id`),
  KEY `pid` (`pid`),
  KEY `name` (`name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    public function down()
    {
        $this->db->query('DROP TABLE IF EXISTS `tbl_running_processes_detailed`');
    }
}
