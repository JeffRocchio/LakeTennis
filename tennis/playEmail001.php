<html>
 <head>
  <title>PHP Send Email Test</title>
 </head>
 <body>
 
 
 <?php

//include('Mail.php');
require_once('Mail.php');


echo "<p>Test Email Send from PHP<BR>This is PHP v5 and Apache v2.</p>";

mail("rsvp@laketennis.com", "test from PHP", "This is a test.", "From: d529518@laketennis.com", "-fd529518@laketennis.com");
mail("rocchio@rocketmail.com", "test from PHP", "This is a test.", "From: d529518@laketennis.com", "-fd529518@laketennis.com");


echo "<p>Sending test message via php mail()...</p>";

?>

</body>
</html>


