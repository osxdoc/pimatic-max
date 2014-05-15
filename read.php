<?php

// Uncomment the follwing 2 lines
// $host = "192.168.2.32"; // Your Cube-IP or hostame Here!
// $port = "80";

$fp = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$fp)
{
if ($errno == 111) echo "<b style=\"color: #FF0000\">Local software is running</b> - ".$errstr;
elseif ($errno == 113) echo "<b style=\"color: #FF0000\">No Connection</b> - ".$errstr;
else echo $errno." <b style=\"color: #FF0000\">Connection Error</b> - ".$errstr;
}
else
{
socket_set_blocking($fp,false);
sleep(1);
$finished = 0;

$jetzt = time();
$buff = "";
while (!feof($fp) && time() < $jetzt+20 && $finished == 0)
{
  $line = fgets($fp);
  if (strpos($line,"L:") !== false) $finished = 1;
  if ($line != "")  $buff .= $line."\n";
}
fclose($fp);
if ($finished != 1) echo "\n<b style=\"color: #FF0000\">No Connection</b>";
else
{
ob_start();

$arr = explode("\n\n",$buff);
print_r($arr);
echo '<hr />';
foreach ($arr as $v)
{
if (substr($v,0,2) == "H:")
{
$arr2 = explode(',',substr($v,2,strlen($v)));
$str = base64_decode($arr2[2]);

$data["sys"]["SerialNumber"] = $arr2[0];
$data["sys"]["RFAdress"] = $arr2[1];
$data["sys"]["Firmware"] = $arr2[2];
$data["sys"]["1?"] = $arr2[3]; //00000000
$data["sys"]["HTTP-ConnID"] = $arr2[4];
$data["sys"]["2?"] = $arr2[5];
$data["sys"]["3?"] = $arr2[6]; //31
$data["sys"]["Date"] = hexdec(substr($arr2[7],4,2)).".".hexdec(substr($arr2[7],2,2)).".".hexdec(substr($arr2[7],0,2));
$data["sys"]["Time"] = hexdec(substr($arr2[8],0,2)).":".hexdec(substr($arr2[8],2,2));
$data["sys"]["Timestamp"] = mktime(hexdec(substr($arr2[8],0,2)),hexdec(substr($arr2[8],2,2)),0,hexdec(substr($arr2[7],2,2)),hexdec(substr($arr2[7],4,2)),hexdec(substr($arr2[7],0,2)));
 
}
if (substr($v,0,2) == "M:")
{
$arr2 = explode(',',$v);
$str = base64_decode($arr2[2]);


$pos = 0;
$readlen =  1; $data["meta"]["?1"] = dechex(ord(substr($str,$pos,1)))."";  $pos += $readlen;
$readlen =  1; $data["meta"]["?2"] = dechex(ord(substr($str,$pos,1)))."";  $pos += $readlen; 
$readlen =  1; $data["meta"]["RoomCount"] = dechex(ord(substr($str,$pos,1)))."";  $pos += $readlen;


for($j = 1 ; $j <= $data["meta"]["RoomCount"] ; $j++)
{
$readlen =            1;  $RoomID = dechex(ord(substr($str,$pos,1)))."";$data["rooms"][$RoomID]["RoomID"] = $RoomID;  $pos += $readlen;
$readlen =            1;  $data["rooms"][$RoomID]["RoomNameLength"] = htmlentities(ord(substr($str,$pos,1))).""; $pos += $readlen;
$readlen = $data["rooms"][$RoomID]["RoomNameLength"];  for($i = $pos; $i < $readlen+$pos ; $i++) $data["rooms"][$RoomID]["RoomName"] .= htmlentities(substr($str,$i,1)).""; $pos += $readlen;
$readlen =            3;  for($i = $pos; $i < $readlen+$pos ; $i++) $data["rooms"][$RoomID]["RFAdress(?)"] .= str_pad(dechex(ord(substr($str,$i,1))),2,"0",STR_PAD_LEFT)."";  $pos += $readlen;
}

$readlen =            1; $data["meta"]["DevCount"] = dechex(ord(substr($str,$pos,1))).""; $pos += $readlen;


for($j = 1 ; $j <= $data["meta"]["DevCount"]; $j++)
{
unset($hilf);
$readlen =            1; $hilf["DeviceType"] = dechex(ord(substr($str,$pos,1))).""; $pos += $readlen;
$readlen =            3; for($i = $pos; $i < $readlen+$pos ; $i++) $hilf["RFAdress"] .= str_pad(dechex(ord(substr($str,$i,1))),2,"0",STR_PAD_LEFT).""; $pos += $readlen; 
$readlen =           10; $hilf["SerialNumber"] .= htmlentities(substr($str,$pos,10)).""; $pos += $readlen;
$readlen =            1; $hilf["NameLength"] = htmlentities(ord(substr($str,$pos,1))).""; $pos += $readlen; 
$readlen = $hilf["NameLength"]; for($i = $pos; $i < $readlen+$pos ; $i++) $hilf["Name"] .= htmlentities(substr($str,$i,1)).""; $pos += $readlen;
$readlen =            1; $hilf["RoomID"] = dechex(ord(substr($str,$pos,1))).""; $pos += $readlen;

$data["rooms"][$hilf["RoomID"]]["Devices"][$j]["DeviceType"] = $hilf["DeviceType"];
$data["rooms"][$hilf["RoomID"]]["Devices"][$j]["RFAdress"] = $hilf["RFAdress"];
$data["rooms"][$hilf["RoomID"]]["Devices"][$j]["SerialNumber"] = $hilf["SerialNumber"];
$data["rooms"][$hilf["RoomID"]]["Devices"][$j]["NameLength"] = $hilf["NameLength"];
$data["rooms"][$hilf["RoomID"]]["Devices"][$j]["Name"] = $hilf["Name"];
$data["rooms"][$hilf["RoomID"]]["Devices"][$j]["RoomID"] = $hilf["RoomID"];
}

}
if (substr($v,0,2) == "C:")
{
$arr2 = explode(',',$v);
$str = base64_decode($arr2[1]);

unset($hilf);

$pos = 0;
$readlen =  1; $hilf["?1"] = dechex(ord(substr($str,$pos,1)))."";  $pos += $readlen;
$readlen =  3; for($i = $pos; $i < $readlen+$pos ; $i++) $hilf["RFAdress"] .= str_pad(dechex(ord(substr($str,$i,1))),2,"0",STR_PAD_LEFT)."";  $pos += $readlen;
$readlen =  1; $hilf["DeviceType"] = dechex(ord(substr($str,$pos,1)))."";  $pos += $readlen;
$readlen =  3; for($i = $pos; $i < $readlen+$pos ; $i++) $hilf["?2"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen = 10; $hilf["SerialNumber"] = htmlentities(substr($str,$pos,10))."";  $pos += $readlen;

$deviceconf[$hilf["RFAdress"]]["?1"] = $hilf["?1"];
$deviceconf[$hilf["RFAdress"]]["RFAdress"] = $hilf["RFAdress"];
$deviceconf[$hilf["RFAdress"]]["DeviceType"] = $hilf["DeviceType"];
$deviceconf[$hilf["RFAdress"]]["?2"] = $hilf["?2"];
$deviceconf[$hilf["RFAdress"]]["SerialNumber"] = $hilf["SerialNumber"];


switch($deviceconf[$hilf["RFAdress"]]["DeviceType"])
{
case "0":
// Cube
$readlen =  1; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["PortalEnabled"] = dechex(ord(substr($str,$i,1)))."";  $pos += $readlen;
$readlen =  4; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?3"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen =  8; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?4"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen = 21; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?5"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen =  4; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?6"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen =  8; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?7"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen = 21; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?8"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen = 36; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["PortalURL"] .= htmlentities(substr($str,$i,1))."";  $pos += $readlen;
$readlen = 60; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?9"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen = 33; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?A"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen =  3; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?B"] .= htmlentities(substr($str,$i,1))."";  $pos += $readlen;
$readlen =  9; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?C"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;
$readlen =  4; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?D"] .= htmlentities(substr($str,$i,1))."";  $pos += $readlen;
$readlen =  9; for($i = $pos; $i < $readlen+$pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?E"] .= dechex(ord(substr($str,$i,1)))." ";  $pos += $readlen;

break;

case "1":
// Thermostat
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["ComfortTemperature"] = (ord(substr($str,$pos,1))/2)."";  $pos += $readlen; 
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["EcoTemperature"] = (ord(substr($str,$pos,1))/2)."";  $pos += $readlen;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["MaxSetPointTemperature"] = (ord(substr($str,$pos,1))/2)."";  $pos += $readlen;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["MinSetPointTemperature"] = (ord(substr($str,$pos,1))/2)."";  $pos += $readlen;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["TemperatureOffset"] = (ord(substr($str,$pos,1)))/2-3.5."";  $pos += $readlen;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["WindowOpenTemperature"] = (ord(substr($str,$pos,1))/2)."";  $pos += $readlen;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["WindowOpenDuration"] = dechex(ord(substr($str,$pos,1)))."";  $pos += $readlen;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["Boost"] = str_pad(decbin(ord(substr($str,$pos,1))),8,"0",STR_PAD_LEFT)."";  $pos += $readlen;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["BoostDuration"] = bindec(substr($deviceconf[$hilf["RFAdress"]]["Boost"],0,3))*5;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["BoostValue"] = bindec(substr($deviceconf[$hilf["RFAdress"]]["Boost"],3,5))*5;

$readlen =  1; $deviceconf[$hilf["RFAdress"]]["Decalc"] = str_pad(decbin(ord(substr($str,$pos,1))),8,"0",STR_PAD_LEFT)."";  $pos += $readlen;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["DecalcDay"] = bindec(substr($deviceconf[$hilf["RFAdress"]]["Decalc"],0,3));
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["DecalcTime"] = bindec(substr($deviceconf[$hilf["RFAdress"]]["Decalc"],3,5));
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["MaximumValveSetting"] = dechex(ord(substr($str,$pos,1)))*(100/255)."";  $pos += $readlen;
$readlen =  1; $deviceconf[$hilf["RFAdress"]]["ValveOffset"] = dechex(ord(substr($str,$pos,1)))*(100/255)."";  $pos += $readlen;

for ($j = 1 ; $j <= 7 ; $j++)
{
$readlen = 26;// Sat, Sun, Mon, Tue, Weg, Thu, Fri
for($i = $pos; $i < $readlen+$pos ; $i+=2)
{
$bin  = str_pad(decbin(hexdec(dechex(ord(substr($str,$i,1))))),8,"0",STR_PAD_LEFT).str_pad(decbin(hexdec(dechex(ord(substr($str,$i+1,1))))),8,"0",STR_PAD_LEFT);

$deg = bindec(substr($bin,0,7));

$min = bindec(substr($bin,7,9));

$deviceconf[$hilf["RFAdress"]]["WeeklyProgramm".$j."deg"][] = ($deg/2);
$deviceconf[$hilf["RFAdress"]]["WeeklyProgramm".$j."time"][] = number_format(($min*5/60),2);
}
$pos += $readlen;
}
break;

case "4":
// Fensterkontakt
break;

default:
// Other
break;
}
}

if (substr($v,0,2) == "L:")
{
$v = substr($v,2,strlen($v));
$str = base64_decode($v);

$pos = 0;

for($j = 1 ; $j <= $data["meta"]["DevCount"]; $j++)
{
unset($hilf);
$readlen =              1; $hilf["ReadLength"] = htmlentities(ord(substr($str,$pos,1))).""; $pos += $readlen;
$readlen =              3; for($i = $pos; $i < $readlen+$pos ; $i++) $hilf["RFAdress"] .= str_pad(dechex(ord(substr($str,$i,1))),2,"0",STR_PAD_LEFT).""; $pos += $readlen;
$readlen =              1; $hilf["?1"] = dechex(ord(substr($str,$pos,1))).""; $pos += $readlen;
$readlen =              1; $hilf["Data1"] .= str_pad(decbin(ord(substr($str,$pos,1))),8,"0",STR_PAD_LEFT).""; $pos += $readlen;
$readlen =              1; $hilf["Data2"] .= str_pad(decbin(ord(substr($str,$pos,1))),8,"0",STR_PAD_LEFT).""; $pos += $readlen;

if($hilf["ReadLength"] == 11)
{
$readlen =              1; $hilf["?2"] = dechex(ord(substr($str,$pos,1))).""; $pos += $readlen;
$readlen =              1; $hilf["Temperature"] = (ord(substr($str,$pos,1))/2).""; $pos += $readlen;
$readlen =              2; for($i = $pos; $i < $readlen+$pos ; $i++) $hilf["DateUntil"] .= str_pad(decbin(ord(substr($str,$i,1))),8,"0",STR_PAD_LEFT).""; $pos += $readlen;
$readlen =              1; $hilf["TimeUntil"] = (ord(substr($str,$pos,1))*0.5).""; $pos += $readlen;
}

$deviceconf[$hilf["RFAdress"]]["LiveReadLength"] = $hilf["ReadLength"];
$deviceconf[$hilf["RFAdress"]]["LiveRFAdress"] = $hilf["RFAdress"];
$deviceconf[$hilf["RFAdress"]]["Live?1"] = $hilf["?1"];
if($hilf["ReadLength"] == 11)
{
$deviceconf[$hilf["RFAdress"]]["Live?2"] = $hilf["?2"];
$deviceconf[$hilf["RFAdress"]]["Temperature"] = $hilf["Temperature"];
$deviceconf[$hilf["RFAdress"]]["DateUntil"] = $hilf["DateUntil"];
$year = substr($hilf["DateUntil"],-6,6);
$month = substr($hilf["DateUntil"],0,3).substr($hilf["DateUntil"],8,1);
$day = substr($hilf["DateUntil"],3,5);
$deviceconf[$hilf["RFAdress"]]["DateUntil"] = bindec($day).".".bindec($month).".".bindec($year);
$deviceconf[$hilf["RFAdress"]]["TimeUntil"] = $hilf["TimeUntil"];
$deviceconf[$hilf["RFAdress"]]["TimestampUntil"] = mktime(floor($hilf["TimeUntil"]),($hilf["TimeUntil"]-floor($hilf["TimeUntil"]))*60,0,bindec($month),bindec($day),bindec($year));
}

$deviceconf[$hilf["RFAdress"]]["valid"] = substr($hilf["Data1"],3,1);
$deviceconf[$hilf["RFAdress"]]["Error"] = substr($hilf["Data1"],4,1);
$deviceconf[$hilf["RFAdress"]]["isAnswer"] = substr($hilf["Data1"],5,1);
$deviceconf[$hilf["RFAdress"]]["initialized"] = substr($hilf["Data1"],6,1);
$deviceconf[$hilf["RFAdress"]]["LiveData7"] = substr($hilf["Data1"],7,1);

$deviceconf[$hilf["RFAdress"]]["LowBatt"] = substr($hilf["Data2"],0,1);
$deviceconf[$hilf["RFAdress"]]["LinkError"] = substr($hilf["Data2"],1,1);
$deviceconf[$hilf["RFAdress"]]["PanelLock"] = substr($hilf["Data2"],2,1);
$deviceconf[$hilf["RFAdress"]]["GatewayOK"] = substr($hilf["Data2"],3,1);
$deviceconf[$hilf["RFAdress"]]["DST"] = substr($hilf["Data2"],4,1);
$deviceconf[$hilf["RFAdress"]]["Not used"] = substr($hilf["Data2"],5,1);
switch (substr($hilf["Data2"],6,2))
{
case "00" : $deviceconf[$hilf["RFAdress"]]["Mode"] = "auto"; break;
case "01" : $deviceconf[$hilf["RFAdress"]]["Mode"] = "manu"; break;
case "10" : $deviceconf[$hilf["RFAdress"]]["Mode"] = "vacation"; break;
case "11" : $deviceconf[$hilf["RFAdress"]]["Mode"] = "boost"; break;
}
$deviceconf[$hilf["RFAdress"]]["LiveDataX"] = $hilf["Data3"];



echo "\n";
}
//echo $v;
}

}


ob_end_clean();
}
}
?>