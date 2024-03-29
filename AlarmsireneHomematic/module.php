<?php

/**
 * @project       Alarmsirene/AlarmsireneHomematic/
 * @file          module.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

declare(strict_types=1);

include_once __DIR__ . '/helper/ASIRHM_autoload.php';

class AlarmsireneHomematic extends IPSModule
{
    //Helper
    use ASIRHM_AlarmProtocol;
    use ASIRHM_ConfigurationForm;
    use ASIRHM_Control;
    use ASIRHM_Signaling;
    use ASIRHM_TriggerCondition;

    //Constants
    private const LIBRARY_GUID = '{FAA4CD97-9BA5-6415-C57C-EB2BD5B1E143}';
    private const MODULE_GUID = '{90C71DCF-603A-A5C8-7955-CB3FFC4FF58C}';
    private const MODULE_NAME = 'Alarmsirene Homematic';
    private const MODULE_PREFIX = 'ASIRHM';
    private const MODULE_VERSION = '7.0-3, 25.03.2023';
    private const ABLAUFSTEUERUNG_MODULE_GUID = '{0559B287-1052-A73E-B834-EBD9B62CB938}';
    private const ABLAUFSTEUERUNG_MODULE_PREFIX = 'AST';
    private const ALARMPROTOCOL_MODULE_GUID = '{66BDB59B-E80F-E837-6640-005C32D5FC24}';

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        ########## Properties

        ##### Info
        $this->RegisterPropertyString('Note', '');

        ##### Device acoustic alarm
        $this->RegisterPropertyInteger('DeviceTypeAcousticAlarm', 0);
        $this->RegisterPropertyInteger('DeviceInstanceAcousticAlarm', 0);
        $this->RegisterPropertyInteger('DeviceStateAcousticAlarm', 0);
        $this->RegisterPropertyInteger('SwitchingDelayAcousticAlarm', 0);

        ##### Device optical alarm
        $this->RegisterPropertyInteger('DeviceTypeOpticalAlarm', 0);
        $this->RegisterPropertyInteger('DeviceInstanceOpticalAlarm', 0);
        $this->RegisterPropertyInteger('DeviceStateOpticalAlarm', 0);
        $this->RegisterPropertyInteger('SwitchingDelayOpticalAlarm', 0);

        ##### Device tone acknowledgement
        $this->RegisterPropertyInteger('DeviceTypeToneAcknowledgement', 0);
        $this->RegisterPropertyInteger('DeviceInstanceToneAcknowledgement', 0);
        $this->RegisterPropertyInteger('DeviceStateToneAcknowledgement', 0);
        $this->RegisterPropertyInteger('SwitchingDelayToneAcknowledgement', 0);

        ##### Alarm levels
        //Pre alarm
        $this->RegisterPropertyInteger('PreAlarmDuration', 30);
        $this->RegisterPropertyBoolean('UsePreAlarmAcousticAlarm', true);
        $this->RegisterPropertyInteger('PreAlarmAcousticDuration', 3);
        $this->RegisterPropertyBoolean('UsePreAlarmOpticalAlarm', true);
        $this->RegisterPropertyInteger('PreAlarmOpticalDuration', 30);
        //Main alarm
        $this->RegisterPropertyInteger('MainAlarmDuration', 180);
        $this->RegisterPropertyInteger('MaximumSignallingAmountAcousticAlarm', 3);
        $this->RegisterPropertyBoolean('UseMainAlarmAcousticAlarm', true);
        $this->RegisterPropertyInteger('MainAlarmAcousticDuration', 180);
        $this->RegisterPropertyBoolean('UseMainAlarmOpticalAlarm', true);
        $this->RegisterPropertyInteger('MainAlarmOpticalDuration', 180);
        //Post alarm
        $this->RegisterPropertyBoolean('UsePostAlarmOpticalAlarm', true);
        $this->RegisterPropertyInteger('PostAlarmOpticalDuration', 300);
        //Panic alarm
        $this->RegisterPropertyInteger('PanicAlarmDuration', 60);
        $this->RegisterPropertyBoolean('UsePanicAlarmAcousticAlarm', true);
        $this->RegisterPropertyInteger('PanicAlarmAcousticDuration', 60);
        $this->RegisterPropertyBoolean('UsePanicAlarmOpticalAlarm', true);
        $this->RegisterPropertyInteger('PanicAlarmOpticalDuration', 60);

        ##### Trigger list
        $this->RegisterPropertyString('TriggerList', '[]');

        ##### Command Control
        $this->RegisterPropertyInteger('CommandControl', 0);

        ##### Alarm protocol
        $this->RegisterPropertyInteger('AlarmProtocol', 0);
        $this->RegisterPropertyString('Location', '');

        ##### Automatic deactivation
        $this->RegisterPropertyBoolean('UseAutomaticDeactivation', false);
        $this->RegisterPropertyString('AutomaticDeactivationStartTime', '{"hour":22,"minute":0,"second":0}');
        $this->RegisterPropertyString('AutomaticDeactivationEndTime', '{"hour":6,"minute":0,"second":0}');

        ##### Visualisation
        $this->RegisterPropertyBoolean('EnableActive', false);
        $this->RegisterPropertyBoolean('EnableAlarmSiren', true);
        $this->RegisterPropertyBoolean('EnableAlarmLevel', true);
        $this->RegisterPropertyBoolean('EnableSignalingAmount', true);
        $this->RegisterPropertyBoolean('EnableResetSignalingAmount', true);
        $this->RegisterPropertyBoolean('EnableToneAcknowledgement', false);

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
            IPS_SetIcon(@$this->GetIDForIdent('AlarmSiren'), 'Alert');
        }

        //Alarm level
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.AlarmLevel';
        if (IPS_VariableProfileExists($profile)) {
            IPS_DeleteVariableProfile($profile);
        }
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
            IPS_SetVariableProfileIcon($profile, 'Rocket');
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Aus', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Voralarm', '', 0xFFFF00);
        IPS_SetVariableProfileAssociation($profile, 2, 'Hauptalarm', '', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 3, 'Nachalarm', '', 0xFF9500);
        IPS_SetVariableProfileAssociation($profile, 4, 'Panikalarm', '', 0xFF0000);
        $this->RegisterVariableInteger('AlarmLevel', 'Alarmstufe', $profile, 30);

        //Signalling amount
        $id = @$this->GetIDForIdent('SignallingAmount');
        $this->RegisterVariableInteger('SignallingAmount', 'Auslösungen', '', 40);
        if (!$id) {
            IPS_SetIcon(@$this->GetIDForIdent('SignallingAmount'), 'Warning');
        }

        //Reset signalling amount
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.ResetSignallingAmount';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Reset', 'Repeat', 0xFF0000);
        $this->RegisterVariableInteger('ResetSignallingAmount', 'Rückstellung', $profile, 50);
        $this->EnableAction('ResetSignallingAmount');

        ///Tone acknowledgement
        $profile = self::MODULE_PREFIX . '.' . $this->InstanceID . '.ToneAcknowledgement';
        if (!IPS_VariableProfileExists($profile)) {
            IPS_CreateVariableProfile($profile, 1);
            IPS_SetVariableProfileIcon($profile, 'Speaker');
        }
        IPS_SetVariableProfileAssociation($profile, 0, 'Alarm Aus', '', 0x00FF00);
        IPS_SetVariableProfileAssociation($profile, 1, 'Außensensoren scharf (intern scharf)', '', 0x0000FF);
        IPS_SetVariableProfileAssociation($profile, 2, 'Alle Sensoren scharf (extern scharf)', '', 0xFF0000);
        IPS_SetVariableProfileAssociation($profile, 3, 'Alarm blockiert', '', 0x00FFFF);
        $this->RegisterVariableInteger('ToneAcknowledgement', 'Quittungston', $profile, 60);
        $this->EnableAction('ToneAcknowledgement');

        ########## Timers

        $this->RegisterTimer('DeactivateAcousticAlarm', 0, self::MODULE_PREFIX . '_ToggleAcousticAlarm(' . $this->InstanceID . ', false);');
        $this->RegisterTimer('DeactivateOpticalAlarm', 0, self::MODULE_PREFIX . '_ToggleOpticalAlarm(' . $this->InstanceID . ', false);');
        $this->RegisterTimer('CheckNextAlarmLevel', 0, self::MODULE_PREFIX . '_CheckNextAlarmLevel(' . $this->InstanceID . ');');
        $this->RegisterTimer('ResetSignallingAmount', 0, self::MODULE_PREFIX . '_ResetSignallingAmount(' . $this->InstanceID . ');');
        $this->RegisterTimer('StartAutomaticDeactivation', 0, self::MODULE_PREFIX . '_StartAutomaticDeactivation(' . $this->InstanceID . ');');
        $this->RegisterTimer('StopAutomaticDeactivation', 0, self::MODULE_PREFIX . '_StopAutomaticDeactivation(' . $this->InstanceID . ',);');
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

        ############ References and messages

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
        $names[] = ['propertyName' => 'DeviceInstanceAcousticAlarm', 'useUpdate' => false];
        $names[] = ['propertyName' => 'DeviceInstanceOpticalAlarm', 'useUpdate' => false];
        $names[] = ['propertyName' => 'DeviceInstanceToneAcknowledgement', 'useUpdate' => false];
        $names[] = ['propertyName' => 'CommandControl', 'useUpdate' => false];
        $names[] = ['propertyName' => 'AlarmProtocol', 'useUpdate' => false];

        foreach ($names as $name) {
            $id = $this->ReadPropertyInteger($name['propertyName']);
            if ($id > 1 && @IPS_ObjectExists($id)) {
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
                        if ($id > 1 && @IPS_ObjectExists($id)) {
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
                                if ($id > 1 && @IPS_ObjectExists($id)) {
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
        IPS_SetHidden($this->GetIDForIdent('AlarmLevel'), !$this->ReadPropertyBoolean('EnableAlarmLevel'));
        IPS_SetHidden($this->GetIDForIdent('SignallingAmount'), !$this->ReadPropertyBoolean('EnableSignalingAmount'));
        IPS_SetHidden($this->GetIDForIdent('ResetSignallingAmount'), !$this->ReadPropertyBoolean('EnableResetSignalingAmount'));
        IPS_SetHidden($this->GetIDForIdent('ToneAcknowledgement'), !$this->ReadPropertyBoolean('EnableToneAcknowledgement'));

        //Reset
        $this->SetTimerInterval('DeactivateAcousticAlarm', 0);
        $this->SetTimerInterval('DeactivateOpticalAlarm', 0);
        $this->SetTimerInterval('CheckNextAlarmLevel', 0);
        $this->SetTimerInterval('ResetSignallingAmount', (strtotime('next day midnight') - time()) * 1000);
        $this->SetValue('SignallingAmount', 0);
        $this->SetAlarmLevel();

        $this->SetAutomaticDeactivationTimer();
        $this->CheckAutomaticDeactivationTimer();
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();

        //Delete profiles
        $profiles = ['AlarmLevel', 'ResetSignallingAmount', 'ToneAcknowledgement'];
        if (!empty($profiles)) {
            foreach ($profiles as $profile) {
                $profileName = self::MODULE_PREFIX . '.' . $this->InstanceID . '.' . $profile;
                $this->UnregisterProfile($profileName);
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
            $infoText = 'Instanz mit der ID ' . $id . ' wurde erfolgreich erstellt!';
        } else {
            $infoText = 'Instanz konnte nicht erstellt werden!';
        }
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
    }

    public function CreateAlarmProtocolInstance(): void
    {
        $id = @IPS_CreateInstance(self::ALARMPROTOCOL_MODULE_GUID);
        if (is_int($id)) {
            IPS_SetName($id, 'Alarmprotokoll');
            $infoText = 'Instanz mit der ID ' . $id . ' wurde erfolgreich erstellt!';
        } else {
            $infoText = 'Instanz konnte nicht erstellt werden!';
        }
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
    }

    public function UIShowMessage(string $Message): void
    {
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $Message);
    }

    #################### Request Action

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {

            case 'Active':
                if (!$Value) {
                    $this->SetAlarmLevel();
                }
                $this->SetValue($Ident, $Value);
                break;

            case 'AlarmSiren':
                $this->ToggleAlarmSiren($Value);
                break;

            case 'ResetSignallingAmount':
                $this->ResetSignallingAmount();
                break;

            case 'ToneAcknowledgement':
                $this->ExecuteToneAcknowledgement($Value);
                break;

        }
    }

    #################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function UnregisterProfile(string $Name): void
    {
        if (!IPS_VariableProfileExists($Name)) {
            return;
        }
        foreach (IPS_GetVariableList() as $VarID) {
            if (IPS_GetParent($VarID) == $this->InstanceID) {
                continue;
            }
            if (IPS_GetVariable($VarID)['VariableCustomProfile'] == $Name) {
                return;
            }
            if (IPS_GetVariable($VarID)['VariableProfile'] == $Name) {
                return;
            }
        }
        foreach (IPS_GetMediaListByType(MEDIATYPE_CHART) as $mediaID) {
            $content = json_decode(base64_decode(IPS_GetMediaContent($mediaID)), true);
            foreach ($content['axes'] as $axis) {
                if ($axis['profile' === $Name]) {
                    return;
                }
            }
        }
        IPS_DeleteVariableProfile($Name);
    }

    /**
     * Checks if the module is active or inactive.
     *
     * @return bool
     * false =  active
     * true =   inactive
     */
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