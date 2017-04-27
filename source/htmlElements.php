<?php
/*
 * Create table
 * @class string
 * @align string
 * @attr string
 */
function table($class = '', $align = '', $attr = '') {
    if($class)
        $class = "class='$class'";
    if($align)
        $align = "align='$align'";
    d("<table $class $align $attr>");
}

/*
 * End table
 */
function table_() {
    d('</table>');
}

/*
 * Create thead
 */
function thead() {
    d('<thead>');
}

/*
 * End thead
 */
function thead_() {
    d('</thead>');
}

/*
 * Create tbody
 */
function tbody() {
    d('<tbody>');
}

/*
 * End tbody
 */
function tbody_() {
    d('</tbody>');
}

/*
 * Create tr
 * @class string
 * @attr string
 */
function tr($class = '', $attr = '') {
    if($class)
        $class = "class='$class'";
    d("<tr $class $attr>");
}

/*
 * End tr
 */
function tr_() {
    d('</tr>');
}

/*
 * Create th
 * @class string
 * @attr string
 */
function th($class = '', $attr = '') {
    if($class)
        $class = "class='$class'";
    d("<th $class $attr>");
}

/*
 * End th
 */
function th_() {
    d('</th>');
}

/*
 * Create td
 * @class string
 * @attr string
 */
function td($class = '', $attr = '') {
    if($class)
        $class = "class='$class'";
    d("<td $class $attr>");
}

/*
 * End td
 */
function td_() {
    d('</td>');
}

/*
 * Create image
 * @url string
 * @class string
 * @attr string
 */
function img($url = '', $class = '', $attr = '') {
    if($class)
        $class = "class='$class'";
    d("<img src='$url' $class $attr/>");
}

/*
 * Create link
 * @url string
 * @class string
 * @attr string
 */
function a($url = '', $class = '', $attr = '') {
    if($class)
        $class = "class='$class'";
    d("<a href='$url' $class $attr>");
}

/*
 * End link
 */
function a_() {
    d('</a>');
}
/*
 * Create br
 */
function br() {
    d('<br>');
}

/*
 * Create script tag
 */
function script() {
    d('<script>');
}

/*
 * End script tag
 */
function script_() {
    d('</script>');
}