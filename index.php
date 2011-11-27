<?php
require("ipcity.php");

$time = date("c");
$userip = getenv("HTTP_X_FORWARDED_FOR");

$result = trim(ipCity("$userip"));
$hostname = getenv("HTTP_HOST");

$filename = date('omd');
$fp = fopen($filename, 'a');
fwrite($fp, "$time,$userip,$result,$hostname\n");
fclose($fp);

// echo "$time,$userip,$result,$hostname";

?>

<script language="javascript">
	var province = '<?php echo $result;?>';
	var match = /(联通|内蒙古|北京|吉林|黑龙江|辽宁|山东|天津|西藏|新疆|河北|河南|湖北|陕西|青海|甘肃|山西)/.test(province);
	if(match) {
		window.location.href="http://n.htm";
	}
	else{
		window.location.href="http://s.htm";
	}
</script>
