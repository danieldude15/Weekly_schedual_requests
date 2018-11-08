<?php
//convert sidur from DB to array
function convert_sidur_to_arr($week,$conn){
    $sql="SELECT * FROM archive WHERE date_range_rash='$week' ORDER BY team, indexG;";
    $result = mysqli_query($conn, $sql);

$row["qty"]=mysqli_num_rows($result);
if ($row["qty"] > 0) 
{   $i=0;
    while($row[$i] = mysqli_fetch_assoc($result)) {$i++;}
        return $row;
}
}

function emp_list_to_arr($conn){
    $sql="SELECT * FROM `emp`";
    $result = mysqli_query($conn, $sql);

$row["qty"]=mysqli_num_rows($result);
if ($row["qty"] > 0) 
{   $i=0;
    while($row[$i] = mysqli_fetch_assoc($result)) {
        $emp[$row[$i]["inerid"]]=$row[$i];
        $i++;}
        return $emp;
}
}

//print to user demand table to later convert to array
function get_demand_table(){
$shiftname = array("בוקר","צהריים","לילה"); 
echo '<table border="1" cellpadding="10" font-size="8">
<tbody>
<tr>
<th style="width: 30%;"></td>
<th style="width: 10%;">יום א</td>
<th style="width: 10%;">יום ב</td>
<th style="width: 10%;">יום ג</td>
<th style="width: 10%;">יום ד</td>
<th style="width: 10%;">יום ה</td>
<th style="width: 10%;">יום ו</td>
<th style="width: 10%;">יום ש</td>
</tr>
<tr>
<td>קבוצה 1</td>';
for($i=0;$i<7;$i++){
echo '<td><input type="text" name="demand7'.$i.'" value="" maxlength="2" size="2"></td>';
}
echo '</tr>';
for($j=0;$j<3;$j++)
{
    echo '<tr>
    <td>משמרת '.$shiftname[$j].'</td>';
    for($i=0;$i<7;$i++){
        if($j<=1){echo '<td><input type="number" name="demand'.$i.$j.'" value="4" min="0" max="25" style="width:45px;"></td>';}
        else{echo '<td><input type="number" name="demand'.$i.$j.'" value="3" min="0" max="25" style="width:45px;"></td>';}
    }
	echo '</tr>';
}

echo '</tbody></table>';
}

//this should convert $_POST of demand table to array
function demand_table_2arr($post){
    $demand = array(array(),array());
    for($i=0;$i<8;$i++)
    {
        for($j=0;$j<7;$j++)
        {
            $demand[$i][$j] = $post["demand".strval($i).strval($j)];
        }
    }
    return $demand;
    
}

//this should print the DEMAND table with conflicts
function check_sidur_conflict($ava,$demand){
$days = array("ראשון","שני","שלישי","רביעי","חמישי","שישי","שבת"); 
$shiftname = array("בוקר","צהריים","לילה"); 
$flag=1;
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
<td style="width: 10%;">יום ש</td></tr>
<tr><td>קבוצה 1</td>';
for($i=0;$i<7;$i++){
echo '<td>'.$demand[7][$i].'</td>';
}
for($j=0;$j<3;$j++)
{
    echo '<tr>
    <td>משמרת '.$shiftname[$j].'</td>';
    for($i=0;$i<7;$i++){
        if($ava[$i][$j] >= $demand[$i][$j])
        {
            echo '<td bgcolor="#00FF00">'.$demand[$i][$j].'</td>';
        }
        else 
        {
            $delta=$demand[$i][$j] - $ava[$i][$j];
            echo '<td bgcolor="#FF0000"><b>'.$demand[$i][$j].'</b> ('.$delta.'-)</td>';
            $problem["day"]=$i;
            $problem["shift"]=$j;
            $problem["delta"]=$delta;
        }
    }
	echo '</tr>';
}

echo '</tbody></table>';
if(isset($problem)){return $problem;}
else return FALSE;
}

function availability_by_arr($arr,$list,$table)
{
$counter=array(array(),array());
for($i=0;$i<$arr["qty"];$i++){
    count_availability($arr[$i],$counter);
}
if($table==1){print_ava_table($counter);}
if($list==1)
{
    echo '<div style="clear:both;float:right;">';
    for($i=0;$i<$arr["qty"];$i++){
    print_emp_ava_table($row[$i]);
    }
    echo '</div>';
}
return $counter;
}

