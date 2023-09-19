<?php

  $db = new SQLite3("/db/msensors.db") or die("Error connect to db!");

  $result = $db->query("select d.sens_id, d.value, d.date from data d where d.date=(select max(s.date) from data s where d.sens_id = s.sens_id);");

  echo "###;";

  while( $res = $result->fetchArray(1) )
  {
    echo $res["value"];
    echo ";";
    //$r += number_format($res["value"], 0, '.', '') + ";";
  }

//  echo $r;
?>
