<?php
//functions.php

//declare veriables
$sidurdatabase = "dbname";
$home_url = "domain_name";
$debugmod = FALSE;
$sendmail = FALSE;
$adminmail = 'your_email@domain.com';
$headers = 'From: name'
        . ' <noreply@domain_name>' . "\r\n" .
    'Reply-To: Your Name <your_email@domain.com>' . "\r\n" .
    'Content-type: text/html; charset=utf8' . "\r\n".
    'MIME-Version: 1.0' . "\r\n".
    'X-Mailer: PHP/' . phpversion();

//פונקציה להדפסת בעיות ותקלות
function died($msg){
    echo '<p>'. $msg."<br></p>";
}
//here are database LOGIN info . returns $conn variable
function connect_sql()
{
$servername = "hostname";
$username = "username";
$password = "password";
$dbname = "databasename";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn && $GLOBALS["debugmod"]) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
return $conn;
}

//User Authentication function. check if user is registered/admin/none
function login($usr,$pass,$conn)
{
$sql = "SELECT * FROM `emp`";
$result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_array($result))
    {
    if(strcasecmp($row["inerid"],$usr)==0)
    {
        if($row["pass"]==$pass)
        {
            if($row["admin"]=="1")
            {
            return 2;
            }
        return 1;
        }
        else 
        {
        echo "סיסמה עבור משתמש ".$usr. " שגויה!<br>";
        return 0;
        }
    }
    }
    echo "לא מצא משתמש ". $usr;
    return 0;
}

function userexist($usr,$conn)
{
$sql = "SELECT * FROM `emp`";
$result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_array($result))
    {
    if(strcasecmp($row["inerid"],$usr)==0)
    {
        return $row;
    }
    }
    echo "לא מצא משתמש ". $usr;
    return FALSE;
}


//marks employee as available all week if didnt give sidur and has misra!=0
function update_all_empty_sidurs($conn,$week)
{
$sql="SELECT * FROM ".$GLOBALS["sidurdatabase"].".emp";
$result=mysqli_query($conn, $sql);
$empnum=mysqli_num_rows($result);
$j=$i=0;
while($emps[$i] = mysqli_fetch_assoc($result)){$i++;}
$sql = "SELECT * FROM ".$GLOBALS["sidurdatabase"].".archive WHERE date_range_rash ='".$week."'";
$result=mysqli_query($conn, $sql);
$gave_sidur_num=mysqli_num_rows($result);
if ($gave_sidur_num>=$empnum) //on second thought this will never happen if the manager wont leave a sidur or if there is a emp on leave(חופשת לידה וכו)
{
    echo ' או שכל העובדים שלחו סידור השבוע או שכבר ביקשת סידור לתאריך זה ולכל מי שלא שלח סידור עודכן סידור פתוח'.$week.'<br>';
    if($GLOBALS['debugmod']){died($sql.'<br>mysqli_num_rows($result) in update_all_empty_sidurs returnd false<br>');}
    return FALSE;
}
else 
{
    while($empsidur[$j]=mysqli_fetch_assoc($result)){$j++;}
    for($i=0;$i<$empnum;$i++){
        $flag=1;
        for($j=0;$j<$gave_sidur_num;$j++){
            if(!strcmp($empsidur[$j]["inerid"],$emps[$i]["inerid"])){$flag=0;}
        }
        if($flag && $emps[$i]["misra"]!=0){
        for($j=0;$j<7;$j++)
        {
        $fakepost["shift".$j."5"]=1;    
        }
        $fakepost["fake"]="yes";
        $fakepost["week"]=$week;
        $fakepost["cando"]="auto_fill";
        $fakepost["inerid"]=$fakepost["taz"]=$emps[$i]["inerid"];
        $shifts=get_shifts($fakepost);
        if(insert_sql_archive($conn,$emps[$i],$shifts,$fakepost))
        {
            if($GLOBALS["debugmod"]){echo $emps[$i]["firstname_rash"].' '.$emps[$i]["lastname_rash"].'   סידור נשמר בארכיון! <br>';}
        }
        else 
        {
            died('could not update archive for '.$emps[$i]["firstname_rash"].' '.$emps[$i]["lastname_rash"].'<br>');
            return FALSE;
        }
        }
    }
return TRUE;    
}
 
}

