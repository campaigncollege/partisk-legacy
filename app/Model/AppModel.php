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
 * @package     app.Model
 * @license     http://opensource.org/licenses/MIT MIT
 */

App::uses('Model', 'Model');

class AppModel extends Model {
    public $actsAs = array('Containable');
	var $inserted_ids = array();
	
    function afterSave($created, $options = array()) {
        if($created) {
            $this->inserted_ids[] = $this->getInsertID();
        }
        clearCache('*');
        return true;
    }
    
    function afterDelete() {
        clearCache('*');
        return true;
    }

    public function getIdsFromModel($model, $parties, $idField = "id") {
        $partyIds = array();

        foreach ($parties as $party) {
            array_push($partyIds, $party[$model][$idField]);
        }

        return $partyIds;
    }
    
    public function getControllerCacheName($controller) {
        $explodedRoute = explode("/", Router::url(array('controller' => $controller, 'action' => 'index')));
        $name = rawurlencode($explodedRoute[2]);
        return strtolower(str_replace("%", "_", $name));
    }
}
