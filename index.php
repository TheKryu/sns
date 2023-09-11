<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>mSensorsData</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="static/pico.min.css">
  <link rel="stylesheet" href="static/custom.css">
  <link rel="icon" href="static/favicon.ico">
  <meta http-equiv="refresh" content="300">
  <!-- <link rel="icon" href="data:,"> -->

  <style>

    .container {
      /*max-width: 1000px; */
      --block-spacing-vertical: calc(var(--spacing) * 1);
      }
    
    body {--block-spacing-vertical: calc(var(--spacing) * 1);}
  </style>

</head> 

<body>

  <nav class="container-fluid">
    <ul>
      <li><u onclick="location='./'" style="cursor:pointer;"><kbd>SenSData</kbd></u></li>
    </ul>
  </nav>

  <main class="container">
<?php
  require("config.php");
  require("err_msg.php");

  $up_arrow = "&#8599;";
  $dn_arrow = "&#8601;";
  $nc_arrow = "&#8596;";

  $result = $db->exec("create temp table if not exists temp_s 
                        (id integer, name text, type text, measure text, note text, cur_val real, 
                        prev_val real, date datetime);");
  $result = $db->exec("insert into temp_s(id, name, type, measure, note) select id, name, type, measure, note from sens where status=1;");
  $result = $db->exec("update temp_s set date = (select max(date) from data where sens_id = temp_s.id);");
  $result = $db->exec("update temp_s set cur_val = (select value from data where data.sens_id=temp_s.id and data.date=temp_s.date);");
  $result = $db->exec("update temp_s set prev_val = (select value from data where data.sens_id=temp_s.id and datetime(data.date)=datetime(temp_s.date, '-5 minutes'));");
  
  $result = $db->query("select id, name, type, measure, note, cur_val, prev_val, date from temp.temp_s order by id;");
  
  if ( $result === false )
  {
    err_msg($db->lastErrorMsg());
    $db->close();
    exit;
  }

  $col = 1;

  while( $res = $result->fetchArray(1) )
  {

    if ( $col === 1 )
    {
      echo "<div class='grid'>\n";
    }

    $id = $res["id"];

    // echo "<div><article style='padding: 15px; cursor: pointer' onclick=\"location='./lastgraph.php?id=" . $id . "'\">";
    echo "<div><article style='padding: 15px;'>";
    echo "<div class='headings'><h1><a href='./lastgraph.php?id=" .$id . "' data-tooltip='". $res["date"]."'>" . $res["cur_val"] . " " . $res["measure"] . "</a>&nbsp;&nbsp;";

    $cv = $res["cur_val"];
    $pv = $res["prev_val"];

    // var_dump($cv, $pv);

    echo "<a href='./lastdata.php?id=".$id."'>";
    
    if ( $cv > $pv ) {
      echo $up_arrow;
    } elseif ( $cv == $pv ) {
      echo $nc_arrow;
    } else {
      echo $dn_arrow;
    }

    echo "</a></h1>";
    echo "<h6>" . $res["name"] . "</h6>";
    echo "<p>" . $res["note"] . " " . $res["type"] . "</p>";
    echo "</div></article></div>\n";

    $col++;

    if ( $col === 4)
    {
      echo "</div>\n";
      $col = 1;
    }

  }
  
  $db->close();
  
?>
  </main>
    <footer>
      <main class="container" style="text-align: center;">
        <small>v1.05 01.11.2022 <a href="mailto:i@kruee.su">i@kruee.su</a></small>
      </main>
    </footer>
  </body>
</html>
