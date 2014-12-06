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
 * @package     app.View.Elements
 * @license     http://opensource.org/licenses/MIT MIT
 */
?>

<table class="table table-bordered table-striped narrow-table table-hover">
<?php foreach ($answers as $answer): ?>
    <tr>
      <th>
        <?php if ($this->Permissions->isLoggedIn()) {
              if($answer['Question']['done']) {
                  echo "<i class='fa fa-check-square'></i> ";
              } else {
                  echo "<i class='fa fa-square'></i> ";
              }
          }?>
        <?php echo $this->Html->link($answer['Question']['title'],
                  array('controller' => 'questions', 'action' => 'view', 'title' => $this->Url->slug($answer['Question']['title']))); ?>
      </th>
      <?php echo $this->element('answerTableCell', array('answer' => $answer, 
                          'question' => $answer)); ?>
      <?php if ($this->Permissions->isLoggedIn()) { ?>
        <td>
            <?php echo $this->element('answerAdminToolbox', array('answer' => $answer, 'questionTitle' => $answer['Question']['title'])); ?> 
        </td>
      <?php } ?>
    </tr>
<?php endforeach; ?>
</table>

