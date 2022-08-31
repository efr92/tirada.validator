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

global $APPLICATION;

$APPLICATION->SetAdditionalCSS('/bitrix/css/tirada.validator/admin.css');

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

$arOrderTabs = [];
if (Loader::includeModule('sale')) {
    $arOrderTabs = [
        [
            'DIV' => 'order',
            'TAB' => Loc::getMessage('TV_TAB_ORDER'),
            'TITLE' => Loc::getMessage('TV_TAB_ORDER'),
        ],
    ];
}

$arTabs = array_merge(
    [[
        'DIV'   => 'main',
        'TAB'   => Loc::getMessage('TV_TAB_MAIN'),
        'ICON'  => 'connection_settings',
        'TITLE' => Loc::getMessage('TV_TAB_MAIN'),
    ]],
    $arCrmTabs,
    $arOrderTabs,
    [[
        'DIV'   => 'attr',
        'TAB'   => Loc::getMessage('TV_TAB_ATTR'),
        'TITLE' => Loc::getMessage('TV_TAB_ATTR'),
    ]],
);

$k = 0;
$arGroups = [
    'MAIN'     => ['TITLE' => Loc::getMessage('TV_GROUP_MAIN'), 'TAB' => $k],
    'DADATA'   => ['TITLE' => 'Dadata', 'TAB' => $k],
];

if (Loader::includeModule('crm')) {
    $arGroups = array_merge($arGroups, [
        'LEAD'     => ['TITLE' => Loc::getMessage('TV_GROUP_VALIDATION_RULE'), 'TAB' => ++$k],
        'DEAL'     => ['TITLE' => Loc::getMessage('TV_GROUP_VALIDATION_RULE'), 'TAB' => ++$k],
        'CONTACT'  => ['TITLE' => Loc::getMessage('TV_GROUP_VALIDATION_RULE'), 'TAB' => ++$k],
        'COMPANY'  => ['TITLE' => Loc::getMessage('TV_GROUP_VALIDATION_RULE'), 'TAB' => ++$k],
    ]);
}

if (Loader::includeModule('sale')) {
    $arGroups = array_merge($arGroups, [
        'ORDER'     => ['TITLE' => Loc::getMessage('TV_GROUP_VALIDATION_RULE'), 'TAB' => ++$k],
    ]);
}

$arGroups = array_merge($arGroups, [
    'ATTR'     => ['TAB' => ++$k],
]);

$arOptions = [
    'VALIDATOR_ENABLE' => [
        'GROUP' => 'MAIN',
        'TITLE' => Loc::getMessage('TV_ENABLE_MODULE'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '10',
    ],
    'JQUERY_ENABLE' => [
        'GROUP' => 'MAIN',
        'TITLE' => Loc::getMessage('TV_ENABLE_JQUERY'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '20',
    ],
    'PHONE_MASK' => [
        'GROUP' => 'MAIN',
        'TITLE' => Loc::getMessage('TV_PHONE_MASK'),
        'TYPE' => 'STRING',
        'SIZE' => '50',
        'DEFAULT' => '',
        'SORT' => '30',
    ],
    'FOREIGN_COUNTRY' => [
        'GROUP' => 'DADATA',
        'TITLE' => Loc::getMessage('TV_FOREIGN_COUNTRY'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '50',
    ],
    'TOKEN' => [
        'GROUP' => 'DADATA',
        'TITLE' => Loc::getMessage('TV_TOKEN'),
        'TYPE' => 'STRING',
        'SIZE' => '50',
        'DEFAULT' => '',
        'SORT' => '70',
        'NOTES' => Loc::getMessage('TV_TOKEN_NOTE'),
    ],
];

$validateOptions = [
    'REFERENCE' => [
        '-',
        Loc::getMessage('TV_RULE_PHONE'),
        Loc::getMessage('TV_RULE_ADDRESS'),
        Loc::getMessage('TV_RULE_COMPANY'),
    ],
    'REFERENCE_ID' => [
        '-',
        'PHONE',
        'ADDRESS',
        'COMPANY'
    ]
];

$ruleOptions = [
    'REFERENCE' => [Loc::getMessage('TV_HTML_ATTR'), Loc::getMessage('TV_JQUERY_SELECTOR')],
    'REFERENCE_ID' => ['ATTR', 'JQUERY']
];

$arOptions['ATTR_VALIDATE'] = [
    'GROUP' => 'ATTR',
    'TYPE' => 'CUSTOM',
    'SORT' => 1,
    'RULES' => $ruleOptions,
    'VALUES' => $validateOptions,
];

if (Loader::includeModule('crm')) {
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
}

$opt = new Options($arTabs, $arGroups, $arOptions, false);
$opt->ShowHTML();
