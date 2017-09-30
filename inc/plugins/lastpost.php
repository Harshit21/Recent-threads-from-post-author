<?php

// Plugin : Add lastpost
// Author : Harshit Shrivastava
// Date : 2016-2017

// Disallow direct access to this file for security reasons

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
$plugins->add_hook("showthread_end", "lastpost_show");

function lastpost_info()
{
	return array(
		"name"			=> "Last posts from author",
		"description"	=> "Show recent post from the post author",
		"website"		=> "http://mybb.com",
		"author"		=> "Harshit Shrivastava",
		"authorsite"	=> "mailto:harshit_s21@rediffmail.com",
		"version"		=> "2.0",
		"compatibility" => "18*"
	);
}
function lastpost_validate($gid)
{
	global $mybb;
	if($mybb->settings['lastpost_group'])
	{
		$gids = explode(",", $mybb->settings['lastpost_group']);
		if(in_array($gid, $gids))
		{
			return True;
		}
	}
	return False;
}
function lastpost_show()
{
	global $mybb, $quickreply, $threadnotesbox;
	if ($mybb->settings['lastpost_show'] == 1)
	{
		if ($mybb->settings['lastpost_position'] == 'float_top')
			$threadnotesbox .= lastpost();
		else if($mybb->settings['lastpost_position'] == 'float_bottom')
			$quickreply =  lastpost().$quickreply;
		
	}
}

function lastpost()
{
	global $mybb,$db,$thread,$icon_cache,$icon,$lang;
	$lang->load("lastpost");
	$uname = htmlspecialchars_uni($thread['username']);
	$data = "<table class=\"tborder clear\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\"><tr><td class=\"thead\" colspan=\"5\">".$lang->sprintf($lang->lastpost_msg, $uname)."</td></tr><tr><td class=\"tcat\" colspan=\"2\" width=\"65%\"><span class=\"smalltext\">".$lang->lastpost_threads."</td><td class=\"tcat\" width=\"15%\"><span class=\"smalltext\">".$lang->lastpost_date."</td><td class=\"tcat\" width=\"15%\"><span class=\"smalltext\">".$lang->lastpost_views."</td><td class=\"tcat\" width=\"15%\"><span class=\"smalltext\">".$lang->lastpost_replies."</td></tr>";
	$limit = (int)$mybb->settings['lastpost_post'];
	$order = $mybb->settings['lastpost_type'] == 'show_recent'?"dateline":($mybb->settings['lastpost_type'] == 'show_views'?"views":"replies");
	$query = $db->simple_select("threads","subject,tid,icon,dateline, views,replies","uid = '{$thread['uid']}'  AND tid!='{$thread['tid']}' ORDER BY ".$order." DESC LIMIT 0,{$limit}");
	while($posts = $db->fetch_array($query))
		{			
			$icon = $icon_cache[$posts['icon']];
			$iconpath = "";
			if(!empty($icon['path']))
				$iconpath = "<img src=".htmlspecialchars_uni($icon['path']).">";
			$data .=  "<tr><td class=\"trow1 forumdisplay_regular\" width=\"5%\">".$iconpath."</td><td class=\"trow1 forumdisplay_regular\"><a href=\"showthread.php?tid={$posts["tid"]}\">".htmlspecialchars_uni($posts["subject"])."</a></td><td class=\"trow1 forumdisplay_regular\">".my_date('relative', $posts['dateline'])."</td><td class=\"trow1 forumdisplay_regular\">".$posts['views']."</td><td class=\"trow1 forumdisplay_regular\">".$posts['replies']."</td></tr>";
		}
		$data .= "</table>";
		return $data;
}
function lastpost_activate()
{
global $db, $mybb;
$lastpost_group = array(
        'gid'    => 'NULL',
        'name'  => 'lastpost',
        'title'      => 'Last posts from author',
        'description'    => 'Show recent post from the post author',
        'disporder'    => "1",
        'isdefault'  => "0",
    ); 
$db->insert_query('settinggroups', $lastpost_group);
$gid = $db->insert_id(); 
// Enable / Disable
$lastpost_setting1 = array(
        'sid'            => 'NULL',
        'name'        => 'lastpost_show',
        'title'            => 'Enable this plugin',
        'description'    => 'If you set this option to yes, this plugin will add lastpost from the author to your posts.',
        'optionscode'    => 'yesno',
        'value'        => '1',
        'disporder'        => 1,
        'gid'            => intval($gid),
    );
	
$lastpost_setting2 = array(
        'sid'            => 'NULL',
        'name'        => 'lastpost_post',
        'title'            => 'Number of posts to show',
        'description'    => 'Enter the number of post to show',
        'optionscode'    => 'text',
        'value'        => '5',
        'disporder'        => 2,
        'gid'            => intval($gid),
    );
$lastpost_setting3 = array(
        'sid'            => 'NULL',
        'name'        => 'lastpost_position',
        'title'            => 'Position',
        'description'    => 'Place to show the recent post',
        'optionscode'    => 'select
float_top=Show at top
float_bottom=Show at bottom',
        'value'        => 'float_bottom',
        'disporder'        => 3,
        'gid'            => intval($gid),
    );
$lastpost_setting4 = array(
        'sid'            => 'NULL',
        'name'        => 'lastpost_type',
        'title'            => 'Show by category',
        'description'    => 'Show the threads by category',
        'optionscode'    => 'select
show_recent=Show the recent threads
show_views=Show popular posts by views
show_reply = Show the popular posts by replies',
        'value'        => 'show_recent',
        'disporder'        => 4,
        'gid'            => intval($gid),
    );
	$db->insert_query('settings', $lastpost_setting1);
	$db->insert_query('settings', $lastpost_setting2);
	$db->insert_query('settings', $lastpost_setting3);
	$db->insert_query('settings', $lastpost_setting4);
rebuild_settings();
}
function lastpost_deactivate()
{
  global $db;
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'lastpost_show'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'lastpost_post'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'lastpost_position'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name = 'lastpost_type'");
  $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='lastpost'");
  rebuild_settings();
}
?>
