<?php

namespace Tirada;

use \Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use \Tirada\Options;


class Validator
{
    public static function initValidator() {

        if (!Loader::includeModule(Options::$moduleId)) {
            return false;
        }

        $arOptions = unserialize(\COption::GetOptionString(Options::$moduleId, 'OPTIONS'));

        if ($arOptions['VALIDATOR_ENABLE'] != 'Y') {
            return false;
        }
        \CJSCore::Init(array("jquery"));
        $asset = Asset::getInstance();
        $asset->addJs('/bitrix/js/tirada.validator/jquery.suggestions.min.js');
        $asset->addCss('/bitrix/css/tirada.validator/suggestions.min.css');
        $asset->addCss('/bitrix/css/tirada.validator/additional.css');
        $asset->addCss('/bitrix/css/tirada.validator/custom.css');

        $stringJs = self::makeValidateJs($arOptions);

        $asset->addString($stringJs);
    }

    public static function makeValidateJs($arOptions) {
        $token = $arOptions['TOKEN'];

        $leadRules = $dealRules = $contactRules = $companyRules = [];

        foreach ($arOptions as $code => $option) {
            if (strpos($code, 'LEAD_VALIDATE_') !== false) {
                if ($option != '-') {
                    $field = str_replace('LEAD_VALIDATE_', '', $code);
                    $leadRules[$field] = $option;
                }
            }
            if (strpos($code, 'DEAL_VALIDATE_') !== false) {
                if ($option != '-') {
                    $field = str_replace('DEAL_VALIDATE_', '', $code);
                    $dealRules[$field] = $option;
                }
            }
            if (strpos($code, 'CONTACT_VALIDATE_') !== false) {
                if ($option != '-') {
                    $field = str_replace('CONTACT_VALIDATE_', '', $code);
                    $contactRules[$field] = $option;
                }
            }
            if (strpos($code, 'COMPANY_VALIDATE_') !== false) {
                if ($option != '-') {
                    $field = str_replace('COMPANY_VALIDATE_', '', $code);
                    $companyRules[$field] = $option;
                }
            }
        }

        $leadRulesJs = $dealRulesJs = $contactRulesJs = $companyRulesJs = '';
        foreach ($leadRules as $fieldCode => $rule) {
            $leadRulesJs .= '
                $(document).on("click", "[data-cid=\'' . $fieldCode . '\'] input", function(){
                    $(this).suggestions(options_' . $rule . ');
                });';
        }
        foreach ($dealRules as $fieldCode => $rule) {
            $dealRulesJs .= '
                $(document).on("click", "[data-cid=\'' . $fieldCode . '\'] input", function(){
                    $(this).suggestions(options_' . $rule . ');
                });';
        }
        foreach ($contactRules as $fieldCode => $rule) {
            $contactRulesJs .= '
                $(document).on("click", "[data-cid=\'' . $fieldCode . '\'] input", function(){
                    $(this).suggestions(options_' . $rule . ');
                });';
        }
        foreach ($companyRules as $fieldCode => $rule) {
            $companyRulesJs .= '
                $(document).on("click", "[data-cid=\'' . $fieldCode . '\'] input", function(){
                    $(this).suggestions(options_' . $rule . ');
                });';
        }

        $stringJs = '<script>
            $(document).ready(function() {

                let options_COMPANY = {
                    token: "'.$token.'",
                    type: "PARTY",
                    count: 7,
                };
                
                let options_ADDRESS = {
                    token: "'.$token.'",
                    type: "ADDRESS",
                    count: 7,
                    constraints: {
                        locations: { '. ($arOptions['FOREIGN_COUNTRY'] == 'Y' ? 'country: "*"' : 'country_iso_code: "RU"') .' }
                    },
                };
                
                let entityType = $("[name=\'EDITOR_CONFIG_ID\']").val();
                
                if (entityType == "lead_details") {
                    ' . $leadRulesJs . '
                } else if (entityType == "deal_details") {
                    ' . $dealRulesJs . '
                } else if (entityType == "contact_details") {
                    ' . $contactRulesJs . '
                } else if (entityType == "company_details") {
                    ' . $companyRulesJs . '
                }           
                
            });
        </script>';

        return $stringJs;
    }
}