<tr class="active">
        <td colspan="5">
            <div class="col-lg-8">
                <div class="filterTasks">
                    <ul class="list-inline">
                        <li>Filter:</li>
                        <li>
                            <?php
                            $tskAllFilterClass = '';
                            if(stlParam('filter') == '') {
                                $tskAllFilterClass = 'labelWarning';
                            }
                             a(_SITE_URL, $tskAllFilterClass);
                                d(_LNG_TSK_ALL);
                            a_();
                            ?>
                        </li>
                        <li>
                            <?php
                            $tskInProgressFilterClass = '';
                            if(stlParam('filter') === '0') {
                                $tskInProgressFilterClass = 'labelWarning';
                            }
                            a(_SITE_URL .'/index.php?filter=0', $tskInProgressFilterClass);
                                d(_LNG_TSK_IN_PROGRESS);
                            a_();
                            ?>
                        </li>
                        <li>
                            <?php
                            $tskDoneFilterClass = '';
                            if(stlParam('filter') == 1) {
                                $tskDoneFilterClass = 'labelWarning';
                            }
                            a(_SITE_URL .'/index.php?filter=1', $tskDoneFilterClass);
                                d(_LNG_TSK_DONE);
                            a_();
                            ?>
                        </li>
                        <li>
                            <?php
                            $tskCompleteFilterClass = '';
                            if(stlParam('filter') == 2) {
                                $tskCompleteFilterClass = 'labelWarning';
                            }
                            a(_SITE_URL .'/index.php?filter=2', $tskCompleteFilterClass);
                                d(_LNG_TSK_COMPLETE);
                            a_();
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
        </td>
    </tr>