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

if (!class_exists('ChitikaWidget')) {

  class ChitikaWidget extends providerWidget {

    public static $provider;

    function ChitikaWidget($name = 'ChitikaWidget') {
      parent::providerWidget($name, self::$provider);
    }

    function widget($args, $instance) {
      extract($args);
      $title = apply_filters('widget_title', $instance['title']);
      echo $before_widget;
      if ($title) {
        echo $before_title . $title . $after_title;
      }
      $format = self::$provider->get('widgetformat');
      $adText = self::$provider->mkAdText($format);
      if (empty($adText)) {
        echo "Empty Widget Text from <code>" . $this->name . "</code>";
      }
      else {
        echo $this->decorate($adText);
        echo $after_widget;
      }
    }

    public static function setProvider(&$p) {
      self::$provider = $p;
    }

  }

  // class ChitikaWidget
}
if (!class_exists('Chitika')) {

  class Chitika extends provider {

    // ------------ Widget handling ----------------
    function buildWidget() {
      if ($this->isActive && $this->get('widget')) {
        $widgetClass = ezNS::ns($this->name . 'Widget');
        if (!class_exists($widgetClass)) {
          $widgetClass = 'providerWidget';
        }
        eval($widgetClass . '::setProvider($this) ;');
        add_action('widgets_init', create_function('', 'return register_widget("' . $widgetClass . '");'));
      }
    }

    function mkAdText($size = '', $suffix = '') {
      $userid = $this->get('userid' . $suffix);
      if ($size == '') {
        $size = $this->get('format' . $suffix);
      }
      if ($userid == "Your Chitika ID") {
        $adText = ezExtras::handleDefaultText('', $size);
        return $adText;
      }
      $x = strpos($size, 'x' . $suffix);
      $w = substr($size, 0, $x);
      $h = substr($size, $x + 1);
      $type = $this->get('type' . $suffix);
      $channel = $this->get('channel' . $suffix);
      $linkColor = $this->get('linkcolor' . $suffix);
      $textColor = $this->get('textcolor' . $suffix);
      $bgColor = $this->get('bgcolor' . $suffix);
      $borderColor = $this->get('bordercolor' . $suffix);
      $fallBack = $this->get('fallback' . $suffix);
      $fallBackURL = $this->get('fallbackurl' . $suffix);
      switch ($fallBack) {
        case "collapse" :
          $chitikaFallBack = "";
          break;
        case "alt" :
          if (!empty($fallBackURL)) {
            $chitikaFallBack = 'ch_alternate_ad_url = "' . $fallBackURL . '";';
          }
          break;
        case "altaut" :
          $fallBackURL = "http://www.thulasidas.com/ads/ads1.php?size=$size";
          $chitikaFallBack = 'ch_alternate_ad_url = "' . $fallBackURL . '";';
          break;
        case "backfill" :
          $chitikaFallBack = "ch_backfill = 1;";
          break;
      }
      $adText = "<script type=\"text/javascript\">\n" .
              "ch_client = \"$userid\";\n" .
              "ch_width = $w;\n" .
              "ch_height = $h;\n" .
              "ch_type = \"$type\";\n" .
              "ch_sid = \"$channel\";\n" .
              "ch_color_site_link = \"#$linkColor\";\n" .
              $chitikaFallBack .
              "ch_color_title = \"#$linkColor\";\n" .
              "ch_color_border = \"#$borderColor\";\n" .
              "ch_color_text = \"#$textColor\";\n" .
              "ch_color_bg = \"#$bgColor\";\n" .
              "</script>\n" .
              "<script src=\"http://scripts.chitika.net/eminimalls/amm.js\" type=\"text/javascript\">\n" .
              "</script>";
      return $adText;
    }

    function buildAdBlocks() {
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      if ($this->isActive) {
        foreach ($this->plugin->positions as $key) {
          $name = $this->name . '-' . $key;
          $this->adBlocks[$key] = new adBlock($name);
          $adText = $this->mkAdText();
          $this->adBlocks[$key]->set($adText);
        }
      }
    }

    function defineOptions() { // Add all options
      unset($this->options);

      $option = &$this->addOption('message', 'intro');
      $properties = array('desc' => sprintf(__("About %s", 'easy-ads'), $this->name),
          'before' => '<br /><table><tr><th colspan="3"><b>',
          'after' => '</b></th></tr><tr style="text-align:left;vertical-align:middle"><td style="width:20%">');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'active');
      $properties = array('desc' => '&nbsp;' .
          sprintf(__("Activate %s?", 'easy-ads'), $this->name),
          'title' => sprintf(__("Check to activate %s", 'easy-ads'), $this->name),
          'value' => true,
          'before' => '',
          'after' => '<br />');
      $option->set($properties);

      $option = &$this->addOption('message', 'referral');
      $properties = array('desc' => $this->referral,
          'before' => '</td><td style="width:20%">&nbsp;',
          'after' => '</td>');
      $option->set($properties);

      $option = &$this->addOption('message', 'info');
      $properties = array('desc' => $this->desc,
          'before' => '<td >',
          'after' => '</td></tr></table><hr />');
      $option->set($properties);

      // >>> Mini Tab
      $miniTab = &$this->addOption('miniTab', 'textTab');
      $properties = array('desc' => 'Tabbie',
          'title' => __(" tab interface ", 'easy-ads'),
          'value' => $this->name,
          'before' => '<table style="border-collapse:separate;border-spacing:10px;"><tr style="text-align:left;vertical-align:top"><td style="width:55%"><br />',
          'after' => '</td>');
      $miniTab->set($properties);

      $mTab = &$miniTab->addTab('body');
      $properties = array('desc' => __('Unit', 'easy-ads'),
          'title' => __(" tab interface ", 'easy-ads'),
          'value' => $this->name . 'body');
      $mTab->set($properties);

      $option = &$mTab->addTabOption('message', 'unit');
      $properties = array('desc' => '<b>' . sprintf(__('Select Unit Options for your %s ads', 'easy-ads'), $this->name) . ' </b>',
          'title' => __('Format, Type and Fallback Options', 'easy-ads'),
          'value' => '',
          'before' => '',
          'after' => '<br />');
      $option->set($properties);

      $option = &$mTab->addTabOption('text', 'userid');
      $properties = array('desc' => __("Your Chitika Account Name: ", 'easy-ads'),
          'title' => "",
          'value' => "Your Chitika ID",
          'before' => '<table style="width:80%"><tr><td style="width:50%">',
          'between' => '</td><td style="width:50%">',
          'after' => '</td></tr>');
      $option->set($properties);

      $option = &$mTab->addTabOption('text', 'channel');
      $properties = array('desc' => __("Chitika Channel: ", 'easy-ads'),
          'title' => "",
          'value' => "Chitika Default",
          'before' => '<tr><td>',
          'between' => '</td><td>',
          'after' => '</td></tr>');
      $option->set($properties);

      $select = &$mTab->addTabOption('select', 'format');
      $properties = array('desc' => 'Format',
          'title' => __('Choose the Format', 'easy-ads'),
          'value' => "300x250",
          'style' => 'width:80%',
          'before' => '<tr><td style="width:50%">',
          'between' => '</td><td style="width:50%">',
          'after' => '</td></tr>');
      $select->set($properties);
      $sizes = array("120x600", "160x160", "160x600", "180x150", "180x300",
          "200x200", "250x250", "300x125", "300x150", "300x250", "300x70",
          "334x100", "336x160", "336x280", "400x90", "430x90", "450x90",
          "468x120", "468x180", "468x250", "468x60", "468x90", "500x250",
          "550x120", "550x90", "728x90",);
      if (!empty($sizes)) {
        sort($sizes);
        foreach ($sizes as $size) {
          $choice = &$select->addChoice($size, $size, $size);
        }
      }

      $select = &$mTab->addTabOption('select', 'type');
      $properties = array('desc' => __('Type', 'easy-ads'),
          'title' => __('Type option is not fully implemented yet', 'easy-ads'),
          'value' => "mpu",
          'style' => 'width:80%',
          'before' => '<tr><td>',
          'between' => '</td><td>',
          'after' => '</td></tr>');
      $select->set($properties);
      $types = array('mpu' => __("Muti-purpose Unit", 'easy-ads'),
          'map' => __("Map Unit", 'easy-ads'),
          'mobile' => __("Mobile Unit", 'easy-ads'));

      foreach ($types as $key => $type) {
        $choice = &$select->addChoice($key, $key, $type);
      }

      $select = &$mTab->addTabOption('select', 'fallback');
      $properties = array('desc' => __('Fallback Options', 'easy-ads'),
          'title' => __('What are Fallback Options? Chitika ad units will only display to select traffic. Select a fallback option to tell Chitika what to do when they decide not to show an ad. Collapse means the ad-slot effectively disapplears. Backfill (available in certain sizes) shows a solid color. Alternate Ad is the URL is for a backup ad.', 'easy-ads'),
          'value' => "backfill",
          'style' => 'width:80%',
          'before' => '<tr><td>',
          'between' => '</td><td>',
          'after' => '</td></tr>');
      $select->set($properties);

      $fallBacks = array(
          'collpase' => __("Collapse", 'easy-ads'),
          'alt' => __("Show an Alternate Ad", 'easy-ads'),
          'altaut' => __("Show the Author Ad", 'easy-ads'),
          'backfill' => __("Show Chitika Backfill", 'easy-ads'));

      foreach ($fallBacks as $key => $fallBack) {
        $choice = &$select->addChoice($key, $key, $fallBack);
      }

      $option = &$mTab->addTabOption('text', 'fallbackurl');
      $properties = array('desc' => __("Fallback URL:", 'easy-ads') . "&nbsp;&nbsp;&nbsp;&nbsp; ",
          'title' => __("This value is used only if the 'Alternate Ad' option is selected above.", 'easy-ads'),
          'value' => "Your Fallback URL",
          'style' => 'width:60%;text-align:left;',
          'before' => '<tr><td colspan="2">',
          'after' => '</td></tr></table>');
      $option->set($properties);

      ////////////
      $mTab = &$miniTab->addTab('colors');
      $properties = array('desc' => 'Colors',
          'title' => "Set Chitika Colors",
          'value' => $this->name . 'colors');
      $mTab->set($properties);

      $option = &$mTab->addTabOption('message', 'colors');
      $properties = array('desc' => '<b>' . sprintf(__('Pick colors for your %s ads', 'easy-ads'), $this->name) . ' </b>',
          'title' => 'Click on the color to popup a color picker',
          'value' => '',
          'after' => '<br />');
      $option->set($properties);

      $option = &$mTab->addTabOption('colorPicker', 'linkcolor');
      $properties = array('desc' => __('Link color: ', 'easy-ads'),
          'value' => '164675',
          'title' => __("Type in or pick color", 'easy-ads'),
          'style' => 'width:80%',
          'before' => '<table style="width:80%"><tr><td style="width:50%">',
          'between' => '</td><td style="width:50%">',
          'after' => '</td></tr>');
      $option->set($properties);

      $option = &$mTab->addTabOption('colorPicker', 'textcolor');
      $properties = array('desc' => __('Text color: ', 'easy-ads'),
          'value' => '333333',
          'title' => __("Type in or pick color", 'easy-ads'),
          'style' => 'width:80%',
          'before' => '<tr><td>',
          'between' => '</td><td>',
          'after' => '</td></tr>');
      $option->set($properties);

      $option = &$mTab->addTabOption('colorPicker', 'bgcolor');
      $properties = array('desc' => __('Background color: ', 'easy-ads'),
          'value' => '#FFFFFF',
          'title' => __("Type in or pick color", 'easy-ads'),
          'style' => 'width:80%',
          'before' => '<tr><td>',
          'between' => '</td><td>',
          'after' => '</td></tr>');
      $option->set($properties);

      $option = &$mTab->addTabOption('colorPicker', 'bordercolor');
      $properties = array('desc' => __('Border color: ', 'easy-ads'),
          'value' => 'B0C9EB',
          'title' => __("Type in or pick color", 'easy-ads'),
          'style' => 'width:80%',
          'before' => '<tr><td>',
          'between' => '</td><td>',
          'after' => '</td></tr></table>');
      $option->set($properties);

      ///////////////////
      $mTab = &$miniTab->addTab('widget');
      $properties = array('desc' => __('Widget', 'easy-ads'),
          'title' => __("Set Chitika Widget", 'easy-ads'),
          'value' => $this->name . 'widget');
      $mTab->set($properties);

      $option = &$mTab->addTabOption('checkbox', 'widget');
      $properties = array('desc' => sprintf(__('Enable widgets for %s', 'easy-ads'), $this->name),
          'title' => __("Widgets can be added from", 'easy-ads'),
          'value' => true,
          'before' => '&nbsp;',
          'after' => '<br />');
      $option->set($properties);

      $select = &$mTab->addTabOption('select', 'widgetformat');
      $properties = array('desc' => __('Widget Format', 'easy-ads'),
          'title' => __('Choose the Format (size)', 'easy-ads'),
          'value' => "160x600",
          'style' => 'width:30%',
          'before' => '&nbsp;',
          'after' => '<br />');
      $select->set($properties);

      if (!empty($sizes)) {
        sort($sizes);
        foreach ($sizes as $size) {
          $choice = &$select->addChoice($size, $size, $size);
        }
      }

      $msg = &$mTab->addTabOption('message', 'widgetLink');
      $properties = array('desc' => sprintf(__('Go to %s to find and place this widget on your sidebar', 'easy-ads'), '<a href="widgets.php"> ' . __('Appearance', 'easy-ads') . ' &rarr; ' . __('Widgets', 'easy-ads') . '</a>'),
          'before' => '<br >',
          'after' => '<br />');
      $msg->set($properties);

      //////////////

      $option = &$this->addOption('message', 'alignment');
      $properties = array(
          'desc' => "<b>" . __("Ad Alignment. Where to show ad blocks?", 'easy-ads') . "</b>",
          'before' => '<td><table><tr style="text-align:center;vertical-align:middle"><th colspan="5">',
          'after' => "</th></tr>\n" . '<tr style="text-align:center;vertical-align:middle">' .
          '<td>&nbsp;</td><td>&nbsp;Align Left&nbsp;</td><td>&nbsp;Center&nbsp;</td>' .
          '<td>&nbsp;Align Right&nbsp;</td><td>&nbsp;Suppress&nbsp;</td></tr>');
      $option->set($properties);

      $radio = &$this->addOption('radio', 'show_top');
      $properties = array('desc' => __('Top', 'easy-ads'),
          'title' => __('Where to show the top ad block?', 'easy-ads'),
          'value' => "left",
          'before' => '<tr style="text-align:center;vertical-align:middle"><td>' .
          __('Top', 'easy-ads') . '</td>',
          'after' => '</tr>');
      $radio->set($properties);

      $choice = &$radio->addChoice('left');
      $properties = array('value' => "left",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $choice = &$radio->addChoice('center');
      $properties = array('value' => "center",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $choice = &$radio->addChoice('right');
      $properties = array('value' => "right",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $choice = &$radio->addChoice('no');
      $properties = array('value' => "no",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $radio = &$this->addOption('radio', 'show_middle');
      $properties = array('desc' => 'Middle',
          'title' => __('Where to show the mid-text ad block?', 'easy-ads'),
          'value' => "left",
          'before' => '<tr style="text-align:center;vertical-align:middle"><td>' .
          __('Middle', 'easy-ads') . '</td>',
          'after' => '</tr>');
      $radio->set($properties);

      $choice = &$radio->addChoice('left');
      $properties = array('value' => "left",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $choice = &$radio->addChoice('center');
      $properties = array('value' => "center",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $choice = &$radio->addChoice('right');
      $properties = array('value' => "right",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $choice = &$radio->addChoice('no');
      $properties = array('value' => "no",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $radio = &$this->addOption('radio', 'show_bottom');
      $properties = array('desc' => 'Bottom',
          'title' => __('Where to show the bottom ad block?', 'easy-ads'),
          'value' => "right",
          'after' => '<br />',
          'before' => '<tr style="text-align:center;vertical-align:middle"><td>' .
          __('Bottom', 'easy-ads') . '</td>',
          'after' => '</tr></table>');
      $radio->set($properties);

      $choice = &$radio->addChoice('left');
      $properties = array('value' => "left",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $choice = &$radio->addChoice('center');
      $properties = array('value' => "center",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $choice = &$radio->addChoice('right');
      $properties = array('value' => "right",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $choice = &$radio->addChoice('no');
      $properties = array('value' => "no",
          'before' => '<td>',
          'after' => '</td>');
      $choice->set($properties);

      $option = &$this->addOption('message', 'show_or_hide');
      $properties = array(
          'desc' => "<b>" . __("Suppress Ad Blocks in:", 'easy-ads') . "</b>",
          'before' => '<table><tr style="text-align:left;vertical-align:middle"><td>',
          'after' => '</td><td></td></tr>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_feed');
      $properties = array('desc' => __('RSS feeds', 'easy-ads'),
          'title' => __("RSS feeds from your blog", 'easy-ads'),
          'value' => true,
          'before' => '<tr><td>&nbsp;',
          'after' => '</td>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_page');
      $properties = array('desc' =>
          '<a href="http://codex.wordpress.org/Pages" target="_blank">' .
          __('Static Pages', 'easy-ads') . '</a>',
          'title' => __("Ads appear only on blog posts, not on blog pages. Click to see the difference between posts and pages.", 'easy-ads'),
          'value' => true,
          'before' => '<td>&nbsp;',
          'after' => '</td></tr>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_home');
      $properties = array('desc' => __("Home Page", 'easy-ads'),
          'title' => __("Home Page and Front Page are the same for most blogs", 'easy-ads'),
          'value' => true,
          'before' => '<tr><td>&nbsp;',
          'after' => '</td>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_front_page');
      $properties = array('desc' => __("Front Page", 'easy-ads'),
          'title' => __("Home Page and Front Page are the same for most blogs", 'easy-ads'),
          'value' => true,
          'before' => '<td>&nbsp;',
          'after' => '</td></tr>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_attachment');
      $properties = array('desc' => __("Attachment Page", 'easy-ads'),
          'title' => __("Pages that show attachments", 'easy-ads'),
          'value' => true,
          'before' => '<tr><td>&nbsp;',
          'after' => '</td>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_category');
      $properties = array('desc' => __("Category Pages", 'easy-ads'),
          'title' => __("Pages that come up when you click on category names", 'easy-ads'),
          'value' => true,
          'before' => '<td>&nbsp;',
          'after' => '</td></tr>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_search');
      $properties = array('desc' => __("Search Page", 'easy-ads'),
          'title' => __("Pages that show search results", 'easy-ads'),
          'value' => true,
          'before' => '<tr><td>&nbsp;',
          'after' => '</td>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_sticky');
      $properties = array('desc' => __("Sticky Front Page", 'easy-ads'),
          'title' => __("Post that is defined as the sticky front page", 'easy-ads'),
          'value' => false,
          'before' => '<td>&nbsp;',
          'after' => '</td></tr>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_tag');
      $properties = array('desc' => __("Tag Pages", 'easy-ads'),
          'title' => __("Pages that come up when you click on tag names", 'easy-ads'),
          'value' => true,
          'before' => '<tr><td>&nbsp;',
          'after' => '</td>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_archive');
      $properties = array('desc' => __("Archive Pages", 'easy-ads'),
          'title' => __("Pages that come up when you click on year/month archives", 'easy-ads'),
          'value' => true,
          'before' => '<td>&nbsp;',
          'after' => '</td></tr></table></td></tr></table>');
      $option->set($properties);
    }

  }

}
