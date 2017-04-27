<?php
if(stlParam('id')) {
    $database = stlGlobal('database');
    $database->query(" 
            SELECT
                *
            FROM 
                tasks
            INNER JOIN
                web_directories wd
            ON 
               wd.task_id = tasks.id
            WHERE 
                tasks.id = ?                
    ");
    $database->bind(1, (int)stlParam('id'));
    $row = $database->single();
}

if ( stlRecord('tasksForm') && stlRecord('tasksForm') == 'submitted' ) {
    $record = stlRecord('record');

    if($record['title'] == '')
        $errors = true;

    $database->query(" 
            SELECT
                *
            FROM 
                tasks
            WHERE 
                id = ?                
    ");
    $database->bind(1, (int)stlParam('id'));
    $row = $database->single();

    if(!isset($errors) && $row['id'] != 0) {

        $parentId = $record['parent_id'];
        if($parentId == '' && $record['task_web_directory_id'] != 0)
            $parentId = $record['task_web_directory_id'];

        $tskHasCircularDependency = tskCheckCircularDependency($row['id'], $parentId);
        if($tskHasCircularDependency == 0 && false && tskCheckWebDirectory($parentId, tskGetWebDirectoryId($row['id'])) == 1) {
            $database = new Database();
            $database->query('
            UPDATE 
                tasks
            SET                
               title = ?, parent_id = ?
            WHERE
              id = '.$row['id'].'
            ');
            $database->bind(1, $record['title']);
            $database->bind(2, $parentId);
            $database->execute();

            tskUpdateParentStatus($row['id'], $row['status']);
            header('location:' . _SITE_URL . '/edit.php?id='. $row['id'] . '&success=1');
            exit();
        }
        header('location:' . _SITE_URL . '/edit.php?id='. $row['id']);
        exit();
    }
}
?>
<?php if(isset($errors)) { ?>
    <div class="alert alert-danger" role="alert"><?php d(_LNG_TSK_TITLE_NOT_NULL_MSG)?>.</div>
<?php } ?>
<?php if(stlParam('success')) { ?>
    <div class="alert alert-success" role="alert"><?php d(_LNG_TSK_EDIT_SUCCESS_MSG)?>.</div>
<?php } ?>
<div id="showCircularDependency" class="hide">
    <div class="alert alert-danger" role="alert">Circular Dependency:</div>
</div>
<div class="form-group alert alert-danger hide" id="wrongWdId" role="alert">Destination node is in another tree!</div>
<form name="tasks" id="form" method="POST" onsubmit="return tskCheckForm(this);">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th colspan="2">Task info</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="caption" width="14%">
                <label for="task_title">Title:</label>
            </td>
            <td class="data">
                <div class="form-group col-lg-9">
                    <input name="record[title]" id="task_title" type="text" class="form-control" value="<?php d(isset($row['title']) ? $row['title'] : '' ) ?>">
                </div>
                <span class="error">*</span>
                <span id="task_title_msg" class="hide"><?php d(_LNG_TSK_TITLE_NOT_NULL_MSG)?></span>
            </td>
        </tr>
        <tr>
            <td class="caption" width="14%">
                <label for="task_parent_id">Parent id:</label>
            </td>
            <td class="data">
                <div class="form-group col-lg-4">
                    <input name="record[parent_id]" id="task_parent_id" onchange="tskCheckCircularDependency($('#task_record_id').val(), $('#task_parent_id').val());tskCheckWebDirectory($('#task_parent_id').val(), $('#task_web_directory_id').val());" type="text" class="form-control" value="<?php d(isset($row['parent_id']) ? $row['parent_id'] : '' ) ?>">
                </div>
            </td>
        </tr>
        <tr>
            <td class="caption" width="14%">
                <label for="task_web_directory_id">Web directory:</label>
            </td>
            <td class="data">
                <div class="form-group col-lg-5">
                    <?php tskDisplayWebDirectoryCombo($row['web_directory_id'], 1); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="hidden" name="record[record_id]" id="task_record_id" value="<?php d(isset($row['id']) ? $row['id'] : '' ) ?>" >
                <input type="hidden" name="tasksForm" value="submitted" >
                <input type="submit" name="submit" id="editBtn" value="Save" class="btn btn-default btn-primary">
            </td>
        </tr>
        </tbody>
    </table>
</form>