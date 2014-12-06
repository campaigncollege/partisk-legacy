<?php
/**
 * Copyright 2013-2014 Partisk.nu Team
 * https://www.partisk.nu/
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 * @copyright   Copyright 2013-2014 Partisk.nu Team
 * @link        https://www.partisk.nu
 * @package     app.View.Users
 * @license     http://opensource.org/licenses/MIT MIT
 */

$this->Html->addCrumb('Användare');

?>
<div class="row">
    <div class="col-md-6">
<?php if ($this->Permissions->isLoggedIn()) { ?>
<div class="tools">
<?php echo $this->element('saveUser'); ?>
</div>
<?php } ?>

<table class="table table-bordered table-hover table-striped">
<?php foreach ($users as $user): ?>
    <tr>
        <th>
            <?php echo $this->Html->link($user['User']['username'],
                                         array('controller' => 'users', 'action' => 'view', 'name' => $user['User']['username'])); ?> 
        </th>
        <?php if ($this->Permissions->isLoggedIn()) { ?>
        <td>
            <?php echo $this->element('userAdminToolbox', array('user' => $user)); ?>
        </td>
        <?php } ?>
    </tr>
    <?php endforeach; ?>
</table>
    </div>
</div>
