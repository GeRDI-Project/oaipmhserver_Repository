<?php

# @todo config database path

$db = new SQLite3('../tests/test.db');

$db->exec('DELETE FROM resumptionTokens WHERE 1');

print("Done")

// $dbhandle = sqlite_open('../tests/test.db');
// $query = sqlite_exec($dbhandle, "DELETE FROM resumptionTokens WHERE 1", $error);
// if (!$query) {
//     exit("Fehler in der Abfrage: '$error'");
// } else {
//     echo 'Anzahl geänderter Zeilen: ', sqlite_changes($dbhandle);
// }
?>