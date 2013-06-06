<?
IncludeModuleLangFile(__FILE__);

$module_id = "shoppilot.reviews";
$CAT_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($CAT_RIGHT >= "R"):

global $MESS;

CModule::IncludeModule("sale");

include_once($GLOBALS["DOCUMENT_ROOT"]."/bitrix/modules/shoppilot.reviews/include.php");

if($REQUEST_METHOD=="POST" && strlen($Update)>0 && $CAT_RIGHT=="W" && check_bitrix_sessid()) {
	
	COption::SetOptionString("shoppilot.reviews", "shoppilot_api_auth_key", $shoppilot_api_auth_key);
	COption::SetOptionString("shoppilot.reviews", "shoppilot_api_endpoint", $shoppilot_api_endpoint);
	COption::SetOptionString("shoppilot.reviews", "shoppilot_pay_status", $shoppilot_pay_status);
	COption::SetOptionString("shoppilot.reviews", "shoppilot_order_status", $shoppilot_order_status);
	
	if($_REQUEST["back_url_settings"] <> "" && $_REQUEST["Apply"] == "")
		echo '<script type="text/javascript">window.location="'.CUtil::JSEscape($_REQUEST["back_url_settings"]).'";</script>';
	
} else {
	$shoppilot_api_auth_key = COption::GetOptionString("shoppilot.reviews", "shoppilot_api_auth_key");
	$shoppilot_api_endpoint = COption::GetOptionString("shoppilot.reviews", "shoppilot_api_endpoint");
	$shoppilot_pay_status = COption::GetOptionString("shoppilot.reviews", "shoppilot_pay_status");
	$shoppilot_order_status = COption::GetOptionString("shoppilot.reviews", "shoppilot_order_status");
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SHOPPILOT_REVIEWS_TAB_COMMON"), "TITLE" => GetMessage("SHOPPILOT_REVIEWS_TAB_TITLE_COMMON")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$tabControl->Begin(); ?>

<style>
	div.clear {
		clear:both;
	}
	
	div.options_form {
		margin:20px 0 0 0;
	}
	
	div.options_form div.option_field {
		margin:15px 0;
	}
	
	div.options_form div.option_field div.name  {
		margin-bottom:2px;
	}
	
	div.options_form div.option_field div.value  {
		float:left;
	}
	
	div.options_form div.option_field div.remark  {
		float:left;
		padding:5px;
	}
</style>

<form method="POST">
<?=bitrix_sessid_post();?>
<input type="hidden" name="Update" value="Y">

<? $tabControl->BeginNextTab(); ?>

<tr><td colspan="2">

<a href="<?= GetMessage("SHOPPILOT_REVIEWS_OPTIONS_ABOUT_LINK_URL") ?>" target="_blank"><?= GetMessage("SHOPPILOT_REVIEWS_OPTIONS_ABOUT_LINK_NAME") ?></a>

<div class="options_form">
	
    <div class="option_field">
    	<div class="name"><?= GetMessage("SHOPPILOT_REVIEWS_AUTH_KEY") ?></div>
        <div class="value"><input type="text" name="shoppilot_api_auth_key" value="<?= $shoppilot_api_auth_key ?>" style="width:300px" /></div>
        <div class="remark"><a href="<?= GetMessage("SHOPPILOT_REVIEWS_OPTIONS_GET_AUTH_LINK_URL") ?>" target="_blank"><?= GetMessage("SHOPPILOT_REVIEWS_OPTIONS_GET_AUTH_LINK_NAME") ?></a></div>
        <div class="clear"></div>
    </div>
    
    
    <div class="option_field">
        <div class="name"><?= GetMessage("SHOPPILOT_REVIEWS_API_ENDPOINT") ?></div>
        <div class="value"><input type="text" name="shoppilot_api_endpoint" value="<?= $shoppilot_api_endpoint ?>" style="width:300px" /></div>
        <div class="clear"></div>
    </div>
    
    
    <div class="option_field">
        <div class="name"><?= GetMessage("SHOPPILOT_REVIEWS_ORDER_STATUS") ?></div>
        <div class="value">
        	<select name="shoppilot_order_status" style="width:300px">
            	<option value="0"><?= GetMessage("SHOPPILOT_REVIEWS_NO_MATTER") ?></option>
            	<?
					$rsStatus = CSaleStatus::GetList(
						array("SORT" => "ASC"),
						array("LID" => trim(LANGUAGE_ID)!="" ? LANGUAGE_ID : SITE_ID)
					);
					while($arStatus = $rsStatus->Fetch()):
                ?><option value="<?= $arStatus["ID"] ?>"<?= $shoppilot_order_status==$arStatus["ID"] ? ' selected="selected"' : '' ?>><?= $arStatus["NAME"] ?></option>
                <? endwhile; ?>
            </select>
        </div>
        <div class="clear"></div>
    </div>
    
    
    <div class="option_field">
    	<div class="name"><?= GetMessage("SHOPPILOT_REVIEWS_PAY_STATUS") ?></div>
        <div class="value">
        	<select name="shoppilot_pay_status" style="width:300px">
            	<option value="0"><?= GetMessage("SHOPPILOT_REVIEWS_NO_MATTER") ?></option>
                <option value="Y"<?= $shoppilot_pay_status=="Y" ? ' selected="selected"' : '' ?>><?= GetMessage("SHOPPILOT_REVIEWS_PAYED") ?></option>
                <option value="N"<?= $shoppilot_pay_status=="N" ? ' selected="selected"' : '' ?>><?= GetMessage("SHOPPILOT_REVIEWS_NO_PAYED") ?></option>
            </select>
        </div>
        <div class="clear"></div>
    </div>
    
</div>

<p><a href="<?= GetMessage("SHOPPILOT_REVIEWS_OPTIONS_USERPAGE_LINK_URL") ?>" target="_blank"><?= GetMessage("SHOPPILOT_REVIEWS_OPTIONS_USERPAGE_LINK_NAME") ?></a></p>

</td></tr>

<? $tabControl->Buttons(); ?>

<input <? if($CAT_RIGHT<"W") echo "disabled" ?> type="submit" name="Apply" value="<? echo GetMessage("SHOPPILOT_REVIEWS_APPLY")?>" title="<? echo GetMessage("SHOPPILOT_REVIEWS_OPT_APPLY_TITLE")?>">

<? $tabControl->End(); ?>
</form>

<? endif; ?>
