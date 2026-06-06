<?php

/**
 * Application-wide procedural functions (loaded on every request via bootstrap).
 *
 * @see https://codeigniter.com/user_guide/extending/common.html
 */

$timeHelper = __DIR__ . '/Helpers/time_helper.php';
if (is_file($timeHelper)) {
    require_once $timeHelper;
}