function fix_problem_table($problem,$row){
    if($problem==NULL){return NULL;}
    $m=$problem["day"];
    $n=$problem["shift"];
    $days = array("ראשון","שני","שלישי","רביעי","חמישי","שישי","שבת"); 
    $shiftname = array("בוקר","צהריים","לילה"); 
    echo '<br><h2>'.'טיפול בבעיה במשמרת '.$shiftname[$n].' ביום '.$days[$m].'</h2>';
    echo '<table border="1" class="reassignment"><tbody>'
    . '<tr>'
    . '<th>הסר</th>'
    . '<th>שם</th>'
    . '<th>זמינות</th>'
    . '<th>אחמ"ש</th>'
    . '<th>נהג</th>'
    . '<th>לילות</th>'
    . '</tr>';
    for($i=0;$i<$row["qty"];$i++){
        if($row[$i]["shift[".$m."][".$n."]"]==1 && $row[$i]["misra"]>0){
        echo '<tr>'
        . '<td><input type="hidden" name="'.$row[$i]["inerid"].'" value="1"><input type="checkbox" name="'.$row[$i]["inerid"].'" value="2"></td>'
        . '<td>'.$row[$i]["firstname_rash"].' '.$row[$i]["lastname_rash"].'</td>'
        . '<td bgcolor="#00FF00"><img src="Check.png" class="check_image"></td>';
        if($row[$i]["super"]==1){echo '<td><img src="wonder-woman-icon.png"></td>';}
        else {echo '<td><img src="officer.png"></td>';}
        if($row[$i]["driver"]==1){echo '<td><img src="small_car.png"></td>';}
        else {echo '<td><img src="walking.png"></td>';}
        if($row[$i]["nights"]==1){echo '<td><img src="half_moon.png"></td>';}
        else {echo '<td><img src="sun.png"></td>';}
        echo '</tr>';
        }
    }
    echo '</tbody></table>';
    echo '<table border="1" class="reassignment"><tbody>'
    . '<tr>'
    . '<th>הוסף</th>'
    . '<th>שם</th>'
    . '<th>זמינות</th>'
    . '<th>אחמ"ש</th>'
    . '<th>נהג</th>'
    . '<th>לילות</th>'
    . '<th>דתי</th>'
    . '<th>הערה</th>'
    . '</tr>';
    for($i=0;$i<$row["qty"];$i++){
        if($row[$i]["shift[".$m."][".$n."]"]==0 && $row[$i]["misra"]>0){echo '<tr>'
            . '<td><input type="checkbox" name="'.$row[$i]["inerid"].'" value="1"></td>'
            . '<td>'.$row[$i]["firstname_rash"].' '.$row[$i]["lastname_rash"].'</td>'
            . '<td bgcolor="#FF0000"><img src="icon_x_mark.png" class="check_image"></td>';
        if($row[$i]["super"]==1){echo '<td><img src="wonder-woman-icon.png"></td>';}
        else {echo '<td><img src="officer.png"></td>';}
        if($row[$i]["driver"]==1){echo '<td><img src="small_car.png"></td>';}
        else {echo '<td><img src="walking.png"></td>';}
        if($row[$i]["nights"]==1){echo '<td><img src="half_moon.png"></td>';}
        else {echo '<td><img src="sun.png"></td>';}
        if($row[$i]["religion"]==1){echo '<td><img src="jew.png"></td>';}
        else {echo '<td></td>';}
        echo '<td>'.$row[$i]['shift['.$m.'][6]'].'</td>';
        echo '</tr>';
        }
    }
    echo '</tbody></table><input type="hidden" name="fixprob" value="1">';
}

function reassign_emps($post,&$row,$problem){
    if($problem==NULL){return NULL;}
    $m=strval($problem["day"]);
    $n=strval($problem["shift"]);
    for($i=0;$i<$row["qty"];$i++){
        $str = str_replace(".", "_", $row[$i]["inerid"]);
        if($post[$str]==1){
            $row[$i]['shift['.$m.']['.$n.']']=1;
            if($n==2){//if night shift block morning and afternoon
                $row[$i]['shift['.$m.'][1]']=$row[$i]['shift['.$m.'][0]']=0;
                if($m !=6){$row[$i][($m+1)][0]=0;;}//if not shabat block tommorow morning  
            }
            elseif($n==1) {//if afternoon shift block morning and night
                $row[$i]['shift['.$m.'][0]']=$row[$i]['shift['.$m.'][2]']=0;
                }
            elseif($n==0){//if morning shift block afternoon and night
                $row[$i]['shift['.$m.'][1]']=$row[$i]['shift['.$m.'][2]']=0;
                if($m != 0){$row[$i]['shift['.($m-1).'][2]']=0;}//if not sunday block yesterday night
            }
        }
        elseif($post[$str]== 2){
            $row[$i]['shift['.$m.']['.$n.']']=0;
        }
    }
}

