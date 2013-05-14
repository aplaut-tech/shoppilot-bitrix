<?
IncludeModuleLangFile(__FILE__);

class shoppilot_client_api
{
	function sendRequest($url, $fields, $method) {
		
		$ch = curl_init();
		
		foreach($fields as $name => $value)
			$fields_json[] = '"'.addslashes($name).'":"'.addslashes($value).'"';
		
		$postfields = "{ ".implode(", ", $fields_json)." }";
		
		if( strtoupper(SITE_CHARSET)!="UTF-8" )
			$postfields = iconv(SITE_CHARSET, "utf-8", $postfields);
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$response = curl_exec($ch);
		curl_close($ch);
		
		print_r($response);
		return $response;
	}
	
	function sendUpdate($id) {
		
		CModule::IncludeModule("sale");
		$arOrder = CSaleOrder::GetByID($id);
		if(
			$arOrder &&
			$arOrder["CANCELED"]!="Y" &&
			(
			 	COption::GetOptionString("shoppilot.client", "shoppilot_pay_status")=="0" ||
				COption::GetOptionString("shoppilot.client", "shoppilot_pay_status")==($arOrder["PAYED"]=="Y" ? "Y" : "N")
			)
				&&
			(
			 	COption::GetOptionString("shoppilot.client", "shoppilot_order_status")=="0" ||
				COption::GetOptionString("shoppilot.client", "shoppilot_order_status")==$arOrder["STATUS_ID"]
			)
		) {
			
			$rsUser = CUser::GetByID( $arOrder["USER_ID"] );
			if( $arUser = $rsUser->Fetch() ) {
				
				$userFullNameParts = array($arUser["LAST_NAME"], $arUser["NAME"], $arUser["SECOND_NAME"]);
				foreach($userFullNameParts as $idx => $val)
				if( trim($val)=="" ) unset($userFullNameParts[$idx]);
				
				$arStatuses = array();
				$rsStatus = CSaleStatus::GetList(
					array("SORT" => "ASC"),
					array("LID" => LANGUAGE_ID)
				);
				while($arStatus = $rsStatus->Fetch()) $arStatuses[ $arStatus["ID"] ] = $arStatus["NAME"];
				
				
				$arFields = array(
					"auth_token" => COption::GetOptionString("shoppilot.client", "shoppilot_api_auth_key"),
					"number" => $arOrder["ID"],
					"email" => $arUser["EMAIL"],
					"full_name" => implode(" ", $userFullNameParts),
					"financial_status" => ( $arOrder["PAYED"]=="Y" ? GetMessage("SHOPPILOT_CLIENT_PAYED") : GetMessage("SHOPPILOT_CLIENT_NO_PAYED") ),
					"delivery_status" => $arStatuses[ $arOrder["STATUS_ID"] ]
				);
				
				$url = COption::GetOptionString("shoppilot.client", "shoppilot_api_endpoint");
				$short_url = $url;
				if( substr($url, -1)!="/" ) $url .= "/";
				$url .= $id;
				
				if( shoppilot_client_api::sendRequest($url, $arFields, "PUT")!="true" ) {
					shoppilot_client_api::sendRequest($short_url, $arFields, "POST");
				}
			}
		}
	}
	
	function OnOrderAdd($id, $data) {
		shoppilot_client_api::sendUpdate($id);
	}
	
	function OnOrderUpdate($id, $data) {
		shoppilot_client_api::sendUpdate($id);
	}
} ?>