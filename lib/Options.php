<?php

namespace Tirada;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

class Options
{
    public $arCurOptionValues = array();

    public static $moduleId = 'tirada.validator';
    protected $arTabs;
    protected $arGroups;
    protected $arOptions;
    protected $need_access_tab;

    public function __construct($arTabs, $arGroups, $arOptions, $need_access_tab = false)
    {
        $this->arTabs = $arTabs;
        $this->arGroups = $arGroups;
        $this->arOptions = $arOptions;
        $this->need_access_tab = $need_access_tab;

        if($need_access_tab)
            $this->arTabs[] = array(
                'DIV' => 'edit_access_tab',
                'TAB' => Loc::getMessage('RIGHTS_TAB'),
                'ICON' => '',
                'TITLE' => Loc::getMessage('RIGHTS_TAB_TITLE')
            );

        if($_REQUEST['update'] == 'Y' && check_bitrix_sessid()){
            $this->SaveOptions();
            if($this->need_access_tab)
            {
                $this->SaveGroupRight();
            }

            $this->GetCurOptionValues();
        } else {
            $this->GetCurOptionValues();
        }
    }

    protected function SaveOptions()
    {
        $arSaveOptions = [];

        foreach($this->arOptions as $opt => $arOptParams)
        {
            if($arOptParams['TYPE'] != 'CUSTOM')
            {
                $val = $_REQUEST[$opt];

                if($arOptParams['TYPE'] == 'CHECKBOX' && $val != 'Y')
                    $val = 'N';
                elseif(is_array($val))
                    $val = serialize($val);

                $arSaveOptions[$opt] = $val;
            } else {
                if (count($_REQUEST['CUSTOM_RULE_PATTERN']) > 0) {
                    $arSaveOptions['CUSTOM_RULE_PATTERN'] = $_REQUEST['CUSTOM_RULE_PATTERN'];
                    $arSaveOptions['CUSTOM_RULE_VALUE'] = $_REQUEST['CUSTOM_RULE_VALUE'];
                    $arSaveOptions['CUSTOM_RULE_RULE'] = $_REQUEST['CUSTOM_RULE_RULE'];
                }
            }
        }

        if(!empty($arSaveOptions)) {
            $options = serialize($arSaveOptions);
            \COption::SetOptionString(self::$moduleId, 'OPTIONS', $options);
        }
    }

    protected function SaveGroupRight()
    {
        \CMain::DelGroupRight(self::$moduleId);
        $GROUP = $_REQUEST['GROUPS'];
        $RIGHT = $_REQUEST['RIGHTS'];

        foreach($GROUP as $k => $v) {
            if($k == 0) {
                \COption::SetOptionString(self::$moduleId, 'GROUP_DEFAULT_RIGHT', $RIGHT[0], 'Right for groups by default');
            }
            else {
                \CMain::SetGroupRight(self::$moduleId, $GROUP[$k], $RIGHT[$k]);
            }
        }


    }

    protected function GetCurOptionValues()
    {
        $arGetOptions = unserialize(\COption::GetOptionString(self::$moduleId, 'OPTIONS'));

        foreach($this->arOptions as $opt => $arOptParams)
        {
            if($arOptParams['TYPE'] != 'CUSTOM')
            {
                $this->arCurOptionValues[$opt] = $arGetOptions[$opt];
                if(in_array($arOptParams['TYPE'], array('MSELECT')))
                    $this->arCurOptionValues[$opt] = unserialize($this->arCurOptionValues[$opt]);
            } else {
                $this->arCurOptionValues['CUSTOM_RULE_PATTERN'] = $arGetOptions['CUSTOM_RULE_PATTERN'];
                $this->arCurOptionValues['CUSTOM_RULE_VALUE'] = $arGetOptions['CUSTOM_RULE_VALUE'];
                $this->arCurOptionValues['CUSTOM_RULE_RULE'] = $arGetOptions['CUSTOM_RULE_RULE'];
            }
        }
    }

