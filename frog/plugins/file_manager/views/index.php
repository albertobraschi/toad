<?php
  $out = '';
  $progres_path = '';
  $paths = explode('/', $dir); 
  $nb_path = count($paths)-1; // -1 to didn't display current dir as a link
  foreach ($paths as $i => $path) {
    if ($i+1 == $nb_path) {
      $out .= $path;
    } else if ($path != '') {
      $progres_path .= $path.'/';
      $out .= '<a href="'.get_url('plugin/file_manager/browse/'.rtrim($progres_path, '/')).'">'.$path.'</a>/';
    }
  }
?>
<h1><a href="<?php echo get_url('plugin/file_manager'); ?>">public</a>/<?php echo $out; ?></h1>
<table id="files-list" class="index" cellpadding="0" cellspacing="0" border="0">
  <thead>
    <tr>
      <th class="files"><?php echo __('File'); ?></th>
      <th class="size"><?php echo __('Size'); ?></th>
      <th class="permissions"><?php echo __('Permissions'); ?></th>
      <th class="mtime"><?php echo __('Modify'); ?></th>
      <th class="modify"><?php echo __('Action'); ?></th>
    </tr>
  </thead>
  <tbody>
<?php foreach ($files as $file): ?>
    <tr class="<?php echo odd_even(); ?>">
      <td><?php echo $file->link; ?></td>
      <td><code><?php echo $file->size; ?></code></td>
      <td><code><?php echo $file->perms; ?> (<a href="#" onclick="toggle_chmod_popup('<?php echo $dir.$file->name; ?>'); return false;" title="<?php echo __('Change mode'); ?>"><?php echo $file->chmod; ?></a>)</code></td>
      <td><code><?php echo $file->mtime; ?></code></td>
      <td>
        <a href="#" onclick="toggle_rename_popup('<?php echo $dir.$file->name; ?>', '<?php echo $file->name; ?>'); return false;" title="<?php echo __('Rename'); ?>"><img src="images/icon-rename.gif" alt="rename icon" /></a> 
        <a href="<?php echo get_url('plugin/file_manager/delete/'.$dir.$file->name); ?>" onclick="return confirm('<?php echo __('Are you sure you wish to delete'); ?> <?php echo $file->name; ?>?');"><img src="images/icon-remove.gif" alt="remove icon" /></a>
      </td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>

<div id="popups">
  <div class="popup" id="create-file-popup" style="display:none;">
    <h3><?php echo __('Create new file'); ?></h3>
    <form action="<?php echo get_url('plugin/file_manager/create_file'); ?>" method="post"> 
      <div>
        <input id="create_file_path" name="file[path]" type="hidden" value="<?php echo ($dir == '') ? '/': $dir; ?>" />
        <input id="create_file_name" maxlength="255" name="file[name]" type="text" value="" /> 
        <input id="create_file_button" name="commit" type="submit" value="<?php echo __('Create'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="Element.hide('create-file-popup'); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>
  <div class="popup" id="create-directory-popup" style="display:none;">
    <h3><?php echo __('Create new directory'); ?></h3>
    <form action="<?php echo get_url('plugin/file_manager/create_directory'); ?>" method="post">
      <div>
        <input id="create_directory_path" name="directory[path]" type="hidden" value="<?php echo ($dir == '') ? '/': $dir; ?>" />
        <input id="create_directory_name" maxlength="255" name="directory[name]" type="text" value="" /> 
        <input id="file_button" name="commit" type="submit" value="<?php echo __('Create'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="Element.hide('create-directory-popup'); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>
  <div class="popup" id="upload-file-popup" style="display:none;">
    <form action="<?php echo get_url('plugin/file_manager/upload'); ?>" method="post" enctype="multipart/form-data"> 
      <h3><?php echo __('Upload file'); ?> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input id="upload_overwrite" name="upload[overwrite]" type="checkbox" value="1" /><label for="upload_overwrite"><small><?php echo __('overwrite it?'); ?></small></label></h3>
      <div>
        <input id="upload_path" name="upload[path]" type="hidden" value="<?php echo ($dir == '') ? '/': $dir; ?>" />
        <input id="upload_file" name="upload_file" type="file" />
        <input id="upload_file_button" name="commit" type="submit" value="<?php echo __('Upload'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="Element.hide('upload-file-popup'); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>
  <div class="popup" id="chmod-popup" style="display:none;">
    <h3><?php echo __('Change mode'); ?></h3>
    <form action="<?php echo get_url('plugin/file_manager/chmod'); ?>" method="post"> 
      <div>
        <input id="chmod_file_name" name="file[name]" type="hidden" value="" />
        <input id="chmod_file_mode" maxlength="4" name="file[mode]" type="text" value="" /> 
        <input id="chmod_file_button" name="commit" type="submit" value="<?php echo __('Change mode'); ?>" />
      </div>
      <p><a class="close-link" href="#" onclick="Element.hide('chmod-popup'); return false;"><?php echo __('Close'); ?></a></p>
    </form>
  </div>
  <div class="popup" id="rename-popup" style="display:none;">
      <h3><?php echo __('Rename'); ?></h3>
      <form action="<?php echo get_url('plugin/file_manager/rename'); ?>" method="post"> 
        <div>
          <input id="rename_file_current_name" name="file[current_name]" type="hidden" value="" />
          <input id="rename_file_new_name" maxlength="50" name="file[new_name]" type="text" value="" /> 
          <input id="rename_file_button" name="commit" type="submit" value="<?php echo __('Rename'); ?>" />
        </div>
        <p><a class="close-link" href="#" onclick="Element.hide('rename-popup'); return false;"><?php echo __('Close'); ?></a></p>
      </form>
    </div>
</div>