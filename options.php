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
        'TAB'   => 'Основные',
        'ICON'  => 'connection_settings',
        'TITLE' => 'Основные',
    ],
    [
        'DIV'   => 'lead',
        'TAB'   => 'Лид',
        'ICON'  => 'connection_settings',
        'TITLE' => 'Лид',
    ],
    [
        'DIV'   => 'deal',
        'TAB'   => 'Сделка',
        'ICON'  => 'connection_settings',
        'TITLE' => 'Сделка',
    ],
    [
        'DIV'   => 'contact',
        'TAB'   => 'Контакты',
        'ICON'  => 'connection_settings',
        'TITLE' => 'Контакты',
    ],
    [
        'DIV'   => 'company',
        'TAB'   => 'Компании',
        'ICON'  => 'connection_settings',
        'TITLE' => 'Компании',
    ],
];

$arGroups = [
    'MAIN'     => ['TITLE' => 'Основные', 'TAB' => 0],
    'DADATA'   => ['TITLE' => 'Dadata', 'TAB' => 0],
    'LEAD'     => ['TITLE' => 'Правила валидации', 'TAB' => 1],
    'DEAL'     => ['TITLE' => 'Правила валидации', 'TAB' => 2],
    'CONTACT'  => ['TITLE' => 'Правила валидации', 'TAB' => 3],
    'COMPANY'  => ['TITLE' => 'Правила валидации', 'TAB' => 4],
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