//create $shifts variable to hold sidur values
function get_shifts($post)
{
$days = array("ראשון","שני","שלישי","רביעי","חמישי","שישי","שבת"); 
$shifts = array(array(),array());
if(isset($post["fake"]) && $post["fake"]=="yes"){
    for($i=0;$i<7;$i++){
    $shifts[$i][0] = $shifts[$i][1] = $shifts[$i][2] = $shifts[$i][5] =1;
    $shifts[$i][3] = $shifts[$i][4] = $shifts[$i][6] = NULL;
    }
    return $shifts;
}
for($i=0;$i<7;$i++)//store all user values into 2D array
{
    for($j=0;$j<6;$j++)
    {
        $shifts[$i][$j] = $post["shift".strval($i).strval($j)];
    }
    $shifts[$i][6]=$post['shift'.$i.'6'];
    $shifts[$i][7]="יום " . $days[$i];
}
if(strcmp($post["cando"],"cant")==0)//if user marked cant then flip all values 1<->0
{ 
    for($i=0;$i<7;$i++)
    {
        for($j=0;$j<3;$j++)
        {
            if($shifts[$i][$j]==1){$shifts[$i][$j]=0;}
                else{$shifts[$i][$j]=1;}
        }
    }
}
for($i=0;$i<7;$i++)
{
    if($shifts[$i][0] && $shifts[$i][1] && $shifts[$i][2] && !$shifts[$i][3] && !$shifts[$i][4])
    {
        $shifts[$i][5]=1;
    }
    else if($shifts[$i][5]==1)
    {
        $shifts[$i][0] = $shifts[$i][1] = $shifts[$i][2] = 1;
    }
    if($shifts[$i][4] || $shifts[$i][3])//check if request not working today
    {
        if(($shifts[$i][0] || $shifts[$i][1] || $shifts[$i][2]) && $post["cando"]=="can"){
        $shifts[$i][4] = $shifts[$i][3] = 0;
        }
        else {
            $shifts[$i][0] = $shifts[$i][1] = $shifts[$i][2] = 0;
        }
    }
    }
return $shifts;
}

//update weekly sidur in $table table
function update_table_row($conn,$shifts,$row,$post,$table)
{
$sql = "UPDATE `".$table."` SET 
`firstname_rash` = '".$row['firstname_rash']."',
`lastname_rash` = '".$row['lastname_rash']."',
`doit_rash` = '".$post['cando']."', 
`date_range_rash` = '".$post['week']."',
`indexG` = '".$row['indexG']."',
`super` = '".$row['super']."',
`driver` = '".$row['driver']."',
`nightsuper` = '".$row['nightsuper']."',
`religion` = '".$row['religion']."',
`mail` = '".$row['mail']."' ";

for($i=0;$i<7;$i++)
{
    for($j=0;$j<7;$j++){
            $sql .= ",`shift[".$i."][".$j."]` = '".$shifts[$i][$j]."' ";
    }
}
$sql .= "WHERE `".$table."`.`inerid` = '".$row['inerid']."' AND `".$table."`.`date_range_rash` = '".$post['week']."'";

if (mysqli_query($conn, $sql)==TRUE) 
{
    if($GLOBALS["debugmod"]){echo "שאילתת מסד נתונים לעדכון שולחן".$table." הצליחה ";}
    return TRUE;
} 
else 
{
    died('ERROR updating table name '.$table.'<br>ERROR:'. mysqli_error($conn).'<br>');
    echo '<br>'.$sql;
    return FALSE;
}
}

//returns emp $row array from database by inerid=$taz from $table
function get_emp($conn,$table,$user,$date)
{
$sql = "SELECT * FROM ".$GLOBALS["sidurdatabase"].".".$table."
WHERE inerid='".$user."'";
$num = count($search);
if($date!=NULL){
    $sql .= " AND date_range_rash='".$date."'";
}
$result=mysqli_query($conn, $sql);

if (mysqli_num_rows($result)==1) {
    return mysqli_fetch_assoc($result);
    }
else {
    died("לא נמצא עובד ברשימה ".$table." במשתמש".$user);
    return FALSE;
}
}

