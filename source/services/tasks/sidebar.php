<div class="defaultBlock well">
    <div>
        <img src="theme/images/user.png" alt="" class="img-thumbnail center-block" width="128"
             height="128">
        <h5 class="text-center">User - Sheidaei</h5>
        <h4 class="text-center">+ Web Developer</h4>
    </div>
    <ul class="list-unstyled grayBlock">
        <li>
            <?php
            a(_SITE_URL);
            img('upload/content/tasks/all-tasks.png', '','alt="'._LNG_TSK_TASK_ITEMS.'" width="16" height="16"');
            if(stlParam('filter') == '') {
                d('<b>');
            }
            d(_LNG_TSK_TASK_ITEMS . ' (' . tskGetCountByStatus() . ')');
            if(stlParam('filter') == '') {
                d('</b>');
            }
            a_();
            ?>
        </li>
        <li>
            <?php
            a(_SITE_URL .'/index.php?filter=0');
            img('upload/content/tasks/in-progress-tasks.png', '','alt="'._LNG_TSK_IN_PROGRESS_ITEMS.'" width="16" height="16"');
            if(stlParam('filter') === '0') {
                d('<b>');
            }
            d(_LNG_TSK_IN_PROGRESS_ITEMS . ' (' . tskGetCountByStatus('0') . ')');
            if(stlParam('filter') === '0') {
                d('</b>');
            }
            a_();
            ?>
        </li>
        <li>
            <?php
            a(_SITE_URL .'/index.php?filter=1');
            img('upload/content/tasks/done-tasks.png', '','alt="'._LNG_TSK_DONE_ITEMS.'" width="16" height="16"');
            if(stlParam('filter') == 1) {
                d('<b>');
            }
            d(_LNG_TSK_DONE_ITEMS . ' (' . tskGetCountByStatus(1) . ')');
            if(stlParam('filter') == 1) {
                d('</b>');
            }
            a_();
            ?>
        </li>
        <li>
            <?php
            a(_SITE_URL .'/index.php?filter=2');
            img('upload/content/tasks/complete-tasks.png', '','alt="'._LNG_TSK_COMPLETE_ITEMS.'" width="16" height="16"');
            if(stlParam('filter') == 2) {
                d('<b>');
            }
            d(_LNG_TSK_COMPLETE_ITEMS . ' (' . tskGetCountByStatus(2) . ')');
            if(stlParam('filter') == 2) {
                d('</b>');
            }
            a_();
            ?>
        </li>
    </ul>
</div>