<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Class tirada_validator extends CModule
{
    const MODULE_ID = 'tirada.validator';
    var $MODULE_ID = 'tirada.validator';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;

    function __construct()
    {
        $arModuleVersion = array();

        include(__DIR__.'/version.php');

        if (!empty($arModuleVersion['VERSION']))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = Loc::getMessage("TIRADA_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TIRADA_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("TIRADA_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("TIRADA_PARTNER_URI");
    }

    function InstallDB($arParams = array())
    {
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        return true;
    }

    function UnInstallDB($arParams = array())
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible("main", "OnProlog", $this->MODULE_ID, "\\Tirada\\Validator","initValidator");

        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler("main", "OnProlog", $this->MODULE_ID, "\\Tirada\\Validator","initValidator");
        return true;
    }

    function InstallFiles($arParams = array())
    {
        CopyDirFiles(__DIR__."/../assets/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$this->MODULE_ID."/");
        CopyDirFiles(__DIR__."/../assets/css/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/css/".$this->MODULE_ID."/");
        return true;
    }

    function UnInstallFiles()
    {
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$this->MODULE_ID."/");
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/bitrix/css/".$this->MODULE_ID."/");
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        $this->InstallEvents();
    }

    function DoUninstall()
    {
        global $APPLICATION;
        $this->UnInstallEvents();
        $this->UnInstallDB();
        $this->UnInstallFiles();
    }
}
