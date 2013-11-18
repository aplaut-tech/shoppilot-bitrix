<?
IncludeModuleLangFile(__FILE__);

class shoppilot_reviews_api
{
	function isAssocArray($array) {
		return (bool)count(array_filter(array_keys($array), 'is_string'));
	}

	function toJson($object) {
		if( !is_array( $object ) ) {
			return '"'.$object.'"';
		}

		if( shoppilot_reviews_api::isAssocArray($object) ) {
			foreach($object as $key => $value) {
				$key = '"'.addslashes($key).'"';
				$value = shoppilot_reviews_api::toJson( $value );
				$json[] = "$key: $value";
			}
			return "{ ".implode(", ", $json)." }";
		} else {
			foreach($object as $value) {
				$json[] = shoppilot_reviews_api::toJson( $value );
			}
			return "[ " . implode( ", ", $json ) . " ]";
		}
	}

	function sendRequest($url, $fields, $method) {

		$ch = curl_init();

		$postfields = shoppilot_reviews_api::toJson($fields);

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
			 	COption::GetOptionString("shoppilot.reviews", "shoppilot_pay_status")=="0" ||
				COption::GetOptionString("shoppilot.reviews", "shoppilot_pay_status")==($arOrder["PAYED"]=="Y" ? "Y" : "N")
			)
				&&
			(
			 	COption::GetOptionString("shoppilot.reviews", "shoppilot_order_status")=="0" ||
				COption::GetOptionString("shoppilot.reviews", "shoppilot_order_status")==$arOrder["STATUS_ID"]
			)
		) {

			$rsUser = CUser::GetByID( $arOrder["USER_ID"] );

			if( $arUser = $rsUser->Fetch() ) {

				// Order Properties (e.g. FIO EMAIL PHONE ZIP LOCATION ADDRESS)
				$arOrderPropsValue = array();
				$dbOrderProps = CSaleOrderPropsValue::GetOrderProps($arOrder["ID"]);
				while ($arOrderProps = $dbOrderProps->Fetch()) {
					$arOrderPropsValue[$arOrderProps["CODE"]] = $arOrderProps["VALUE"];
				}
				$created_at = new DateTime($arOrder['DATE_INSERT']);

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
					"auth_token" => COption::GetOptionString("shoppilot.reviews", "shoppilot_api_auth_key"),
					"number" => $arOrder["ID"],
					"email" => $arUser["EMAIL"],
					"phone" => $arOrderPropsValue["PHONE"],
					"full_name" => implode(" ", $userFullNameParts),
					"created_at" => $created_at->getTimestamp(),
					"details" => array(
						"total_price" => $arOrder["PRICE"],
						"financial_status" => ( $arOrder["PAYED"]=="Y" ? GetMessage("SHOPPILOT_REVIEWS_PAYED") : GetMessage("SHOPPILOT_REVIEWS_NO_PAYED") ),
						"delivery_status" => $arStatuses[ $arOrder["STATUS_ID"] ]
					),
					"order_lines" => array()
				);

				$rsBasket = CSaleBasket::GetList(
					array("SORT" => "ASC"),
					array("ORDER_ID" => $arOrder["ID"])
				);
				while($arOrderItem = $rsBasket->Fetch()) {
					$arFields["order_lines"][] = array(
						"title" => $arOrderItem["NAME"],
						"price" => $arOrderItem["PRICE"],
						"count" => $arOrderItem["QUANTITY"]
					);
				}

				$url = COption::GetOptionString("shoppilot.reviews", "shoppilot_api_endpoint");
				$short_url = $url;
				if( substr($url, -1)!="/" ) $url .= "/";
					$url .= $id;

				if( shoppilot_reviews_api::sendRequest($url, $arFields, "PUT")!="200" ) {
					shoppilot_reviews_api::sendRequest($short_url, $arFields, "POST");
				}
			}
		}
	}

	function OnOrderAdd($id, $data) {
		shoppilot_reviews_api::sendUpdate($id);
	}

	function OnOrderUpdate($id, $data) {
		shoppilot_reviews_api::sendUpdate($id);
	}
} ?>
