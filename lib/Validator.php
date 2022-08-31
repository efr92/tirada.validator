<?php

namespace Tirada;

use \Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use \Tirada\Options;


class Validator
{
    protected static $options;

    public static function initValidator() {

        if (!Loader::includeModule(Options::$moduleId)) {
            return false;
        }

        self::$options = unserialize(\COption::GetOptionString(Options::$moduleId, 'OPTIONS'));

        if (self::$options['VALIDATOR_ENABLE'] != 'Y') {
            return false;
        }

        if (self::$options['JQUERY_ENABLE'] == 'Y') {
            \CJSCore::Init(array("jquery"));
        }

        $asset = Asset::getInstance();
        $asset->addJs('/bitrix/js/tirada.validator/jquery.suggestions.min.js', true);
        $asset->addJs('/bitrix/js/tirada.validator/jquery.inputmask.min.js', true);
        $asset->addJs('/bitrix/js/tirada.validator/jquery.validate.min.js', true);
        $asset->addCss('/bitrix/css/tirada.validator/suggestions.min.css');
        $asset->addCss('/bitrix/css/tirada.validator/additional.css');
        $asset->addCss('/bitrix/css/tirada.validator/custom.css');

        $stringJs = self::makeValidateJs();

        $asset->addString($stringJs);
    }

    public static function makeValidateJs() {
        $token = self::$options['TOKEN'];

        $leadRules = $dealRules = $contactRules = $companyRules = [];

        foreach (self::$options as $code => $option) {
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

        $leadRulesJs = self::getCrmValidateJs($leadRules);
        $dealRulesJs = self::getCrmValidateJs($dealRules);
        $contactRulesJs = self::getCrmValidateJs($contactRules);
        $companyRulesJs = self::getCrmValidateJs($companyRules);

        $stringJs = '<script>
            $(document).ready(function() {
                
                $("form").each(function () {
                    $(this).validate();
                });
                
                $("[name=\'user_name\']").rules("add", {
                    required: true,
                    messages: {
                        required: "Обязательное поле"
                    }
                });
                
                $("[name=\'user_email\']").rules("add", {
                    required: true,
                    email: true,
                    messages: {
                        required: "Обязательное поле",
                        email: "Введите корректный E-mail адрес"
                    }
                });
                
                $("[data-cid=\'EMAIL\' input[type=\'text\']").rules("add", {
                    required: true,
                    email: true,
                    messages: {
                        required: "Обязательное поле",
                        email: "Введите корректный E-mail адрес"
                    }
                });

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
                        locations: { '. (self::$options['FOREIGN_COUNTRY'] == 'Y' ? 'country: "*"' : 'country_iso_code: "RU"') .' }
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

    private static function getCrmValidateJs($rules) {
        $ruleJs = '';
        foreach ($rules as $fieldCode => $rule) {
            if($rule == 'PHONE') {
                $ruleJs .= '$("[data-cid=\'' . $fieldCode . '\'] input").inputmask('
                    . ((self::$options['PHONE_MASK']) ? self::$options['PHONE_MASK'] : '"+7(999)-999-99-99"') . '); ';
            } elseif($rule == 'ADDRESS' || $rule == 'COMPANY') {
                $ruleJs .= '
                $(document).on("focus", "[data-cid=\'' . $fieldCode . '\'] input", function(){
                    $(this).suggestions(options_' . $rule . ');
                });';
            }
        }
        return $ruleJs;
    }
}