//insert weekly sidur to archive if not exist and update if exist
function insert_sql_archive($conn,$row,$shifts,$post)
{
    $sql = "INSERT INTO `archive` (`inerid`, `firstname_rash`, `lastname_rash`, `team`, `misra`,`date_range_rash`,`doit_rash`,`driver`,`super`,`nightsuper`,`religion`,`indexG`,`mail`";
    for($i=0;$i<7;$i++)
    {
        for($j=0;$j<7;$j++)
        {
            $sql .= ", `shift[".$i."][".$j."]`";
        }
    }
    $sql .= ") VALUES ('".$row['inerid']."', '".$row['firstname_rash']."','".$row['lastname_rash']."','".$row['team']."','".$row['misra']."','".$post['week']."','".$post['cando']."','".$row['driver']."','".$row['super']."','".$row['nightsuper']."','".$row['religion']."','".$row['indexG']."','".$row['mail']."'";
    for($i=0;$i<7;$i++)
    {
        for($j=0;$j<7;$j++)
        {
            $sql .= ",'".$shifts[$i][$j]."' ";
        }
    }
    $sql .= ")";
    if (mysqli_query($conn, $sql)==TRUE) 
    {
        if($GLOBALS["debugmod"]){echo 'נשמר בארכון <br>';}
        return TRUE;
    }
    else 
    {
        died("Error INSERTING record: " . mysqli_error($conn));
        return FALSE;
    }

    
}

//Updated sidur of employee to database
function update_weekly_sidur($conn,$post,$usr)
{
if(($row = get_emp($conn,"emp",$usr,NULL))==FALSE)
{
    echo 'לא נמצא משתמש '.$usr;
    exit;
}
//$shifts holds the values of the sidur from form.php
$shifts = get_shifts($post);
reshift_specials($shifts,$row);
//update latest sidur of emp to database
$sql = "SELECT * FROM ".$GLOBALS["sidurdatabase"].".archive WHERE date_range_rash='".$_SESSION['week']."' AND inerid='".$usr."'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result)==1) {
    if( update_table_row ($conn,$shifts,$row,$post,"archive")){echo "סידור עודכן בהצלחה ומחליף סידור קודם שנרשם<br>";}
}
else if( mysqli_num_rows ($result)==0){
    if(insert_sql_archive($conn,$row,$shifts,$post)==TRUE)
            {echo '<h1>סידור עודכן בהצלחה! ונכנס לארכיון</h1>';}
}
else {
    if($GLOBALS["debugmod"]){died("לא מצא שורות לפי תאריך ומשתמש. מספר שורות שנמצאו:".mysqli_num_rows($result)."<br>משמע שולחן הארעיון לא התעדכן ולא קיבל רישום חדש.");}
}
$sql = "UPDATE `emp` SET `date_range_rash` = '".$post['week']."', `doit_rash` = '".$post["cando"]."' WHERE `emp`.`inerid` = '".$usr."'";
    if(mysqli_query($conn, $sql) && $GLOBALS["debugmod"]){echo "<br>עודכן תאריך אחרון ברשימת עובדים<br>";}
}

function reshift_specials(&$shifts,$row){
    if($row["nights"]==0){
        for($i=0;$i<7;$i++){
            $shifts[$i][2]=0;
        }
    }
    if($row["religion"]==1){
        $shifts[5][1]=$shifts[5][2]=$shifts[6][0]=$shifts[6][1]=0;
    }
}

//prints an employee sidur table
function print_sidur_table($row)
{
$weekend = date('d-m-Y', strtotime($row["date_range_rash"]. ' + 6 days'));
$week = date('d-m-Y', strtotime($row["date_range_rash"]));
echo '<table class="sidur_table" border="1" cellpadding="10" font-size="8">
<tbody>
<tr >
<td>'.$row["misra"].'</td>
<td colspan="3">'.$row["firstname_rash"].' '.$row["lastname_rash"].' </td>
<td colspan="4">מתאריך'.$week.' עד '.$weekend.'</td>
</tr>
<tr >
<td style="width: 5%;"></td>
<td style="width: 5%;">א</td>
<td style="width: 5%;">ב</td>
<td style="width: 5%;">ג</td>
<td style="width: 5%;">פגרה</td>
<td style="width: 5%;">חש</td>
<td style="width: 5%;">פתוח</td>
<td style="width: 65%;">הערות</td>
</tr>';
$days = array("ראשון","שני","שלישי","רביעי","חמישי","שישי","שבת"); 
for($i=0;$i<7;$i++)
{
    echo '<tr>
    <td>'.$days[$i].'</td>';
    for($j=0;$j<3;$j++)
    {
        if((int)$row['shift['.$i.']['.$j.']'])
        {
            echo '<td bgcolor="#00FF00"><img src="Check.png" class="check_image"></td>';
        }
        else 
        {
            echo '<td bgcolor="#FF0000"><img src="icon_x_mark.png" class="check_image"></td>';}
    }
    for($j=3;$j<6;$j++)
    {
        if($row['shift['.$i.']['.$j.']']){
            echo '<td bgcolor="#00FF00"><img src="Check.png" class="check_image"></td>';
        }
        else 
        {
            echo '<td></td>';}
    }
    echo '<td>'.$row['shift['.$i.'][6]'].'</td>';
    echo '</tr>';
}
echo '</tbody></table>';
}

