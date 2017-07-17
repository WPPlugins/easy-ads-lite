<?php

/*
  Copyright (C) 2008 www.ads-ez.com

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License as
  published by the Free Software Foundation; either version 3 of the
  License, or (at your option) any later version.

  This program is distributed in the hope that they will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with the programs.  If not, see <http://www.gnu.org/licenses/>.
 */

$ezErrorMsg = "";
$phpVersion = floatval(phpversion());
if (!empty($_SERVER['SCRIPT_FILENAME'])) {
  $scriptName = strtolower(basename(dirname($_SERVER['SCRIPT_FILENAME'])));
}
else {
  $scriptName = "Warning";
}
if ($phpVersion < 5.0) {
  $ezErrorMsg = "<b><em>$scriptName</em></b>: " .
          "Your PHP version (" . $phpVersion . ") is too old. This plugin " .
          "needs version 5.3+. Please get your contact your web-hosting " .
          "support to upgrade your PHP version, or consider the " .
          "<a href='http://www.thulasidas.com/plugins/easy-adsense/' " .
          "target='_blank'><em>Easy AdSense</em></a> or <a " .
          "href='http://www.thulasidas.com/plugins/adsense-now/' " .
          "target='_blank'><em>AdSense Now!</em></a> plugin.<br/>";
  exit($ezErrorMsg);
}
$required = array("ezAPI.php", "ezExtras.php", "providers.php");
$optionals = array("chitika.php", "clicksor.php", "bidvertiser.php",
    "google.php", "pro/functions.php");

foreach ($required as $r) {
  if (file_exists("$pwd/$r")) {
    include_once("$pwd/$r");
  }
  else {
    $ezErrorMsg = "Required file $r not found! Quitting...";
    exit($ezErrorMsg);
  }
}
foreach ($optionals as $o) {
  if (file_exists("$pwd/$o")) {
    include_once("$pwd/$o");
  }
}

if (!function_exists("ezCheckCompat")) {
  if (!function_exists('is_plugin_active')) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
  }

  function ezCheckCompat() {
    $incompats = array("Easy Ads", "Google AdSense", "Easy Chitika");
    $active = array();
    $activating = urldecode($_GET['plugin']);
    foreach ($incompats as $name) {
      $pro = strtr(strtolower($name), " ", "-");
      $pro .= "/$pro.php";
      if (is_plugin_active($pro)) {
        $active[$name . " Pro"] = $pro;
      }
      if ($activating == $pro) {
        $activating = $name . " Pro";
      }
    }
    foreach ($incompats as $name) {
      $lite = strtr(strtolower($name), " ", "-");
      $lite .= "-lite/$lite.php";
      if (is_plugin_active($lite)) {
        $active[$name . " Lite"] = $lite;
      }
      if ($activating == $lite) {
        $activating = $name . " Lite";
      }
    }
    if (count($active) > 0) {
      $errorMsg = "<b><i>$activating</i></b>: The following plugin(s) cannot be active with the plugin you are trying to activate. Please use only one of them.<br />";
      foreach ($active as $k => $v) {
        $errorMsg .= "<b><i>$k</i></b><br />";
      }
      die($errorMsg);
    }
  }

}

register_activation_hook($plgFile, "ezCheckCompat");
