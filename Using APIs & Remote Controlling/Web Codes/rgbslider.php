<!DOCTYPE html>
<html>
<head>
  <title>RGB LED (Red Channel)</title>
  <style>
    body { font-family: Arial; text-align:center; margin-top:50px; }
    input[type=range] { width: 60%; margin: 20px; }
  </style>
</head>
<body>
  <h2>Control the Red LED Brightness (0–255)</h2>

  <form method="get">
    <input type="range" name="red" min="0" max="255" value="0">
    <input type="submit" value="Set Brightness">
  </form>

  <?php
  if (isset($_GET['red'])) {
      $r = intval($_GET['red']);
      file_put_contents("color.txt", "R=$r");
      echo "<p>Current Red Value: $r</p>";
  } else {
      if (file_exists("color.txt")) {
          echo "<p>Current Value: " . file_get_contents("color.txt") . "</p>";
      } else {
          echo "<p>No value set yet.</p>";
      }
  }
  ?>
</body>
</html>
