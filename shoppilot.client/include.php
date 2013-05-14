<?
global $DBType, $APPLICATION;

CModule::AddAutoloadClasses(
	"shoppilot.client",
	array(
		"shoppilot_client_api" => "classes/general/shoppilot_api.php",
	)
);

/*function nbs_bsl_put_to_log($message) {
	
	$err_log = fopen($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/nbsmedia.bsl/error_log.txt", "a");
	fwrite($err_log, date("d.m.Y H:i:s ").$message."\r\n\r\n" );
	fclose($err_log);
}*/
	
?>