<?php
function insertsqlite3($time, $userip, $result, $hostname) {
    if ($db = sqlite_open('record.db')) {
        sqlite_query($db, "insert into record values($time, &userip, &result, $hostname)");
     }
}
?>
