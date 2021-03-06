<?php

## ---------------------------------------------------
#  ADDICTIVE COMMUNITY
## ---------------------------------------------------
#  Developed by Brunno Pleffken Hosti
#  File: adm_system_optimization.php
#  License: GPLv2
#  Copyright: (c) 2017 - Addictive Community
## ---------------------------------------------------

use \AC\Kernel\Database;
use \AC\Kernel\Html;
use \AC\Kernel\Http;

// ---------------------------------------------------
// Execute queries, if defined
// ---------------------------------------------------

$execute = (Http::request("execute")) ? Http::request("execute") : false;

if($execute) {
	switch($execute) {
		// Recount members

		case "members":

			// Update member count

			Database::query("SELECT COUNT(*) AS count FROM c_members;");
			$total = Database::fetch();
			$total = $total['count'];

			Database::query("UPDATE c_stats SET member_count = '{$total}';");

			// Exit

			$Admin->registerLog("Executed system optimization: member counting.");
			echo Html::notification("Registered members have been successfully recounted.", "success");

			break;

		// Recount member's threads and posts

		case "threads":

			// Update posts

			Database::query("SELECT COUNT(*) AS count FROM c_posts;");
			$total = Database::fetch();
			$total = $total['count'];

			Database::query("UPDATE c_stats SET post_count = '{$total}';");

			// Update threads

			Database::query("SELECT COUNT(*) AS count FROM c_threads;");
			$total = Database::fetch();
			$total = $total['count'];

			Database::query("UPDATE c_stats SET thread_count = '{$total}';");

			// Update members thread count

			$members = Database::query("SELECT m_id FROM c_members;");

			while($_members = Database::fetch($members)) {
				$posts = Database::query("SELECT COUNT(*) AS total FROM c_posts WHERE author_id = '{$_members['m_id']}';");

				while($post_count = Database::fetch($posts)) {
					Database::query("UPDATE c_members SET posts = '{$post_count['total']}' WHERE m_id = '{$_members['m_id']}';");
				}
			}

			// Exit

			$Admin->registerLog("Executed system optimization: threads and posts counting.");
			echo Html::notification("Threads and posts have been successfully recounted.", "success");

			break;

		// Recount replies

		case "replies":

			// List threads (global)

			$threads = Database::query("SELECT t_id FROM c_threads;");

			// Replies counting

			while($_threads = Database::fetch($threads)) {
				$posts = Database::query("SELECT COUNT(p_id) AS post_count FROM c_posts WHERE thread_id = '{$_threads['t_id']}';");

				while($_posts = $Db2->fetch($posts)) {
					Database::query("UPDATE c_threads SET replies = '{$_posts['post_count']}' WHERE t_id = '{$_threads['t_id']}';");
				}
			}

			// Exit

			$Admin->registerLog("Executed system optimization: replies counting.");
			echo Html::notification("Replies have been successfully recounted.", "success");

			break;
	}
}

?>

<h1>System Optimization</h1>

<div class="block">
	<form action="" method="post">
		<table class="table">
			<thead>
				<tr>
					<th colspan="4">Predefined Tasks</th>
				</tr>

				<tr>
					<td>Task Description</td>
					<td width="1%">Execute</td>
				</tr>
			</thead>
			<tr>
				<td>
					<b>Recount members</b><br>
					This will recount registered members based on current number of members from the database and reset last registered member. This should not be used if you wish to retain your registered member counts.
				</td>
				<td style="text-align:center"><a href="main.php?act=system&p=optimization&execute=members"><i class="fa fa-play-circle-o"></i></a></td>
			</tr>
			<tr>
				<td>
					<b>Recount member's threads and posts</b><br>
					This will recount members threads and posts based on current posts from the database. This will almost certainly REDUCE the post counts for your members as deleted and pruned posts will no longer be counted.
				</td>
				<td style="text-align:center"><a href="main.php?act=system&p=optimization&execute=threads"><i class="fa fa-play-circle-o"></i></a></td>
			</tr>
			<tr>
				<td>
					<b>Recount replies</b><br>
					This will recount replies, attachments and last poster for all your topics. This may take a while to complete!
				</td>
				<td style="text-align:center"><a href="main.php?act=system&p=optimization&execute=replies"><i class="fa fa-play-circle-o"></i></a></td>
			</tr>
		</table>
	</form>
</div>
