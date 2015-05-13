<div id="fb-root"></div>
<script>(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<!-- FIRST POST -->

<div class="thread-first-post">
	<div class="header">
		<div class="author">
			<a href="#"><?php __(Html::Crop($first_post_info['avatar'], 42, 42, "avatar")) ?></a>
			<div class="author-info">
				<a href="profile/<?php __($first_post_info['author_id']) ?>"><?php __($first_post_info['username']) ?></a>
				<div><?php __($first_post_info['member_title']) ?></div>
				<div><?php __($first_post_info['posts']) ?> <?php __("T_POSTS_LOWCASE") ?></div>
			</div>
			<div class="fright" style="margin-top: 12px">
				<div class="fb-share-button" data-href="<?php echo Html::CurrentUrl() ?>" data-layout="button_count"></div>
			</div>
			<div class="fright" style="margin-top: 12px">
				<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
			</div>
		</div>
	</div>
	<div>
		<p class="title"><?php __($first_post_info['title']) ?></p>
		<div class="text">
			<span><?php __($first_post_info['post']) ?></span>
			<div class="attachments">
				<?php if($first_post_info['attach_id'] != 0): ?>
					<div class="file">
						<a href="<?php printf("public/attachments/%s/%s", $first_post_info['member_id'], $first_post_info['filename']) ?>" target="_blank" rel="nofollow">
							<span class="fileIcon <?php __($first_post_info['type']) ?>"></span>
							<div class="fileName"><span><?php __($first_post_info['filename']) ?></span><?php __(String::FileSizeFormat($first_post_info['size'])) ?></div>
						</a>
					</div>
				<?php endif; ?>
			</div>
			<?php if($first_post_info['signature']): ?>
				<div class="signature"><?php __($first_post_info['signature']) ?></div>
			<?php endif; ?>
		</div>
	</div>
	<div class="footer">
		<p class="fleft"><i class="fa fa-clock-o"></i> <?php __("T_POSTED_ON", array($first_post_info['post_date'])) ?></p>
		<?php if($this->Session->member_info['m_id'] != 0): ?>
			<p class="fright"><i class="fa fa-warning"></i> <a href="?module=report&amp;t_id=<?php __($thread_id) ?>" class="fancybox fancybox.ajax"><?php __("T_REPORT_ABUSE") ?></a></p>
		<?php endif; ?>
	</div>
</div>

<!-- NUMBER OF REPLIES / ADD REPLY BUTTON -->

<div class="thread-buttons">
	<p class="replies fleft"><?php __("T_REPLIES") ?>: <span><?php __($thread_info['post_count_display']) ?></span></p>
	<div class="fright">
		<?php if($this->Session->IsMember()): ?>
			<?php if($thread_info['obsolete']): ?>
				<a href="javascript:void()" class="default-button disabled"><?php __("T_BTN_OBSOLETE") ?></a>
			<?php elseif($thread_info['locked'] == 0 && $thread_info['allow_to_reply']): ?>
				<a href="thread/reply/<?php __($thread_id) ?>" class="default-button"><?php __("T_BTN_ADD") ?></a>
			<?php else: ?>
				<a href="javascript:void()" class="default-button disabled"><?php __("T_BTN_LOCKED") ?></a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<!-- REPLIES -->

<?php if(!empty($reply)): ?>
	<?php foreach($reply as $k => $v): ?>
		<div class="thread-post-reply <?php __($reply[$k]['bestanswer_class']) ?>">
			<div class="author">
				<div class="photostack">
					<a href="profile/<?php __($reply[$k]['author_id']) ?>">
						<?php __(Html::Crop($reply[$k]['avatar'], 96, 96, "avatar")) ?>
					</a>
				</div>
				<p class="name"><a href="profile/<?php __($reply[$k]['author_id']) ?>"><?php __($reply[$k]['username']) ?></a></p>
				<p class="member-title"><?php __($reply[$k]['member_title']) ?></p>
				<ul class="user-info">
					<li><b><?php __("T_POST_POSTS") ?></b> <?php __($reply[$k]['posts']) ?> <?php __("T_POSTS_LOWCASE") ?></li>
					<li><b><?php __("T_POST_REGISTERED") ?></b> <?php __($reply[$k]['joined']) ?></li>
					<li><b><?php __("T_POST_LOCATION") ?></b> <?php __($reply[$k]['location']) ?></li>
				</ul>
			</div>
			<div class="content">
				<div class="date"><?php __("T_POSTED_ON", array($reply[$k]['post_date'])) ?> <?php __($reply[$k]['edited']) ?></div>
				<div class="text">
					<span><?php __(html_entity_decode($reply[$k]['post'])) ?></span>
					<div class="attachments">
						<?php if($reply[$k]['attach_id'] != 0): ?>
							<span class="title">Attachment</span>
							<div class="file">
								<a href="<?php printf("public/attachments/%s/%s", $reply[$k]['member_id'], $reply[$k]['filename']) ?>" target="_blank" rel="nofollow">
									<span class="fileIcon <?php __($reply[$k]['type']) ?>"></span>
									<div class="fileName"><span><?php __($reply[$k]['filename']) ?></span><?php __(String::FileSizeFormat($reply[$k]['size'])) ?></div>
								</a>
							</div>
						<?php endif; ?>
					</div>
					<?php if($reply[$k]['signature']): ?>
						<div class="signature"><?php __($reply[$k]['signature']) ?></div>
					<?php endif; ?>
				</div>
				<div class="footer">
					<div class="fleft">
						<?php if($this->Session->IsMember()): ?>
							<a href="?module=report&amp;t_id=<?php __($thread_id) ?>&amp;p_id=<?php __($reply[$k]['p_id']) ?>" class="small-button grey fancybox fancybox.ajax"><?php __("T_REPORT_POST") ?></a>
						<?php endif; ?>
					</div>
					<div class="fright">
						<?php __($reply[$k]['post_controls']) ?>
						<?php __($reply[$k]['thread_controls']) ?>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
<?php else: ?>
	<div class="box center"><?php __("T_NO_REPLIES") ?></div>
<?php endif; ?>

<!-- PAGINATION -->

<div class="thread-buttons">
	<div class="fleft"><?php __($pagination) ?></div>
	<div class="fright">
		<?php if($this->Session->IsMember()): ?>
			<?php if($thread_info['obsolete']): ?>
				<a href="javascript:void()" class="default-button disabled"><?php __("T_BTN_OBSOLETE") ?></a>
			<?php elseif($thread_info['locked'] == 0 && $thread_info['allow_to_reply']): ?>
				<a href="thread/reply/<?php __($thread_id) ?>" class="default-button"><?php __("T_BTN_ADD") ?></a>
			<?php else: ?>
				<a href="javascript:void()" class="default-button disabled"><?php __("T_BTN_LOCKED") ?></a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<!-- RELATED THREADS -->

<div class="thread-related">
	<h2><?php __("T_RELATED") ?></h2>
	<?php if($related_thread_list): ?>
		<?php foreach($related_thread_list as $k => $v): ?>
			<div class="item">
				<span><?php __($related_thread_list[$k]['thread_date']) ?></span>
				<a href="thread/<?php echo $related_thread_list[$k]['t_id'] ?>"><?php __($related_thread_list[$k]['title']) ?></a>
			</div>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="item"><span><?php __("T_NO_RELATED") ?></span></div>
	<?php endif; ?>
</div>