<!DOCTYPE html>
<html data-theme="light">
<head>
  <title>Sensor graph data</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="static/pico.min.css">
  <link rel="stylesheet" href="static/custom.css">
  <link rel="icon" href="static/favicon.ico">
  <meta http-equiv="refresh" content="300">
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

    <nav class="container-fluid">
      <ul>
        <li><u onclick="location='./'" style="cursor:pointer;"><kbd>SenSData</kbd></u></li> 
        <!-- <li><u onclick="history.back()" style="cursor:pointer;"><kbd>SenSData</kbd></u></li> -->
      </ul>
      <ul>
        <li onclick="location='?id=<?php echo $sid; ?>&t=h'" style="cursor:pointer;">
        <?php if ($st == "h"): ?>
          <u>1hour</u></a>
        <?php else: ?>
          1H</a>
        <?php endif ?>
        </li>
        <li onclick="location='?id=<?php echo $sid; ?>&t=d'" style="cursor:pointer;">
        <?php if ( $st == "d" ): ?>
          <u>1day</u></a>
        <?php else: ?>
          1D</a>
        <?php endif ?>
        </li>
        <li onclick="location='?id=<?php echo $sid; ?>&t=w'" style="cursor:pointer;">
        <?php if ( $st == "w" ): ?>
          <u>1week</u></a>
        <?php else: ?>
          1W</a>
        <?php endif ?>
        </li>
        <li onclick="location='?id=<?php echo $sid; ?>&t=m'" style="cursor:pointer;">
        <?php if ( $st == "m" ) echo "<u>1month</u></a>"; 
        else echo "1M</a>";
        ?>
        </li>
        <li><button style="padding: 7px;" onclick="location='./'">back</button></li>
      </ul>
    </nav>
  <!-- </main> -->

  <div id="curve_chart" style="width: auto; height: 89vh;"></div>

  <?php

    $edate = date("Y-m-d H:i:s");

    if ( $st == "h" ) $sdate = date("Y-m-d H:i:s", strtotime("-1 hours"));
    if ( $st == "d" ) $sdate = date("Y-m-d H:i:s", strtotime("-1 day"));
    if ( $st == "w" ) $sdate = date("Y-m-d H:i:s", strtotime("-1 week"));
    if ( $st == "m" ) $sdate = date("Y-m-d H:i:s", strtotime("-1 month"));

    //echo $st, " ", $sdate, " ", $edate;
    
    //$db->exec('drop table if exists temp_d;');

    $db->exec('create temp table temp_d (sens_id integer, date datetime, name text, type text, unit text, note text, min real, max real, value real);');
    $db->exec('insert into temp_d(sens_id, name, type, unit, note) select id, name, type, measure, note from sens where id='.$sid.';');
    $db->exec('update temp_d set date = (select date from dates where date=(select max(date) from dates));');
    $db->exec('update temp_d set value = (select value from data where sens_id = temp_d.sens_id and data.date_id = (select max(date_id) from data));');
    $db->exec('update temp_d set min = (select min(value) from data data, dates dt where data.sens_id = temp_d.sens_id
               and data.date_id = dt.id and dt.date between "' . $sdate . '" and "' . $edate . '");');
    $db->exec('update temp_d set max = (select max(data.value) from data data, dates dt where data.date_id = dt.id
                and data.sens_id = temp_d.sens_id and dt.date between "' . $sdate . '" and "' . $edate . '");');

    $sens_dt = $db->querySingle('select * from temp_d;', true);

    if ( $st == "h" || $st == "d")
    {
      $sql = 'select dt.date, d.value from data d left join dates dt on dt.id = d.date_id left join sens s on s.id = d.sens_id 
                where d.sens_id=' . $sid . ' and dt.date between "' . $sdate . '" and "' . $edate . '";';
    }
    else
    {
      $sql='select dt.date, d.value from data d 
          left join dates dt on dt.id = d.date_id 
          left join sens s on s.id = d.sens_id 
          where d.sens_id=' . $sid . ' and dt.date between "' . $sdate . '" and "' . $edate . '" 
          and strftime("%M", dt.date) % 60 = 0;';
    }

    $result = $db->query($sql);

  ?>


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
  </body>
</html>
