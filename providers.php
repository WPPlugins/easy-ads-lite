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

if (!class_exists("adBlock")) {

  class adBlock extends ezOption { // an ad block that will be displayed

    var $comment, $slug;

    function __construct($name, $slug = '') {// constructior
      parent::ezOption('adBlock', $name);
      $this->slug = $slug;
    }

    function decorate() {// apply styles
      if (empty($this->style)) {
        $inline = '';
      }
      else {
        $inline = 'style="' . $this->style . '"';
      }
      if (!empty($this->value)) {
        $slug = $this->slug;
        $this->value = "<div class='$slug $slug-" . strtolower($this->name) .
                "' $inline>{$this->value}</div>\n";
      }
    }

  }

  // End: Class adBlock
}

if (!class_exists("provider")) {

  class provider extends ezTab {

    var $maxAds = 3;
    var $adBlocks = array(); // ad blocks that will be injected by the content filter
    var $positions = array('top', 'middle', 'bottom');
    var $killOptions = array('page', 'home', 'attachment', 'front_page',
        'category', 'sticky', 'search', 'tag', 'archive', 'feed');

    // function provider($name, $desc, $referral) {// constructor
    function __construct($name, $defaults) {// constructor
      parent::ezTab($name, $defaults);
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
      $properties = array('desc' => __('Tabbie', 'easy-ads'),
          'title' => __(" tab interface ", 'easy-ads'),
          'value' => $this->name,
          'before' => '<table style="border-collapse:separate;border-spacing:10px;"><tr style="text-align:left;vertical-align:top"><td style="width:50%"><br />',
          'after' => '</td>');
      $miniTab->set($properties);

      $mTab = &$miniTab->addTab('body');
      $properties = array('desc' => __('Body', 'easy-ads'),
          'title' => __(" tab interface ", 'easy-ads'),
          'value' => $this->name . 'body');
      $mTab->set($properties);

      $option = &$mTab->addTabOption('textarea', 'body');
      $properties = array('desc' => '<b>' .
          sprintf(__('Enter your %s code here:', 'easy-ads'), $this->name) .
          ' </b>',
          'title' => sprintf(__('Logon and generate advert code for %s and paste it in its entirity here.', 'easy-ads'), $this->name),
          'value' => $this->plugin->defaults['defaultText'],
          'after' => '<br />');
      $option->set($properties);

      $mTab = &$miniTab->addTab('widget');
      $properties = array('desc' => __('Widget', 'easy-ads'),
          'title' => __("Enter your widget code here", 'easy-ads'),
          'value' => $this->name . 'widget');
      $mTab->set($properties);

      $option = &$mTab->addTabOption('textarea', 'widget-text');
      $properties = array('desc' => '<b>' .
          sprintf(__('Enter your %s code here:', 'easy-ads'), $this->name) .
          ' </b>',
          'title' => sprintf(__('Logon and generate advert code for %s and paste it in its entirity here.', 'easy-ads'), $this->name),
          'style' => "width: 96%; height: 140px;",
          'value' => $this->plugin->defaults['defaultText'],
          'after' => '<br />');
      $option->set($properties);

      $option = &$mTab->addTabOption('checkbox', 'widget');
      $properties = array('desc' => sprintf(__('Enable widgets for %s', 'easy-ads'), $this->name),
          'title' => __("Widgets can be added from", 'easy-ads'),
          'value' => true,
          'before' => '&nbsp;');
      $option->set($properties);

      $msg = &$mTab->addTabOption('message', 'widgetLink');
      $properties = array('desc' => sprintf(__('Go to %s to find and place this widget on your sidebar', 'easy-ads'), '<a href="widgets.php"> ' . __('Appearance', 'easy-ads') . ' &rarr; ' . __('Widgets', 'easy-ads') . '</a>'),
          'before' => '<br >',
          'after' => '<br />');
      $msg->set($properties);


      // <<< end Mini Tab

      $option = &$this->addOption('message', 'alignment');
      $properties = array(
          'desc' => "<b>" . __("Ad Alignment. Where to show ad blocks?", 'easy-ads') . "</b>",
          'before' => '<td><table><tr style="text-align:center;vertical-align:middle"><th colspan="5">',
          'after' => "</th></tr>\n" . '<tr style="text-align:center;vertical-align:middle">' .
          '<td>&nbsp;</td><td>&nbsp;Align Left&nbsp;</td><td>&nbsp;Center&nbsp;</td>' .
          '<td>&nbsp;Align Right&nbsp;</td><td>&nbsp;Suppress&nbsp;</td></tr>');
      $option->set($properties);

      // >>> Radiobox
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
      // <<< end  Radiobox
      // >>> Radiobox
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
      // <<< end  Radiobox
      // >>> Radiobox
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
      // <<< end  Radiobox
      // >>> Radiobox
      $option = &$this->addOption('message', 'show_or_hide');
      $properties = array(
          'desc' => "<b>" . __("Suppress Ad Blocks in:", 'easy-ads') . "</b>",
          'before' => '<table><tr style="text-align:left;vertical-align:middle"><td>',
          'after' => '</td><td></td></tr>');
      $option->set($properties);

      $option = &$this->addOption('checkbox', 'kill_feed');
      $properties = array('desc' =>
          __('RSS feeds', 'easy-ads'),
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

    function defineSubmitButtons() { // Add submit buttons
      parent::defineSubmitButtons();

      $button = &$this->addSubmitButton('submit', 'update');
      $properties = array('value' => __('Save Changes', 'easy-ads'),
          'title' => __("Save the changes as specified above.", 'easy-ads'));
      $button->set($properties);

      $button = &$this->addSubmitButton('submit', 'reset');
      $properties = array('value' => __('Reset Options', 'easy-ads'),
          'title' => sprintf(__('DANGER: Reset all %s options to default.', 'easy-ads'), $this->name));
      $button->set($properties);

      $button = &$this->addSubmitButton('submit', 'clean_db');
      $properties = array('value' => __('Clean Database', 'easy-ads'),
          'title' => sprintf(__('DANGER: Delete all %s options from the database.', 'easy-ads'), $this->name));
      $button->set($properties);
    }

    function renderContent() {
      $name = $this->name;
      echo '<h3>' . sprintf(__('Options for %s ', 'easy-ads'), $name) . "</h3>\n";
    }

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

    // ------------ Content Filter -----------------
    function buildAdBlocks() {
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      if ($this->isActive) {
        foreach ($this->plugin->positions as $key) {
          $name = $this->name . '-' . $key;
          $this->adBlocks[$key] = new adBlock($name, $this->plugin->baseName);
          $adText = stripslashes($this->get('body')); // The option will be called 'body-'.$key
          $adText = ezExtras::handleDefaultText($adText);
          $this->adBlocks[$key]->set($adText);
        }
      }
    }

    function applyAdminOptions() { // From option values to inline style strings
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      if ($this->isActive) {
        foreach ($this->plugin->positions as $key) {
          $adBlock = & $this->adBlocks[$key];
          $showKey = 'show_' . $key;
          $alignment = $this->options[$showKey]->get();
          if ($alignment == 'no') {
            $name = $this->name;
            $emptyText = "\n<!-- {$this->plugin->name}: Suppressed $name by $showKey option -->\n";
            $adBlock->set($emptyText);
          }
          else {
            if ($alignment == 'left') {
              $style = 'float:left;display:block;';
            }
            if ($alignment == 'right') {
              $style = 'float:right;display:block;';
            }
            if ($alignment == 'center') {
              $style = 'display:block;text-align:center;';
            }
            if (!empty($style)) {
              $properties = array();
              $properties['style'] = $style;
              $adBlock->set($properties);
            }
          }
        }
      }
    }

    function applyMetaOptions() { // Read options given as post meta tags.
      // Post meta options are of the kind:
      //   key = provider name, pname-top, pname-bottom, pname-widget etc
      //   value = no, left, right, center etc.
      // Example: clicksor => no, adsense-bottom => left etc
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      if ($this->isActive) {
        global $post;
        $metaKey = strtolower($this->name);
        $customStyle = get_post_custom_values($metaKey, $post->ID, true);
        if (is_array($customStyle)) {
          $metaStyle = strtolower($customStyle[0]);
        }
        else {
          $metaStyle = strtolower($customStyle);
        }
        if ($metaStyle == 'no') {
          foreach ($this->plugin->positions as $key) {
            $emptyText = "\n<!-- {$this->plugin->name}: Suppressed $key by custom tag: $metaKey -->\n";
            $this->adBlocks[$key]->set($emptyText);
          }
          return;
        }
        foreach ($this->plugin->positions as $key) {
          $metaKey = $this->name . '-' . $key;
          $customStyle = get_post_custom_values($metaKey, $post->ID, true);
          if (is_array($customStyle)) {
            $metaStyle = strtolower($customStyle[0]);
          }
          else {
            $metaStyle = strtolower($customStyle);
          }

          if ($metaStyle == 'left') {
            $style = 'float:left;display:block;';
          }
          if ($metaStyle == 'right') {
            $style = 'float:right;display:block;';
          }
          if ($metaStyle == 'center') {
            $style = 'text-align:center;display:block;';
          }
          if ($metaStyle == 'no') {
            $emptyText = "\n<!-- {$this->plugin->name}: Suppressed $key by custom tag: $metaKey -->\n";
            $this->adBlocks[$key]->set($emptyText);
          }
          if (!empty($style)) {
            $properties = array();
            $properties['style'] = $style;
            $this->adBlocks[$key]->set($properties);
          }
        }
      }
    }

    function decorateAdBlocks() { // From option values to inline style strings
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      if ($this->isActive) {
        foreach ($this->plugin->positions as $key) {
          $this->adBlocks[$key]->slug = $this->plugin->baseName;
          $this->adBlocks[$key]->decorate();
        }
      }
    }

    function buildAdStacks() {
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      if ($this->isActive) {
        foreach ($this->killOptions as $k) {
          $fn = 'is_' . $k;
          $key = 'kill_' . $k;
          if (function_exists($fn)) {
            if ($this->options[$key]->get() && $fn()) {
              return;
            }
          }
          else {
            $errorMessage = '<div class="error"><p><b><em>ezAPI</em></b>: ' .
                    sprintf(__("%s: Unknown function (%s) requested in %s", 'easy-ads'), $this->name, $fn, "<code>" . __FUNCTION__ . "</code>.") .
                    "</p></div>\n";
            echo $errorMessage;
          }
        }
        $this->applyMetaOptions(); // meta options are defined only within the Loop
        $this->decorateAdBlocks();
        // build an ad stack each per position (top, middle, bottom)
        foreach ($this->plugin->positions as $k) {
          array_push($this->plugin->adArrays[$k], $this->adBlocks[$k]->get());
        }
      }
    }

  }

  // End: Class provider
}

if (!class_exists("providerWidget")) {

  class providerWidget extends WP_Widget {

    var $name, $slug, $plgName;
    public static $provider;

    function providerWidget($name = 'providerWidget', $provider = '') {
      if ($provider == '') {
        $provider = self::$provider;
      }
      $plgName = $provider->plugin->name;
      $widget_ops = array('classname' => 'providerWidget',
          'description' => sprintf(__("Show %s (%s) block in your sidebar as a widget.", 'easy-ads'), $plgName, $provider->name),
//        'customize_selective_refresh' => true // TODO: Causes refresh loop in customizer
        );
      parent::__construct($name, "$plgName: {$provider->name}", $widget_ops);
      $this->plgName = $plgName . ' Lite';
      $this->slug = $provider->plugin->slug . '-lite';
    }

    public static function setProvider(&$p) {
      self::$provider = & $p;
    }

    function decorate($adText) {// apply styles
      if (empty($this->style)) {
        $inline = '';
      }
      else {
        $inline = 'style="' . $this->style . '"';
      }
      if (!empty($adText)) {
        echo "<div class='{$this->slug} {$this->slug}-widget' $inline>$adText</div>\n";
      }
    }

    function widget($args, $instance) {
      extract($args);
      $title = '';
      if (!empty($instance['title'])) {
        $title = apply_filters('widget_title', $instance['title']);
      }
      echo $before_widget;
      if ($title) {
        echo $before_title . $title . $after_title;
      }
      $adText = stripslashes(self::$provider->get('widget-text'));
      if (empty($adText)) {
        echo sprintf(__("Empty Widget Text from %s", 'easy-ads'), "<code>" . $this->name . "</code>");
      }
      else {
        $adText = ezExtras::handleDefaultText($adText, '160x600');
        echo $this->decorate($adText);
        echo $after_widget;
      }
    }

    function update($new_instance, $old_instance) {
      $instance = $old_instance;
      $instance['title'] = '';
      if (!empty($new_instance['title'])) {
        $instance['title'] = strip_tags($new_instance['title']);
      }
      return $instance;
    }

    function form($instance) {
      $title = '';
      if (!empty($instance['title'])) {
        $title = esc_attr($instance['title']);
      }
      echo '<p><label for="' . $this->get_field_id('title') . '">' .
      __('Title:', 'easy-ads') .
      '  <input class="widefat" id="' . $this->get_field_id('title') .
      '" name="' . $this->get_field_name('title') . '" type="text" value="' .
      $title . '" /></label></p>';
      echo "<p>" . sprintf(__("Configure it at %s", 'easy-ads'), "<br /><a href='options-general.php?page={$this->slug}.php'> " . __("Settings", 'easy-ads') . " &rarr; {$this->plgName}</a></p>\n");
    }

  }

  // class providerWidget
}
if (!class_exists('Overview')) {

  class Overview extends ezOverview {

    function __construct() {
      $this->name = "Overview";
      $this->isActive = false;
      $this->isAdmin = true;
    }

    function renderContent() {
      if (!$this->checkDependencyInjection(__FUNCTION__))
        return;
      $ezPlugin = & $this->plugin;
      $slug = $ezPlugin->slug;
      $plgName = $ezPlugin->name;
      $instructionText = '<h4>Instructions</h4>' .
              '<ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >' .
              ezTab::makeLIwithTooltip(__('Sign up', 'easy-ads'), __('Sign up for the ad providers shown on the right (which will give me some referral income).', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Generate Code', 'easy-ads'), __('Generate your ad code from the ad-provider web site.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Enter Code', 'easy-ads'), __('Enter your ad code into the text-boxes under the ad providers tabs.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Configure', 'easy-ads'), __('If needed, configure other options for each ad provider in the corresponding tab.', 'easy-ads')) .
              "</ul><br />\n";
      $fetureText = '<h4>Features</h4><ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >' .
              ezTab::makeLIwithTooltip(__('Admin Control Panel', 'easy-ads'), __('The <b>Admin</b> tab gives you general options that apply to all providers, and common tools and actions (like Reset All Options, Clean Database etc.) You also have a button to migrate options between different plugin versions.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Positions and Slots', 'easy-ads'), __('Three positions (Top, Middle and Bottom) and a configurable number of slots for ads. (See the Admin Tab for details and an illustration.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Custom Field Control', 'easy-ads'), sprintf(__('In %s, you have more options [through <strong>custom fields</strong>] to control ad blocks in individual posts/pages. Add custom fields with keys like %s,  and with values like <strong>left, right, center</strong> or <strong>no</strong> to have control how the ad blocks show up in each post or page. The value <strong>no</strong> suppresses all the ad blocks in the post or page for that provider.', 'easy-ads'), $plgName, "<strong>$slug-adsense-top, $slug-adsense-middle, $slug-adsense-bottom</strong>")) .
              ezTab::makeLIwithTooltip(__('CSS Control', 'easy-ads'), sprintf(__('All %s that %s creates have the class attribute %s. Furthermore, they have class attributes like %s, %s etc., (ie, %s). You can set the style for these classes in your theme <code>style.css</code> to control their appearance.', 'easy-ads'), "<code>&lt;div&gt;</code>s", $plgName, "<code>$slug</code>", "<code>$slug-adsense-top</code>", "<code>$slug-clicksor-bottom</code>", "<code>$slug-provider-position</code>")) .
              "</ul><br />\n";
      $planText = '<h4>' . __('Future Plans', 'easy-ads') . '</h4><ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >' .
              ezTab::makeLIwithTooltip(__('Widgets', 'easy-ads'), __('I will release options to include sidebar widgets with optional ad customization. That is, you will be able to use the same ad code for both main text and the widgets, or have different texts, to be customized on the widgets page.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Ad Rotation', 'easy-ads'), __('I will provide means to rotate ads among various providers with user-defined frequency.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('More Providers', 'easy-ads'), __('This plugin is designed with extensibility in mind. I will keep adding more ad providers, or even let the end-users add them.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Provider Specificity', 'easy-ads'), __('This initial release treats all ad providers essentially the same way. In the next release, I will start introducing more specificity, like specialized fields for HopID, PubID, colors, etc.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Expertise Level', 'easy-ads'), __('I plan to introduce expertise levels (Easy, Advanced and Expert tabs) within the tab for each ad provider.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Max Number of Ad blocks', 'easy-ads'), __('Since some providers require you to limit the number of ad blocks to some policy-driven ceiling, I will expose that option to you.<br />Also to be customized is the number of ads per slot. In this initial release, there are three slots (top, middle and bottom), each of which can take two ad blocks. In a future release, you will have much more customization options.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Ad Block Customization', 'easy-ads'), __('Right now, all the ad blocks are designed to display the same ad code, for which the providers will serve different text. In a future release, I will give you a means of introducing different texts for different locations, possibly in a tabbed interface.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Internationalization', 'easy-ads'), __('Future versions will provide MO/PO files for internationalization.', 'easy-ads')) .
              "</ul><br />\n";
      echo '<table style="width:95%">', "\n";
      echo '<tr style="text-align:center;vertical-align:middle">', "\n";
      echo '<td style="width:46%">', "\n";
      echo '<table style="width:100%">', "\n";
      echo '<tr style="text-align:center;vertical-align:middle">', "\n";
      echo '<td style="text-align:left">', "\n";
      echo $instructionText;
      echo $fetureText;
      echo $planText;

      echo "</td></tr></table>\n";
      echo "</td>\n";
      echo '<td style="width:54%">', "\n";
      echo '<table style="width:100%">';
      echo '<tr><th colspan="2">' .
      __('The following providers are supported', 'easy-ads') .
      '</th></tr>', "\n";
      if ($this->checkDependencyInjection(__FUNCTION__)) {
        $ezPlugin = & $this->plugin;
        foreach ($ezPlugin->tabs as $p) {
          if (!$p->isAdmin) {
            echo '<tr style="text-align:center;vertical-align:middle">', "\n";
            echo '<td style="width:33%">';
            echo $p->referral;
            echo "<br /><br /></td>\n";
            echo "<td style='text-align:left'>", $p->options['info']->desc, "</td>\n";
            echo "</tr>\n";
          }
        }
      }
      echo "</table></td>\n";
      echo "</tr></table>\n";

      echo '<table style="width:80%;margin-left:auto;margin-right:auto;"><tr style="vertical-align:top">';
      $ez = $ezPlugin->ez;
      $ez->renderHeadText();
      echo '</tr></table>';

      $ez->renderSupport();
    }

  }

  // End: Class Overview
}
if (!class_exists('Admin')) {

  class Admin extends EzAdminTab {

    function __construct() {
      parent::EzAdminTab("Admin", "");
      $this->isActive = false;
      $this->isAdmin = true;
    }

    function renderContent() {
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      $ezPlugin = & $this->plugin;
      $slug = $ezPlugin->slug;
      $plgName = $ezPlugin->name;
      $infoText = '<h4>' . __('Ad Positions, Slots and Blocks', 'easy-ads') . '</h4>' .
              sprintf(__('You can define ad blocks in three positions in your post - Top, Middle and Bottom. Each position can have multiple "slots". See the picture for details. By default, you have one slot per position, but you can change it below [Pro feature]. In addition, you have widgets that you can place anywhere on your sidebar as many times as you want, by %s.', 'easy-ads'), '<a href="widgets.php">' . __('Appearance', 'easy-ads') . ' &rarr; ' . __('Widgets', 'easy-ads') . '</a>');
      $compText = '<h4>' . __('Competition', 'easy-ads') . '</h4>' .
              sprintf(__('The providers supported in %s may not all be compatible with each other, or, in particular, with AdSense. Be careful how you use them. But competition is probably a good thing. You may find your earnings go up because of it, which is the objective behind my many advertising plugins.', 'easy-ads'), $plgName);
      $adminText = '<h4>' . __('Admin Control Panel', 'easy-ads') . '</h4>' .
              __('This Admin tab gives you a control panel with tools and options that apply to the ad blocks from all the providers, en masse. You can', 'easy-ads') . ' <ul style="padding-left:10px;list-style-type:circle; list-style-position:inside;" >' .
              ezTab::makeLIwithTooltip(__('Set Active', 'easy-ads'), __('By checking the box against their name below, you can set an ad provider as <b>Active</b> so that their ads appear on your pages. When you deactivate a provider, the corresponding tab header  will sport red fonts.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Reset All Options', 'easy-ads'), __('You can reset all options to their default values if you feel that you have irrevocably messed them up.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Migrate Options', 'easy-ads'), __('When you upgrade the plugin to a newer version, some of the options may become incompatible. You can migrate your old options (to the extent possible) to the new version using this button.', 'easy-ads')) .
              ezTab::makeLIwithTooltip(__('Clean Database', 'easy-ads'), sprintf(__('This button gives you the option of cleaning all the %s options from your databse as a prelude to deactivating or deleting your plugin. In a future release, you will have a button to deactivate the plugin directly.', 'easy-ads'), $plgName)) .
              "</ul><br />\n";
      $url = $this->plugin->URL;
      $picText = "<span id='ad-slot' style='text-decoration:underline' " .
              "onmouseover=\"Tip('" .
              htmlspecialchars(__("In the Pro version, you can see an illustration of the ad slots.", 'easy-ads') .
                      "<br /><a href=\"http://buy.thulasidas.com/$slug\" title=\"" .
                      sprintf(__('Buy the Pro version of the %s plugin for $5.95', 'easy-ads'),$plgName) .
                      "\"><b>" .
                      __("Get Pro Version!", 'easy-ads') . "</b></a>") .
              "', WIDTH, 200,  FIX, [this, -40, -140], TITLE, '" .
              __("Click to close", 'easy-ads') .
              "', STICKY, 1, CLOSEBTN, true, CLICKCLOSE, true)\" onmouseout=\"UnTip()\">" .
              __("Hover on the picture to see details", 'easy-ads') .
              "<br /><br /><img src='" . $url . "/ad-slots-small.gif' style='border:0' alt='[ad-slots-small]' /></span>";
      echo '<table style="width:95%;padding:10px;border-spacing:10px;">' . "\n";
      echo '<tr><th colspan="2"><b>' .
              __('General Information', 'easy-ads') .
              '</b></th></tr>' . "\n";
      echo '<tr style="text-align:center;vertical-align:middle">', "\n";
      echo '<td style="width:50%;text-align:left">', "\n";
      echo $infoText;
      echo "</td>\n";
      echo "<td>\n";
      echo $picText;
      echo "</td></tr>\n";
      echo '<tr><td>';
      echo $adminText;
      echo "</td>\n";
      echo "<td>\n";
      echo $compText;
      echo "</td></tr>\n";
      echo '<tr><th colspan="2"><b>' .
              __('Global Options', 'easy-ads') .
              '</b></th></tr>', "\n";
      echo "</table>\n";
    }

    function renderForm() {
      if (!$this->checkDependencyInjection(__FUNCTION__)) {
        return;
      }
      $ezPlugin = & $this->plugin;
      $ez = $ezPlugin->ez;
      $ez->renderWhyPro();
    }

    function defineOptions() {
      unset($this->options);
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

      $button = &$this->addSubmitButton('submit', 'clean_db0');
      $properties = array('value' => __('Clean Database', 'easy-ads'),
          'title' => __('DANGER: Delete all options from the database.  [Not active yet.]', 'easy-ads'));
      $button->set($properties);
    }

  }

  // End: Class Admin
}
if (!class_exists('About')) {

  class About extends ezAbout {

  }

}
