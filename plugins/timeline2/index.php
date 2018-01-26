<!DOCTYPE html>
<html >
<head>
  <meta charset="UTF-8">
  <title>Timeline</title>


<?php
 require_once("../support/config.php");
if(!isLoggedIn()){
  toLogin();
  die();
}
makeHead("Project Schedule");
?>
  
</head>

<body>
<button id="toggleButton">Toggle</button>

<ul class="timeline" id="timeline">
  <li class="li complete">
    <div class="timestamp">
      <span class="author">Abhi Sharma</span>
      <span class="date">11/15/2014<span>
    </div>
    <div class="status">
      <h4> Shift Created </h4>
    </div>
  </li>
  <li class="li complete">
    <div class="timestamp">
      <span class="author">PAM Admin</span>
      <span class="date">11/15/2014<span>
    </div>
    <div class="status">
      <h4> Email Sent </h4>
    </div>
  </li>
  <li class="li complete">
    <div class="timestamp">
      <span class="author">Aaron Rodgers</span>
      <span class="date">11/15/2014<span>
    </div>
    <div class="status">
      <h4> SIC Approval </h4>
    </div>
  </li>
  <li class="li">
    <div class="timestamp">
      <span class="author">PAM Admin</span>
      <span class="date">TBD<span>
    </div>
    <div class="status">
      <h4> Shift Completed </h4>
    </div>
  </li>
 </ul>

<?php
makeFoot(WEBAPP);
?>

</body>
</html>
