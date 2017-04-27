<?php
if(stlParam('function_call')) {
    if(stlParam('function_call') == 'tskDisplayTableRecords') {
        tskDisplayTableRecords();
    }
    else if(stlParam('function_call') == 'tskCheckIsComplete') {
        tskCheckIsComplete();
    }
    else if(stlParam('function_call') == 'tskUpdateRecordStatus') {
        tskUpdateRecordStatus();
    }
    else if(stlParam('function_call') == 'tskUpdateParentStatus') {
        tskUpdateParentStatus();
    }
    else if(stlParam('function_call') == 'tskCheckCircularDependency') {
        tskCheckCircularDependency();
    }
    else if(stlParam('function_call') == 'tskCheckWebDirectory') {
        tskCheckWebDirectory();
    }
}

tskDisplayTableParentRecords();
