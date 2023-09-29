<?php

  $db = new SQLite3("/db/msensors.db") or die("Error connect to db!");

  $result = $db->query("select d.sens_id, d.value, d.date from data d where d.date=(select max(s.date) from data s where d.sens_id = s.sens_id) order by d.sens_id;");

  header('Content-Type: application/json; charset=utf-8');

  $r = '{ "sensors": [';

  while( $res = $result->fetchArray(1) )
  {

//    $r = $r . '"sens' . $res["sens_id"] . '":{"id": ' . $res["sens_id"] . ',"value":' . $res["value"] . '},';
    $r = $r . '{"id": ' . $res["sens_id"] . ',"value":' . $res["value"] . '},';

  }

  $r = substr($r, 0, -1);
  $r = $r . "]}";

  echo $r;

  //echo json_encode($r);

  $db->close();
?>
