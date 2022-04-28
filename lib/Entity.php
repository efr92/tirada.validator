<?php

namespace Tirada;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

class Entity
{
    static $leadFields = [
        'TITLE',
        'NAME',
        'LAST_NAME',
        'SECOND_NAME',
        'COMPANY_TITLE',
        'POST',
        'ADDRESS',
    ];

    static $dealFields = [
        'TITLE'
    ];

    static $contactFields = [
        'NAME',
        'LAST_NAME',
        'SECOND_NAME',
        'POST',
        'PHONE',
        'EMAIL',
        'WEB',
        'ADDRESS',
    ];

    static $companyFields = [
        'TITLE',
        'PHONE',
        'EMAIL',
        'WEB',
        'ADDRESS',
    ];

    static public function getLeadFields()
    {
        $arLeadFields = [];

        if (!Loader::includeModule('crm')) {
            return false;
        }

        foreach (self::$leadFields as $oneField) {
            $arLeadFields[$oneField] = Loc::getMessage('LEAD_FIELD_' . $oneField);
        }

        $dbFields = \CUserTypeEntity::GetList([], ["ENTITY_ID" => "CRM_LEAD", "LANG" => LANGUAGE_ID]);
        while ($userField = $dbFields->Fetch()) {
            if ($userField['USER_TYPE_ID'] == 'string') {
                $arLeadFields[$userField['FIELD_NAME']] = $userField['EDIT_FORM_LABEL'] ? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME'];
            }
        }

        return $arLeadFields;
    }

    static public function getDealFields()
    {
        $arDealFields = [];

        if (!Loader::includeModule('crm')) {
            return false;
        }

        foreach (self::$dealFields as $oneField) {
            $arDealFields[$oneField] = Loc::getMessage('DEAL_FIELD_' . $oneField);
        }

        $dbFields = \CUserTypeEntity::GetList([], ["ENTITY_ID" => "CRM_DEAL", "LANG" => LANGUAGE_ID]);
        while ($userField = $dbFields->Fetch()) {
            if ($userField['USER_TYPE_ID'] == 'string') {
                $arDealFields[$userField['FIELD_NAME']] = $userField['EDIT_FORM_LABEL'] ? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME'];
            }
        }

        return $arDealFields;
    }

    static public function getContactFields()
    {
        $arContactFields = [];

        if (!Loader::includeModule('crm')) {
            return false;
        }

        foreach (self::$contactFields as $oneField) {
            $arContactFields[$oneField] = Loc::getMessage('CONTACT_FIELD_' . $oneField);
        }

        $dbFields = \CUserTypeEntity::GetList([], ["ENTITY_ID" => "CRM_CONTACT", "LANG" => LANGUAGE_ID]);
        while ($userField = $dbFields->Fetch()) {
            if ($userField['USER_TYPE_ID'] == 'string') {
                $arContactFields[$userField['FIELD_NAME']] = $userField['EDIT_FORM_LABEL'] ? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME'];
            }
        }

        return $arContactFields;
    }

    static public function getCompanyFields()
    {
        $arCompanyFields = [];

        if (!Loader::includeModule('crm')) {
            return false;
        }

        foreach (self::$companyFields as $oneField) {
            $arCompanyFields[$oneField] = Loc::getMessage('COMPANY_FIELD_' . $oneField);
        }

        $dbFields = \CUserTypeEntity::GetList([], ["ENTITY_ID" => "CRM_COMPANY", "LANG" => LANGUAGE_ID]);
        while ($userField = $dbFields->Fetch()) {
            if ($userField['USER_TYPE_ID'] == 'string') {
                $arCompanyFields[$userField['FIELD_NAME']] = $userField['EDIT_FORM_LABEL'] ? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME'];
            }
        }

        return $arCompanyFields;
    }
}