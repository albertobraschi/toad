<h1><?php echo __('Pages'); ?></h1>

<div id="site-map-def">
    <div class="page"><?php echo __('Page'); ?> (<a href="#" id="toggle_reorder" onclick="toggle_reorder = !toggle_reorder; toggle_copy = false; $$('.handle_reorder').each(function(e) { e.style.display = toggle_reorder ? 'inline': 'none'; }); $$('.handle_copy').each(function(e) { e.style.display = toggle_copy ? 'inline': 'none'; }); return false;"><?php echo __('reorder'); ?></a> or <a id="toggle_copy" href="#" onclick="toggle_copy = !toggle_copy; toggle_reorder = false; $$('.handle_copy').each(function(e) { e.style.display = toggle_copy ? 'inline': 'none'; }); $$('.handle_reorder').each(function(e) { e.style.display = toggle_reorder ? 'inline': 'none'; }); return false;"><?php echo __('copy'); ?></a>)</div>
    <div class="status"><?php echo __('Status'); ?></div>
    <div class="modify"><?php echo __('Modify'); ?></div>
</div>

<ul id="site-map-root">
    <li id="page-0" class="node level-0">
      <div class="page" style="padding-left: 4px">
        <span class="w1">
<?php if ($root->is_protected && ! AuthUser::hasPermission('administrator') && ! AuthUser::hasPermission('developer')): ?>
          <img align="middle" class="icon" src="images/page.png" alt="page icon" /> <span class="title"><?php echo $root->title; ?></span>
<?php else: ?>
          <a href="<?php echo get_url('page/edit/1'); ?>" title="/"><img align="middle" class="icon" src="images/page.png" alt="page icon" /> <span class="title"><?php echo $root->title; ?></span></a>
<?php endif; ?>
        </span>
      </div>
      <div class="status published-status"><?php echo __('Published'); ?></div>
      <div class="modify">
          <a href="<?php echo get_url('page/add/1'); ?>"><img src="images/plus.png" align="middle" alt="<?php echo __('Add child'); ?>" /></a>&nbsp; 
          <img class="remove" src="images/icon-remove-disabled.gif" align="middle" alt="remove icon disabled" />
      </div>
    </li>
</ul>

<?php echo $content_children; ?>

