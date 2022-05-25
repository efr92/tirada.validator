<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Tirada\Options;
use Tirada\Entity;

Loc::loadLanguageFile(__FILE__);

$module_id = 'tirada.validator';

if (!Loader::includeModule($module_id)) {
    echo 'NO MODULE ' . $module_id;
    return;
}

$arTabs = [
    [
        'DIV'   => 'main',
        'TAB'   => Loc::getMessage('TV_TAB_MAIN'),
        'ICON'  => 'connection_settings',
        'TITLE' => Loc::getMessage('TV_TAB_MAIN'),
    ],
    [
        'DIV'   => 'attr',
        'TAB'   => Loc::getMessage('TV_TAB_ATTR'),
        'TITLE' => Loc::getMessage('TV_TAB_ATTR'),
    ],
];

$arCrmTabs = [];
if (Loader::includeModule('crm')) {
    $arCrmTabs = [
        [
            'DIV' => 'lead',
            'TAB' => Loc::getMessage('TV_TAB_LEAD'),
            'TITLE' => Loc::getMessage('TV_TAB_LEAD'),
        ],
        [
            'DIV' => 'deal',
            'TAB' => Loc::getMessage('TV_TAB_DEAL'),
            'TITLE' => Loc::getMessage('TV_TAB_DEAL'),
        ],
        [
            'DIV' => 'contact',
            'TAB' => Loc::getMessage('TV_TAB_CONTACT'),
            'TITLE' => Loc::getMessage('TV_TAB_CONTACT'),
        ],
        [
            'DIV' => 'company',
            'TAB' => Loc::getMessage('TV_TAB_COMPANY'),
            'TITLE' => Loc::getMessage('TV_TAB_COMPANY'),
        ],
    ];
}

$arTabs = array_merge($arTabs, $arCrmTabs);

$arGroups = [
    'MAIN'     => ['TITLE' => Loc::getMessage('TV_GROUP_MAIN'), 'TAB' => 0],
    'DADATA'   => ['TITLE' => 'Dadata', 'TAB' => 0],
    'LEAD'     => ['TITLE' => Loc::getMessage('TV_GROUP_VALIDATION_RULE'), 'TAB' => 1],
    'DEAL'     => ['TITLE' => Loc::getMessage('TV_GROUP_VALIDATION_RULE'), 'TAB' => 2],
    'CONTACT'  => ['TITLE' => Loc::getMessage('TV_GROUP_VALIDATION_RULE'), 'TAB' => 3],
    'COMPANY'  => ['TITLE' => Loc::getMessage('TV_GROUP_VALIDATION_RULE'), 'TAB' => 4],
];

$arOptions = [
    'VALIDATOR_ENABLE' => [
        'GROUP' => 'MAIN',
        'TITLE' => Loc::getMessage('TV_ENABLE_MODULE'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '10',
    ],
    'FOREIGN_COUNTRY' => [
        'GROUP' => 'DADATA',
        'TITLE' => Loc::getMessage('TV_FOREIGN_COUNTRY'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '20',
    ],
    'TOKEN' => [
        'GROUP' => 'DADATA',
        'TITLE' => Loc::getMessage('TV_TOKEN'),
        'TYPE' => 'STRING',
        'SIZE' => '50',
        'DEFAULT' => '',
        'SORT' => '30',
    ],
];

$validateOptions = [
    'REFERENCE' => ['(нет)', 'Адрес (подсказки dadata)', 'Название компании (подсказки dadata)'],
    'REFERENCE_ID' => ['-', 'ADDRESS', 'COMPANY']
];

$leadFields = Entity::getLeadFields();
$sort = 1;
foreach ($leadFields as $leadCode => $leadField) {
    $arOptions['LEAD_VALIDATE_' . $leadCode] = [
        'GROUP' => 'LEAD',
        'TITLE' => $leadField,
        'TYPE' => 'SELECT',
        'VALUES' => $validateOptions,
        'SORT' => $sort,
    ];
    $sort++;
}

$dealFields = Entity::getDealFields();
$sort = 1;
foreach ($dealFields as $dealCode => $dealField) {
    $arOptions['DEAL_VALIDATE_' . $dealCode] = [
        'GROUP' => 'DEAL',
        'TITLE' => $dealField,
        'TYPE' => 'SELECT',
        'VALUES' => $validateOptions,
        'SORT' => $sort,
    ];
    $sort++;
}

$contactFields = Entity::getContactFields();
$sort = 1;
foreach ($contactFields as $contactCode => $contactField) {
    $arOptions['CONTACT_VALIDATE_' . $contactCode] = [
        'GROUP' => 'CONTACT',
        'TITLE' => $contactField,
        'TYPE' => 'SELECT',
        'VALUES' => $validateOptions,
        'SORT' => $sort,
    ];
    $sort++;
}

$companyFields = Entity::getCompanyFields();
$sort = 1;
foreach ($companyFields as $companyCode => $companyField) {
    $arOptions['COMPANY_VALIDATE_' . $companyCode] = [
        'GROUP' => 'COMPANY',
        'TITLE' => $companyField,
        'TYPE' => 'SELECT',
        'VALUES' => $validateOptions,
        'SORT' => $sort,
    ];
    $sort++;
}

$opt = new Options($arTabs, $arGroups, $arOptions, false);
$opt->ShowHTML();
