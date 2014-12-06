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
 * @package     app.View.Elements.bootstrap
 * @license     http://opensource.org/licenses/MIT MIT
 */

   	
        $sameMode = isset($validationErrors) && $validationErrors['mode'] == $mode;
   	$error = $sameMode && isset($validationErrors[$model][$field]) ? $validationErrors[$model][$field][0] : null;
        $postData = isset($formData) && $sameMode && isset($formData[$model][$field]) ? $formData[$model][$field] : null;
        
        $selected = isset($selected) ? $selected : $postData;
        
?>

<div class="input select form-group">
	<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
	<select name="<?php echo "data[$model][$field]"; ?>" id="<?php echo $id; ?>" class="form-control">
	<?php  foreach ($options as $option) { 
		$isSelected = isset($selected) && $selected == $option[$modelField][$idField]; 
                $isDeleted = isset($option[$modelField]['deleted']) && $option[$modelField]['deleted'];
                $hasMultipleAnswers = isset($option[0]['multiple_answers']) && $option[0]['multiple_answers'] < 1;
                $isNotApproved = isset($option[$modelField]['approved']) && !$option[$modelField]['approved'];
                $classString = ($isDeleted ? 'dropdown-deleted ' : '') . ($hasMultipleAnswers ? 'dropdown-few-answers ' : '') . ($isNotApproved ? 'dropdown-not-approved' : '');

                echo $idField; ?>
	    <option value="<?php echo $option[$modelField][$idField]; ?>" class="<?php echo $classString; ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                <?php if ($hasMultipleAnswers) { echo "("; }
                    echo ucfirst($option[$modelField][$titleField]); 
                    if ($hasMultipleAnswers) { echo ")"; }
                    ?>
	    </option>
	<?php } ?>
	</select>
</div>