//return sidur table
function return_sidur_table($row)
{
$weekend = date('d-m-Y', strtotime($row["date_range_rash"]. ' + 6 days'));
$week = date('d-m-Y', strtotime($row["date_range_rash"]));
$ret =' שלום
        '.$row["firstname_rash"].' '.$row["lastname_rash"].'
        להלן הסידור שביקשת במערכת סידור עבודה.<br>';
$ret .= '<table style="direction:rtl;" border="1" cellpadding="10" font-size="8" >
<tbody>
<tr >
<td>'.$row["misra"].'</td>
<td colspan="3">'.$row["firstname_rash"].' '.$row["lastname_rash"].' </td>
<td colspan="4">מתאריך'.$week.' עד '.$weekend.'</td>
</tr>
<tr >
<td style="width: 5%;"></td>
<td style="width: 5%;">א</td>
<td style="width: 5%;">ב</td>
<td style="width: 5%;"> ג</td>
<td style="width: 5%;"> פגרה</td>
<td style="width: 5%;"> חש</td>
<td style="width: 5%;"> פתוח</td>
<td style="width: 65%;">הערות</td>
</tr>';
$days = array("ראשון","שני","שלישי","רביעי","חמישי","שישי","שבת"); 
for($i=0;$i<7;$i++)
{
    $ret .= '<tr >
    <td>'.$days[$i].'</td>';
    for($j=0;$j<3;$j++)
    {
        if((int)$row['shift['.$i.']['.$j.']'])
        {
            $ret .= '<td bgcolor="#00FF00">V</td>';
        }
        else 
        {
            $ret .= '<td bgcolor="#FF0000">X</td>';}
    }
    for($j=3;$j<6;$j++)
    {
        if($row['shift['.$i.']['.$j.']']){
            $ret .= '<td bgcolor="#00FF00">V</td>';
        }
        else 
        {
            $ret .= '<td></td>';}
    }
    $ret .= '<td>'.$row['shift['.$i.'][6]'].'</td></tr>';
}
$ret .= '</tbody></table>';
return $ret;
}

//prints table with all employees
function print_emp_table($result) 
{
    $past5 = date('Y-m-d', strtotime('-5 days'));
echo '<table class="responstable emp_table">
<tr>
<th>#</th>
<th>קבוצה</th>
<th>משתמש</th>
<th>שם מלא</th>
<th>EMAIL</th>
<th>משמרות</th>
<th>רשיון</th>
<th>אחמש</th>
<th>אחמש לילה</th>
<th>עובד לילה</th>
<th>דתי</th>
<th>תאריך סידור אחרון</th></tr>';
while($row = mysqli_fetch_assoc($result)) 
{
    $week = date('d-m-Y', strtotime($row["date_range_rash"]));
    if(strtotime(date('Y-m-d',strtotime($row["update_time"]))) < strtotime($past5)) 
        {$updated_sidur='<font color="red">'.$week.'</font>';}
    else 
        {$updated_sidur=$week;}
    echo '<tr>
    <td>'.$row["indexG"].'</td>
    <td>'.$row["team"].'</td>
    <td>'.$row["inerid"].'</td>
    <td>'.$row["firstname_rash"].' '.$row["lastname_rash"].'</td>
    <td>'.$row["mail"].'</td>
    <td>'.$row["misra"].'</td>
    <td>';
    if($row["driver"]){echo 'כן';}
    else {echo 'לא';}
    echo '</td>
    <td>';if($row["super"]){echo 'כן';}
    else {echo 'לא';}
    echo '</td>
    <td>';if($row["nightsuper"]){echo 'כן';}
    else {echo 'לא';}
    echo '</td>
    <td>';if($row["nights"]){echo 'כן';}
    else {echo 'לא';}
    echo '</td>
    <td>';if($row["religion"]){echo 'כן';}
    else {echo 'לא';}
    echo '</td>
    <td>'.$updated_sidur;
    if($row["doit_rash"]=="auto_fill"){echo ' הוגש על ידי מערכת';}
    echo '</td>
    </tr>';
}
echo '</table>';
}

