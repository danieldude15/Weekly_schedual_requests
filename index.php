<?php include_once 'header.php';
?>
<h1>עמוד התחברות</h1>
<form action="form.php" method="post">
<table style="float:right;direction:rtl;"><tr>
<td>
משתמש:
</td>
<td> 
<input style="direction:rtl;" type="text" name="taz" value="" required>
</td></tr><tr><td>
סיסמא:</td>
<td> 
<input style="direction:rtl;" type="password" name="pass" value="">
</td></tr>
<tr><td>
מהו שמו הפרטי של מנהל האשכול שלך(בעברית):</td>
<td> 
<input style="direction:rtl;" type="text" name="capcha" value="" required>
</td></tr>
</table>
<input style="float:right;padding:10px;margin:10px;" type="submit" value="התחבר\י">
<p style="float:right;"><a href="forgot.php">שכחתי סיסמא!</a>
</p>
</form>
</body>
</html>
