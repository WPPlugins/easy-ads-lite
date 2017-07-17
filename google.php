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

if (!class_exists('AdSenseWidget')) {

  class AdSenseWidget extends providerWidget {

    public static $provider;

    function AdSenseWidget($name = 'AdSenseWidget') {
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
        $adText = ezExtras::handleDefaultText($adText, '160x600');
        echo $this->decorate($adText);
        echo $after_widget;
      }
    }

    public static function setProvider(&$p) {
      self::$provider = & $p;
    }

  }

  // class AdSenseWidget
}
if (!class_exists('AdSense')) {

  class AdSense extends provider {

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
      if ($userid == "Your AdSense ID") {
        $adText = ezExtras::handleDefaultText('', $size);
        return $adText;
      }
      if (strpos($userid, "pub") === false) {
        $userid = "pub-" . $userid;
      }
      if ($size == '') {
        $size = $this->get('format' . $suffix);
      }
      $x = strpos($size, 'x' . $suffix);
      $w = substr($size, 0, $x);
      $h = substr($size, $x + 1);
      $type = $this->get('type' . $suffix);
      $channel = $this->get('channel' . $suffix);
      $linkColor = $this->get('linkcolor' . $suffix);
      $urlColor = $this->get('urlcolor' . $suffix);
      $textColor = $this->get('textcolor' . $suffix);
      $bgColor = $this->get('bgcolor' . $suffix);
      $borderColor = $this->get('bordercolor' . $suffix);
      $corners = $this->get('corners' . $suffix);

      $adText = "<script type=\"text/javascript\"><!--\n" .
              "google_ad_client = \"$userid\";\n" .
              "google_alternate_color = \"FFFFFF\";\n" .
              "google_ad_width = $w;\n" .
              "google_ad_height = $h;\n" .
              "google_ad_format = \"$size\";\n" .
              "google_ad_type = \"$type\";\n" .
              "google_ad_channel =\"$channel\";\n" .
              "google_color_border = \"$borderColor\";\n" .
              "google_color_link = \"$linkColor\";\n" .
              "google_color_bg = \"$bgColor\";\n" .
              "google_color_text = \"$textColor\";\n" .
              "google_color_url = \"$urlColor\";\n" .
              "google_ui_features = \"$corners\";\n" .
              "//--></script>\n" .
              "<script type=\"text/javascript\"\n" .
              "src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">\n" .
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
          $adText = ezExtras::handleDefaultText($adText);
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
          'title' => "Check to activate " . $this->name,
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
      $properties = array('desc' => __("Your AdSense Account Name: ", 'easy-ads'),
          'title' => __("Enter your AdSense Pub-ID", 'easy-ads'),
          'value' => "Your AdSense ID",
          'before' => '<table style="width:80%"><tr><td style="width:50%">',
          'between' => '</td><td style="width:50%">',
          'after' => '</td></tr>');
      $option->set($properties);

      $option = &$mTab->addTabOption('text', 'channel');
      $properties = array('desc' => __("AdSense Channel: ", 'easy-ads'),
          'title' => "",
          'value' => "AdSense Default",
          'before' => '<tr><td>',
          'between' => '</td><td>',
          'after' => '</td></tr>');
      $option->set($properties);

      $select = &$mTab->addTabOption('select', 'format');
      $properties = array('desc' => __('Format', 'easy-ads'),
          'title' => __('Choose the Format', 'easy-ads'),
          'value' => "300x250",
          'style' => 'width:80%',
          'before' => '<tr><td style="width:50%">',
          'between' => '</td><td style="width:50%">',
          'after' => '</td></tr>');
      $select->set($properties);

      $sizes = array("234x60", "468x60", "728x90", "120x600", "160x600", "120x240",
          "125x125", "180x150", "468x15", "728x15", "160x90", "200x200", "300x250",
          "336x280", "250x250", "120x90", "180x90", "200x90");

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
      $types = array('text' => __("Text Ad", 'easy-ads'),
          'image' => __("Image Ad", 'easy-ads'),
          'text_image' => __("Text and Image", 'easy-ads'));

      foreach ($types as $key => $type) {
        $choice = &$select->addChoice($key, $key, $type);
      }

      $select = &$mTab->addTabOption('select', 'corners');
      $properties = array('desc' => __('Corner Style', 'easy-ads'),
          'title' => __('Google lets you choose normal (squre) corners or rounded ones.', 'easy-ads'),
          'value' => "rc:0",
          'style' => 'width:80%',
          'before' => '<tr><td>',
          'between' => '</td><td>',
          'after' => '</td></tr></table>');
      $select->set($properties);

      $corners = array(
          'rc:0' => __("Normal", 'easy-ads'),
          'rc:6' => __("Rounded", 'easy-ads'));

      foreach ($corners as $key => $corner) {
        $choice = &$select->addChoice($key, $key, $corner);
      }

      ////////////
      $mTab = &$miniTab->addTab('colors');
      $properties = array('desc' => __('Colors', 'easy-ads'),
          'title' => __("Set AdSense Colors", 'easy-ads'),
          'value' => $this->name . 'colors');
      $mTab->set($properties);

      $option = &$mTab->addTabOption('message', 'colors');
      $properties = array('desc' => '<b>' . sprintf(__('Pick colors for your %s ads', 'easy-ads'), $this->name) . ' </b>',
          'title' => __('Click on the color to popup a color picker', 'easy-ads'),
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

      $option = &$mTab->addTabOption('colorPicker', 'urlcolor');
      $properties = array('desc' => __('URL color: ', 'easy-ads'),
          'value' => '2666F5',
          'title' => __("Type in or pick color", 'easy-ads'),
          'style' => 'width:80%',
          'before' => '<tr><td>',
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
          'title' => __("Set AdSense Widget", 'easy-ads'),
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
          'before' => '<tr style="text-align:center;vertical-align:middle"><td>Top</td>',
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
      $properties = array('desc' => __('Middle', 'easy-ads'),
          'title' => __('Where to show the mid-text ad block?', 'easy-ads'),
          'value' => "left",
          'before' => '<tr style="text-align:center;vertical-align:middle"><td>Middle</td>',
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
      $properties = array('desc' => __('Bottom', 'easy-ads'),
          'title' => __('Where to show the bottom ad block?', 'easy-ads'),
          'value' => "right",
          'after' => '<br />',
          'before' => '<tr style="text-align:center;vertical-align:middle"><td>Bottom</td>',
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
          'value' => false,
          'before' => '<tr><td>&nbsp;',
          'after' => '</td>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_front_page');
      $properties = array('desc' => __("Front Page", 'easy-ads'),
          'title' => __("Home Page and Front Page are the same for most blogs", 'easy-ads'),
          'value' => false,
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
