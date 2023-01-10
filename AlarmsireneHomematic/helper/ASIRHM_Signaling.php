<?php

/**
 * @project       Alarmsirene/AlarmsireneHomematic
 * @file          ASIRHM_Signaling.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait ASIRHM_Signaling
{
    /**
     * Toggles the acoustic alarm off or on.
     *
     * @param bool $State
     * false =  Off
     * true =   On
     *
     * @return bool
     * @throws Exception
     */
    public function ToggleAcousticAlarm(bool $State): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $statusText = 'Aus';
        $value = 'false';
        if ($State) {
            $statusText = 'An';
            $value = 'true';
        }
        $this->SendDebug(__FUNCTION__, 'Status: ' . $statusText, 0);
        if ($State) {
            if ($this->CheckMaintenance()) {
                return false;
            }
        }
        $result = false;
        $id = $this->ReadPropertyInteger('DeviceInstanceAcousticAlarm');
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            $result = true;
            $actualValue = $this->GetValue('AcousticAlarm');
            $this->SetValue('AcousticAlarm', $State);
            switch ($this->ReadPropertyInteger('DeviceTypeAcousticAlarm')) {
                case 1: //HM-Sec-Sir-WM
                case 2: //HM-Sec-SFA-SM
                case 3: //HM-LC-Sw4-WM
                    IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayAcousticAlarm'));
                    $commandControl = $this->ReadPropertyInteger('CommandControl');
                    if ($commandControl > 1 && @IPS_ObjectExists($commandControl)) { //0 = main category, 1 = none
                        $commands = [];
                        $commands[] = '@HM_WriteValueBoolean(' . $id . ", 'STATE', " . $value . ');';
                        $this->SendDebug(__FUNCTION__, 'Befehl: ' . json_encode(json_encode($commands)), 0);
                        $scriptText = self::ABLAUFSTEUERUNG_MODULE_PREFIX . '_ExecuteCommands(' . $commandControl . ', ' . json_encode(json_encode($commands)) . ');';
                        $this->SendDebug(__FUNCTION__, 'Ablaufsteuerung: ' . $scriptText, 0);
                        $result = @IPS_RunScriptText($scriptText);
                    } else {
                        $parameter = @HM_WriteValueBoolean($id, 'STATE', $State);
                        if (!$parameter) {
                            $this->SendDebug(__FUNCTION__, 'Beim akustischen Alarm ist ein Fehler aufgetreten!', 0);
                            $this->SendDebug(__FUNCTION__, 'Der Schaltvorgang wird wiederholt.', 0);
                            IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayAcousticAlarm'));
                            $parameter = @HM_WriteValueBoolean($id, 'STATE', $State);
                            if (!$parameter) {
                                $result = false;
                            }
                        }
                    }
                    if (!$result) {
                        //Revert
                        $this->SetValue('AcousticAlarm', $actualValue);
                        $this->SendDebug(__FUNCTION__, 'Der akustische Alarm konnte für die Alarmsirene ID ' . $id . ' nicht erfolgreich geschaltet werden!', 0);
                        $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der akustische Alarm konnte für die Alarmsirene ID . ' . $id . ' nicht erfolgreich geschaltet werden!', KL_ERROR);
                    }
                    break;

            }
        }
        $this->SetTimerInterval('CheckDeviceState', 5000);
        return $result;
    }

    /**
     * Toggles the optical alarm off or on.
     *
     * @param bool $State
     * false =  Off
     * true =   On
     *
     * @return bool
     * @throws Exception
     */
    public function ToggleOpticalAlarm(bool $State): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $statusText = 'Aus';
        $value = 'false';
        if ($State) {
            $statusText = 'An';
            $value = 'true';
        }
        $this->SendDebug(__FUNCTION__, 'Status: ' . $statusText, 0);
        if ($State) {
            if ($this->CheckMaintenance()) {
                return false;
            }
        }
        $result = false;
        $id = $this->ReadPropertyInteger('DeviceInstanceOpticalAlarm');
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            $result = true;
            $actualValue = $this->GetValue('OpticalAlarm');
            $this->SetValue('OpticalAlarm', $State);
            switch ($this->ReadPropertyInteger('DeviceTypeOpticalAlarm')) {
                case 1: //HM-Sec-SFA-SM
                case 2: //HM-LC-Sw4-WM
                    IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayOpticalAlarm'));
                    $commandControl = $this->ReadPropertyInteger('CommandControl');
                    if ($commandControl > 1 && @IPS_ObjectExists($commandControl)) { //0 = main category, 1 = none
                        $commands = [];
                        $commands[] = '@HM_WriteValueBoolean(' . $id . ", 'STATE', " . $value . ');';
                        $this->SendDebug(__FUNCTION__, 'Befehl: ' . json_encode(json_encode($commands)), 0);
                        $scriptText = self::ABLAUFSTEUERUNG_MODULE_PREFIX . '_ExecuteCommands(' . $commandControl . ', ' . json_encode(json_encode($commands)) . ');';
                        $this->SendDebug(__FUNCTION__, 'Ablaufsteuerung: ' . $scriptText, 0);
                        $result = @IPS_RunScriptText($scriptText);
                    } else {
                        $parameter = @HM_WriteValueBoolean($id, 'STATE', $State);
                        if (!$parameter) {
                            $this->SendDebug(__FUNCTION__, 'Beim optischen Alarm ist ein Fehler aufgetreten!', 0);
                            $this->SendDebug(__FUNCTION__, 'Der Schaltvorgang wird wiederholt.', 0);
                            IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayOpticalAlarm'));
                            $parameter = @HM_WriteValueBoolean($id, 'STATE', $State);
                            if (!$parameter) {
                                $result = false;
                            }
                        }
                    }
                    if (!$result) {
                        //Revert
                        $this->SetValue('OpticalAlarm', $actualValue);
                        $this->SendDebug(__FUNCTION__, 'Der optische Alarm konnte für die Alarmsirene ID ' . $id . ' nicht erfolgreich geschaltet werden!', 0);
                        $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der optische Alarm konnte für die Alarmsirene ID . ' . $id . ' nicht erfolgreich geschaltet werden!', KL_ERROR);
                    }
                    break;

                default: //HM-Sec-Sir-WM
                    return false;

            }
        }
        $this->SetTimerInterval('CheckDeviceState', 5000);
        return $result;
    }

    /**
     * Executes the tone acknowledgement.
     *
     * @param int $Value
     * 0 =  Alarm Off
     * 1 =  Internally armed
     * 2 =  Externally armed
     * 3 =  Alarm blocked
     *
     * @return bool
     * @throws Exception
     */
    public function ExecuteToneAcknowledgement(int $Value): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SendDebug(__FUNCTION__, 'Wert: ' . $Value, 0);
        if ($this->CheckMaintenance()) {
            return false;
        }
        $result = false;
        $id = $this->ReadPropertyInteger('DeviceInstanceToneAcknowledgement');
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            $result = true;
            $actualValue = $this->GetValue('ToneAcknowledgement');
            $this->SetValue('ToneAcknowledgement', $Value);
            switch ($this->ReadPropertyInteger('DeviceTypeToneAcknowledgement')) {
                case 1: //HM-Sec-Sir-WM
                    $commandControl = $this->ReadPropertyInteger('CommandControl');
                    if ($commandControl > 1 && @IPS_ObjectExists($commandControl)) { //0 = main category, 1 = none
                        $commands = [];
                        $commands[] = '@HM_WriteValueInteger(' . $id . ", 'ARMSTATE', " . $Value . ');';
                        $this->SendDebug(__FUNCTION__, 'Befehl: ' . json_encode(json_encode($commands)), 0);
                        $scriptText = self::ABLAUFSTEUERUNG_MODULE_PREFIX . '_ExecuteCommands(' . $commandControl . ', ' . json_encode(json_encode($commands)) . ');';
                        $this->SendDebug(__FUNCTION__, 'Ablaufsteuerung: ' . $scriptText, 0);
                        $result = @IPS_RunScriptText($scriptText);
                    } else {
                        IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayToneAcknowledgement'));
                        $parameter = @HM_WriteValueInteger($id, 'ARMSTATE', $Value);
                        if (!$parameter) {
                            $this->SendDebug(__FUNCTION__, 'Beim Quittungston ist ein Fehler aufgetreten!', 0);
                            $this->SendDebug(__FUNCTION__, 'Der Schaltvorgang wird wiederholt.', 0);
                            IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayToneAcknowledgement'));
                            $parameter = @HM_WriteValueInteger($id, 'ARMSTATE', $Value);
                            if (!$parameter) {
                                $result = false;
                            }
                        }
                    }
                    if (!$result) {
                        //Revert
                        $this->SetValue('ToneAcknowledgement', $actualValue);
                        $this->SendDebug(__FUNCTION__, 'Der Quittungston konnte für die Alarmsirene ID ' . $id . ' nicht erfolgreich geschaltet werden!', 0);
                        $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der Quittungston konnte für die Alarmsirene ID . ' . $id . ' nicht erfolgreich geschaltet werden!', KL_ERROR);
                    }
                    break;

                default: //HM-Sec-SFA-SM, HM-LC-Sw4-WM
                    return false;

            }
        }
        return $result;
    }

    /**
     * Checks the device status.
     *
     * @throws Exception
     */
    public function CheckDeviceState(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SetTimerInterval('CheckDeviceState', 0);
        $acousticAlarm = $this->ReadPropertyInteger('DeviceStateAcousticAlarm');
        if ($acousticAlarm > 1 && @IPS_ObjectExists($acousticAlarm)) { //0 = main category, 1 = none
            $this->SetValue('AcousticAlarm', GetValue($acousticAlarm));
        }
        $opticalAlarm = $this->ReadPropertyInteger('DeviceStateOpticalAlarm');
        if ($opticalAlarm > 1 && @IPS_ObjectExists($opticalAlarm)) { //0 = main category, 1 = none
            $this->SetValue('OpticalAlarm', GetValue($opticalAlarm));
        }
        $toneAcknowledgement = $this->ReadPropertyInteger('DeviceStateToneAcknowledgement');
        if ($toneAcknowledgement > 1 && @IPS_ObjectExists($toneAcknowledgement)) { //0 = main category, 1 = none
            $this->SetValue('ToneAcknowledgement', GetValue($toneAcknowledgement));
        }
    }
}