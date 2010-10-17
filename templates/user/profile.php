<h1>Profile</h1>
<h2><?php echo $vars['user']->get('title'); ?></h2>

<form method="post" action="">
    <input type="hidden" name="form_action" value="update_profile">
<table cellspacing="1" cellpadding="5" border="0">
    <tr>
        <td>Id</td>
        <td><?php echo $vars['user']->get('_id'); ?></td>
    </tr>
    <tr>
        <td>Email</td>
        <td><?php echo $vars['user']->get('email'); ?></td>
    </tr>
    <tr>
        <td>Pass Hash</td>
        <td><?php echo $vars['user']->get('passhash'); ?></td>
    </tr>
    <tr>
        <td>Status</td>
        <td><input type="text" name="status" value="<?php echo $vars['user']->get('status'); ?>"></td>
    </tr>
    <tr>
        <td>Last Login</td>
        <td><?php echo date('Y-M-d h:i:s', $vars['user']->get('last_login')->sec); ?></td>
    </tr>
    <tr>
        <td>Last Access</td>
        <td><?php echo date('Y-M-d h:i:s', $vars['user']->get('last_access')->sec); ?></td>
    </tr>
    <tr>
        <td>Privacy</td>
        <td><input type="text" name="privacy" value="<?php echo $vars['user']->get('privacy'); ?>"></td>
    </tr>
    <tr>
        <td>Avatar</td>
        <td><input type="text" name="avatar" value="<?php echo $vars['user']->get('avatar'); ?>"></td>
    </tr>
    <tr>
        <td>Uploaded</td>
        <td><?php echo $vars['user']->get('uploaded'); ?></td>
    </tr>
    <tr>
        <td>Downloaded</td>
        <td><?php echo $vars['user']->get('downloaded'); ?></td>
    </tr>
    <tr>
        <td>Title</td>
        <td><input type="text" name="title" value="<?php echo $vars['user']->get('title'); ?>"></td>
    </tr>
    <tr>
        <td>Torrents Per Page</td>
        <td><input type="text" name="torrents_per_page" value="<?php echo $vars['user']->get('torrents_per_page'); ?>"></td>
    </tr>
    <tr>
        <td colspan="2"><input type="submit" value="Edit"></td>
    </tr>
</table>
</form>