<?php
IncludeModuleLangFile(__FILE__);
use Bitrix\Highloadblock as HL;
if (class_exists('htc_sociallikes'))
{
    return;
}

Class htc_sociallikes extends CModule
{
    var $MODULE_ID = 'htc.sociallikes';
    var $HLBLOCK_NAME = 'sociallikes';
    var $ENTITY_ID = 'HLBLOCK_%s';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function htc_sociallikes()
    {
        error_reporting(E_WARNING);
        $arModuleVersion = array();

        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include($path . '/version.php');

        if (is_array($arModuleVersion)) {
            if (array_key_exists('VERSION', $arModuleVersion)) {
                $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            }

            if (array_key_exists('VERSION_DATE', $arModuleVersion)) {
                $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            }
        }

        $this->MODULE_NAME = GetMessage('HTC_SOCIALLIKES_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('HTC_SOCIALLIKES_MODULE_DESC');
        $this->PARTNER_NAME = GetMessage('HTC_SOCIALLIKES_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('HTC_SOCIALLIKES_PARTNER_URI');
    }

    /**
     * Устанавливает компоненты модуля
     * @return bool|void
     */
    function InstallFiles()
    {
        CopyDirFiles(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/components',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components',
            true,
            true
        );

        return true;
    }

    /**
     * Удаляет компоненты модуля
     * @return bool|void
     */
    function UnInstallFiles()
    {
        DeleteDirFilesEx('/bitrix/components/htc/sociallikesbx');

        return true;
    }

    /**
     * Добавляет предопределенные поля в HLBlock
     * @param $hlblockId
     * @param array $fieldsExcluded
     */
    private function addCustomFieldsInHlblock($hlblockId, $fieldsExcluded = array())
    {
        $customFields = array(
            'UF_USER_ID' => array(
                'FIELD_NAME' => 'UF_USER_ID',
                'USER_TYPE_ID'      => 'string',
                'EDIT_FORM_LABEL'   => array(
                    'RU' => GetMessage('HTC_CUSTOM_FIELD_NAME_RU_UF_USER_ID'),
                    'EN' => GetMessage('HTC_CUSTOM_FIELD_NAME_EN_UF_USER_ID'),
                ),
            ),
			'UF_USER_NAME' => array(
                'FIELD_NAME' => 'UF_USER_NAME',
                'USER_TYPE_ID'      => 'string',
                'EDIT_FORM_LABEL'   => array(
                    'RU' => GetMessage('HTC_CUSTOM_FIELD_NAME_RU_UF_USER_NAME'),
                    'EN' => GetMessage('HTC_CUSTOM_FIELD_NAME_EN_UF_USER_NAME'),
                ),
            ),
			'UF_WEB_USER_PAGE' => array(
                'FIELD_NAME' => 'UF_WEB_USER_PAGE',
                'USER_TYPE_ID'      => 'string',
                'EDIT_FORM_LABEL'   => array(
                    'RU' => GetMessage('HTC_CUSTOM_FIELD_NAME_RU_UF_WEB_USER_PAGE'),
                    'EN' => GetMessage('HTC_CUSTOM_FIELD_NAME_EN_UF_WEB_USER_PAGE'),
                ),
            ),
            'UF_ELEMENT_ID' => array(
                'FIELD_NAME' => 'UF_ELEMENT_ID',
                'USER_TYPE_ID'      => 'string',
                'EDIT_FORM_LABEL'   => array(
                    'RU' => GetMessage('HTC_CUSTOM_FIELD_NAME_RU_UF_ELEMENT_ID'),
                    'EN' => GetMessage('HTC_CUSTOM_FIELD_NAME_EN_UF_ELEMENT_ID'),
                ),
            ),
            'UF_DATE' => array(
                'FIELD_NAME' => 'UF_DATE',
                'USER_TYPE_ID'      => 'datetime',
                'EDIT_FORM_LABEL'   => array(
                    'RU' => GetMessage('HTC_CUSTOM_FIELD_NAME_RU_UF_DATE'),
                    'EN' => GetMessage('HTC_CUSTOM_FIELD_NAME_EN_UF_DATE'),
                ),
            ),
            'UF_SOCIAL_NETWORK' => array(
                'FIELD_NAME' => 'UF_SOCIAL_NETWORK',
                'USER_TYPE_ID'      => 'string',
                'EDIT_FORM_LABEL'   => array(
                    'RU' => GetMessage('HTC_CUSTOM_FIELD_NAME_RU_UF_SOCIAL_NETWORK'),
                    'EN' => GetMessage('HTC_CUSTOM_FIELD_NAME_EN_UF_SOCIAL_NETWORK'),
                ),
            ),
        );

        $userTypeEntity    = new CUserTypeEntity();

        foreach ($customFields as $fieldName => $customField)
        {
            if (count($fieldsExcluded) == 0 || in_array($fieldName, $fieldsExcluded)) {
                $dataUserFields = array(
                    'ENTITY_ID' => sprintf($this->ENTITY_ID, $hlblockId),
                    'FIELD_NAME' => $customField['FIELD_NAME'],
                    'USER_TYPE_ID' => $customField['USER_TYPE_ID'],
                    'MANDATORY' => 'N',
                    'SHOW_FILTER' => 'N',
                    'IS_SEARCHABLE' => 'N',

                    'EDIT_FORM_LABEL'   => array(
                        'ru'    => $customField['EDIT_FORM_LABEL']['RU'],
                        'en'    => $customField['EDIT_FORM_LABEL']['EN'],
                    ),

                    'LIST_COLUMN_LABEL' => array(
                        'ru'    => $customField['EDIT_FORM_LABEL']['RU'],
                        'en'    => $customField['EDIT_FORM_LABEL']['EN'],
                    ),

                    'LIST_FILTER_LABEL' => array(
                        'ru'    => $customField['EDIT_FORM_LABEL']['RU'],
                        'en'    => $customField['EDIT_FORM_LABEL']['EN'],
                    ),
                );

                $userTypeEntity->Add($dataUserFields);
            }
        }
    }

    /**
     * Добавляет HLBlock для хранения количества голосов пользователей за элементы
     */
    private function installHighloadblock()
    {
        if (CModule::IncludeModule('highloadblock')) {
            $selectedResultDB = HL\HighloadBlockTable::getList(
                array(
                    'filter' => array(
                        'TABLE_NAME' => $this->HLBLOCK_NAME
                    ),
                    'select' => array(
                        'ID'
                    ),
                ));
            $hlblock = $selectedResultDB->fetch();

            if (empty($hlblock['ID'])) {
                /**
                 * Если HLBlock не найден, то создаем новый
                 */
                $hlblockData = array(
                    'NAME' => ucfirst($this->HLBLOCK_NAME),
                    'TABLE_NAME' => $this->HLBLOCK_NAME
                );

                $result = HL\HighloadBlockTable::add($hlblockData);

                if ($result->isSuccess()) {
                    /**
                     * Добавляем пользовательские поля в HLBlock
                     */
                    $this->addCustomFieldsInHlblock($result->getId());
                }
                else {
                    global $APPLICATION;
                    $APPLICATION->ThrowException(GetMessage('HTC_ERROR_UNABLE_ADD_HIGHLOADBLOCK'));
                }
            }
            else {
                /**
                 * Если HLBlock найден, то проверяем наличие пользовательских полей и добавляем недостающие
                 */
                $userTypeEntity = array('UF_USER_ID', 'UF_USER_NAME', 'UF_WEB_USER_PAGE', 'UF_ELEMENT_ID', 'UF_DATE', 'UF_SOCIAL_NETWORK');

                $selectedResultUserTypeDB = CUserTypeEntity::GetList(
                    array(),
                    array(
                        'ENTITY_ID' => sprintf($this->ENTITY_ID, $hlblock['ID'])
                    ));
                while($field = $selectedResultUserTypeDB->Fetch()) {
                    if (in_array($field['FIELD_NAME'], $userTypeEntity)) {
                        $keys = array_keys($userTypeEntity, $field['FIELD_NAME']);
                        unset($userTypeEntity[$keys[0]]);
                    }
                }

                if (count($userTypeEntity) > 0) {
                    /**
                    * Добавляем пользовательские поля в HLBlock
                    */
                   $this->addCustomFieldsInHlblock($hlblock['ID'], $userTypeEntity);
                }
            }
        }
    }

    private function unInstallHlblock($saveHlblock)
    {
        if ($saveHlblock && CModule::IncludeModule('highloadblock')) {
            $selectedResultDB = HL\HighloadBlockTable::getList(
                array(
                    'filter' => array(
                        'TABLE_NAME' => $this->HLBLOCK_NAME
                    ),
                    'select' => array(
                        'ID'
                    ),
                ));

            $hlblock = $selectedResultDB->fetch();

            if (! empty($hlblock['ID'])) {
                HL\HighloadBlockTable::delete($hlblock['ID']);
            }
        }
    }

    function DoInstall()
    {
        global $APPLICATION;
        if (\Bitrix\Main\ModuleManager::isModuleInstalled('socialservices') && \Bitrix\Main\ModuleManager::isModuleInstalled('highloadblock')) {
            $this->InstallFiles();
            $this->installHighloadblock();

            RegisterModule( $this->MODULE_ID );

            $APPLICATION->IncludeAdminFile(
                GetMessage('HTC_SOCIALLIKES_INSTALL_TITLE'),
                $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/step.php'
            );
        }
        else {
            $APPLICATION->IncludeAdminFile(
                GetMessage('HTC_SOCIALLIKES_INSTALL_TITLE'),
                $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/error_not_connected_module.php'
            );
        }
    }

    function DoUninstall()
    {
        global $APPLICATION, $step;
        $step = intval($step);

        $this->unInstallHlblock('Y');

        $this->UnInstallFiles();

        UnRegisterModule( $this->MODULE_ID );
    }
}