<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>mSensorsData</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="static/spectre.min.css">
  <link rel="stylesheet" href="static/spectre-icons.min.css">
  <link rel="icon" href="static/favicon.ico">
  <meta http-equiv="refresh" content="300">
  <!-- <link rel="icon" href="data:,"> -->

  <style>
    .card {
      background: #efefef;
    }

  </style>

</head> 

<body>

  <div class="container grid-lg">

    <header class="navbar">
      <section class="navbar-section">
        <a href="./" class="navbar-brand mr-2">SensData</a>
      </section>
    </header>
<?php
  require("config.php");
  require("err_msg.php");

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
      echo '<div class="columns m-2">';
      echo "\r\n";
    }

    $id = $res["id"];

    echo "\t";
    echo '<div class="column col-4 col-xs-auto col-mx-auto">';
    echo "\r\n\t";
    echo '<div class="card">';
    echo '<div class="card-header"><div class="card-title">';
    echo "\r\n";

    $cv = $res["cur_val"];
    $pv = $res["prev_val"];

    // var_dump($cv, $pv);

    echo "\t<a href='./lastdata.php?id=".$id."'>";
    echo '<i class="icon ';
    
    if ( $cv > $pv ) {
      echo 'icon-upward"></i>';
    } elseif ( $cv == $pv ) {
      echo 'icon-resize-horiz"></i>';
    } else {
      echo 'icon-downward"></i>';
    }
    echo "</a></div></div>\r\n";
    echo '<div class="card-body h2">';

    echo "\t<a href='./lastgraph.php?id=" .$id . "'><b class='tooltip' data-tooltip='".$res["date"]. "'>" . $res["cur_val"] . "</b> " . $res["measure"] . "</a>\r\n";
    echo "</div>";
    echo '<div class="card-footer">';

    echo $res["name"] . "<br>";
    echo $res["note"] . " " . $res["type"];
    echo "</div></div></div>\n";

    $col++;

    if ( $col === 4)
    {
      echo "</div>\n";
      $col = 1;
    }

  }

  $db->close();
  
?>
<!--
  </main>
    <footer>
      <main class="container" style="text-align: center;">
        <small>v1.05 01.11.2022 <a href="mailto:i@kruee.su">i@kruee.su</a></small>
      </main>
    </footer>
  -->
</div>
</div>
  </body>
</html>