function print_comp_sidur($assigned,$rows,$rotation){    
$days = array("ראשון","שני","שלישי","רביעי","חמישי","שישי","שבת"); 
echo '<table border="1" cellpadding="10" font-size="8">
<tbody>
<tr>
<td>#</td><td style="width: 30%;">שם</td>';
for($i=0;$i<7;$i++){
echo '<td style="width: 10%;">יום '.$days[$i].'</td>';
}
echo '</tr><tr>';
echo '<td colspan="2">קבוצה 1</td>';
for($i=0;$i<7;$i++){
echo '<td>'.$rotation[7][$i].'</td>';
}
echo '</tr>';
    $i=0;
    while($rows[$i]["team"]==1){
        echo '<tr><td>'.$rows[$i]['indexG'].'</td>'
        .'<td>'.$rows[$i]['firstname_rash'].' '.$rows[$i]['lastname_rash'].'</td>';
        for($j=0;$j<7;$j++){
            echo '<td>';
            $t=print_shift($j, $assigned[$rows[$i]["inerid"]]);
            if($t!=FALSE){$counter[$j][$t]++;}
            echo '</td>';
        }
        echo '</tr>';
        $i++;
    }
echo '<tr>';
echo '<td colspan="2">קבוצה 2</td>';
for($j=0;$j<7;$j++){
echo '<td>';
if($rotation[7][$i]=="א"){echo "ב";}
else {echo "א";}
echo '</td>';
}
echo '</tr>';
    while($rows[$i]["team"]==2){
        echo '<tr><td>'.$rows[$i]['indexG'].'</td>'
        .'<td>'.$rows[$i]['firstname_rash'].' '.$rows[$i]['lastname_rash'].'</td>';
        for($j=0;$j<7;$j++){
            echo '<td>';
            $t=print_shift($j, $assigned[$rows[$i]["inerid"]]);
            if($t!=FALSE){$counter[$j][$t]++;}
            echo '</td>';
        }
        echo '</tr>';
        $i++;
    }
echo '</tbody></table><br>';

print_ava_table($counter);

}

function print_shift($day,$row){
    $letter = array('א','ב','<span style="background-color: #ff00f7;border-radius: 80%;>ג</span>');
    for($i=0;$i<3;$i++){
    if($row['shift['.$day.']['.$i.']']==1){
        echo '<b><span style="font-size: 22px;padding: 8px;font-family: david;">'.$letter[$i].'</span></b>';
        return $i;
    }
    }
    return FALSE;
}

function assign_shabbat(&$check,$counter,$demand){
for($j=0;$j<3;$j++)
{
    for($i=5;$i<7;$i++){
        if($demand[$i][$j]>0 && $demand[$i][$j]!=$counter[$i][$j]){
            $problem["day"]=$i;
            $problem["shift"]=$j;
            fix_problem_table($problem,$check);
            break;
        }
    }
if(isset($problem)){return $problem;}
}
return FALSE;
}

function assign_shift($day,$shift,&$check,&$assign,&$demand){
    if($demand==0){return FALSE;}
    $problem["day"]=$day;
    $problem["shift"]=$shift;
if($demand[7][$day]=='א' && $shift==0 || $demand[7][$day]=='ב' && $shift==1){
    for($i=0;$i<$check["qty"];$i++){
        if($check[$i]["team"]==1 && $check[$i]["shift[".$day."][".$shift."]"]==1 && $demand[$day][$shift]>0){
            $user= $check[$i]["inerid"];
            $post[$user]=1;
            reassign_emps($post,$check,$problem);
            $assign[$user]["shift[".$day."][".$shift."]"]=1;
            $demand[$day][$shift]--;

        }
    }
    for($i=0;$i<$check["qty"];$i++){
        if($check[$i]["team"]==2 && $check[$i]["shift[".$day."][".$shift."]"]==1 && $demand[$day][$shift]>0){
            $user= $check[$i]["inerid"];
            $post[$user]=1;
            reassign_emps($post,$check,$problem);
            $assign[$user]["shift[".$day."][".$shift."]"]=1;
            $demand[$day][$shift]--;
        }
    }
}
elseif($demand[7][$day]=='א' && $shift==1 || $demand[7][$day]=='ב' && $shift==0) {
    for($i=0;$i<$check["qty"];$i++){
        if($check[$i]["team"]==2 && $check[$i]["shift[".$day."][".$shift."]"]==1 && $demand[$day][$shift]>0){
            $user= $check[$i]["inerid"];
            $post[$user]=1;
            reassign_emps($post,$check,$problem);
            $assign[$user]["shift[".$day."][".$shift."]"]=1;
            $demand[$day][$shift]--;
        }
    }
    for($i=0;$i<$check["qty"];$i++){
        if($check[$i]["team"]==1 && $check[$i]["shift[".$day."][".$shift."]"]==1 && $demand[$day][$shift]>0){
            $user= $check[$i]["inerid"];
            $post[$user]=1;
            reassign_emps($post,$check,$problem);
            $assign[$user]["shift[".$day."][".$shift."]"]=1;
            $demand[$day][$shift]--;
        }
    }
}
else {
    for($i=0;$i<$check["qty"];$i++){
        if($check[$i]["shift[".$day."][".$shift."]"]==1 && $demand[$day][$shift]>0){
            $user= $check[$i]["inerid"];
            $post[$user]=1;
            reassign_emps($post,$check,$problem);
            $assign[$user]["shift[".$day."][".$shift."]"]=1;
            $demand[$day][$shift]--;

        }
    }
}
}

function assign_week(&$check,&$assign,&$demand){
    echo 'מתחיל בבניית סידור שבועי<br> <br>';
    if(build_nights($check,$assign,$demand)){
        echo 'בניית סידור לילות הושלם בהצלחה<br>';
    }
}
function build_nights($check,$assign,$demand){
    $counter=availability_by_arr($check,0,0);
    $minval=$counter[0][2];
    $day=0;
    for($i=1;$i<5;$i++){
        if($minval > $counter[$i][2]){
            $minval=$counter[$i][2];
            $day=$i;
        }
    }
    $list=get_available($check,$day,2);
}
?>