<?php
if ( stlRecord('tasksForm') && stlRecord('tasksForm') == 'submitted' ) {
    $record = stlRecord('record');
    if($record['title'] == '')
        $errors = true;

    if(!isset($errors)) {
        $parentId = $record['parent_id'];
        if($parentId == '' && $record['task_web_directory_id'] != 0)
            $parentId = $record['task_web_directory_id'];

        $database = new Database();
        $database->query('
            INSERT INTO 
                tasks 
            VALUES
                (NULL, ?, 0, ?)
        ');
        $database->bind(1, $record['title']);
        $database->bind(2, $parentId);
        $database->execute();
        $lastInsertId = $database->lastInsertId();

        if($lastInsertId != 0) {
            $database = new Database();
            $database->query('
            INSERT INTO 
                web_directories 
            VALUES
                (NULL, ?, ?)
            ');
            $database->bind(1, $lastInsertId);
            $webDirectoryId = $record['task_web_directory_id'];
            if($webDirectoryId == '0' && $record['parent_id'] == '') {
                $webDirectoryId = $lastInsertId;
            }
            $database->bind(2, $webDirectoryId);
            $database->execute();
            header('location:' . _SITE_URL);
            exit();
        }
        header('location:' . _SITE_URL . '/insert.php');
        exit();
    }
}
?>
<?php if(isset($errors)) { ?>
    <div class="alert alert-danger" role="alert"><?php d(_LNG_TSK_TITLE_NOT_NULL_MSG)?>.</div>
<?php } ?>
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
                    <input name="record[title]" id="task_title" type="text" class="form-control">
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
                    <input name="record[parent_id]" id="task_parent_id" type="text" class="form-control">
                </div>
            </td>
        </tr>
        <tr>
            <td class="caption" width="14%">
                <label for="task_web_directory_id">Web directory:</label>
            </td>
            <td class="data">
                <div class="form-group col-lg-5">
                    <?php tskDisplayWebDirectoryCombo(); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="hidden" name="tasksForm" value="submitted" >
                <input type="submit" name="submit" value="Save" class="btn btn-default btn-primary">
            </td>
        </tr>
        </tbody>
    </table>
</form>