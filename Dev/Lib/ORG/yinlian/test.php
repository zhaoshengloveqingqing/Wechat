<?php
header('Content-type: text/html; charset=gbk');
?>
<html>
<head>
<title>chinapay����֧���ӿ���ʾ����</title>
</head>
<body>
<div style="text-align:center">
<h1>chinapay����֧���ӿ���ʾ����</h1>
<h2><a href="netpayclient_order_submit.php" target="_blank">֧������</a></h2>
<h2><a href="netpayclient_query_submit.php" target="_blank">���ʲ�ѯ</a></h2>
<h2><a href="netpayclient_refund_submit.php" target="_blank">�����˿�</a></h2>
<hr>
<?php
include_once("netpayclient_config.php");
echo "<h2><font color='red'>���ӿ���Ҫ mcrypt �� bcmath ��չ��֧�֣���鿴<a href='phpinfo.php' target='_blank'>PHP����</a>��ȷ�ϰ�װ����������չ��</font></h2>";

echo "<h2>��ǰ��Կ���ã�(<font color='red'>�밴������ʵ������� netpayclient_config.php �����ʵ��޸�</font>)</h2>";
echo "<h4>[˽Կ�ļ�·����".PRI_KEY."]</h4>";
echo "<h4>[��Կ�ļ�·����".PUB_KEY."]</h4>";

echo "<h2>��ʾ����װλ�ã�</h2>";
echo "<h4>[������ʵ�ַ��$site_url]</h4>";
echo "<h4>[����������·����$_SERVER[DOCUMENT_ROOT]]</h4>";

echo "<h2>������IP��ַ��<font color='green'>[$_SERVER[SERVER_ADDR]]</font></h2>";

?>
</div>
</body>
</html>