//prints availability table 
function print_ava_table($counter)
{
$shiftname = array("בוקר","צהריים","לילה"); 
echo '<table style="display:inline-block;" border="1" cellpadding="10" font-size="8">
<tbody>
<tr>
<td style="width: 30%;"></td>
<td style="width: 10%;">יום א</td>
<td style="width: 10%;">יום ב</td>
<td style="width: 10%;">יום ג</td>
<td style="width: 10%;">יום ד</td>
<td style="width: 10%;">יום ה</td>
<td style="width: 10%;">יום ו</td>
<td style="width: 10%;">יום ש</td>';
for($j=0;$j<3;$j++)
{
    echo '<tr>
    <td>משמרת '.$shiftname[$j].'</td>';
    for($i=0;$i<7;$i++)
    {
        echo '<td>';
        if(isset($counter[$i][$j])){echo $counter[$i][$j];}
        echo '</td>';
    }
    echo '</tr>';    
    }
echo '</tbody></table>';
}

//prints an employee short sidur table horizontally
function print_emp_ava_table($row)
{
if(!isset($row)){return NULL;}
$shiftname = array("בוקר","צהריים","לילה"); 
echo '<table style="display:inline-block;" border="1" cellpadding="10" font-size="8">
<tbody>
<tr>
<td style="width: 30%;">'.$row["firstname_rash"].' '.$row["lastname_rash"].'</td>
<td style="width: 10%;">יום א</td>
<td style="width: 10%;">יום ב</td>
<td style="width: 10%;">יום ג</td>
<td style="width: 10%;">יום ד</td>
<td style="width: 10%;">יום ה</td>
<td style="width: 10%;">יום ו</td>
<td style="width: 10%;">יום ש</td>';
for($j=0;$j<3;$j++)
{
    echo '<tr>
    <td>משמרת '.$shiftname[$j].'</td>';
    for($i=0;$i<7;$i++){
        if($row['shift['.$i.']['.$j.']']==1)
        {
            echo '<td bgcolor="#00FF00"><img src="Check.png" class="check_image"></td>';
        }
        else 
        {
            echo '<td bgcolor="#FF0000"><img src="icon_x_mark.png" class="check_image"></td>';
        }
    }
	echo '</tr>';
}

echo '</tbody></table>';
return TRUE;
}

//count all employees availability to variable $count
function count_availability($row,&$count)
{
for($i=0;$i<7;$i++){
    for($j=0;$j<3;$j++){
        if($row['shift['.$i.']['.$j.']']==1)
        {
            if(!isset($count[$i][$j])){$count[$i][$j]=0;}
            $count[$i][$j]++;
        }
    }
}
}

//check availability page
function check_availability($post,$conn,$flag)
{
$weekend = date('d-m-Y', strtotime($post["week"]. ' + 7 days'));
$week = date('d-m-Y', strtotime($post["week"]));
echo '<h1>זמינות עובדים לסידור שבועי מ'.$week.' עד - '.$weekend.'</h1>';

$sql = "SELECT * FROM archive WHERE date_range_rash='".$post['week']."'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) 
{
    $counter=array(array(),array());
// output data of each row
    while($row = mysqli_fetch_assoc($result)) 
        {
        count_availability($row,$counter);
	}
    print_ava_table($counter);
    if($flag==1)
    {
        echo '<div style="clear:both;float:right;">';
        $sql = "SELECT * FROM archive WHERE date_range_rash='".$post['week']."'";
        $result = mysqli_query($conn, $sql);
        while($row = mysqli_fetch_assoc($result)) 
        {
            print_emp_ava_table($row);
        }
        echo '</div>';
    } 
}
else 
{
    echo "0 results";
}
mysqli_close($conn);
}

