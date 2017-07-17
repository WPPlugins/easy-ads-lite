<?php

/*
  Copyright (C) 2008 www.ads-ez.com

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License as
  published by the Free Software Foundation; either version 3 of the
  License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!function_exists('ezUpdateOption')) {

  function ezUpdateOption($name, $option) {
    return update_option($name, $option);
  }

}

if (!function_exists('ezGetOption')) {

  function ezGetOption($name, $option) {
    return get_option($name);
  }

}

if (!class_exists("ezNS")) {

  class ezNS { // Name Space simulation

    static $themeName, $CWD, $baseName, $URL, $name, $pluginName, $pluginKey;
    static $defaults, $locale, $genOptionName, $genOptions, $nameSpaceSuffix;
    static $isFiltered, $isPure, $gCount, $b64;

    static function setNS($FILE, $PLUGINDIR, $killLite = false) {
      $themeName = get_option('stylesheet');
      $CWD = dirname($FILE); // /pwd/easy-plugin
      $baseName = basename($CWD); // easy-plugin
      $URL = plugin_dir_url(__FILE__); // http://blog/wp-content/easy-plugin
      $name = ucwords(str_replace('-', ' ', $baseName)); // Easy Plugin
      if ($killLite) {
        $name = str_replace(' Lite', '', $name);
      }
      $pluginName = str_replace(' ', '-', $name); // Easy-Plugin
      $genOptionName = 'ezAPI-' . $pluginName . '-' . $themeName; //ezAPI-Easy-Plugin
      $nameSpaceSuffix = '_' . str_replace(' ', '_', $name); // _Easy_Plugin
      $pluginKey = $baseName . "/" . basename($FILE); // easy-plugin/easy-plugin.php
      self::$themeName = $themeName;
      self::$CWD = $CWD;
      self::$baseName = $baseName;
      self::$URL = $URL;
      self::$name = $name;
      self::$pluginName = $pluginName;
      self::$genOptionName = $genOptionName;
      self::$nameSpaceSuffix = $nameSpaceSuffix;
      self::$pluginKey = $pluginKey;
    }

    static function getNS() {
      return self::$nameSpaceSuffix;
    }

    static function ns($var) {
      return $var;
    }

    public static function setStaticVars($dynDefaults) {
      self::$defaults = $dynDefaults;
      self::$locale = get_locale();
      self::$genOptions = ezGetOption(self::$genOptionName, self::$genOptions );
      self::$isFiltered = false;
      self::$isPure = false;
      self::$gCount = 0;
      self::$b64 = '';
    }

  }

}
if (!class_exists("ezOption")) {

  class ezOption { // base ezOption class

    var $name, $desc, $title, $value, $type, $class;
    var $width, $height, $before, $between, $after, $style; // display attributes

    function ezOption($type, $name) {// constructior
      $this->type = $type;
      $this->name = $name;
      if ($type == 'submit') {
        $this->class = "button-primary";
      }
    }

    function get() {// get the value of an ezOption
      return $this->value;
    }

    function set($properties, $desc = '') {// set the value or the attributes of an ezOption
      if (!isset($properties)) {
        return;
      }
      if (is_array($properties)) {
        foreach ($properties as $k => $v) {
          $key = strtolower($k);
          if (floatval(phpversion()) > 5.3) {
            if (property_exists($this, $key)) {
              $this->$key = $v;
            }
          }
          else {
            if (array_key_exists($key, $this)) {
              $this->$key = $v;
            }
          }
        }
      }
      else {
        $this->value = $properties;
        if (!empty($desc)) {
          $this->desc = $desc;
        }
      }
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      echo '<label for="', $this->name,
      '" title="', $this->title, '">', "\n",
      '<input type="', $this->type, '" id="', $this->name,
      '" name="', $this->name, '" ';
      if (!empty($this->style)) {
        echo ' style="', $this->style, '"';
      }
      if (!empty($this->class)) {
        echo ' class="', $this->class, '"';
      }
      echo ' value="', $this->value, '"';
      echo ' />', $this->desc, "\n</label>\n";
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

    function updateValue() {
      if (isset($_POST[$this->name])) {
        $this->value = $_POST[$this->name];
      }
    }

  }

  // End: Class ezOption

  class checkbox extends ezOption { // Checkbox

    function checkbox($name) {
      parent::ezOption('checkbox', $name);
    }

    function render($unique = '') {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      $name = $this->name . $unique;
      echo '<label for="', $name, '" title="', $this->title, '">', "\n",
      '<input type="', $this->type, '" id="', $name,
      '" name="', $this->name, '" ';
      if (!empty($this->style)) {
        echo ' style="', $this->style, '"';
      }
      if ($this->value) {
        echo 'checked="checked"';
      }
      echo ' /> ', $this->desc, "\n</label>\n";
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

    function updateValue() {
      $this->value = isset($_POST[$this->name]);
    }

  }

  // End: Class checkbox

  class radio extends ezOption { // Radiobox

    var $choices;

    function radio($name) {
      parent::ezOption('radio', $name);
    }

    function &addChoice($name) {
      $subname = $this->name . '_' . $name;
      $this->choices[$subname] = new ezOption('radio', $subname);
      return $this->choices[$subname];
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      if (!empty($this->choices)) {
        foreach ($this->choices as $k => $v) {
          echo $v->before, "\n";
          echo '<label for="', $k, '" title="', $this->title, '">', "\n";
          echo '<input type="', $v->type, '" id="', $k, '" name="', $this->name, '" ';
          if ($this->value == $v->value) {
            echo 'checked="checked"';
          }
          echo ' value="', $v->value, '" /> ', $v->desc;
          echo "\n</label>\n";
          echo $v->after, "\n";
        }
      }
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

  }

  // End: Class radio

  class select extends ezOption { // Drop-down menu.

    var $choices;

    function select($name) {
      parent::ezOption('select', $name);
    }

    function &addChoice($name, $value = '', $desc = '') {
      $subname = $this->name . '_' . $name;
      if (is_array($this->choices) && array_key_exists($subname, $this->choices)) {
        die(sprintf(__("Fatal Error [%s]: New Choice %s already exists in %s", 'easy-ads'), 'addChoice', $subname, $this->name));
      }
      $this->choices[$subname] = new ezOption('choice', $subname);
      $this->choices[$subname]->value = $value;
      $this->choices[$subname]->desc = $desc;
      return $this->choices[$subname];
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      echo $this->desc;
      if (!empty($this->between)) {
        echo $this->between, "\n";
      }
      echo '<label for="', $this->name, '" title="', $this->title, '">';
      echo '&nbsp;<select id="', $this->name, '" name="', $this->name, '"';
      if (!empty($this->style)) {
        echo ' style="', $this->style, '"';
      }
      echo '>';
      if (!empty($this->choices)) {
        foreach ($this->choices as $k => $v) {
          echo $v->before, '<option value="', $v->value, '" ';
          if ($this->value == $v->value) {
            echo 'selected="selected"';
          }
          echo '>', $v->desc, '</option>', $v->after, "\n";
        }
      }
      echo "</select></label>\n";
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

  }

  // End: Class select

  class message extends ezOption { // Not an option, but a message in the admin panel

    function message($name) { // constructor
      parent::ezOption('', $name);
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      echo '<span id="', $this->name, '" title="', $this->title, '">', "\n";
      if (!empty($this->value)) {
        echo $this->value, "\n";
      }
      if (!empty($this->desc)) {
        echo $this->desc, "\n";
      }
      echo "</span>\n";
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

  }

  // End: Class message

  class blurb extends ezOption { // Not an option, but a fancy message in the admin panel

    var $url, $price, $why;

    function blurb($name) { // constructor
      parent::ezOption('', $name);
      $this->name = substr($name, strpos($name, '_') + 1);
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      $name = $this->name;
      $value = '<em><strong>' . $this->value . '</strong></em>';
      if (empty($this->url)) {
        $this->url = 'http://buy.thulasidas.com/' . $name;
      }
      $link = "<b><a href='{$this->url}' target='_blank' class='popup'> $value</a> </b>";
      $text = $link . $this->desc;
      $price = $this->price;
      $moreInfo = "&nbsp; <a href='http://www.thulasidas.com/plugins/$name' title='More info about $value' class='popup' data-height='1024'>More Info</a>";

      $liteVersion = " <a href='http://buy.thulasidas.com/lite/$name.zip' title='" . sprintf(__("Download the Lite version of %s", 'easy-ads'), $value) . "'>" . __("Get Lite Version", 'easy-ads') . "</a> ";
      $proVersion = " <a href='http://buy.thulasidas.com/$name' title='" . sprintf(__("Buy the Pro version of %s for \$%s", 'easy-ads'), $value, $value) . "' class='popup'>" . "Get Pro Version" . "</a><br />";
      $plugindir = get_option('siteurl') . '/' . PLUGINDIR . '/' . basename(dirname(__FILE__));
      $why = addslashes("<a href='http://buy.thulasidas.com/$name' title='" . sprintf(__("Pro version of the %s plugin", 'easy-ads'), $name) . "'><img src='$plugindir/img/ezpaypal.png' style='border:0' alt='ezPayPal -- Instant PayPal Shop.' class='alignright' /></a><br />") .
              $this->why;
      echo "<li>" . ezTab::makeTextWithTooltip($text, $this->title, $value, 350, false);
      if ($price >= 0) {
        echo ezTab::makeTextWithTooltip($moreInfo, sprintf(__("Read more about %s at its own page.", 'easy-ads'), $value) . "<br />" . $this->title, sprintf(__("More Information about %s", 'easy-ads'), $value), 300, false) .
        ezTab::makeTextWithTooltip($liteVersion, $this->title, sprintf(__("Download %s - the Lite version", 'easy-ads'), $value), 300, false) .
        ezTab::makeTextWithTooltip($proVersion, $why, sprintf(__("Get %s Pro!", 'easy-ads'), $value), 300, false);
      }
      echo "</li>\n";
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

  }

  // End: Class blurb

  class textarea extends ezOption { // Multi-line textareas

    function textarea($name) {
      parent::ezOption('textarea', $name);
      $this->width = 50;
      $this->height = 5;
      $this->style = "width: 96%; height: 180px;";
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      echo $this->desc, '<textarea cols="', $this->width,
      '" rows="', $this->height, '" name="', $this->name,
      '" style="', $this->style, '" title="', $this->title, '">',
      stripslashes(htmlspecialchars($this->value)),
      "</textarea>\n";
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

  }

  // End: Class textarea

  class text extends ezOption { // Multi-line texts

    function text($name) {
      parent::ezOption('text', $name);
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      echo $this->desc;
      if (!empty($this->between)) {
        echo $this->between, "\n";
      }
      echo '<label for="', $this->name,
      '" title="', $this->title, '">', "\n",
      '<input type="', $this->type, '" id="', $this->name,
      '" name="', $this->name, '" ';
      if (!empty($this->style)) {
        echo ' style="', $this->style, '"';
      }
      echo ' value="', $this->value, '"';
      echo " />\n</label>\n";
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

  }

  // End: Class text

  class colorPicker extends ezOption { // colorPickers

    function colorPicker($name) {
      parent::ezOption('text', $name);
      $this->style = "border:0px solid;";
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      echo $this->desc;
      if (!empty($this->between)) {
        echo $this->between, "\n";
      }
      echo '<label for="', $this->name,
      '" title="', $this->title, '">', "\n",
      '&nbsp;<input type="', $this->type, '" id="', $this->name,
      '" name="', $this->name, '" ';
      if (!empty($this->style)) {
        echo ' style="', $this->style, '"';
      }
      echo ' class="color ' . "{hash:false,caps:true,pickerFaceColor:'transparent',pickerFace:3,pickerBorder:0,pickerInsetColor:'black'}" . '"';
      echo ' value="', $this->value, '"';
      echo " />\n</label>\n";
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

  }

  // End: Class colorPicker

  class mTab extends ezOption { // a tab in the mini-tab container, miniTab

    var $mTabOptions;

    function mTab($name) {
      parent::ezOption('mTab', $name);
      $this->mTabOptions = array();
    }

    function &addTabOption($type, $key) {
      $subname = $this->name . '_' . $key;
      if (is_array($this->mTabOptions) && array_key_exists($subname, $this->mTabOptions)) {
        die(sprintf(__("Fatal Error [%s]: New Option %s already exists in %s", 'easy-ads'), 'addTabOption', $subname, $this->name));
      }
      if (class_exists($type)) { // Specialized class for this type of input
        $this->mTabOptions[$key] = new $type($subname);
      }
      else {
        $this->mTabOptions[$key] = new ezOption($type, $subname);
      }
      return $this->mTabOptions[$key];
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      if (!empty($this->mTabOptions)) {
        foreach ($this->mTabOptions as $k => $o) {
          if (!empty($o)) {
            $o->render();
          }
        }
      }
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

    function updateValue() {
      foreach ($this->mTabOptions as $option) {
        $option->updateValue();
      }
    }

  }

  // End: Class mTab

  class miniTab extends ezOption { // a mini-tab container.

    var $mTabs;

    function miniTab($name) {
      parent::ezOption('miniTab', $name);
      $this->mTabs = array();
    }

    function &addTab($name) {
      $subname = $this->name . '-' . $name;
      if (array_key_exists($subname, $this->mTabs)) {
        die(sprintf(__("Fatal Error [%s]: New Tab %s already exists in %s", 'easy-ads'), 'addTab', $subname, $this->name));
      }
      $this->mTabs[$subname] = new mTab($subname);
      return $this->mTabs[$subname];
    }

    function render() {
      if (!empty($this->before)) {
        echo $this->before, "\n";
      }
      echo '<div><ul class="tabs" id="' . $this->name . '_miniTabs">' . "\n";
      $class = 'class="current"';
      foreach ($this->mTabs as $mTab) {
        echo '<li><a href="#" ' . $class . ' id="mtab_' . $mTab->name . '_link">' .
        $mTab->desc . "</a></li>\n";
        $class = '';
      }
      echo "</ul>\n</div><!-- of ul mTabs -->\n";

      $current = '_current';
      foreach ($this->mTabs as $mTab) {
        $name = $mTab->name;
        echo '<div class="tab' . $current . '" id="mtab_', $name, '">', "\n";
        $mTab->render();
        echo "</div><!-- End: $name --> \n";
        $current = '';
      }
      if (!empty($this->after)) {
        echo $this->after, "\n";
      }
    }

    function updateValue() {
      foreach ($this->mTabs as $mTab) {
        $mTab->updateValue();
      }
    }

  }

  // End: Class miniTab
}

if (!class_exists("ezTab")) {

  class ezTab {

    var $plugin, $isActive, $isAdmin, $name, $desc, $referral;
    var $submitMessage, $errorMessage;
    var $submitButtons = array();
    var $options = array();
    var $optionName;

    function ezTab($name, $defaults) {
      $this->name = $name;
      if (is_object($this->plugin)) {
        $themeName = $this->plugin->themeName;
        $CWD = $this->plugin->CWD;
        $baseName = $this->plugin->baseName;
        $pluginName = $this->plugin->pluginName;
      }
      $this->optionName = ezNS::$genOptionName . '-' . $this->name;
      $this->options = ezGetOption($this->optionName, $this->options);
      if (empty($this->options)) {
        $this->defineOptions();
        if (!empty($this->options)) {
          ezUpdateOption($this->optionName, $this->options);
        }
      }
      $this->isActive = true;
      $this->isAdmin = false;
      if (is_array($defaults)) {
        $this->desc = $defaults['desc'];
        $this->referral = $defaults['referral'];
      }
    }

    function checkDependencyInjection($fun) {
      if (empty($this->plugin)) {
        $errorMessage = '<div style="background-color:#fdd;border: solid 1px #f00; ' .
                'padding:5px"><p><b><em>ezAPI</em></b>: ' . $this->name .
                ": Dependency Injection Failure in <code>$fun</code>.</p></div>\n";
        echo $errorMessage;
        return false;
      }
      return true;
    }

    function setPlugin(&$ezPlugin = null) {
      $this->plugin = & $ezPlugin;
      $this->checkDependencyInjection(__FUNCTION__);
    }

    function migrateOptions() {
      if (isset($this->options)) {
        $savedOptions = $this->options;
      }
      unset($this->options);
      $this->defineOptions();
      if (!empty($savedOptions) && !empty($this->options)) {
        $intersection = array_intersect_key($savedOptions, $this->options);
      }
      if (!empty($intersection)) {
        $this->options = array_merge($this->options, $intersection);
      }
      if (!empty($this->options)) {
        ezUpdateOption($this->optionName, $this->options);
      }
    }

    function resetOptions() {
      delete_option($this->optionName);
      unset($this->options);
      $this->defineOptions();
      if (!empty($this->options)) {
        ezUpdateOption($this->optionName, $this->options);
      }
    }

    function render() {
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      $ezPlugin = & $this->plugin;
      if (empty($this->options)) {
        $this->defineOptions();
        if (!empty($this->options)) {
          ezUpdateOption($this->optionName, $this->options);
        }
      }

      $name = $this->name;
      echo '<div class="tab" id="tab', $ezPlugin->tabID++, '">', "\n";

      echo $this->submitMessage;
      echo $this->errorMessage;

      $this->renderContent();

      $this->renderForm();

      echo "</div><!-- End: $name --> \n";
    }

    function renderContent() {
      $name = $this->name;
      echo 'Sample content for ', $name, ".\n";
    }

    function renderForm() {
      $name = $this->name;
      echo '<form method="post" name="form_', $name,
      '" action="', $_SERVER["REQUEST_URI"], '">', "\n";

      if (!empty($this->options)) {
        foreach ($this->options as $k => $o) {
          if (isset($o)) {
            $o->render();
          }
        }
      }
      echo "<br /><hr />\n", '<div class="submit">', "\n";
      foreach ($this->submitButtons as $k => $o) {
        $o->render();
      }
      echo "</div><!-- End: submit --> \n";
      echo "</form>\n";
    }

    function defineOptions() { // Add all options
      // unset($this->options) ;
    }

    function defineSubmitButtons() { // Add submit buttons
      $button = &$this->addSubmitButton('submit', 'update');
      $properties = array('value' => __('Save Changes', 'easy-ads'),
          'title' => __("Save the changes as specified above.", 'easy-ads'));
      $button->set($properties);

      $button = &$this->addSubmitButton('submit', 'reset');
      $properties = array('value' => __('Reset Options', 'easy-ads'),
          'title' => __('DANGER: Reset all the options to default.', 'easy-ads'));
      $button->set($properties);

      $button = &$this->addSubmitButton('submit', 'clean_db');
      $properties = array('value' => __('Clean Database', 'easy-ads'),
          'title' => __('DANGER: Delete the options from the database.', 'easy-ads'));
      $button->set($properties);
    }

    function handleSubmits() { // Deal with submit button clicks
      foreach ($this->submitButtons as $k => $v) {
        if (isset($_POST[$v->name])) {
          switch ($k) {
            case "update":
              // loop over options and read in the values set
              if (!empty($this->options)) {
                foreach ($this->options as $key => $opt) {
                  $opt->updateValue();
                }
                ezUpdateOption($this->optionName, $this->options);
                $this->submitMessage .= '<div class="updated"><p><strong>' .
                        $this->name . ' ' .
                        __('Settings have been updated in the database.', 'easy-ads') .
                        '</strong></p> </div>';
              }
              else {
                $this->submitMessage .= '<div class="updated"><p><strong>' .
                        $this->name . ' ' .
                        __('No settings defined!', 'easy-ads') .
                        '</strong></p> </div>';
              }
              break;
            case "reset":
              delete_option($this->optionName);
              unset($this->options);
              $this->defineOptions();
              ezUpdateOption($this->optionName, $this->options);
              $this->submitMessage .= '<div class="updated"><p><strong>' . $this->name .
                      ' Settings have been reset to the defaults!</strong></p> </div>';
              break;
            case "clean_db":
              delete_option($this->optionName);
              unset($this->options);
              $this->submitMessage .= '<div class="updated"><p><strong>' .
                      $this->name . ' ' .
                      __('Settings have been discarded, and the database is clean as a whistle!', 'easy-ads') .
                      '<br />' .
                      __('You may want to uninstall the plugin now.', 'easy-ads') .
                      '</strong></p> </div>';
              break;
            default:
              $this->submitMessage .= '<div class="updated"><p><strong>' . $this->name .
                      ' Settings do what? ' . $k . '</strong></p> </div>';
              break;
          }
        }
      }
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      $ezPlugin = & $this->plugin;
      if (!empty($ezPlugin->tabs['Admin']->options[$this->name])) {
        $isActive = $this->options['active']->get();
        $ezPlugin->tabs['Admin']->options[$this->name]->value = $isActive;
        $this->isActive = $isActive;
      }
    }

    function &addOption($type, $key) {
      $name = $this->name . '_' . $key;
      if (!empty($this->options) &&
              is_array($this->options) &&
              array_key_exists($key, $this->options)) {
        die(sprintf(__("Fatal Error [%s]: New Option %s already exists in %s", 'easy-ads'), 'addOption', $key, $this->name));
      }
      if (class_exists($type)) { // Specialized class for this type of input
        $this->options[$key] = new $type($name);
      }
      else {
        $this->options[$key] = new ezOption($type, $name);
      }
      return $this->options[$key];
    }

    function &addSubmitButton($type, $key) {
      $name = $this->name . '_' . $key;
      if (!empty($this->submitButtons) &&
              is_array($this->submitButtons) &&
              array_key_exists($name, $this->submitButtons)) {
        die(sprintf(__("Fatal Error [%s]: New Button %s already exists in %s", 'easy-ads'), 'addSubmitButton', $name, $this->submitButtons));
      }
      if (class_exists($type)) { // Specialized class for this type of input
        $this->submitButtons[$key] = new $type($name);
      }
      else {
        $this->submitButtons[$key] = new ezOption($type, $name);
      }
      return $this->submitButtons[$key];
    }

    // ------------ Content Filter -----------------
    function get($optionName) {// Return an option value
      if (!is_array($this->options)) {
        echo (sprintf(__("Fatal Error: %s in %s. Options don't exist (not an array)!", 'easy-ads'), "<code>get('$optionName')</code>", $this->name));
      }
      if (!empty($this->options[$optionName])) {
        return $this->options[$optionName]->get();
      }
      else {
        // look for the option in all the miniTabs
        foreach ($this->options as $o) {
          if ($o->type == 'miniTab') {
            foreach ($o->mTabs as $mTab) {
              if (!empty($mTab->mTabOptions[$optionName])) {
                return $mTab->mTabOptions[$optionName]->get();
              }
            }
          }
        }
      }
    }

    function set($optionName, $value) {// Set an option value
      if (!empty($this->options[$optionName])) {
        return $this->options[$optionName]->set($value);
      }
    }

    static function makeTextWithTooltip($text, $tip, $title = '', $width = '', $underline = true) {
      if (!empty($title)) {
        $titleText = "TITLE, '$title',STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true,";
      }
      if (!empty($width)) {
        $widthText = "WIDTH, $width,";
      }
      $style = '';
      if ($underline) {
        $style = "style='text-decoration:underline' ";
      }
      $return = "<span  $style" .
              "onmouseover=\"Tip('" . htmlspecialchars($tip) . "', " .
              "$widthText $titleText FIX, [this, 5, 5])\" " .
              "onmouseout=\"UnTip()\">$text</span>";
      return $return;
    }

    static function makeLIwithTooltip($text, $tip, $title = '', $width = '') {
      if (empty($title)) {
        $title = $text;
      }
      if (empty($width)) {
        $width = "200";
      }
      $return = "<li>" .
              ezTab::makeTextWithTooltip($text, $tip, $title, $width) .
              "</li>\n";
      return $return;
    }

  }

  // End: Class ezTab
}

if (!class_exists("ezOverview")) {

  class ezOverview extends ezTab {

    function ezOverview() {
      $this->name = "Overview";
      $this->isActive = false;
      $this->isAdmin = true;
    }

    function render() {
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      $ezPlugin = & $this->plugin;

      $name = $this->name;

      echo '<div class="tab_current" id="tab', $ezPlugin->tabID++, '">', "\n";

      echo $this->submitMessage;
      echo $this->errorMessage;

      $this->renderContent();

      echo "</div><!-- End: $name --> \n";
    }

    function renderContent() {
      $supportText = "<div style=\"width:280px;background-color:#dff;padding:5px;border: solid 1px\" id=\"support\"><b>Support this Plugin!</b><br />Buy <span style=\"text-decoration:underline\" onmouseover=\"TagToTip('unreal', WIDTH, 205, TITLE, 'Buy &lt;em&gt;The Unreal Universe&lt;/em&gt;',STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true, FIX, [this, 5, 2])\"><b style=\"color:#a48;font-variant: small-caps;text-decoration:underline\">The Unreal Universe</b></span> or <span style=\"text-decoration:underline\" onmouseover=\"TagToTip('pqd', WIDTH, 205, TITLE, '&lt;em&gt;Principles of Quant. Devel.&lt;/em&gt;',STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true, FIX, [this, 5, 2])\"><b style=\"color:#84a;font-variant: small-caps;text-decoration:underline\">Principles of Quantitative Development</b></span>.</div><br />";
      echo $supportText;
    }

  }

  // End: Class Overview
}

if (!class_exists("EzAdminTab")) {

  class EzAdminTab extends ezTab {

    function EzAdminTab() {
      parent::ezTab("Admin", "");
      $this->isActive = false;
      $this->isAdmin = true;
    }

    function defineSubmitButtons() { // Add submit buttons
      unset($this->submitButtons);

      $button = &$this->addSubmitButton('submit', 'update');
      $properties = array('value' => __('Save Changes', 'easy-ads'),
          'title' => __("Save the changes as specified above.", 'easy-ads'));
      $button->set($properties);

      $button = &$this->addSubmitButton('submit', 'reset');
      $properties = array('value' => __('Reset All Options', 'easy-ads'),
          'title' => __('DANGER: Reset all the options to default.', 'easy-ads'));
      $button->set($properties);

      $button = &$this->addSubmitButton('submit', 'migrate');
      $properties = array('value' => __('Migrate Options', 'easy-ads'),
          'title' => __('Update the options to be compatible with the current version of the plugin.', 'easy-ads'));
      $button->set($properties);

      $button = &$this->addSubmitButton('submit', 'clean_db');
      $properties = array('value' => 'Clean Database',
          'title' => 'DANGER: Delete all the options from the database.');
      $button->set($properties);
    }

    function handleSubmits() { // Deal with submit button clicks
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      $ezPlugin = & $this->plugin;
      foreach ($this->submitButtons as $k => $v) {
        if (isset($_POST[$v->name])) {
          switch ($k) {
            case "update":
              // loop over options and read in the values set
              if (!empty($this->options)) {
                foreach ($this->options as $key => $opt) {
                  $opt->updateValue();
                }
                ezUpdateOption($this->optionName, $this->options);
                // apply the active flags
                foreach ($ezPlugin->tabs as $key => $p) {
                  if (!$p->isAdmin) {
                    $isActive = $this->options[$p->name]->get();
                    $p->isActive = $isActive;
                    $p->set('active', $isActive);
                    ezUpdateOption($p->optionName, $p->options);
                  }
                }
                $pluginVersion = $ezPlugin->getVersion();
                $ezPlugin->genOptions['Version'] = $pluginVersion;
                ezUpdateOption($ezPlugin->genOptionName, $ezPlugin->genOptions);
                $this->submitMessage .= '<div class="updated"><p><strong>' .
                        $this->name . ' ' .
                        __('Settings have been updated in the database.', 'easy-ads') .
                        '</strong></p> </div>';
              }
              else {
                $this->submitMessage .= '<div class="updated"><p><strong>' .
                        $this->name . ' ' .
                        __('No settings defined!', 'easy-ads') .
                        '</strong></p> </div>';
              }
              break;
            case "clean_db0":
            case "reset": // FIXME: Make this case parallel by adding ezTab::resetOptions()?
              foreach ($ezPlugin->tabs as $key => $p) {
                $p->resetOptions();
              }
              delete_option(ezNS::$genOptionName);
              $pluginVersion = $ezPlugin->getVersion();
              $ezPlugin->genOptions['Version'] = $pluginVersion;
              ezUpdateOption($ezPlugin->genOptionName, $ezPlugin->genOptions);
              $this->submitMessage .= '<div class="updated"><p><strong>' .
                      __('All Settings have been reset to the defaults!', 'easy-ads') .
                      '</strong></p> </div>';
              break;
            case "migrate":
              foreach ($ezPlugin->tabs as $key => $p) {
                $p->migrateOptions();
              }
              $pluginVersion = $ezPlugin->getVersion();
              $ezPlugin->genOptions['Version'] = $pluginVersion;
              ezUpdateOption($ezPlugin->genOptionName, $ezPlugin->genOptions);
              $this->submitMessage .= '<div class="updated"><p><strong>' .
                      sprintf(__('All Settings have been migrated to version %s.', 'easy-ads'), $pluginVersion) .
                      '</strong></p> </div>';
              break;
            case "clean_db":
              foreach ($ezPlugin->tabs as $key => $p) {
                delete_option($ezPlugin->tabs[$key]->optionName);
                unset($ezPlugin->tabs[$key]->options);
              }
              // delete_option(ezNS::$genOptionName) ;
              $this->submitMessage .= '<div class="updated"><p><strong>' .
                      $this->name . ' ' .
                      __('Settings have been discarded, and the database is clean as a whistle!', 'easy-ads') .
                      '<br />' .
                      __('You may want to uninstall the plugin now.', 'easy-ads') .
                      '</strong></p> </div>';
              break;
            default:
              $this->submitMessage .= '<div class="updated"><p><strong>' . $this->name .
                      ' Settings do what? ' . $k . '</strong></p> </div>';
              break;
          }
        }
      }
    }

  }

  // End: Class EzAdminTab
}

if (!class_exists("ezAbout")) {

  class ezAbout extends ezTab {

    var $blurbs = array();

    function ezAbout() {
      $this->name = "About";
      $this->isActive = false;
      $this->isAdmin = true;
      $this->defineOptions();
    }

    function render() {
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      $ezPlugin = & $this->plugin;

      $name = $this->name;

      echo '<div class="tab" id="tab', $ezPlugin->tabID++, '">', "\n";

      echo $this->submitMessage;
      echo $this->errorMessage;

      $ez = $ezPlugin->ez;
      $ez->renderSupport();

      $myPluginsU = array_unique($ezPlugin->myPlugins, SORT_REGULAR);
      $keys = array_rand($myPluginsU, 4);
      if (!in_array($ez->slug, $keys)) {
        $keys[] = $ez->slug;
      }

      foreach ($keys as $k) {
        $plg = $myPluginsU[$k];
        $option = &$this->addOption('blurb', $k);
        $properties = array('value' => $plg['value'],
            'desc' => $plg['desc'],
            'title' => $plg['title'],
            'price' => $plg['price'],
            'why' => $plg['pro']);
        $option->set($properties);
      }
      // Credits - set the price to negative so that it renders without "buy" links.
      // ugly, I know...
      if (is_object($this->plugin)) {
        $name = $this->plugin->name;
      }
      else {
        $name = "This plugin";
      }

      $option = &$this->addOption('blurb', 'tooltips');
      $properties = array('value' => 'Walter Zorn',
          'url' => 'http://sourceforge.net/projects/wztip/',
          'price' => -1,
          'desc' => sprintf(__('%s uses the excellent Javascript/DHTML tooltips by Walter Zorn.', 'easy-ads'), "<b>$name</b>"),
          'title' => __('Javascript, DTML Tooltips - The tooltip you are looking at is based on the work of Walter Zorn.', 'easy-ads'),
          'before' => "</ul>\n</td>\n</tr>\n<tr><th scope='row'><b>Credits</b></th></tr>\n<tr><td>" .
          '<ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >');
      $option->set($properties);

      $option = &$this->addOption('blurb', 'tabs');
      $properties = array('value' => __('Web Developer Blog', 'easy-ads'),
          'url' => 'http://webdevel.blogspot.com/2009/03/pure-accessible-javascript-tabs.html',
          'price' => -1,
          'desc' => sprintf(__('%s uses a modified version JavaScript tabs from Web Developer Blog.', 'easy-ads'), "<b>$name</b>"),
          'title' => __('A simple but poweful, CSS based tab implementation.', 'easy-ads'));
      $option->set($properties);

      $option = &$this->addOption('blurb', 'colorpicker');
      $properties = array('value' => __('jscolor, JavaScript Color Picker', 'easy-ads'),
          'url' => 'http://jscolor.com',
          'price' => -1,
          'desc' => sprintf(__('%s uses the JavaScript Color Picker kindly developed and distributed by Honza Odvarko.', 'easy-ads'), "<b>$name</b>"),
          'title' => __('An excellent and widely popular color picker.', 'easy-ads'));
      $option->set($properties);

      $this->blurbs = $this->options;
      unset($this->options); // to ensure nothing is saved in the DB

      $this->renderContent();

      echo "</div><!-- End: $name --> \n";
      ?>
      <script type = "text/javascript">
        function popupwindow(url, title, w, h) {
          return ezPopUp(url, title, w, h);
        }
        function ezPopUp(url, title, w, h) {
          var wLeft = window.screenLeft ? window.screenLeft : window.screenX;
          var wTop = window.screenTop ? window.screenTop : window.screenY;
          var left = wLeft + (window.innerWidth / 2) - (w / 2);
          var top = wTop + (window.innerHeight / 2) - (h / 2);
          window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
          return true;
        }
        jQuery(document).ready(function () {
          jQuery('body').on('click', ".popup", function (e) {
            e.preventDefault();
            var url = jQuery(this).attr('href');
            var title = "Window";
            var w = 1024;
            var h = 728;
            if (jQuery(this).attr('data-height')) {
              h = jQuery(this).attr('data-height');
              w = 1000;
            }
            return ezPopUp(url, title, w, h);
          });
        });
      </script>
      <!-- Google translator -->
      <script type='text/javascript'>
        function googleTranslateElementInit() {
          new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
        }
      </script>
      <script type='text/javascript' src='//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'></script>
      <?php
    }

    function defineOptions() {
      unset($this->options);
      unset($this->blurbs);
    }

    function renderContent() {
      $ez = $this->plugin->ez;
      echo '<table class="form-table" >', "\n", '<tr><th scope="row"><b>',
      $this->plugin->name, "</b></th></tr>\n<tr><td>\n",
      '<ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >', "\n";
      if (!empty($this->blurbs[$ez->slug])) {
        $this->blurbs[$ez->slug]->render();
      }

      echo "</ul>\n</td>\n</tr>\n<tr><th scope='row'><b>Other Plugins and Apps</b></th></tr>\n<tr><td>",
      '<ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >';

      if (!empty($this->blurbs)) {
        foreach ($this->blurbs as $k => $b) {
          if ($k != $ez->slug && !empty($b)) {
            $b->render();
          }
        }
      }
      echo "</ul>\n</td>\n</tr>\n</table>\n";
      // @include ($this->plugin->CWD . '/ezBlurb.php');
    }

  }

  // End: Class ezAbout
}

if (!class_exists("ezAdsPlugin")) {

  class ezAdsPlugin {

    var $defaults, $tabID, $submitMessage, $errorMessage, $lang;
    var $name, $CWD, $URL, $baseName, $genOptionName;
    var $plgDir, $plgURL, $ez, $myPlugins, $options;
    var $genOptions = array();
    var $tabs = array();
    var $top = array();
    var $middle = array();
    var $bottom = array();

    function ezAdsPlugin($buildTabs = true) { // Constructor
      $this->CWD = ezNS::$CWD;
      $this->baseName = ezNS::$baseName;
      $this->URL = ezNS::$URL;
      $this->name = ezNS::$name;
      $this->genOptionName = ezNS::$genOptionName;
      if (file_exists($this->CWD . '/defaults.php')) {
        include ($this->CWD . '/defaults.php');
      }
      if (empty($defaults)) {
        $this->errorMessage = '<div class="error"><p><b><em>ezAPI</em></b>: ' .
                'Could not locate <code>defaults.php</code>. ' .
                'Base Class <code>ezPlugin</code> loaded!</p></div>';
      }
      if (!is_array($defaults['tabs'])) {
        $baseTabs = array('Overview' => array(),
            'Admin' => array(),
            'Example' => array(),
            'About' => array());
        $defaults['tabs'] = $baseTabs;
      }
      else { // build tabs from defaults.php
        $baseTabs = array('Overview' => array(),
            'Admin' => array());
        $baseTabs = array_merge($baseTabs, $defaults['tabs']);
        $defaults['tabs'] = $baseTabs;
        if (empty($defaults['tabs']['About'])) {
          $aboutTab = array('About' => array());
          $defaults['tabs'] = array_merge($defaults['tabs'], $aboutTab);
        }
      }
      if ($buildTabs) {
        foreach ($defaults['tabs'] as $k => $v) {
          $className = ezNS::ns($k);
          if (class_exists($className)) {
            $this->tabs[$k] = new $className($k, $v);
          }
          else {
            $this->tabs[$k] = new ezTab($k, $v);
          }
          if (!empty($this->tabs[$k]->options['active'])) {
            $this->tabs[$k]->isActive = $this->tabs[$k]->options['active']->value;
          }
        }
      }
      $this->defaults = $defaults;
    }

    function writeAdminHeader() {
      echo "<link rel='stylesheet' type='text/css' href=\"" .
      $this->URL . '/ezTabs.css" />' . "\n";
      echo '<script type="text/javascript" src="' .
      $this->URL . '/ezTabs.js"></script>' . "\n";
      echo '<script type="text/javascript" src="' .
      $this->URL . '/ezColor/jscolor.js"></script>' . "\n";
      ?>
      <style type="text/css">
        /* Google Translator top bar and tooltips */
        .goog-te-banner-frame.skiptranslate {display: none !important;}
        body { top: 0px !important; }
        .goog-tooltip {
          display: none !important;
        }
        .goog-tooltip:hover {
          display: none !important;
        }
        .goog-text-highlight {
          background-color: transparent !important;
          border: none !important;
          box-shadow: none !important;
        }
      </style>
      <?php
    }

    function setGoogleTranCookie() {
      if (!empty($_GET['lang'])) {
        $lang = $_GET['lang'];
      }
      else {
        $locale = get_locale();
        $lang = substr($locale, 0, 2);
      }
      $lang = strtolower($lang);
      if ($lang == 'en') {
        setcookie("googtrans", "", 1, '/');
      }
      else {
        setcookie("googtrans", "/en/$lang", time() + 300, '/');
      }
      $this->lang = $lang;
    }

    function printGoogleTran() {
      if ($this->lang != 'en') {
        ?>
        <div class="updated">
          <p>
            View this admin page in your language: &nbsp;
            <span id='GoogleTranslatorWidget' style='display:inline-block'>
              <span id='google_translate_element'></span>
            </span>
          </p>
        </div>
        <?php
      }
    }

    function getVersion() {
      $me = ezNS::$pluginKey;
      $plugins = get_plugins();
      $str = $plugins[$me]['Title'] . " V" . $plugins[$me]['Version'];
      return $str;
    }

    function migrateOptions() {
      foreach ($this->tabs as $key => $p) {
        $p->migrateOptions();
      }
      $pluginVersion = $this->getVersion();
      $this->genOptions['Version'] = $pluginVersion;
      ezUpdateOption($this->genOptionName, $this->genOptions);
    }

    function resetOptions() {
      delete_option(ezNS::$genOptionName);
      foreach ($this->tabs as $key => $p) {
        $p->resetOptions();
      }
    }

    function showOptionMigration() {
      $pluginVersion = $this->getVersion();
      $storedVersion = $this->genOptions['Version'];
      if ($storedVersion != $pluginVersion) {
        echo '<div class="error" id="migrate"><form id="genOptionMigrationForm" method="post" action=""><p>' .
        sprintf(__('Your saved options (%s) look out of date. Want to migrate the options to the current version (%s)?', 'easy-ads'), $storedVersion, $pluginVersion) .
        '</p><p>' .
        __('Resetting all the options (and re-entering them) is recommended.', 'easy-ads') .
        '</p><input type = "button" id = "migrateButton" value = "Migrate" onclick = "mButtonWhich(\'Migrate\')" /><input type = "button" id = "resetButton" value = "' .
        __('Reset', 'easy-ads') .
        '" onclick = "mButtonWhich(\'Reset\')" /><input type="hidden" id="genOptionMigration" name="genOptionMigration" value="none" /><input type="hidden" id="genOptionReset" name="genOptionReset" value="none" /></form>',
        '<script type = "text/javascript">',
        'function hideVersion() {',
        'document.getElementById("migrate").style.display = \'none\';',
        '}',
        'function mButtonWhich(message) {',
        '  document.getElementById("genOptionMigration").value = message;',
        '  document.getElementById("migrateButton").style.display = \'none\';',
        '  document.getElementById("resetButton").disabled = \'true\';',
        '  document.getElementById("resetButton").value = \'Thank you!\';',
        '  setTimeout(\'hideVersion()\', 6000);',
        '  document.forms["genOptionMigrationForm"].submit();',
        '}',
        '</script>',
        "</div><br />\n";
      }
    }

    function handleOptionMigration() {
      if (!empty($_POST['genOptionMigration'])) {
        $action = $_POST['genOptionMigration'];
      }
      if (isset($action)) {
        $pluginVersion = $this->getVersion();
        if ($action == "Migrate") {
          $submitMessage = '<div class="updated"><p><strong>' .
                  $this->name . ' ' .
                  sprintf(__("Options migrated to %s.", 'easy-ads'), $pluginVersion) .
                  "</strong></p> </div>";
          $this->migrateOptions();
        }
        if ($action == "Reset") {
          $submitMessage = '<div class="updated"><p><strong>' .
                  $this->name . ' ' .
                  sprintf(__("Options reset to %s.", 'easy-ads'), $pluginVersion) .
                  "</strong></p> </div>";
          $this->resetOptions();
        }
        $this->genOptions['Version'] = $pluginVersion;
        ezUpdateOption($this->genOptionName, $this->genOptions);
        return $submitMessage;
      }
    }

    function renderAdminPage() {
      foreach ($this->tabs as $k => $p) {// Dependency injection to tabs
        $this->tabs[$k]->setPlugin($this);
      }
      $this->printGoogleTran();
      echo '<script type="text/javascript" src="' . $this->URL . '/wz_tooltip.js"></script>' . "\n";
      echo '<div class="wrap" style="width:900px">' . "\n";

      echo "<h2>", $this->name, " Setup</h2><br />";

      ezNS::setStaticVars($this->defaults);

      foreach ($this->tabs as $k => $p) {
        $this->tabs[$k]->defineSubmitButtons();
        $this->tabs[$k]->handleSubmits();
      }

      $this->genOptions = ezGetOption(ezNS::$genOptionName, $this->genOptions);
      ezNS::$genOptions = $this->genOptions; // in case handleSubmits changed options
      $pluginVersion = $this->getVersion();
      $this->submitMessage .= $this->handleOptionMigration($pluginVersion);
      echo $this->submitMessage;
      echo $this->errorMessage;
      $this->showOptionMigration($pluginVersion);

      echo '<div><ul class="tabs" id="tabs">' . "\n";
      $this->tabID = 0;
      $class = 'class="current"';
      foreach ($this->tabs as $p) {
        if ($p->isActive) {
          $style = " style='color:green;font-weight:bold;'";
        }
        else {
          $style = " style='color:red;'";
        }
        if ($p->isAdmin) {
          $style = " style='color:blue;font-weight:bold;'";
        }
        echo '<li><a href="#" ' . $class . $style . ' id="tab' . $this->tabID++ . '_link">' .
        $p->name . "</a></li>\n";
        $class = '';
      }
      echo "</ul>\n</div><!-- of ul tabs -->\n";

      $this->tabID = 0;
      foreach ($this->tabs as $k => $p) {
        $p->render();
      }
      echo "\n</div><!-- of wrap -->\n";
    }

    function filterContent($content) {
      return "<b>ezAPI:</b> " . "Filtered this post." . " (Trivial Filtering.)<br />\n" . $content;
    }

  }

} //End: Class ezPlugin
