<html>
<title>Contacts</title>
<h1>Contacts</h1>
<?php
  $path = __DIR__."/contact_text_files/";

  array_map(function($file) use ($path) {
	$exploded = explode("\n", file_get_contents($path . $file));

	echo "<h2>$exploded[0]</h2>";
	$body = (implode("\n", array_slice($exploded, 1)));
	echo "<p>$body</p>";
  }, array_diff(scandir($path), array('.', '..')));
?>
</html>