//adds employee to database
function add_emp($post,$conn){
	$taz=$post["inerid"];
	$first=$post["f_name"];
	$last=$post["l_name"];
	$pass=$post["newpass"];
	$misra=$post["misra"];
	$index = $post["indexG"];
	$team=$post["teamnum"];
	$super=$post["super"];
	$nightsuper=$post["nightsuper"];
	$driver=$post["driver"];
	$religion=$post["religion"];
	$nights=$post["nights"];
    $sql = "
	INSERT INTO `emp`
	( `inerid`, `firstname_rash`, `lastname_rash`, `pass`, `driver`, `indexG`, `super`, `nightsuper`, `religion`, `nights`, `team`, `misra`) 
	VALUES ('".$taz."', '".$first."','".$last."','".$pass."',".$driver.",".$index.",".$super.",".$nightsuper.",".$religion.",".$nights.",".$team.",".$misra.")";
    //$sql = mysql_real_escape_string($sql);
    if (mysqli_query($conn, $sql)) {
    echo ' עובד בשם '.$first.' נוסף בהצלחה ';
    } else {
    died("Error INSERTING record: " . mysql_error($conn) . "sql:<br>".$sql);
    }
 
}

function edit_emp($post,$conn){
$taz=$post["inerid"];
$first=$post["f_name"];
$last=$post["l_name"];
$pass=$post["newpass"];
$misra=$post["misra"];
$index = $post["indexG"];
$team=$post["teamnum"];
$super=$post["super"];
$nightsuper=$post["nightsuper"];
$driver=$post["driver"];
$religion=$post["religion"];
$nights=$post["nights"];
$sql = "SELECT * FROM ".$GLOBALS["sidurdatabase"].".emp
WHERE inerid='";
$sql .= $taz."'";
$result=mysqli_query($conn, $sql);
if (mysqli_num_rows($result)==1) {
$sql = "UPDATE `emp` SET `inerid` = '".$post['inerid']."'";
if("" != trim($first)){$sql .= ',`firstname_rash` = "'.$first.'"';}
if("" != trim($last)){$sql .= ',`lastname_rash` = "'.$last.'"';}
if("" != trim($pass)){$sql .= ',`pass` = "'.$pass.'"';}
if("" != trim($misra)){$sql .= ",`misra` = '".$misra."'";}
if("" != trim($index)){$sql .= ",`indexG` = '".$index."'";}
if("" != trim($team)){$sql .= ",`team` = '".$team."'";}
if("" != trim($super)){$sql .= ",`super` = '".$super."'";}
if("" != trim($nightsuper)){$sql .= ",`nightsuper` = '".$nightsuper."'";}
if("" != trim($driver)){$sql .= ",`driver` = '".$driver."'";}
if("" != trim($religion)){$sql .= ",`religion` = '".$religion."'";}
if("" != trim($nights)){$sql .= ",`nights` = '".$nights."'";}

$sql .= "WHERE `emp`.`inerid` = '".$post['inerid']."'";
 if (mysqli_query($conn, $sql)) {
    echo 'פרטי עובד עודכנו בהצלחה ';
    } 
 else {
    died("Error updating record: " . mysqli_error($conn)."<br> tried to exe sql:<br>".$sql);
    }
}
else {
    
    died("could not find emp - '$taz': <br> tried to exe sql:<br>$sql");
}
}

//removes employee from database by username
function remove_emp($post,$conn){

$taz=$post["inerid"];

$sql = "DELETE FROM ".$GLOBALS["sidurdatabase"].".emp
WHERE inerid='$taz'";

if (mysqli_query($conn, $sql)) {
    echo '<h1>עובד  '.$taz.' הוסר בהצלחה</h1>';
	
} else {
    echo "<h1>Error updating record: " . mysqli_error($conn)."</h1>";
}
}

function printone($post,$conn){
$taz=$post["inerid"];
$week=$post["week"];
$sql = "SELECT * FROM ".$GLOBALS["sidurdatabase"].".archive
WHERE inerid='$taz' AND date_range_rash='$week'";
$result=mysqli_query($conn, $sql);
if (mysqli_num_rows($result)==1) {
    $row = mysqli_fetch_assoc($result);
        print_sidur_table($row);
	}
else {
    echo "0 results";
}
}
function add_ship_notice($arr,$conn){
    
    $sql = "INSERT INTO `notice` (`shift_date`, `ship_name`, `shift[0]`, `shift[1]`, `shift[2]`, `note` ) VALUES ('".$arr["week"]."', '".$arr["ship"]."','".$arr["shift0"]."','".$arr["shift1"]."','".$arr["shift2"]."','".$arr["note"]."')";

    if (mysqli_query($conn, $sql)) {
    echo 'האוניה '.$arr["ship"].' נוספה בהצלחה ללוז!';
    } else {
    died("Error updating record: " . mysqli_error($conn));
    }
 
}


