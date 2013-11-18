<?
global $MESS;

$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install.php"));

if (class_exists("shoppilot_reviews")) return;

Class shoppilot_reviews extends CModule {
	var $MODULE_ID = "shoppilot.reviews";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function shoppilot_reviews() //����������� ������ ������
	{
		//������ ��������, ��������, ������ � ���� ������
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = SHOPPILOT_REVIEWS_VERSION;
			$this->MODULE_VERSION_DATE = SHOPPILOT_REVIEWS_VERSION_DATE;
		}

		$this->PARTNER_NAME = GetMessage("SHOPPILOT_REVIEWS_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("SHOPPILOT_REVIEWS_PARTNER_URI");

		$this->MODULE_NAME = GetMessage("SHOPPILOT_REVIEWS_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SHOPPILOT_REVIEWS_INSTALL_DESCRIPTION");
	}

	//���������� ����������� ������
	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallDB();
		$this->InstallFiles();
		$GLOBALS["errors"] = $this->errors;

		if( count($errors)<=0 ):
			echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
		else:
			for($i=0; $i<count($errors); $i++) $alErrors .= $errors[$i]."<br>";
			echo CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_INST_ERR"), "DETAILS"=>$alErrors, "HTML"=>true));
		endif;

		?><form action="<?= $APPLICATION->GetCurPage() ?>">
		<p>
			<input type="hidden" name="lang" value="<?= LANG ?>">
			<input type="submit" name="" value="<?= GetMessage("MOD_BACK") ?>">
		</p>
		<form><?
	}

	//���������� �������� ������
	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("SHOPPILOT_REVIEWS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shoppilot.reviews/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("SHOPPILOT_REVIEWS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shoppilot.reviews/install/unstep2.php");
		}
	}

	//����������� ������ � ��
	function InstallDB()
	{
		$this->errors = array();

		if( !CModule::IncludeModule("sale") ) $this->errors[] = GetMessage("SHOPPILOT_NO_SALE_MODULE");

		RegisterModule("shoppilot.reviews");

		//������������� ��������� ������ �� ���������
		COption::SetOptionString("shoppilot.reviews", "shoppilot_api_auth_key", "");
		COption::SetOptionString("shoppilot.reviews", "shoppilot_api_endpoint", "https://shoppilot.ru/api/v2/orders");
		COption::SetOptionString("shoppilot.reviews", "shoppilot_pay_status", "Y");
		COption::SetOptionString("shoppilot.reviews", "shoppilot_order_status", "0");

		//������������ ����������� ������� OnOrderAdd
		RegisterModuleDependences("sale", "OnOrderAdd", "shoppilot.reviews", "shoppilot_reviews_api", "OnOrderAdd");
		//������������ ����������� ������� OnOrderUpdate
		RegisterModuleDependences("sale", "OnOrderUpdate", "shoppilot.reviews", "shoppilot_reviews_api", "OnOrderUpdate");

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		$this->errors = false;

		//������� �����������
		UnRegisterModuleDependences("sale", "OnOrderAdd", "shoppilot.reviews", "shoppilot_reviews_api", "OnOrderAdd");
		UnRegisterModuleDependences("sale", "OnOrderUpdate", "shoppilot.reviews", "shoppilot_reviews_api", "OnOrderUpdate");

		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			//������� ��������� ������
			COption::RemoveOption("shoppilot.reviews", "shoppilot_api_auth_key");
			COption::RemoveOption("shoppilot.reviews", "shoppilot_api_endpoint");
			COption::RemoveOption("shoppilot.reviews", "shoppilot_pay_status");
			COption::RemoveOption("shoppilot.reviews", "shoppilot_order_status");

			if($this->errors !== false)
			{
				$APPLICATION->ThrowException(implode("", $this->errors));
				return false;
			}
		}

		UnRegisterModule("shoppilot.reviews");

		return true;
	}


	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shoppilot.reviews/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", true, true);

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shoppilot.reviews/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");

		return true;
	}
}
?>
