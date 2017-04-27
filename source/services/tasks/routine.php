<?php
require_once 'lang_eng.php';
define('_TSK_TASK_STATUS_0', _LNG_TSK_IN_PROGRESS);
define('_TSK_TASK_STATUS_1', _LNG_TSK_DONE);
define('_TSK_TASK_STATUS_2', _LNG_TSK_COMPLETE);
stlGlobalSetVar('taskStatus', [_TSK_TASK_STATUS_0, _TSK_TASK_STATUS_1, _TSK_TASK_STATUS_2]);

function tskGetStatusTitle($status) {
    return (stlGlobal('taskStatus')[$status]);
}

function tskGetCountDependencies($id, $status) {
    $database = stlGlobal('database');
    $database->query(" 
            SELECT count(*) as count
            FROM 
                tasks
            WHERE 
                parent_id = ?
            AND 
                status = ?                  
    ");
    $database->bind(1, $id);
    $database->bind(2, $status);
    $row = $database->single();
    return $row['count'];
}
stlGlobalSetVar('countChildren', 0);
class TreeNode {

    protected $data;

    public function __construct(array $element) {
        if (!isset($element['title']))
            throw new InvalidArgumentException('Element has no title.');

        if (isset($element['children']) && !is_array($element['children']))
            throw new InvalidArgumentException('Element has invalid children.');

        $this->data = $element;
    }

    public function getTitle() {
        return $this->data['title'];
    }

    public function getStatus() {
        return ($this->data['status']);
    }

    public function getId() {
        return ($this->data['id']);
    }

    public function hasChildren() {
        return isset($this->data['children']) && count($this->data['children']);
    }

    /**
     * @return array of child TreeNode elements
     */
    public function getChildren() {
        $children = $this->hasChildren() ? $this->data['children'] : array();
        $class = get_called_class();
        foreach ($children as &$element)
        {
            $element = new $class($element);
        }
        unset($element);

        return $children;
    }
}

class TreeNodesIterator implements \RecursiveIterator {

    private $nodes;

    public function __construct(array $nodes) {
        $this->nodes = new \ArrayIterator($nodes);
    }

    public function getInnerIterator() {
        return $this->nodes;
    }

    public function getChildren() {
        return new TreeNodesIterator($this->nodes->current()->getChildren());
    }

    public function hasChildren() {
        return $this->nodes->current()->hasChildren();
    }

    public function rewind() {
        $this->nodes->rewind();
    }

    public function valid() {
        return $this->nodes->valid();
    }

    public function current() {
        return $this->nodes->current();
    }

    public function key() {
        return $this->nodes->key();
    }

    public function next() {
        return $this->nodes->next();
    }
}

class RecursiveListIterator extends \RecursiveIteratorIterator {

    private $elements;
    /**
     * @var ListDecorator
     */
    private $decorator;

    public function addDecorator(ListDecorator $decorator) {
        $this->decorator = $decorator;
    }

    public function __construct($iterator, $mode = \RecursiveIteratorIterator::SELF_FIRST, $flags = 0) {
        parent::__construct($iterator, $mode, $flags);
    }

    private function event($title) {
        $callback = array($this->decorator, $title);
        is_callable($callback) && call_user_func($callback);
    }

    public function beginElement() {
        $this->event('beginElement');
    }

    public function beginChildren() {
        $this->event('beginChildren');
    }

    public function endChildren() {
        $this->testEndElement();
        $this->event('endChildren');
    }

    private function testEndElement($depthOffset = 0) {
        $depth = $this->getDepth() + $depthOffset;
        isset($this->elements[$depth]) || $this->elements[$depth] = 0;
        $this->elements[$depth] && $this->event('endElement');

    }

    public function nextElement() {
        $this->testEndElement();
        $this->event('{nextElement}');
        $this->event('beginElement');
        $this->elements[$this->getDepth()] = 1;
    }

    public function beginIteration() {
        $this->event('beginIteration');
    }

    public function endIteration() {
        $this->event('endIteration');
    }
}

class ListDecorator {

    private $iterator;

    public function __construct(RecursiveListIterator $iterator) {
        $this->iterator = $iterator;
    }

    public function inset($add = 0) {
        return str_repeat('  ', $this->iterator->getDepth() * 2 + $add);
    }

    public function beginElement() {
        if($this->iterator->getDepth() != 0) {
            tr();
                td();
        }
    }

    public function endElement() {
        if($this->iterator->getDepth() != 0) {
            td_();
            tr_();
        }
    }

    public function beginChildren() {
        table('table table-bordered', 'center', 'style="width:90%"');
    }

    public function endChildren() {
        table_();
    }

    public function beginIteration() {
        td('','colspan="5"');
    }

    public function endIteration() {
        td_();
    }
}

/*
 * Display table of records
 * @rootRecordID
 * example http://localhost/task/index.php?function_call=tskDisplayTableRecords&id=1
 */
function tskDisplayTableRecords() {
    $rootRecordId = (int)stlParam('id');
    $database = stlGlobal('database');
    $database->query(' 
            SELECT c1.id as parent_id,
                   c1.title as parent_title,
                   c1.status as parent_status,
                   c2.id as child_id,
                   c2.title as child_title,
                   c2.status as child_status,
                   wd.web_directory_id as web_directory_id
            FROM 
                    tasks c1
            RIGHT JOIN 
                    tasks c2 ON (c2.parent_id = c1.id)
            INNER JOIN 
                    web_directories wd ON wd.task_id = c2.id
            WHERE 
                    c1.id = ? OR c2.id = ? OR wd.web_directory_id = ?
    ');
    $database->bind(1, $rootRecordId);
    $database->bind(2, $rootRecordId);
    $database->bind(3, $rootRecordId);
    $rows = $database->resultset();

    $flat = array();
    foreach ($rows as $row)
    {
        $flat[$row['child_title']]['title'] = $row['child_title'];
        $flat[$row['child_title']]['status'] = $row['child_status'];
        $flat[$row['child_title']]['id'] = $row['child_id'];
        if (null === $row['parent_title'])
        {
            $rows = &$flat[$row['child_title']];
        } else
        {
            $flat[$row['parent_title']]['children'][] = &$flat[$row['child_title']];
        }
    }
    unset($flat);
    $root = new TreeNode($rows);
    $it = new TreeNodesIterator(array($root));
    $rit = new RecursiveListIterator($it);
    $decor = new ListDecorator($rit);
    $rit->addDecorator($decor);

    ob_clean();
    ob_start();
    foreach ($rit as $item) {
        table('table table-bordered table-hover');
        thead();
            tr();
                th('text-center', 'width="4%"');
                    d('#');
                th_();
                th();
                    d( _LNG_TSK_TASK_INFO );
                th_();
                th('text-center', 'width="14%"');
                    d( _LNG_TSK_STATUS );
                th_();
                th('text-center', 'width="16%"');
                    d( _LNG_TSK_STATUS_CHECKBOX );
                th_();
                th('text-center', 'width="10%"');
                    d( _LNG_TSK_ACTIONS );
                th_();
            tr_();
        thead_();
        tbody();
            tr('' , 'id="tsk'.$item->getId().'"');
                td('text-center');
                    d($item->getId());
                td_();
                td();
                    d('<span>');
                        d('<span class="caption">'. _LNG_TSK_TITLE .':</span>');
                        d($item->getTitle());
                    d('</span>');
                    if($item->hasChildren()) {
                        br();
                        d('<span>');
                            d('<span class="caption">'. _LNG_TSK_IN_PROGRESS .':</span>');
                            d(tskGetCountDependencies($item->getId(), 0));
                        d('</span>');
                        d('<span>');
                            d('<span class="caption">'. _LNG_TSK_DONE .':</span>');
                            d(tskGetCountDependencies($item->getId(), 1));
                        d('</span>');
                        d('<span>');
                            d('<span class="caption">'. _LNG_TSK_COMPLETE .':</span>');
                            d(tskGetCountDependencies($item->getId(), 2));
                        d('</span>');
                    }
                td_();
                td('text-center', 'id="tskStatus'.$item->getId().'"');
                    d(tskGetStatusTitle($item->getStatus()));
                td_();
                td('text-center');
                    $checked = $item->getStatus() != 0 ? "checked='checked'" : "";
                    d("<input type='checkbox' $checked onclick='tskSetIsDone(".$item->getId().", 0, ".(int)$item->hasChildren().")' id='tskSetIsDone".$item->getId()."'/>");
                td_();
                td('text-center');
                    if($item->hasChildren())
                    {
                        a('javascript:void(0)', '', 'title="' . _LNG_TSK_DEPENDENCIES . '" onclick="tskToggleShowDependencies($(this))"');
                            img(stlThemeImage('tasks', 'dependencies.png'), 'taskDependenciesBtn', 'alt="' . _LNG_TSK_DEPENDENCIES . '" width="16" height="16"');
                        a_();
                    }
                    a(_SITE_URL . '/edit.php?id='. $item->getId(), '', 'title="'._LNG_TSK_EDIT.'"');
                        img(stlThemeImage('tasks', 'edit.png'), 'taskEditBtn', 'alt="'._LNG_TSK_EDIT.'" width="16" height="16"');
                    a_();
                td_();
            tr_();
        tbody_();
        table_();
    }
    $tableInfo = ob_get_clean();
    d($tableInfo);
    die();
}

/*
 * Check does a record has children
 * @id
 * return boolean
 */
function tskHasChildren($id) {
    $database = stlGlobal('database');
    $database->query(" 
            SELECT count(*) as count
            FROM 
                tasks
            WHERE 
                parent_id = ?                 
    ");
    $database->bind(1, $id);
    $row = $database->single();
    return $row['count'] > 0 ? true : false ;
}

/*
 * Get parent records
 * return array parents
 */
function tskGetParentRecords($filter = '') {
    $database = stlGlobal('database');
    $whereClause = ($filter != '') ? ' AND status = ?' : '';
    $database->query(" 
            SELECT
                *
            FROM 
                tasks
            WHERE 
                parent_id = 0
                $whereClause
    ");

    if($filter != '') {
        $database->bind(1, (int)$filter);
    }

    $rows = $database->resultset();
    return $rows;
}

/*
 * Get parent record
 * @id
 * return array parent
 */
function tskGetParentRecord($id) {
    $database = stlGlobal('database');
    $database->query(" 
            SELECT
                *
            FROM 
                tasks
            WHERE 
                parent_id = ?                
    ");
    $database->bind(1, $id);
    $row = $database->single();
    return $row;
}

/*
 *  Display table of parent records
 */
function tskDisplayTableParentRecords() {
    $records_per_page = stlGlobal('records_per_page');
    $starting_position = 0;
    if (stlParam('page_no')) {
        $starting_position = (stlParam('page_no') - 1) * $records_per_page;
    }
    $whereClause = (stlParam('filter') != '') ? ' AND status = ?' : '';
    $query = " 
        SELECT
            *
        FROM 
            tasks
        WHERE 
            parent_id = 0 
            $whereClause        
    ";
    $newQuery = " 
        SELECT
            *
        FROM 
            tasks
        WHERE 
            parent_id = 0 
            $whereClause
        LIMIT $starting_position, $records_per_page
    ";
    //
    $database = stlGlobal('database');
    $database->query($newQuery);
    if(stlParam('filter') != '') {
        $database->bind(1, (int) stlParam('filter'));
    }
    $rows = $database->resultset();

    table('table table-bordered table-hover');
        thead();
            include 'tasksListHeader.php';
            tr();
                th('text-center', 'width="4%"');
                    d('#');
                th_();
                th();
                    d( _LNG_TSK_TASK_INFO );
                th_();
                th('text-center', 'width="14%"');
                    d( _LNG_TSK_STATUS );
                th_();
                th('text-center', 'width="16%"');
                    d( _LNG_TSK_STATUS_CHECKBOX );
                th_();
                th('text-center', 'width="10%"');
                    d( _LNG_TSK_ACTIONS );
                th_();
            tr_();
        thead_();
        tbody();
        $counter = 0;
        foreach ($rows as $row) {
            $trClass = '';
            if($counter % 2 != 0) {
                $trClass = 'active';
            }
            tr($trClass, 'id="tsk'.$row['id'].'"');
                td('text-center');
                    d($row['id']);
                td_();
                td();
                    d('<span>');
                        d('<span class="caption">'. _LNG_TSK_TITLE .':</span>');
                        d($row['title']);
                    d('</span>');
                    if(tskHasChildren($row['id'])) {
                        br();
                        d('<span>');
                            d('<span class="caption">'. _LNG_TSK_IN_PROGRESS .':</span>');
                            d(tskGetCountDependencies($row['id'], 0));
                        d('</span>');
                        d('<span>');
                            d('<span class="caption">'. _LNG_TSK_DONE .':</span>');
                            d(tskGetCountDependencies($row['id'], 1));
                        d('</span>');
                        d('<span>');
                            d('<span class="caption">'. _LNG_TSK_COMPLETE .':</span>');
                            d(tskGetCountDependencies($row['id'], 2));
                        d('</span>');
                    }
                td_();
                td('text-center', 'id="tskStatus'.$row['id'].'"');
                    d(tskGetStatusTitle($row['status']));
                td_();
                td('text-center');
                    $checked = $row['status'] != 0 ? "checked='checked'" : "";
                    d("<input type='checkbox' $checked onclick='tskSetIsDone(".$row['id'].", 1, ".(int)tskHasChildren($row['id']).")' id='tskSetIsDone".$row['id']."'/>");
                td_();
                td('text-center');
                    if(tskHasChildren($row['id']))
                    {
                        a('javascript:void(0)', '', 'title="' . _LNG_TSK_DEPENDENCIES . '" onclick="tskShowDependencies('.$row['id'].');"');
                            img(stlThemeImage('tasks', 'dependencies.png'), 'taskDependenciesBtn', 'alt="' . _LNG_TSK_DEPENDENCIES . '" width="16" height="16"');
                        a_();
                    }
                    a(_SITE_URL. '/edit.php?id='. $row['id'], '', 'title="'._LNG_TSK_EDIT.'"');
                        img(stlThemeImage('tasks', 'edit.png'), 'taskEditBtn', 'alt="'._LNG_TSK_EDIT.'" width="16" height="16"');
                    a_();
                td_();
            tr_();
            $counter++;
        }
        if($counter == 0) {
            tr();
                td('text-center', 'colspan="5"');
                    d('<b>' . _LNG_TSK_NO_RESULT . '</b>');
                td_();
            tr_();
        }
        tbody_();
    table_();
    if($counter != 0) {
        $self = $_SERVER['PHP_SELF'];
    $database->query($query);
    if(stlParam('filter') != '') {
        $database->bind(1, stlParam('filter'));
    }
    $database->execute();

    $total_no_of_records = $database->rowCount();
    $filterList = stlParam('filter') != '' ? '&filter='. (int)stlParam('filter') : '';
    d('<nav aria-label="..." class="text-center"><ul class="pagination">');
    if ($total_no_of_records > 0) {
        $total_no_of_pages = ceil($total_no_of_records / $records_per_page);
        $current_page = 1;
        if (stlParam('page_no')) {
            $current_page = stlParam('page_no');
        }
        if ($current_page != 1) {
            $previous = $current_page - 1;
            d('<li>');
                /*a($self . '?page_no=1', ' aria-label="Previous"');
                    d('<span aria-hidden="true">First</span>');
                a_();*/
                a($self . '?page_no=' . $previous . $filterList, ' aria-label="Previous"');
                    d('<span aria-hidden="true">«</span>');
                a_();
            d('</li>');
        }
        for ($i = 1; $i <= $total_no_of_pages; $i ++) {
            if ($i == $current_page) {
                d('<li class="active">');
                    a($self . '?page_no=' . $i . $filterList);
                        d($i);
                        d('<span class="sr-only">current</span>');
                    a_();
                d('</li>');
            }
            else {
                d('<li>');
                    a($self . '?page_no=' . $i . $filterList);
                        d($i);
                    a_();
                d('</li>');
            }
        }
        if ($current_page != $total_no_of_pages) {
            $next = $current_page + 1;
            d('<li>');
            a($self . '?page_no=' . $next . $filterList, ' aria-label="Next"');
                d('<span aria-hidden="true">»</span>');
            a_();
            d('</li>');
            /*a($self . '?page_no=' . $total_no_of_pages, ' aria-label="Previous"');
                d('<span aria-hidden="true">Last</span>');
            a_();*/
        }
    }
    d('</ul></nav>');
    }

}

/*
 * Display Trees combo box
 * @wdId
 * @readOnly
 */
function tskDisplayWebDirectoryCombo($wdId = '', $readOnly = 0) {
    $rows = tskGetParentRecords();
    $setReadOnly = '';
    if($readOnly == 1) {
        $setReadOnly = 'readonly="readonly"';
    }
    d('<select name="record[task_web_directory_id]" id="task_web_directory_id" class="form-control" '.$setReadOnly.'>');
    if($wdId == '')
    {
        d('<option value="0">Create new tree</option>');
    }
    foreach ($rows as $row) {
        $selected = '';
        if($row['id'] == $wdId)
            $selected = 'selected';
        if($readOnly == 1) {
            if($row['id'] == $wdId) {
                d('<option value="' . $row['id'] . '" ' . $selected . '>' . $row['title'] . '</option>');
            }
        }
        else {
            d('<option value="' . $row['id'] . '" ' . $selected . '>' . $row['title'] . '</option>');
        }
    }
    d('</select>');
}

/*
 * Get count by status
 * @status
 */
function tskGetCountByStatus($status = '') {
    $database = stlGlobal('database');
    $whereClause = ($status != '') ? 'WHERE status = ?' : '';
    $database->query(" 
            SELECT count(*) as count
            FROM 
                tasks
            $whereClause                  
    ");
    if($status != '')
        $database->bind(1, $status);
    $row = $database->single();
    return $row['count'];
}

/*
 * Check task is complete
 * - Check all children, if all of them are complete parent should changes to complete
 * @id record id
 */
function tskCheckIsComplete($id = '') {
    $rootRecordId = ($id != '') ? $id : (int)stlParam('id');

    $database = new Database();
    $database->query('select checkParentIsComplete(?) as is_complete');
    $database->bind(1, $rootRecordId);
    $row = $database->single();
    if($id != '') {
        return $row['is_complete'];
    }
    else
    {
        ob_clean();
        ob_start();
        d($row['is_complete']);
        die();
    }
}

/*
 * Update task status
 * @id record id
 * @recStatus
 */
function tskUpdateRecordStatus($id = '', $recStatus = '') {
    $rootRecordId = ($id != '') ? $id : (int)stlParam('id');
    $rootRecordStatus = ($recStatus != '') ? $recStatus : (int)stlParam('status');

    $status = array(0, 1, 2);
    if(!in_array($rootRecordStatus, $status))
        return false;

    $database = stlGlobal('database');
    $database->query('UPDATE tasks SET status = :status WHERE id = :id');
	$database->bind(':status', $rootRecordStatus);
	$database->bind(':id', $rootRecordId);
	$database->execute();
	if($id == '' && $recStatus == '')
    {
        ob_clean();
        ob_start();
        d(1);
        die();
    }
}

/*
 * Get web directory id
 * @nodeId
 */
function tskGetWebDirectoryId($nodeId) {
    $database = stlGlobal('database');
    $database->query(" 
            SELECT web_directory_id as id
            FROM 
                web_directories
            WHERE 
                id = ?                              
    ");
    $database->bind(1, $nodeId);
    $row = $database->single();
    return $row['id'];
}

/*
 * Get task by id
 * @id
 */
function tskGetTaskById($id) {
    $database = stlGlobal('database');
    $database->query(" 
            SELECT
                *
            FROM 
                tasks
            WHERE 
                id = ?
    ");

    $database->bind(1, $id);
    $row = $database->single();
    return $row;
}

/*
 *  Get ancestry
 * @id record id
 */
function tskGetAncestry($id) {
    $database = new Database();
    $database->query('select getAncestry(?) as parent_nodes');
    $database->bind(1, $id);
    $row = $database->single();
    $parentNodes = explode(',', $row['parent_nodes']);
    return $parentNodes;
}

/*
 * Update task parents status
 * @id record id
 * @status
 */
function tskUpdateParentStatus($givenId = '', $givenStatus = '') {
    $recordId = ($givenId != '') ? $givenId : (int)stlParam('id');
    $recordStatus = ($givenStatus != '') ? $givenStatus : (int)stlParam('status');

    $status = array(0, 1, 2);
    if(!in_array($recordStatus, $status))
        return false;

    $webDirectoryId = tskGetWebDirectoryId($recordId);
    $database = new Database();
    $database->query('select GetRootNodes(?) as nodes');
    $database->bind(1, $webDirectoryId);
    $row = $database->single();

    $nodeIds = array_reverse(explode(',', $webDirectoryId . ',' . $row['nodes']));


    $parentRecords = tskGetAncestry($recordId);

    foreach ($nodeIds as $nodeId) {
        $isNodeComplete = tskCheckIsComplete($nodeId);

        if($isNodeComplete == 1 && $recordStatus == 2) {
            if(in_array($nodeId, $parentRecords)) {
                tskUpdateRecordStatus($nodeId, 2);
            }
        }
        else {
            if($recordStatus == 0 || $recordStatus == 1 ) {
                if(in_array($nodeId, $parentRecords))
                {
                    $record = tskGetTaskById($nodeId);
                    if ($record['status'] == 2)
                    {
                        tskUpdateRecordStatus($nodeId, 1);
                    }
                }
            }
        }
    }

    if($givenId == '' && $givenStatus == '')
    {
        ob_clean();
        ob_start();
        d(1);
        die();
    }
}

/*
 * Check for circular dependency
 * @id record id
 * @recStatus
 */
function tskCheckCircularDependency($id = '', $givenParentId = '') {
    $recordId = ($id != '') ? $id : (int)stlParam('id');
    $parentId = ($givenParentId != '') ? $givenParentId : (int)stlParam('parent_id');

    $database = new Database();
    $database->query('SELECT hasCircularDependency(?, ?) as hasCircularDependency');
    $database->bind(1, $recordId);
    $database->bind(2, $parentId);
    $row = $database->single();
    $rows = '';
    if($row['hasCircularDependency'] == 'yes') {
        $tskAncestryIds = tskGetAncestry($parentId);
        $tskAncestryIds = implode(',', $tskAncestryIds);
        $tskAncestryIds .= ',' . $parentId;

        $database = stlGlobal('database');
        $database->query(" 
                SELECT
                    *
                FROM 
                    tasks
                WHERE 
                    id IN($tskAncestryIds)                    
        ");
        $rows = $database->resultset();
    }

	if($id == '' && $givenParentId == '')
    {
        ob_clean();
        if($row['hasCircularDependency'] == 'yes')
        {
            $data = '';
            foreach ($rows as $row) {
                $data .= '<div class="alert alert-danger" role="alert">';
                $data .= 'Id: ' . $row['id']. ' - Title: ' . $row['title']. ' - Parent_id: '. $row['parent_id'] .'</div>';
            }
            d($data);
        }
        else {
            d(1);
        }
        die();
    }
    else {
        return $row['hasCircularDependency'] == 'yes' ? 1 : 0;
    }
}


/*
 * Check web directory
 * @parent id
 * @web directory id
 */
function tskCheckWebDirectory($givenParentId = '', $givenWdId = '') {
    $recordId = ($givenParentId != '') ? $givenParentId : (int)stlParam('parentId');
    $wdId = ($givenWdId != '') ? $givenWdId : (int)stlParam('wdId');

    if($givenParentId == '' && $givenWdId == '')
    {
        ob_clean();
        if (tskGetWebDirectoryId($recordId) == $wdId)
            d(1);
        else
            d(0);
        die();
    }
    else {
        if (tskGetWebDirectoryId($recordId) == $wdId)
            return 1;
        else
            return 0;
    }
}
