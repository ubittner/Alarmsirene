<?php

/**
 * @project       Alarmsirene/AlarmsireneHomematicIP
 * @file          module.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

declare(strict_types=1);

include_once __DIR__ . '/helper/ASIRHMIP_autoload.php';

class AlarmsireneHomematicIP extends IPSModule
{
    //Helper
    use ASIRHMIP_Config;
    use ASIRHMIP_Control;
    use ASIRHMIP_Signaling;
    use ASIRHMIP_TriggerCondition;

    //Constants
    private const MODULE_NAME = 'Alarmsirene Homematic IP';
    private const MODULE_PREFIX = 'ASIRHMIP';
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
        $this->RegisterPropertyBoolean('EnableAlarmSiren', true);
        $this->RegisterPropertyBoolean('EnableAcousticSignal', true);
        $this->RegisterPropertyBoolean('EnableOpticalSignal', true);
        $this->RegisterPropertyBoolean('EnableDurationUnit', true);
        $this->RegisterPropertyBoolean('EnableDurationValue', true);
        $this->RegisterPropertyInteger('DeviceType', 0);
        $this->RegisterPropertyInteger('DeviceInstance', 0);
        $this->RegisterPropertyInteger('DeviceStateAcousticAlarm', 0);
        $this->RegisterPropertyInteger('DeviceStateOpticalAlarm', 0);
        $this->RegisterPropertyInteger('SwitchingDelay', 0);
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

        //Alarm siren
        $id = @$this->GetIDForIdent('AlarmSiren');
        $this->RegisterVariableBoolean('AlarmSiren', 'Alarmsirene', '~Switch', 20);
        $this->EnableAction('AlarmSiren');
        if (!$id) {
            IPS_SetIcon($this->GetIDForIdent('AlarmSiren'), 'Alert');
        }

        //Acoustic signal
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AcousticSignal';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
            IPS_SetVariableProfileIcon($profile, 'Speaker');
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Kein akustisches Signal', '', -1);
        IPS_SetVariableProfileAssociation($profile, 1, 'Frequenz steigend', '', -1);
        IPS_SetVariableProfileAssociation($profile, 2, 'Frequenz fallend', '', -1);
        IPS_SetVariableProfileAssociation($profile, 3, 'Frequenz steigend/fallend', '', -1);
        IPS_SetVariableProfileAssociation($profile, 4, 'Frequenz tief/hoch', '', -1);
        IPS_SetVariableProfileAssociation($profile, 5, 'Frequenz tief/mittel/hoch', '', -1);
        IPS_SetVariableProfileAssociation($profile, 6, 'Frequenz hoch ein/aus', '', -1);
        IPS_SetVariableProfileAssociation($profile, 7, 'Frequenz hoch ein, lang aus', '', -1);
        IPS_SetVariableProfileAssociation($profile, 8, 'Frequenz tief ein/aus, hoch ein/aus', '', -1);
        IPS_SetVariableProfileAssociation($profile, 9, 'Frequenz tief ein - lang aus, hoch ein - lang aus', '', -1);
        IPS_SetVariableProfileAssociation($profile, 10, 'Batterie leer', '', -1);
        IPS_SetVariableProfileAssociation($profile, 11, 'Unscharf', '', -1);
        IPS_SetVariableProfileAssociation($profile, 12, 'Intern Scharf', '', -1);
        IPS_SetVariableProfileAssociation($profile, 13, 'Extern Scharf', '', -1);
        IPS_SetVariableProfileAssociation($profile, 14, 'Intern verzögert Scharf', '', -1);
        IPS_SetVariableProfileAssociation($profile, 15, 'Extern verzögert Scharf', '', -1);
        IPS_SetVariableProfileAssociation($profile, 16, 'Ereignis', '', -1);
        IPS_SetVariableProfileAssociation($profile, 17, 'Fehler', '', -1);
        $this->RegisterVariableInteger('AcousticSignal', 'Akustisches Signal', $profile, 30);
        $this->EnableAction('AcousticSignal');

        //Optical signal
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.OpticalSignal';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
            IPS_SetVariableProfileIcon($profile, 'Bulb');
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Kein optisches Signal', '', -1);
        IPS_SetVariableProfileAssociation($profile, 1, 'Abwechselndes langsames Blinken', '', -1);
        IPS_SetVariableProfileAssociation($profile, 2, 'Gleichzeitiges langsames Blinken', '', -1);
        IPS_SetVariableProfileAssociation($profile, 3, 'Gleichzeitiges schnelles Blinken', '', -1);
        IPS_SetVariableProfileAssociation($profile, 4, 'Gleichzeitiges kurzes Blinken', '', -1);
        IPS_SetVariableProfileAssociation($profile, 5, 'Bestätigungssignal 0 - lang lang', '', -1);
        IPS_SetVariableProfileAssociation($profile, 6, 'Bestätigungssignal 1 - lang kurz', '', -1);
        IPS_SetVariableProfileAssociation($profile, 7, 'Bestätigungssignal 2 - lang kurz kurz', '', -1);
        $this->RegisterVariableInteger('OpticalSignal', 'Optisches Signal', $profile, 40);
        $this->EnableAction('OpticalSignal');

        //Duration unit
        $id = @$this->GetIDForIdent('DurationUnit');
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.DurationUnit';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
            IPS_SetVariableProfileIcon($profile, 'Clock');
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Sekunden', '', -1);
        IPS_SetVariableProfileAssociation($profile, 1, 'Minuten', '', -1);
        IPS_SetVariableProfileAssociation($profile, 2, 'Stunden', '', -1);
        $this->RegisterVariableInteger('DurationUnit', 'Einheit Zeitdauer', $profile, 50);
        $this->EnableAction('DurationUnit');
        if (!$id) {
            $this->SetValue('DurationUnit', 0);
        }

        //Duration value
        $id = @$this->GetIDForIdent('DurationValue');
        $this->RegisterVariableInteger('DurationValue', 'Wert Zeitdauer', '', 60);
        $this->EnableAction('DurationValue');
        if (!$id) {
            @IPS_SetIcon(@$this->GetIDForIdent('DurationValue'), 'Hourglass');
            $this->SetValue('DurationValue', 5);
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
        $names[] = ['propertyName' => 'DeviceInstance', 'useUpdate' => false];
        $names[] = ['propertyName' => 'DeviceStateAcousticAlarm', 'useUpdate' => true];
        $names[] = ['propertyName' => 'DeviceStateOpticalAlarm', 'useUpdate' => true];

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
        IPS_SetHidden($this->GetIDForIdent('AlarmSiren'), !$this->ReadPropertyBoolean('EnableAlarmSiren'));
        IPS_SetHidden($this->GetIDForIdent('AcousticSignal'), !$this->ReadPropertyBoolean('EnableAcousticSignal'));
        IPS_SetHidden($this->GetIDForIdent('OpticalSignal'), !$this->ReadPropertyBoolean('EnableOpticalSignal'));
        IPS_SetHidden($this->GetIDForIdent('DurationUnit'), !$this->ReadPropertyBoolean('EnableDurationUnit'));
        IPS_SetHidden($this->GetIDForIdent('DurationValue'), !$this->ReadPropertyBoolean('EnableDurationValue'));

        $this->SetAutomaticDeactivationTimer();
        $this->CheckAutomaticDeactivationTimer();

        $this->SetTimerInterval('CheckDeviceState', 0);

        $this->CheckDeviceState();
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();

        //Delete profiles
        $profiles = ['AcousticSignal', 'OpticalSignal', 'DurationUnit'];
        if (!empty($profiles)) {
            foreach ($profiles as $profile) {
                $profileName = self::MODULE_PREFIX . '.' . $this->InstanceID . '.' . $profile;
                if (IPS_VariableProfileExists($profileName)) {
                    IPS_DeleteVariableProfile($profileName);
                }
            }
        }
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

                if ($SenderID == $this->ReadPropertyInteger('DeviceStateAcousticAlarm') || $SenderID == $this->ReadPropertyInteger('DeviceStateOpticalAlarm')) {
                    $this->CheckDeviceState();
                }

                if ($this->CheckMaintenance()) {
                    return;
                }

                //Check trigger conditions
                $valueChanged = 'false';
                if ($Data[1]) {
                    $valueChanged = 'true';
                }
                $scriptText = self::MODULE_PREFIX . '_CheckTriggerConditions(' . $this->InstanceID . ', ' . $SenderID . ', ' . $valueChanged . ');';
                @IPS_RunScriptText($scriptText);
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
                    $this->ToggleAlarmSiren(false);
                }
                break;

            case 'AlarmSiren':
                $this->ToggleAlarmSiren($Value);
                break;

            case 'AcousticSignal':
            case 'OpticalSignal':
            case 'DurationUnit':
            case 'DurationValue':
                $this->SetValue($Ident, $Value);
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