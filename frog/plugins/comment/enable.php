<?php

$PDO = Record::getConnection();
$driver = strtolower($PDO->getAttribute(Record::ATTR_DRIVER_NAME));

// Table structure for table: comment ----------------------------------------

if ($driver == 'mysql')
{
	$PDO->exec("CREATE TABLE ".TABLE_PREFIX."comment (
	  id int(11) unsigned NOT NULL auto_increment,
	  page_id int(11) unsigned NOT NULL default '0',
	  body text,
	  author_name varchar(50) default NULL,
	  author_email varchar(100) default NULL,
	  author_link varchar(100) default NULL,
	  is_approved tinyint(1) unsigned NOT NULL default '1',
	  created_on datetime default NULL,
	  PRIMARY KEY  (id),
	  KEY page_id (page_id),
	  KEY created_on (created_on)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
	
	$PDO->exec("ALTER TABLE ".TABLE_PREFIX."page ADD comment_status tinyint(1) NOT NULL default '0' AFTER status_id");
}
else if ($driver == 'sqlite')
{
	$PDO->exec("CREATE TABLE comment (
	    id INTEGER NOT NULL PRIMARY KEY,
	    page_id int(11) NOT NULL default '0',
	    body text ,
	    author_name varchar(50) default NULL ,
	    author_email varchar(100) default NULL ,
	    author_link varchar(100) default NULL , 
	    is_approved tinyint(1) NOT NULL default '1' , 
	    created_on datetime default NULL 
	)");
	$PDO->exec("CREATE INDEX comment_page_id ON comment (page_id)");
	$PDO->exec("CREATE INDEX comment_created_on ON comment (created_on)");
	
	$PDO->exec("ALTER TABLE page ADD comment_status tinyint(1) NOT NULL default '0' AFTER status_id");
}

// Insert 2 little Snippet like comment exemple form and display

$PDO->exec("INSERT INTO ".TABLE_PREFIX."snippet (id, name, filter_id, content, content_html, created_on, updated_on, created_by_id, updated_by_id) VALUES (3, 'comment-form', '', '<form action=\"<?php echo \$this->url(); ?>\" method=\"post\" id=\"comment_form\"> \r\n<p>\r\n	<input class=\"comment-form-name\" type=\"text\" name=\"comment[author_name]\" id=\"comment_form_name\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_name\"> name (require)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-email\" type=\"text\" name=\"comment[author_email]\" id=\"comment_form_email\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_email\"> email (will not be published) (required)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-link\" type=\"text\" name=\"comment[author_link]\" id=\"comment_form_link\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_link\"> website</label>\r\n</p>\r\n<p>\r\n	<textarea class=\"comment-form-body\" id=\"comment_form_body\" name=\"comment[body]\" cols=\"100%\" rows=\"10\"></textarea>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-submit\" type=\"submit\" name=\"commit-comment\" id=\"comment_form_submit\" value=\"Submit comment\" />\r\n</p>\r\n</form>', '<form action=\"<?php echo \$this->url(); ?>\" method=\"post\" id=\"comment_form\"> \r\n<p>\r\n	<input class=\"comment-form-name\" type=\"text\" name=\"comment[author_name]\" id=\"comment_form_name\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_name\"> name (require)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-email\" type=\"text\" name=\"comment[author_email]\" id=\"comment_form_email\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_email\"> email (will not be published) (required)</label>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-link\" type=\"text\" name=\"comment[author_link]\" id=\"comment_form_link\" value=\"\" size=\"22\" /> \r\n	<label for=\"comment_form_link\"> website</label>\r\n</p>\r\n<p>\r\n	<textarea class=\"comment-form-body\" id=\"comment_form_body\" name=\"comment[body]\" cols=\"100%\" rows=\"10\"></textarea>\r\n</p>\r\n<p>\r\n	<input class=\"comment-form-submit\" type=\"submit\" name=\"commit-comment\" id=\"comment_form_submit\" value=\"Submit comment\" />\r\n</p>\r\n</form>', '".frog_datetime_incrementor()."', NULL, 1, NULL)");
$PDO->exec("INSERT INTO ".TABLE_PREFIX."snippet (id, name, filter_id, content, content_html, created_on, updated_on, created_by_id, updated_by_id) VALUES (4, 'comment-each', '', '<p><strong><?php echo \$num_comments = comments_count(\$this); ?></strong> comment<?php if (\$num_comments > 1) { echo ''s''; } ?></p>\r\n<?php \$comments = comments(\$this); ?>\r\n<ol class=\"comments\">\r\n<?php foreach (\$comments as \$comment): ?>\r\n  <li class=\"comment\">\r\n    <p><?php echo \$comment->body(); ?></p>\r\n    <p> &#8212; <?php echo \$comment->name(); ?> <small class=\"comment-date\"><?php echo \$comment->date(); ?></small></p>\r\n  </li>\r\n<?php endforeach; // comments; ?>\r\n</ol>', '<p><strong><?php echo \$num_comments = comments_count(\$this); ?></strong> comment<?php if (\$num_comments > 1) { echo ''s''; } ?></p>\r\n<?php \$comments = comments(\$this); ?>\r\n<ol class=\"comments\">\r\n<?php foreach (\$comments as \$comment): ?>\r\n  <li class=\"comment\">\r\n    <p><?php echo \$comment->body(); ?></p>\r\n    <p> — <?php echo \$comment->name(); ?> <small class=\"comment-date\"><?php echo \$comment->date(); ?></small></p>\r\n  </li>\r\n<?php endforeach; // comments; ?>\r\n</ol>', '".frog_datetime_incrementor()."', NULL, 1, NULL)");
