<?php

  $db = new SQLite3("/db/msensors.db") or die("Error connect to db!");

// select d.sens_id, d.value, dt.date from data d, dates dt where d.date_id = dt.id and dt.date=(select max(date) from dates);

  $result = $db->query("select d.sens_id, d.value, d.date from data d where d.date=(select max(date) from data);");

  echo "###;";

  while( $res = $result->fetchArray(1) )
  {
    echo $res["value"];
    echo ";";
    //$r += number_format($res["value"], 0, '.', '') + ";";
  }

//  echo $r;
?>
