<?php

/**
 * @project       Alarmsirene/Alarmsirene
 * @file          ASIR_Signaling.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpVoidFunctionResultUsedInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait ASIR_Signaling
{
    /**
     * Toggles the alarm siren off or on.
     *
     * @param bool $State
     * false =  Off
     * true =   On
     *
     * @return void
     * @throws Exception
     */
    public function ToggleAlarmSiren(bool $State): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        //Check for an existing alarm siren
        $existing = false;
        $acousticSignal = $this->ReadPropertyInteger('AcousticAlarm');
        if ($acousticSignal > 1 && @IPS_ObjectExists($acousticSignal)) {
            $existing = true;
        }
        $opticalSignal = $this->ReadPropertyInteger('OpticalAlarm');
        if ($opticalSignal > 1 && @IPS_ObjectExists($opticalSignal)) {
            $existing = true;
        }
        if (!$existing) {
            $this->SendDebug(__FUNCTION__, 'Abbruch, es ist keine Alarmsirene vorhanden!', 0);
            return;
        }
        //Turn alarm siren off
        if (!$State) {
            $this->SetAlarmLevel();
        }
        //Turn alarm siren on
        else {
            //Check condition
            if ($this->CheckMaintenance()) {
                return;
            }
            if (!$this->CheckSignallingAmount()) {
                return;
            }
            if ($this->GetValue('AlarmLevel') != 0) {
                return;
            }
            //Check pre alarm
            $usePreAlarm = false;
            if ($this->ReadPropertyBoolean('UsePreAlarmAcousticAlarm') || $this->ReadPropertyBoolean('UsePreAlarmOpticalAlarm')) {
                $usePreAlarm = true;
                $this->SetAlarmLevel(1);
            }
            //Check main alarm
            $useMainAlarm = false;
            if ($this->ReadPropertyBoolean('UseMainAlarmAcousticAlarm') || $this->ReadPropertyBoolean('UseMainAlarmOpticalAlarm')) {
                $useMainAlarm = true;
            }
            if (!$usePreAlarm && $useMainAlarm) {
                $this->SetAlarmLevel(2);
            }
            //Check post alarm
            $usePostAlarm = $this->ReadPropertyBoolean('UsePostAlarmOpticalAlarm');
            if (!$usePreAlarm && !$useMainAlarm && $usePostAlarm) {
                $this->SetAlarmLevel(3);
            }
        }
    }

    /**
     * Toggles the acoustic alarm off or on.
     *
     * @param bool $State
     * false =  Off
     * true =   On
     *
     * @return void
     * @throws Exception
     */
    public function ToggleAcousticAlarm(bool $State): void
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
                return;
            }
        }
        $id = $this->ReadPropertyInteger('AcousticAlarm');
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayAcousticAlarm'));
            $commandControl = $this->ReadPropertyInteger('CommandControl');
            if ($commandControl > 1 && @IPS_ObjectExists($commandControl)) { //0 = main category, 1 = none
                $commands = [];
                $commands[] = '@RequestAction(' . $id . ', ' . $value . ');';
                $this->SendDebug(__FUNCTION__, 'Befehl: ' . json_encode(json_encode($commands)), 0);
                $scriptText = self::ABLAUFSTEUERUNG_MODULE_PREFIX . '_ExecuteCommands(' . $commandControl . ', ' . json_encode(json_encode($commands)) . ');';
                $this->SendDebug(__FUNCTION__, 'Ablaufsteuerung: ' . $scriptText, 0);
                @IPS_RunScriptText($scriptText);
            } else {
                $parameter = @RequestAction($id, $State);
                if (!$parameter) {
                    $this->SendDebug(__FUNCTION__, 'Beim akustischen Alarm ist ein Fehler aufgetreten!', 0);
                    $this->SendDebug(__FUNCTION__, 'Der Schaltvorgang wird wiederholt.', 0);
                    IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayAcousticAlarm'));
                    $parameter = @RequestAction($id, $State);
                    if (!$parameter) {
                        $this->SendDebug(__FUNCTION__, 'Der akustische Alarm konnte für die Alarmsirene ID ' . $id . ' nicht erfolgreich geschaltet werden!', 0);
                        $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der akustische Alarm konnte für die Alarmsirene ID . ' . $id . ' nicht erfolgreich geschaltet werden!', KL_ERROR);
                    }
                }
            }
        }
    }

    /**
     * Toggles the optical alarm off or on.
     *
     * @param bool $State
     * false =  Off
     * true =   On
     *
     * @return void
     * @throws Exception
     */
    public function ToggleOpticalAlarm(bool $State): void
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
                return;
            }
        }
        $id = $this->ReadPropertyInteger('OpticalAlarm');
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayOpticalAlarm'));
            $commandControl = $this->ReadPropertyInteger('CommandControl');
            if ($commandControl > 1 && @IPS_ObjectExists($commandControl)) { //0 = main category, 1 = none
                $commands = [];
                $commands[] = '@RequestAction(' . $id . ', ' . $value . ');';
                $this->SendDebug(__FUNCTION__, 'Befehl: ' . json_encode(json_encode($commands)), 0);
                $scriptText = self::ABLAUFSTEUERUNG_MODULE_PREFIX . '_ExecuteCommands(' . $commandControl . ', ' . json_encode(json_encode($commands)) . ');';
                $this->SendDebug(__FUNCTION__, 'Ablaufsteuerung: ' . $scriptText, 0);
                @IPS_RunScriptText($scriptText);
            } else {
                $parameter = @RequestAction($id, $State);
                if (!$parameter) {
                    $this->SendDebug(__FUNCTION__, 'Beim optischen Alarm ist ein Fehler aufgetreten!', 0);
                    $this->SendDebug(__FUNCTION__, 'Der Schaltvorgang wird wiederholt.', 0);
                    IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayOpticalAlarm'));
                    $parameter = @RequestAction($id, $State);
                    if (!$parameter) {
                        $this->SendDebug(__FUNCTION__, 'Der optische Alarm konnte für die Alarmsirene ID ' . $id . ' nicht erfolgreich geschaltet werden!', 0);
                        $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der optische Alarm konnte für die Alarmsirene ID . ' . $id . ' nicht erfolgreich geschaltet werden!', KL_ERROR);
                    }
                }
            }
        }
    }

    /**
     * Checks the next alarm level.
     *
     * @return void
     * @throws Exception
     */
    public function CheckNextAlarmLevel(): void
    {
        //Deactivate timer
        $this->SetTimerInterval('CheckNextAlarmLevel', 0);
        //Check alarm level
        switch ($this->GetValue('AlarmLevel')) {
            case 1: //Pre alarm
                $alarmLevel = 0; //Off
                if ($this->ReadPropertyBoolean('UseMainAlarmAcousticAlarm') || $this->ReadPropertyBoolean('UseMainAlarmOpticalAlarm')) {
                    $alarmLevel = 2; //Main alarm
                }
                $this->SetAlarmLevel($alarmLevel);
                break;

            case 2:  //Main Alarm
                $alarmLevel = 0; //Off
                if ($this->ReadPropertyBoolean('UsePostAlarmOpticalAlarm')) {
                    $alarmLevel = 3; //Post alarm
                }
                $this->SetAlarmLevel($alarmLevel);
                break;

            case 3: //Post alarm
            case 4: //Panic alarm
                $this->SetAlarmLevel();
                break;

        }
    }

    /**
     * @param int $AlarmLevel
     * 0 =  Off
     * 1 =  Pre alarm
     * 2 =  Main alarm
     * 3 =  Post alarm
     * 4 =  Panic alarm
     *
     * @return void
     * @throws Exception
     */
    public function SetAlarmLevel(int $AlarmLevel = 0): void
    {
        if ($AlarmLevel != 0) {
            if ($this->CheckMaintenance()) {
                return;
            }
            if (!$this->CheckSignallingAmount()) {
                return;
            }
        }
        //Get values
        $alarmSiren = $this->GetValue('AlarmSiren');
        $alarmLevel = $this->GetValue('AlarmLevel');
        switch ($AlarmLevel) {
            case 1: //Pre alarm
                //Check conditions
                if (!$this->ReadPropertyBoolean('UsePreAlarmAcousticAlarm') && !$this->ReadPropertyBoolean('UsePreAlarmOpticalAlarm')) {
                    break;
                }
                if ($alarmSiren && $alarmLevel > 1) {
                    break;
                }
                //Set values
                $this->SetValue('AlarmSiren', true);
                $this->SetValue('AlarmLevel', 1);
                //Get values
                $preAlarmDuration = $this->ReadPropertyInteger('PreAlarmDuration');
                $acousticDuration = $this->ReadPropertyInteger('PreAlarmAcousticDuration');
                $opticalDuration = $this->ReadPropertyInteger('PreAlarmOpticalDuration');
                //Acoustic alarm
                $acousticAlarmState = false;
                $milliseconds = 0;
                if ($this->ReadPropertyBoolean('UsePreAlarmAcousticAlarm')) {
                    $acousticAlarmState = true;
                    $deactivate = false;
                    //Acoustic signal ends before pre alarm duration
                    if ($acousticDuration < $preAlarmDuration) {
                        $deactivate = true;
                    }
                    //Acoustic signal ends with pre alarm duration
                    else {
                        //Main alarm is not used
                        if (!$this->ReadPropertyBoolean('UseMainAlarmAcousticAlarm') && !$this->ReadPropertyBoolean('UseMainAlarmOpticalAlarm')) {
                            $deactivate = true;
                        }
                        //Main alarm is used
                        else {
                            //No acoustic signal on main alarm
                            if (!$this->ReadPropertyBoolean('UseMainAlarmAcousticAlarm')) {
                                $deactivate = true;
                            }
                        }
                    }
                    if ($deactivate) {
                        $milliseconds = $acousticDuration * 1000;
                    }
                }
                $this->ToggleAcousticAlarm($acousticAlarmState);
                $this->SetTimerInterval('DeactivateAcousticAlarm', $milliseconds);
                //Optical alarm
                $opticalAlarmState = false;
                $milliseconds = 0;
                if ($this->ReadPropertyBoolean('UsePreAlarmOpticalAlarm')) {
                    $opticalAlarmState = true;
                    $deactivate = false;
                    //Optical signal ends before pre alarm duration
                    if ($opticalDuration < $preAlarmDuration) {
                        $deactivate = true;
                    }
                    //Optical signal ends with pre alarm duration
                    else {
                        //Main alarm is not used
                        if (!$this->ReadPropertyBoolean('UseMainAlarmAcousticAlarm') && !$this->ReadPropertyBoolean('UseMainAlarmOpticalAlarm')) {
                            $deactivate = true;
                        }
                        //Main alarm is used
                        else {
                            //No optical signal on main alarm
                            if (!$this->ReadPropertyBoolean('UseMainAlarmOpticalAlarm')) {
                                $deactivate = true;
                            }
                        }
                    }
                    if ($deactivate) {
                        $milliseconds = $opticalDuration * 1000;
                    }
                }
                $this->ToggleOpticalAlarm($opticalAlarmState);
                $this->SetTimerInterval('DeactivateOpticalAlarm', $milliseconds);
                //Set next alarm level check
                $seconds = $this->ReadPropertyInteger('PreAlarmDuration') + 1;
                $this->SetTimerInterval('CheckNextAlarmLevel', $seconds * 1000);
                //Log text
                $text = 'Der Voralarm wurde eingeschaltet.';
                $this->SendDebug(__FUNCTION__, $text, 0);
                if (!$alarmSiren) {
                    $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', Alarmsirene, ' . $text . ' (ID ' . $this->InstanceID . ')';
                    $this->UpdateAlarmProtocol($logText, 0);
                }
                break;

            case 2: //Main alarm
                //Check conditions
                if (!$this->ReadPropertyBoolean('UseMainAlarmAcousticAlarm') && !$this->ReadPropertyBoolean('UseMainAlarmOpticalAlarm')) {
                    break;
                }
                //Set values
                $this->SetValue('AlarmSiren', true);
                $this->SetValue('AlarmLevel', 2);
                //Get values
                $mainAlarmDuration = $this->ReadPropertyInteger('MainAlarmDuration');
                $acousticDuration = $this->ReadPropertyInteger('MainAlarmAcousticDuration');
                $opticalDuration = $this->ReadPropertyInteger('MainAlarmOpticalDuration');
                //Acoustic alarm
                $acousticAlarmState = false;
                $milliseconds = 0;
                if ($this->ReadPropertyBoolean('UseMainAlarmAcousticAlarm')) {
                    $this->SetValue('SignallingAmount', $this->GetValue('SignallingAmount') + 1);
                    $acousticAlarmState = true;
                    $milliseconds = $acousticDuration * 1000;
                }
                $this->ToggleAcousticAlarm($acousticAlarmState);
                $this->SetTimerInterval('DeactivateAcousticAlarm', $milliseconds);
                //Optical alarm
                $opticalAlarmState = false;
                $milliseconds = 0;
                if ($this->ReadPropertyBoolean('UseMainAlarmOpticalAlarm')) {
                    $opticalAlarmState = true;
                    $deactivate = false;
                    //Optical signal ends before main alarm duration
                    if ($opticalDuration < $mainAlarmDuration) {
                        $deactivate = true;
                    }
                    //Optical signal ends with main alarm duration
                    else {
                        //Post alarm is not used
                        if (!$this->ReadPropertyBoolean('UsePostAlarmOpticalAlarm')) {
                            $deactivate = true;
                        }
                    }
                    if ($deactivate) {
                        $milliseconds = $opticalDuration * 1000;
                    }
                }
                $this->ToggleOpticalAlarm($opticalAlarmState);
                $this->SetTimerInterval('DeactivateOpticalAlarm', $milliseconds);
                //Set next alarm level check
                $seconds = $this->ReadPropertyInteger('MainAlarmDuration') + 1;
                $this->SetTimerInterval('CheckNextAlarmLevel', $seconds * 1000);
                //Log text
                $text = 'Der Hauptalarm wurde eingeschaltet.';
                $this->SendDebug(__FUNCTION__, $text, 0);
                $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', Alarmsirene, ' . $text . ' (ID ' . $this->InstanceID . ')';
                $this->UpdateAlarmProtocol($logText, 0);
                break;

            case 3: //Post alarm
                //Check conditions
                if (!$this->ReadPropertyBoolean('UsePostAlarmOpticalAlarm')) {
                    break;
                }
                //Set values
                $this->SetValue('AlarmSiren', true);
                $this->SetValue('AlarmLevel', 3);
                //Optical alarm
                $opticalAlarmState = false;
                $milliseconds = 0;
                if ($this->ReadPropertyBoolean('UsePostAlarmOpticalAlarm')) {
                    $opticalAlarmState = true;
                    $milliseconds = $this->ReadPropertyInteger('PostAlarmOpticalDuration') * 1000;
                }
                $this->ToggleOpticalAlarm($opticalAlarmState);
                $this->SetTimerInterval('DeactivateOpticalAlarm', $milliseconds);
                //Set next alarm level check
                $seconds = $this->ReadPropertyInteger('PostAlarmOpticalDuration') + 1;
                $this->SetTimerInterval('CheckNextAlarmLevel', $seconds * 1000);
                //Log text
                $text = 'Der Nachalarm wurde eingeschaltet.';
                $this->SendDebug(__FUNCTION__, $text, 0);
                $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', Alarmsirene, ' . $text . ' (ID ' . $this->InstanceID . ')';
                $this->UpdateAlarmProtocol($logText, 0);
                break;

            case 4: //Panic alarm
                //Check conditions
                if (!$this->ReadPropertyBoolean('UsePanicAlarmAcousticAlarm') && !$this->ReadPropertyBoolean('UsePanicAlarmOpticalAlarm')) {
                    break;
                }
                //Set values
                $this->SetValue('AlarmSiren', true);
                $this->SetValue('AlarmLevel', 4);
                //Get values
                $panicAlarmDuration = $this->ReadPropertyInteger('PanicAlarmDuration');
                $acousticDuration = $this->ReadPropertyInteger('PanicAlarmAcousticDuration');
                $opticalDuration = $this->ReadPropertyInteger('PanicAlarmOpticalDuration');
                //Acoustic alarm
                $acousticAlarmState = false;
                $milliseconds = 0;
                if ($this->ReadPropertyBoolean('UsePanicAlarmAcousticAlarm')) {
                    $this->SetValue('SignallingAmount', $this->GetValue('SignallingAmount') + 1);
                    $acousticAlarmState = true;
                    $deactivate = false;
                    //Acoustic signal ends before pre alarm duration
                    if ($acousticDuration < $panicAlarmDuration) {
                        $deactivate = true;
                    }
                    if ($deactivate) {
                        $milliseconds = $acousticDuration * 1000;
                    }
                }
                $this->ToggleAcousticAlarm($acousticAlarmState);
                $this->SetTimerInterval('DeactivateAcousticAlarm', $milliseconds);
                //Optical alarm
                $opticalAlarmState = false;
                $milliseconds = 0;
                if ($this->ReadPropertyBoolean('UsePanicAlarmOpticalAlarm')) {
                    $opticalAlarmState = true;
                    $deactivate = false;
                    //Optical signal ends before pre alarm duration
                    if ($opticalDuration < $panicAlarmDuration) {
                        $deactivate = true;
                    }
                    if ($deactivate) {
                        $milliseconds = $opticalDuration * 1000;
                    }
                }
                $this->ToggleOpticalAlarm($opticalAlarmState);
                $this->SetTimerInterval('DeactivateOpticalAlarm', $milliseconds);
                //Set next alarm level check
                $seconds = $this->ReadPropertyInteger('PanicAlarmDuration') + 1;
                $this->SetTimerInterval('CheckNextAlarmLevel', $seconds * 1000);
                //Debug and log text
                $text = 'Der Panikalarm wurde eingeschaltet.';
                $this->SendDebug(__FUNCTION__, $text, 0);
                if (!$alarmSiren) {
                    $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', Alarmsirene, ' . $text . ' (ID ' . $this->InstanceID . ')';
                    $this->UpdateAlarmProtocol($logText, 0);
                }
                break;

            default: //Off
                //Deactivate timers
                $this->SetTimerInterval('DeactivateAcousticAlarm', 0);
                $this->SetTimerInterval('DeactivateOpticalAlarm', 0);
                $this->SetTimerInterval('CheckNextAlarmLevel', 0);
                //Revert
                $this->SetValue('AlarmSiren', false);
                $this->SetValue('AlarmLevel', 0);
                //Turn alarm siren off
                $this->ToggleAcousticAlarm(false);
                $this->ToggleOpticalAlarm(false);
                //Debug and log text
                $text = 'Die Alarmsirene wurde ausgeschaltet.';
                $this->SendDebug(__FUNCTION__, $text, 0);
                if ($alarmSiren) {
                    $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', Alarmsirene, ' . $text . ' (ID ' . $this->InstanceID . ')';
                    $this->UpdateAlarmProtocol($logText, 0);
                }
        }
    }

    /**
     * Resets the signalling amount
     *
     * @return void
     */
    public function ResetSignallingAmount(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SetTimerInterval('ResetSignallingAmount', (strtotime('next day midnight') - time()) * 1000);
        $this->SetValue('SignallingAmount', 0);
        $this->SendDebug(__FUNCTION__, 'Die Anzahl der Auslösungen wurde zurückgesetzt.', 0);
    }

    ###################### Private

    /**
     * Checks the signalling amount.
     *
     * @return bool
     * false =  Maximum signalling amount reached
     * true =   OK
     *
     * @throws Exception
     */
    private function CheckSignallingAmount(): bool
    {
        $maximum = $this->ReadPropertyInteger('MaximumSignallingAmountAcousticAlarm');
        if ($maximum > 0) {
            if ($this->GetValue('SignallingAmount') >= $maximum) {
                $text = 'Abbruch, die maximale Anzahl der Auslösungen wurde bereits erreicht!';
                $this->SendDebug(__FUNCTION__, $text, 0);
                $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', ' . $text, KL_WARNING);
                $this->UpdateAlarmProtocol(date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', Alarmsirene, ' . $text . ' (ID ' . $this->InstanceID . ')', 0);
                return false;
            }
        }
        return true;
    }
}