function print_expected_ships($row,$result) 
{
echo '<table class="responstable emp_table">
<tr>
<th>#</th>
<th>תאריך</th>
<th>שם האוניה</th>
<th>משמרות מבוקשות</th>
<th>הערה</th>
<th>תאריך עדכון</th>';
while($row = mysqli_fetch_assoc($result)) 
{
    $week = date('d-m-Y', strtotime($row["shift_date"]));
    $day=hebrew_day(date('w',strtotime($row["shift_date"])));
    echo '<tr>
    <td>'.$row["uni_id"].'</td>
    <td>'.$week.' יום '.$day.'</td>
    <td>'.$row["ship_name"].'</td>'
    . '<td>';
    if($row["shift[0]"]==1) echo 'בוקר';
    if($row["shift[1]"]==1) echo ' צהריים';
    if($row["shift[2]"]==1) echo ' לילה';
    echo '</td>
    <td>'.$row["note"].'</td>
    <td>'.$row["update_time"].'</td>
    </tr>';
}
echo '</table>';
}
function remove_ship($post,$conn){

$id=$post["id"];

$sql = "DELETE FROM ".$GLOBALS["sidurdatabase"].".notice
WHERE uni_id='$id'";

if (mysqli_query($conn, $sql)) {
    echo '<h1>האוניה הוסרה בהצלחה</h1>';
	
} else {
    echo "<h1>Error updating record: " . mysqli_error($conn)."</h1>";
}
}
function important_shifts($week,$conn){
    $end = date('Y-m-d', strtotime($week) + 24*3600*6);
    $sql = "SELECT * FROM notice WHERE shift_date>='$week' AND shift_date<='$end' ORDER BY update_time";
    $result=mysqli_query($conn, $sql);
if (mysqli_num_rows($result)>0) {
    $i=0;
    $message = "בקר יקר שים לב לצורך במשמרות הבאות:\\n";
    while($row[$i] = mysqli_fetch_assoc($result)){
    $daynum=intval(date('w',strtotime($row[$i]["shift_date"])));
    $shifts[$daynum]=1;
    $day=hebrew_day($daynum);
    $message .= 'ביום '.$day.' נדרש זמינות למשמרת';
    if($row[$i]["shift[0]"]==1) {$message .= ' בוקר ';}//$shifts[$daynum][0]=1;}
    if($row[$i]["shift[1]"]==1) {$message .= ' צהריים ';}//$shifts[$daynum][1]=1;}
    if($row[$i]["shift[2]"]==1) {$message .= ' לילה ';}//$shifts[$daynum][2]=1;}
    $message .= 'בעקבות '.$row[$i]["ship_name"];
    $message .= '\\n';
    }
    echo '<script type="text/javascript">alert("'.$message.'");</script>';
    return $shifts;
}
return FALSE;
}

function hebrew_day($day){
    switch($day){ 
        case 0: 
            return "ראשון";  
        case 1: 
            return "שני"; 
        case 2: 
            return "שלישי"; 
        case 3: 
            return "רביעי";  
        case 4: 
            return "חמישי"; 
        case 5: 
            return "שישי"; 
        case 6: 
            return "שבת";  
} 
}


function email_all($post,$conn){
  $sql = "SELECT * FROM ".$GLOBALS["sidurdatabase"].".emp WHERE `mail`!=''";
    $result=mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)){
    $addresses[] = $row["mail"];
    $names[] = $row["firstname_rash"].' '.$row["lastname_rash"];
	}
$to = implode(", ", $addresses);
$empnames = implode(", ", $names);
$content= '<div style="direction:rtl;">'.$post["msg"].'</div>';
if(mail($to,$post["title"] , $content, $GLOBALS["headers"])){
    echo 'מייל נשלח בהצלחה לעובדים הנ"ל<br>'.$empnames;
}
}
function tell_emp_printed($row,$admin,$week){
    $msg = '<div style="direction:rtl;">עובד יקר!<br>'
            . "לידיעתך, המנהל " . $admin ." הדפיס סידור שבועי"
            . "לשבוע שמתחיל בתאריך : ".$week."<br>"
            . 'להלן הסידור אותו רואה המנהל:<br>'. return_sidur_table($row)
            . "</div>";
    $title = 'סידור שבועי הודפס';
    mail($row['mail'],$title , $msg, $GLOBALS["headers"]);
}
?>
