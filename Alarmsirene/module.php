<?php

/**
 * @project       Alarmsirene/Alarmsirene
 * @file          module.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpRedundantMethodOverrideInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

declare(strict_types=1);

include_once __DIR__ . '/helper/ASIR_autoload.php';

class Alarmsirene extends IPSModule
{
    //Helper
    use ASIR_Config;
    use ASIR_Control;
    use ASIR_Signaling;
    use ASIR_TriggerCondition;

    //Constants
    private const MODULE_NAME = 'Alarmsirene';
    private const MODULE_PREFIX = 'ASIR';
    private const MODULE_VERSION = '7.0-1, 08.09.2022';
    private const ABLAUFSTEUERUNG_MODULE_GUID = '{0559B287-1052-A73E-B834-EBD9B62CB938}';
    private const ABLAUFSTEUERUNG_MODULE_PREFIX = 'AST';

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        ########## Properties

        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyBoolean('EnableActive', false);
        $this->RegisterPropertyBoolean('EnableAcousticAlarm', true);
        $this->RegisterPropertyBoolean('EnableOpticalAlarm', true);
        $this->RegisterPropertyInteger('AcousticAlarm', 0);
        $this->RegisterPropertyInteger('SwitchingDelayAcousticAlarm', 0);
        $this->RegisterPropertyInteger('OpticalAlarm', 0);
        $this->RegisterPropertyInteger('SwitchingDelayOpticalAlarm', 0);
        $this->RegisterPropertyInteger('CommandControl', 0);
        $this->RegisterPropertyString('TriggerList', '[]');
        $this->RegisterPropertyBoolean('UseAutomaticDeactivation', false);
        $this->RegisterPropertyString('AutomaticDeactivationStartTime', '{"hour":22,"minute":0,"second":0}');
        $this->RegisterPropertyString('AutomaticDeactivationEndTime', '{"hour":6,"minute":0,"second":0}');

        ########## Variables

        //Active
        $id = @$this->GetIDForIdent('Active');
        $this->RegisterVariableBoolean('Active', 'Aktiv', '~Switch', 10);
        $this->EnableAction('Active');
        if (!$id) {
            $this->SetValue('Active', true);
        }

        //Acoustic alarm
        $id = @$this->GetIDForIdent('AcousticAlarm');
        $this->RegisterVariableBoolean('AcousticAlarm', 'Akustischer Alarm', '~Switch', 20);
        $this->EnableAction('AcousticAlarm');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('AcousticAlarm'), 'Alert');
        }

        //Optical alarm
        $id = @$this->GetIDForIdent('OpticalAlarm');
        $this->RegisterVariableBoolean('OpticalAlarm', 'Optischer Alarm', '~Switch', 30);
        $this->EnableAction('OpticalAlarm');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('OpticalAlarm'), 'Bulb');
        }

        ########## Timers

        $this->RegisterTimer('StartAutomaticDeactivation', 0, self::MODULE_PREFIX . '_StartAutomaticDeactivation(' . $this->InstanceID . ');');
        $this->RegisterTimer('StopAutomaticDeactivation', 0, self::MODULE_PREFIX . '_StopAutomaticDeactivation(' . $this->InstanceID . ',);');
        $this->RegisterTimer('CheckDeviceState', 0, self::MODULE_PREFIX . '_CheckDeviceState(' . $this->InstanceID . ',);');
    }

    public function ApplyChanges()
    {
        //Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        //Never delete this line!
        parent::ApplyChanges();

        //Check runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        //Delete all references
        foreach ($this->GetReferenceList() as $referenceID) {
            $this->UnregisterReference($referenceID);
        }

        //Delete all update messages
        foreach ($this->GetMessageList() as $senderID => $messages) {
            foreach ($messages as $message) {
                if ($message == VM_UPDATE) {
                    $this->UnregisterMessage($senderID, VM_UPDATE);
                }
            }
        }

        //Register references and update messages
        $names = [];
        $names[] = ['propertyName' => 'AcousticAlarm', 'useUpdate' => true];
        $names[] = ['propertyName' => 'OpticalAlarm', 'useUpdate' => true];
        $names[] = ['propertyName' => 'CommandControl', 'useUpdate' => false];

        foreach ($names as $name) {
            $id = $this->ReadPropertyInteger($name['propertyName']);
            if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
                $this->RegisterReference($id);
                if ($name['useUpdate']) {
                    $this->RegisterMessage($id, VM_UPDATE);
                }
            }
        }

        $variables = json_decode($this->ReadPropertyString('TriggerList'), true);
        foreach ($variables as $variable) {
            if (!$variable['Use']) {
                continue;
            }
            //Primary condition
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
                            $this->RegisterReference($id);
                            $this->RegisterMessage($id, VM_UPDATE);
                        }
                    }
                }
            }
            //Secondary condition, multi
            if ($variable['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($variable['SecondaryCondition'], true);
                if (array_key_exists(0, $secondaryConditions)) {
                    if (array_key_exists('rules', $secondaryConditions[0])) {
                        $rules = $secondaryConditions[0]['rules']['variable'];
                        foreach ($rules as $rule) {
                            if (array_key_exists('variableID', $rule)) {
                                $id = $rule['variableID'];
                                if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
                                    $this->RegisterReference($id);
                                }
                            }
                        }
                    }
                }
            }
        }

        //WebFront options
        IPS_SetHidden($this->GetIDForIdent('Active'), !$this->ReadPropertyBoolean('EnableActive'));
        IPS_SetHidden($this->GetIDForIdent('AcousticAlarm'), !$this->ReadPropertyBoolean('EnableAcousticAlarm'));
        IPS_SetHidden($this->GetIDForIdent('OpticalAlarm'), !$this->ReadPropertyBoolean('EnableOpticalAlarm'));

        $this->SetAutomaticDeactivationTimer();
        $this->CheckAutomaticDeactivationTimer();

        $this->SetTimerInterval('CheckDeviceState', 0);

        $this->CheckDeviceState();
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug(__FUNCTION__, $TimeStamp . ', SenderID: ' . $SenderID . ', Message: ' . $Message . ', Data: ' . print_r($Data, true), 0);
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;

            case VM_UPDATE:

                //$Data[0] = actual value
                //$Data[1] = value changed
                //$Data[2] = last value
                //$Data[3] = timestamp actual value
                //$Data[4] = timestamp value changed
                //$Data[5] = timestamp last value

                $trigger = true;
                $names = ['AcousticAlarm', 'OpticalAlarm'];
                foreach ($names as $name) {
                    if ($SenderID == $this->ReadPropertyInteger($name)) {
                        $this->CheckDeviceState();
                        $trigger = false;
                    }
                }

                if ($this->CheckMaintenance()) {
                    return;
                }

                if ($trigger) {
                    //Check trigger conditions
                    $valueChanged = 'false';
                    if ($Data[1]) {
                        $valueChanged = 'true';
                    }
                    $scriptText = self::MODULE_PREFIX . '_CheckTriggerConditions(' . $this->InstanceID . ', ' . $SenderID . ', ' . $valueChanged . ');';
                    @IPS_RunScriptText($scriptText);
                }
                break;

        }
    }

    public function CreateCommandControlInstance(): void
    {
        $id = IPS_CreateInstance(self::ABLAUFSTEUERUNG_MODULE_GUID);
        if (is_int($id)) {
            IPS_SetName($id, 'Ablaufsteuerung');
            echo 'Instanz mit der ID ' . $id . ' wurde erfolgreich erstellt!';
        } else {
            echo 'Instanz konnte nicht erstellt werden!';
        }
    }

    #################### Request Action

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {

            case 'Active':
                $this->SetValue($Ident, $Value);
                if (!$Value) {
                    $this->ToggleAcousticAlarm(false);
                    $this->ToggleOpticalAlarm(false);
                }
                break;

            case 'AcousticAlarm':
                $this->ToggleAcousticAlarm($Value);
                break;

            case 'OpticalAlarm':
                $this->ToggleOpticalAlarm($Value);
                break;

        }
    }

    #################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function CheckMaintenance(): bool
    {
        $result = false;
        if (!$this->GetValue('Active')) {
            $this->SendDebug(__FUNCTION__, 'Abbruch, die Instanz ist inaktiv!', 0);
            $result = true;
        }
        return $result;
    }
}