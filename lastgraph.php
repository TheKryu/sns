<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Sensor graph data</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="static/spectre.min.css">
  <link rel="stylesheet" href="static/spectre-icons.min.css">
  <link rel="icon" href="static/favicon.ico">
  <!-- <meta http-equiv="refresh" content="300"> -->
  </head>
<body>

<?php

  require('err_msg.php');
  require('config.php');

  $sid = htmlspecialchars($_GET["id"]);
  if ( empty($sid) )
  {
    err_msg('Wrong id!');
    exit;
  }

  //$today = date("Y-m-d");
  //echo $sid;

  $s1 = $db->querySingle("select s.name, s.type, s.measure from sens s where s.id=" . $sid, true);

  if ( empty($s1["name"]) )
  {
    err_msg('No data found!');
    $db->close();
    exit;
  }
  
  if (isset($_GET["t"]) ) $st = htmlspecialchars($_GET["t"]);
  else $st = "d";
  
//  if ( empty($st) )
//  {
//    $st="d";
//  }

?>

<div class="container">

  <header class="navbar">
    <section class="navbar-section">
      <a href="./" class="navbar-brand mr-2">SensData</a>
    </section>
    <section class="navbar-senter">
      <a href="?id=<?php echo $sid; ?>&t=h" class="btn btn-link">
        <?php if ($st == "h"): ?>
          hour
        <?php else: ?>
          1H
        <?php endif ?>
      </a>
      <a href="?id=<?php echo $sid; ?>&t=d" class="btn btn-link">
        <?php if ($st == "d"): ?>
          day
        <?php else: ?>
          1d
        <?php endif ?>
      </a>
      <a href="?id=<?php echo $sid; ?>&t=w" class="btn btn-link">
      <?php if ($st == "w"): ?>
          week
        <?php else: ?>
          1w
        <?php endif ?>
      </a>
      <a href="?id=<?php echo $sid; ?>&t=m" class="btn btn-link">
      <?php if ($st == "m"): ?>
          month
        <?php else: ?>
          1m
        <?php endif ?>
      </a>
      <a href="?id=<?php echo $sid; ?>&t=y" class="btn btn-link">
      <?php if ($st == "y"): ?>
          year
        <?php else: ?>
          1y
        <?php endif ?>
      </a>
      <a href="?id=<?php echo $sid; ?>&t=a" class="btn btn-link">
      <?php if ($st == "a"): ?>
          All
        <?php else: ?>
          all
        <?php endif ?>
      </a>
    </section>
    <section class="navbar-section">
      <a href="./" class="btn btn-link">back</a>
    </section>
  </header>

  <!-- </main> -->

  <?php

    $edate = date("Y-m-d H:i:s");

    if ( $st == "h" ) $sdate = date("Y-m-d H:i:s", strtotime("-1 hours"));
    if ( $st == "d" ) $sdate = date("Y-m-d H:i:s", strtotime("-1 day"));
    if ( $st == "w" ) $sdate = date("Y-m-d H:i:s", strtotime("-1 week"));
    if ( $st == "m" ) $sdate = date("Y-m-d H:i:s", strtotime("-1 month"));
    if ( $st == "y" ) $sdate = date("Y-m-d H:i:s", strtotime("-1 year"));
    if ( $st == "a" ) $sdate = "2000-01-01 00:00:00";

    //echo $st, " ", $sdate, " ", $edate;
    
    //$db->exec('drop table if exists temp_d;');

    $db->exec('create temp table if not exists temp_d (sens_id integer, date datetime, name text, type text, unit text, note text, min real, max real, value real);');
    $db->exec('insert into temp_d(sens_id, name, type, unit, note) select id, name, type, measure, note from sens where id='.$sid.';');
    $db->exec('update temp_d set date = (select max(date) from data where sens_id = ' . $sid . ');');
    $db->exec('update temp_d set value = (select value from data where sens_id = temp_d.sens_id and data.date = temp_d.date);');
    $db->exec('update temp_d set min = (select min(value) from data data where data.sens_id = temp_d.sens_id and data.date between "' . $sdate . '" and "' . $edate . '");');
    $db->exec('update temp_d set max = (select max(data.value) from data data where data.sens_id = temp_d.sens_id and data.date between "' . $sdate . '" and "' . $edate . '");');

    $sens_dt = $db->querySingle('select * from temp_d;', true);

    if ( $st == "h" || $st == "d" )    // hour, day
    {
      $sql = 'select d.date, d.value from data d 
                where d.sens_id=' . $sid . ' and d.date between "' . $sdate . '" and "' . $edate . '";';
    }
    elseif ( $st == "w" )            // week - hourly values
    {
      $sql = 'select d.date, avg(d.value) as value from data d where d.sens_id=' . $sid . ' group by strftime("%Y-%m-%d %H", date);';
    }
    else                            // month/year/all dayly average values
    {
      $sql = 'select d.date, avg(d.value) as value from data d where d.sens_id=' . $sid . ' group by strftime("%Y-%m-%d", d.date)
       union all
      select a.date, a.value from data_all a where a.sens_id=' . $sid . ' and a.date between "' . $sdate . '" and "' . $edate . ';" order by 1';
    } 

//    echo $sql;

    $result = $db->query($sql);

  ?>

<div id="curve_chart" style="width: auto; height: 89vh;"></div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

  <script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
      var data = new google.visualization.DataTable();
        data.addColumn('date', 'date');
        data.addColumn('number', 'value');
        data.addColumn({type: 'string', role: 'tooltip'});

        data.addRows([
          <?php
            while( $res = $result->fetchArray())
            {
              echo "[ new Date('", $res["date"], "'), ", $res["value"], ",'", $res["date"], " ", $sens_dt["type"], "=", $res["value"], " ", $sens_dt["unit"], "'],\n";
            }
            $db->close();
          ?>
      ]);

      var options = {
        title: '<?php echo $sens_dt["name"], " (", $sens_dt["note"], " ", $sens_dt["type"], ",", $sens_dt["unit"], ") ", $sens_dt["min"], " <= ", $sens_dt["value"], " <= ", $sens_dt["max"]; ?>',
        curveType: 'function',
        legend: { position: 'none', textStyle: {fontSize: 16} },
        hAxis: { format: 'HH:mm', textStyle: {fontSize: 12} },
        vAxis: { textStyle: {fontSize: 14} },
        tooltip: { textStyle: {fontSize: 18}},
      };

      var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

      chart.draw(data, options);
    }
  </script>
  </div>
  </body>
</html>
