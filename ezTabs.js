var LINK = "_link";
var CLASS_NAME = "current";

var displayed_tab = null;
var displayed_minitab = null;

(function()
{
  if (window.addEventListener)
  {
    window.addEventListener("load", setup_tabs, false);
  }
  else
  {
    window.attachEvent("onload", setup_tabs);
  }
})();

function setup_tabs()
{
  var tab_elements = document.getElementsByClassName("tabs") ;
  var tab_links ;

  for (var j = 0; j < tab_elements.length; j++)
  {
    tab_links = tab_elements[j].getElementsByTagName("a");
    for (var i = 0; i < tab_links.length; i++)
    {
      setup_tab_link(tab_links[i]);
    }
  }
}

function setup_tab_link(link)
{
  var num = link.id.lastIndexOf("_");
  var isMiniTab = link.id.indexOf("mtab") === 0 ;
  var link_id = link.id.substring(0, num);
  if (isMiniTab) link.onclick = function(){display_minitab(link_id);};
  else link.onclick = function(){display_tab(link_id);};
}

function hide_all_tabs(prefix)
{
  var tab_divs = document.getElementsByTagName("div");

  //Only need those div elements that have an id value starting with 'tab'
  for (var i = 0; i < tab_divs.length; i++)
  {
    if (tab_divs[i].id.indexOf(prefix) === 0)
    {
      change_tab_display(tab_divs[i], "none", "");
    }
  }
}

function display_tab(id)
{
  if (displayed_tab)
  {
    change_tab_display(displayed_tab, "none", "");
  }
  else
  {
    hide_all_tabs("tab");
  }

  //Make the selected tab visible
  displayed_tab = document.getElementById(id);

  change_tab_display(displayed_tab, "block", CLASS_NAME);
}

function display_minitab(id)
{
  var prefix = id.slice(0,id.indexOf("-")) ;
  if (displayed_minitab)
  {
    var displayed_prefix = displayed_minitab.id.slice(0,displayed_minitab.id.indexOf("-")) ;
    if (displayed_prefix == prefix) change_tab_display(displayed_minitab, "none", "");
    else hide_all_tabs(prefix);
  }
  else
  {
    hide_all_tabs(prefix);
  }

  //Make the selected tab visible
  displayed_minitab = document.getElementById(id);

  change_tab_display(displayed_minitab, "block", CLASS_NAME);
}

function change_tab_display(tab_content_element, display, class_name)
{
  var tab_link_id = tab_content_element.id ;
  //Make the selected tab visible or not visible
  tab_content_element.style.display = display;

  //Change the class name of the tab link
  document.getElementById(tab_link_id + LINK).className = class_name;
}
