<!DOCTYPE html>
<html data-theme="auto">
<head>
  <title>Send data log</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/static/pico.classless.min.css">
  <link rel="stylesheet" href="/static/custom.css">
</head> 
<body>
  <main>
    <nav>
      <ul>
        <li><strong><u>SenSData:</strong> last log</u></li>
      </ul>
      <ul>
        <!-- <li><a href="./">back</a></li> -->
        <li><button style="padding: 7px;" onclick="history.back();">back</button></li>
      </ul>
    </nav>

  <a href="#footer">bottom</a><br>
  <br>

  <small>
	% for row in rows:
		{{ !row }}
	% end
  </small>

    <footer id="footer"></footer>
    <a href="#">top</a>
<!-- <button onclick="history.back();">top</button> -->
  
</body>
</html>