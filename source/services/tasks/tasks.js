
function tskShowDependencies(id) {
    $.ajax({
       type: 'GET',
       url: _SITE_URL + '/index.php',
       cache: false,
       data: {
          function_call: 'tskDisplayTableRecords',
          id: id
       },
       dataType: 'html',
       success: function(data) {
           $('#tsk'+id).html(data);
       }
    });
}

function tskSetIsDone(id, root, hasChildren) {
    $.ajax({
       type: 'GET',
       url: _SITE_URL + '/index.php',
       cache: false,
       data: {
          function_call: 'tskCheckIsComplete',
          id: id
       },
       dataType: 'text',
       success: function(data) {
          var status = 0;
          if(data == 1) {
              if($('#tskSetIsDone' + id).is(':checked')) {
                  tskUpdateRecordStatus(id, 2);
                  status = 2;
              }
              else {
                  if(hasChildren == 0) {
                      tskUpdateRecordStatus (id, 0);
                      status = 0;
                  }
                  else {
                      tskUpdateRecordStatus (id, 1);
                      status = 1;
                  }
              }
          }
          else {
              if($('#tskSetIsDone' + id).is(':checked')) {
                  tskUpdateRecordStatus(id, 1);
                  status = 1;
              }
              else{
                  if(hasChildren == 0) {
                      tskUpdateRecordStatus (id, 0);
                      status = 0;
                  }
              }
          }
          if (root == 0)
            tskUpdateParentStatus(id, status);
          window.location.replace (_SITE_URL);
       }
    });
}

function tskUpdateParentStatus(id, status) {
    $.ajax({
       type: 'GET',
       url: _SITE_URL + '/index.php',
       cache: false,
       data: {
          function_call: 'tskUpdateParentStatus',
          id: id,
          status: status
       }
    });
}

function tskUpdateRecordStatus(id, status) {
    $.ajax({
       type: 'GET',
       url: _SITE_URL + '/index.php',
       cache: false,
       data: {
          function_call: 'tskUpdateRecordStatus',
          id: id,
          status: status
       }
    });
}

function tskCheckCircularDependency(id, parentId) {
    var isValid = true;
     $('#showCircularDependency').html('').addClass('hide');
     $('#editBtn').removeAttr('disabled');
     $.ajax({
       type: 'GET',
       url: _SITE_URL + '/index.php',
       cache: false,
       data: {
          function_call: 'tskCheckCircularDependency',
          id: id,
          parent_id: parentId
       },
       success: function (data) {
            if(data == 1) {
                $('#showCircularDependency').html('').addClass('hide');
                isValid = false;
                $('#editBtn').removeAttr('disabled');
            }
            else {
                $('#showCircularDependency').html(data).removeClass('hide');
                isValid = true;
                $('#editBtn').attr('disabled', 'disabled');
            }
       }
    });
    return isValid;
}

function tskCheckWebDirectory(givenParentId, wdId) {
    var isValid = true;
    $('#wrongWdId').addClass('hide');
    $('#editBtn').removeAttr('disabled');
    $.ajax({
        type: 'GET',
        url: _SITE_URL + '/index.php',
        cache: false,
        data: {
          function_call: 'tskCheckWebDirectory',
          parentId: givenParentId,
          wdId: wdId
        },
        success: function (data) {
            if(data == 1) {
                isValid = false;
                $('#wrongWdId').addClass('hide');
                $('#editBtn').removeAttr('disabled');
            }
            else {
                $('#wrongWdId').removeClass('hide');
                isValid = true;
                $('#editBtn').attr('disabled', 'disabled');
            }
        }
    });
    return isValid;
}


function tskToggleShowDependencies(table) {
    $(table).closest('table').next('table').toggle();
}

function tskCheckNotNull(elem, message) {
    if(elem.value == '') {
        alert (message);
        return true;
    }
    return false;
}

function tskCheckForm(formObj) {
    var isValid = true;
        if(tskCheckNotNull(formObj.elements['record[title]'], $('#task_title_msg').text() )) {
            isValid = false;
        }
    return isValid;
}