    public function ShowHTML()
    {
        global $APPLICATION;

        $arP = array();

        foreach($this->arGroups as $group_id => $group_params)
            $arP[$group_params['TAB']][$group_id] = array();

        if(is_array($this->arOptions))
        {
            foreach($this->arOptions as $option => $arOptParams)
            {
                $val = $this->arCurOptionValues[$option];

                if($arOptParams['SORT'] < 0 || !isset($arOptParams['SORT']))
                    $arOptParams['SORT'] = 0;

                $label = (isset($arOptParams['TITLE']) && $arOptParams['TITLE'] != '') ? $arOptParams['TITLE'] : '';
                $opt = htmlspecialchars($option);

                switch($arOptParams['TYPE'])
                {
                    case 'CHECKBOX':
                        $input = '<input type="checkbox" name="'.$opt.'" id="'.$opt.'" value="Y"'.($val == 'Y' ? ' checked' : '').' '.($arOptParams['REFRESH'] == 'Y' ? 'onclick="document.forms[\''.self::$moduleId.'\'].submit();"' : '').' />';
                        break;
                    case 'TEXT':
                        if(!isset($arOptParams['COLS']))
                            $arOptParams['COLS'] = 25;
                        if(!isset($arOptParams['ROWS']))
                            $arOptParams['ROWS'] = 5;
                        $input = '<textarea rows="'.$arOptParams["ROWS"].'" cols="'.$arOptParams['COLS'].'" rows="'.$arOptParams['ROWS'].'" name="'.$opt.'">'.htmlspecialchars($val).'</textarea>';
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'SELECT':
                        $input = SelectBoxFromArray($opt, $arOptParams['VALUES'], $val, '', '', ($arOptParams['REFRESH'] == 'Y'), ($arOptParams['REFRESH'] == 'Y' ? self::$moduleId : ''));
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'MSELECT':
                        $input = SelectBoxMFromArray($opt.'[]', $arOptParams['VALUES'], $val, '', false, $arOptParams['SIZE'] ?: 5);
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'COLORPICKER':
                        if(!isset($arOptParams['FIELD_SIZE']))
                            $arOptParams['FIELD_SIZE'] = 25;
                        ob_start();
                        echo 	'<input id="__CP_PARAM_'.$opt.'" name="'.$opt.'" size="'.$arOptParams['FIELD_SIZE'].'" value="'.htmlspecialchars($val).'" type="text" style="float: left;" '.($arOptParams['FIELD_READONLY'] == 'Y' ? 'readonly' : '').' />
                                <script>
                                    function onSelect_'.$opt.'(color, objColorPicker)
                                    {
                                        var oInput = BX("__CP_PARAM_'.$opt.'");
                                        oInput.value = color;
                                    }
                                </script>';
                        $APPLICATION->IncludeComponent('bitrix:main.colorpicker', '', Array(
                            'SHOW_BUTTON' => 'Y',
                            'ID' => $opt,
                            'NAME' => Loc::getMessage('SELECT_COLOR'),
                            'ONSELECT' => 'onSelect_'.$opt
                        ), false
                        );
                        $input = ob_get_clean();
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'FILE':
                        if(!isset($arOptParams['FIELD_SIZE']))
                            $arOptParams['FIELD_SIZE'] = 25;
                        if(!isset($arOptParams['BUTTON_TEXT']))
                            $arOptParams['BUTTON_TEXT'] = '...';
                        \CAdminFileDialog::ShowScript(Array(
                            'event' => 'BX_FD_'.$opt,
                            'arResultDest' => Array('FUNCTION_NAME' => 'BX_FD_ONRESULT_'.$opt),
                            'arPath' => Array(),
                            'select' => 'F',
                            'operation' => 'O',
                            'showUploadTab' => true,
                            'showAddToMenuTab' => false,
                            'fileFilter' => '',
                            'allowAllFiles' => true,
                            'SaveConfig' => true
                        ));
                        $input = 	'<input id="__FD_PARAM_'.$opt.'" name="'.$opt.'" size="'.$arOptParams['FIELD_SIZE'].'" value="'.htmlspecialchars($val).'" type="text" style="float: left;" '.($arOptParams['FIELD_READONLY'] == 'Y' ? 'readonly' : '').' />
                                    <input value="'.$arOptParams['BUTTON_TEXT'].'" type="button" onclick="window.BX_FD_'.$opt.'();" />
                                    <script>
                                        setTimeout(function(){
                                            if (BX("bx_fd_input_'.strtolower($opt).'"))
                                                BX("bx_fd_input_'.strtolower($opt).'").onclick = window.BX_FD_'.$opt.';
                                        }, 200);
                                        window.BX_FD_ONRESULT_'.$opt.' = function(filename, filepath)
                                        {
                                            var oInput = BX("__FD_PARAM_'.$opt.'");
                                            if (typeof filename == "object")
                                                oInput.value = filename.src;
                                            else
                                                oInput.value = (filepath + "/" + filename).replace(/\/\//ig, \'/\');
                                        }
                                    </script>';
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'CUSTOM':
                        $curRules = "";
                        foreach($this->arCurOptionValues['CUSTOM_RULE_PATTERN'] as $key => $oneRule) {
                            if(empty($oneRule) || $this->arCurOptionValues['CUSTOM_RULE_RULE'][$key] == '-') {
                                continue;
                            }

                            if(!empty($this->arCurOptionValues['CUSTOM_RULE_VALUE'][$key])) {
                                $curRules .= '<tr class="rule-group">
                                                <td class="adm-detail-content-cell-l">
                                                    <label>' . Loc::getMessage('ATTR_NAME') . '</label><br>
                                                    <input name="CUSTOM_RULE_PATTERN[]" type="text" value="' . $oneRule . '">
                                                </td>
                                                <td class="adm-detail-content-cell-r">
                                                    <span>=</span>
                                                    <span class="rule-input">
                                                        <label>' . Loc::getMessage('ATTR_VALUE') . '</label><br>
                                                        <input name="CUSTOM_RULE_VALUE[]" type="text" value="' . $this->arCurOptionValues['CUSTOM_RULE_VALUE'][$key] . '">
                                                    </span>
                                                    <span class="rule-input">
                                                        <label>' . Loc::getMessage('VALIDATE_RULE') . '</label><br>
                                                        ' . SelectBoxFromArray('CUSTOM_RULE_RULE[]', $arOptParams['VALUES'], $this->arCurOptionValues['CUSTOM_RULE_RULE'][$key], '', '', ($arOptParams['REFRESH'] == 'Y'), ($arOptParams['REFRESH'] == 'Y' ? self::$moduleId : '')) . '
                                                    </span>
                                                </td>
                                              </tr>';
                            } else {

                            }
                        }


                        $input = '<div class="rule-add-block">' . SelectBoxFromArray($opt, $arOptParams['RULES'], $val, '', '', ($arOptParams['REFRESH'] == 'Y'), ($arOptParams['REFRESH'] == 'Y' ? self::$moduleId : ''));
                        $input .= '&nbsp;<input id="add-rule" type="button" value="Добавить" /></div>
                                   <script>
                                       BX.bind(BX("add-rule"), "click", function(e) { 
                                           e.preventDefault();
                                           let type = document.querySelector("#ATTR_VALIDATE").value;
                                           if (type == "ATTR") {
                                               let input = BX.create("tr", {
                                                   props: {
                                                      className: "rule-group"
                                                   },
                                                   children: [
                                                       BX.create({
                                                            tag: "td",
                                                            props: {
                                                               className: "adm-detail-content-cell-l"
                                                            },
                                                            children: [
                                                              BX.create({
                                                                 tag: "label",
                                                                 text: "' . Loc::getMessage('ATTR_NAME') . '"
                                                              }),
                                                              BX.create({
                                                                 tag: "br",
                                                              }),
                                                              BX.create({
                                                                 tag: "input",
                                                                 attrs: {
                                                                    name: "CUSTOM_RULE_PATTERN[]",
                                                                    type: "text",
                                                                 },
                                                              }),
                                                           ]
                                                       }),
                                                       BX.create({
                                                            tag: "td",
                                                            props: {
                                                               className: "adm-detail-content-cell-r"
                                                            },
                                                            children: [
                                                              BX.create({
                                                                 tag: "span",
                                                                 text: "=",
                                                              }),
                                                              BX.create({
                                                                 tag: "span",
                                                                 props: {
                                                                    className: "rule-input"
                                                                 },
                                                                 children: [
                                                                    BX.create({
                                                                       tag: "label",
                                                                       text: "' . Loc::getMessage('ATTR_VALUE') . '"
                                                                    }),
                                                                    BX.create({
                                                                       tag: "br",
                                                                    }),
                                                                    BX.create({
                                                                       tag: "input",
                                                                       attrs: {
                                                                          name: "CUSTOM_RULE_VALUE[]",
                                                                          type: "text",
                                                                       },
                                                                    }), 
                                                                 ]
                                                              }),                                                              
                                                              BX.create({   
                                                                 tag: "span",
                                                                 props: {
                                                                    className: "rule-input"
                                                                 },
                                                                 html: \'<label>' . Loc::getMessage('VALIDATE_RULE') . '</label><br>'. SelectBoxFromArray('', $arOptParams['VALUES'], $val, '', '', ($arOptParams['REFRESH'] == 'Y'), ($arOptParams['REFRESH'] == 'Y' ? self::$moduleId : '')) .'\'
                                                              }),
                                                           ]
                                                       }),
                                                       
                                                   ]                                                  
                                               });
                                               BX.insertBefore(input, this.closest("tr"));
                                               let rules = document.querySelectorAll(".rule-group select");
                                               rules[rules.length- 1].setAttribute("name", "CUSTOM_RULE_RULE[]");
                                           }
                                       });
                                   </script>';
                        break;
                    default:
                        if(!isset($arOptParams['SIZE']))
                            $arOptParams['SIZE'] = 25;
                        if(!isset($arOptParams['MAXLENGTH']))
                            $arOptParams['MAXLENGTH'] = 255;
                        $input = '<input type="'.($arOptParams['TYPE'] == 'INT' ? 'number' : 'text').'" size="'.$arOptParams['SIZE'].'" maxlength="'.$arOptParams['MAXLENGTH'].'" value="'.htmlspecialchars($val).'" name="'.htmlspecialchars($option).'" />';
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                }

                if(isset($arOptParams['NOTES']) && $arOptParams['NOTES'] != '')
                    $input .= 	'<div class="notes">
                                    <table cellspacing="0" cellpadding="0" border="0" class="notes">
                                        <tbody>
                                            <tr class="top">
                                                <td class="left"><div class="empty"></div></td>
                                                <td><div class="empty"></div></td>
                                                <td class="right"><div class="empty"></div></td>
                                            </tr>
                                            <tr>
                                                <td class="left"><div class="empty"></div></td>
                                                <td class="content">
                                                    '.$arOptParams['NOTES'].'
                                                </td>
                                                <td class="right"><div class="empty"></div></td>
                                            </tr>
                                            <tr class="bottom">
                                                <td class="left"><div class="empty"></div></td>
                                                <td><div class="empty"></div></td>
                                                <td class="right"><div class="empty"></div></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>';

                $arP[$this->arGroups[$arOptParams['GROUP']]['TAB']][$arOptParams['GROUP']]['OPTIONS'][] =
                    $label != '' ? ($arOptParams['TYPE'] == 'CUSTOM' ? $curRules : '') . '<tr><td valign="top" width="40%">'.$label.'</td><td valign="top" nowrap>'.$input.'</td></tr>'
                              : ($arOptParams['TYPE'] == 'CUSTOM' ? $curRules : '') . '<tr><td valign="top" colspan="2" align="center">'.$input.'</td></tr>';
                $arP[$this->arGroups[$arOptParams['GROUP']]['TAB']][$arOptParams['GROUP']]['OPTIONS_SORT'][] = $arOptParams['SORT'];
            }

            $tabControl = new \CAdminTabControl('tabControl', $this->arTabs);
            $tabControl->Begin();
            echo '<form name="'.self::$moduleId.'" method="POST" action="'.$APPLICATION->GetCurPage().'?mid='.self::$moduleId.'&lang='.LANGUAGE_ID.'" enctype="multipart/form-data">'.bitrix_sessid_post();

            foreach($arP as $tab => $groups)
            {
                $tabControl->BeginNextTab();

                foreach($groups as $group_id => $group)
                {
                    if(sizeof($group['OPTIONS_SORT']) > 0)
                    {
                        if(!empty($this->arGroups[$group_id]['TITLE']))
                        {
                            echo '<tr class="heading"><td colspan="2">'.$this->arGroups[$group_id]['TITLE'].'</td></tr>';
                        }

                        array_multisort($group['OPTIONS_SORT'], $group['OPTIONS']);
                        foreach($group['OPTIONS'] as $opt)
                            echo $opt;
                    }
                }
            }

            if($this->need_access_tab)
            {
                $tabControl->BeginNextTab();
                $moduleId = self::$moduleId;
                require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
            }

            $tabControl->Buttons();

            echo 	'<input type="hidden" name="update" value="Y" />
                    <input type="submit" name="save" value="'.Loc::getMessage('BUTTON_SAVE').'" />
                    <input type="reset" name="reset" value="'.Loc::getMessage('BUTTON_CANCEL').'" />
                    </form>';

            $tabControl->End();
        }
    }
}