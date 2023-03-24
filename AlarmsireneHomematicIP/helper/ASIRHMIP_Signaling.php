<?php

/**
 * @project       Alarmsirene/AlarmsireneHomematicIP
 * @file          ASIRHMIP_Signaling.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait ASIRHMIP_Signaling
{
    #################### Public

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
        $statusText = 'Aus';
        if ($State) {
            $statusText = 'An';
        }
        $this->SendDebug(__FUNCTION__, 'Status: ' . $statusText, 0);

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
            if ($this->ReadPropertyBoolean('UsePreAlarm')) {
                $usePreAlarm = true;
                $this->SetAlarmLevel(1);
            }
            //Check main alarm
            $useMainAlarm = false;
            if ($this->ReadPropertyBoolean('UseMainAlarm')) {
                $useMainAlarm = true;
            }
            if (!$usePreAlarm && $useMainAlarm) {
                $this->SetAlarmLevel(2);
            }
            //Check post alarm
            $usePostAlarm = $this->ReadPropertyBoolean('UsePostAlarm');
            if (!$usePreAlarm && !$useMainAlarm && $usePostAlarm) {
                $this->SetAlarmLevel(3);
            }
        }
    }

    /**
     * Executes the alarm siren signaling with several parameters.
     *
     * @param int $AcousticSignal
     * 0 =  DISABLE_ACOUSTIC_SIGNAL
     * 1 =  FREQUENCY_RISING
     * 2 =	FREQUENCY_FALLING
     * 3 =	FREQUENCY_RISING_AND_FALLING
     * 4 =	FREQUENCY_ALTERNATING_LOW_HIGH
     * 5 =	FREQUENCY_ALTERNATING_LOW_MID_HIGH
     * 6 =	FREQUENCY_HIGHON_OFF
     * 7 =	FREQUENCY_HIGHON_LONGOFF
     * 8 =	FREQUENCY_LOWON_OFF_HIGHON_OFF
     * 9 =	FREQUENCY_LOWON_LONGOFF_HIGHON_LONGOFF
     * 10 = LOW_BATTERY
     * 11 =	DISARMED
     * 12 =	INTERNALLY_ARMED
     * 13 =	EXTERNALLY_ARMED
     * 14 =	DELAYED_INTERNALLY_ARMED
     * 15 =	DELAYED_EXTERNALLY_ARMED
     * 16 =	EVENT
     * 17 =	ERROR
     *
     * @param int $OpticalSignal
     * 0 =	DISABLE_OPTICAL_SIGNAL
     * 1 =	BLINKING_ALTERNATELY_REPEATING
     * 2 =	BLINKING_BOTH_REPEATING
     * 3 =	DOUBLE_FLASHING_REPEATING
     * 4 =	FLASHING_BOTH_REPEATING
     * 5 =	CONFIRMATION_SIGNAL_0 LONG_LONG
     * 6 =	CONFIRMATION_SIGNAL_1 LONG_SHORT
     * 7 =	CONFIRMATION_SIGNAL_2 LONG_SHORT_SHORT
     *
     * @param int $DurationUnit
     * 0 =	SECONDS
     * 1 =	MINUTES
     * 2 =	HOURS
     *
     * @param int $DurationValue
     * 0 - n
     *
     * @return void
     * @throws Exception
     */
    public function ExecuteSignaling(int $AcousticSignal = 0, int $OpticalSignal = 0, int $DurationUnit = 0, int $DurationValue = 5): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $state = true;
        if ($AcousticSignal == 0 && $OpticalSignal == 0) {
            $state = false;
        }
        if ($state) {
            if ($this->CheckMaintenance()) {
                return;
            }
        }
        $this->SendDebug(__FUNCTION__, 'Akustisches Signal: ' . $AcousticSignal, 0);
        $this->SendDebug(__FUNCTION__, 'Optisches Signal: ' . $OpticalSignal, 0);
        $this->SendDebug(__FUNCTION__, 'Einheit Zeitdauer: ' . $DurationUnit, 0);
        $this->SendDebug(__FUNCTION__, 'Wert Zeitdauer: ' . $DurationValue, 0);
        $id = $this->ReadPropertyInteger('DeviceInstance');
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            $this->SetValue('AlarmSiren', $state);
            switch ($this->ReadPropertyInteger('DeviceType')) {
                case 1: //HmIP-ASIR
                case 2: //HmIP-ASIR-2
                case 3: //HmIP-ASIR-O
                    IPS_Sleep($this->ReadPropertyInteger('SwitchingDelay'));
                    $commandControl = $this->ReadPropertyInteger('CommandControl');
                    if ($commandControl > 1 && @IPS_ObjectExists($commandControl)) {
                        $commands = [];
                        $commands[] = '@HM_WriteValueInteger(' . $id . ", 'ACOUSTIC_ALARM_SELECTION', " . $AcousticSignal . ');';
                        $commands[] = '@HM_WriteValueInteger(' . $id . ", 'OPTICAL_ALARM_SELECTION', " . $OpticalSignal . ');';
                        $commands[] = '@HM_WriteValueInteger(' . $id . ", 'DURATION_UNIT', " . $DurationUnit . ');';
                        $commands[] = '@HM_WriteValueInteger(' . $id . ", 'DURATION_VALUE', " . $DurationValue . ');';
                        $this->SendDebug(__FUNCTION__, 'Befehle: ' . json_encode(json_encode($commands)), 0);
                        $scriptText = self::ABLAUFSTEUERUNG_MODULE_PREFIX . '_ExecuteCommands(' . $commandControl . ', ' . json_encode(json_encode($commands)) . ');';
                        $this->SendDebug(__FUNCTION__, 'Ablaufsteuerung: ' . $scriptText, 0);
                        @IPS_RunScriptText($scriptText);
                    } else {
                        $parameter1 = @HM_WriteValueInteger($id, 'ACOUSTIC_ALARM_SELECTION', $AcousticSignal);
                        $parameter2 = @HM_WriteValueInteger($id, 'OPTICAL_ALARM_SELECTION', $OpticalSignal);
                        $parameter3 = @HM_WriteValueInteger($id, 'DURATION_UNIT', $DurationUnit);
                        $parameter4 = @HM_WriteValueInteger($id, 'DURATION_VALUE', $DurationValue);
                        if (!$parameter1 || !$parameter2 || !$parameter3 || !$parameter4) {
                            $this->SendDebug(__FUNCTION__, 'Bei der Signalisierung ist ein Fehler aufgetreten!', 0);
                            $this->SendDebug(__FUNCTION__, 'Der Schaltvorgang wird wiederholt.', 0);
                            IPS_Sleep($this->ReadPropertyInteger('SwitchingDelay'));
                            $parameter1 = @HM_WriteValueInteger($id, 'ACOUSTIC_ALARM_SELECTION', $AcousticSignal);
                            $parameter2 = @HM_WriteValueInteger($id, 'OPTICAL_ALARM_SELECTION', $OpticalSignal);
                            $parameter3 = @HM_WriteValueInteger($id, 'DURATION_UNIT', $DurationUnit);
                            $parameter4 = @HM_WriteValueInteger($id, 'DURATION_VALUE', $DurationValue);
                            if (!$parameter1 || !$parameter2 || !$parameter3 || !$parameter4) {
                                $this->SendDebug(__FUNCTION__, 'Die Signalisierung konnte für die Alarmsirene ID ' . $id . ' nicht erfolgreich ausgeführt werden!', 0);
                                $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', die Signalisierung konnte für die Alarmsirene ID . ' . $id . ' nicht erfolgreich ausgeführt werden!', KL_ERROR);
                            }
                        }
                    }
                    break;

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
                if ($this->ReadPropertyBoolean('UseMainAlarm')) {
                    $alarmLevel = 2; //Main alarm
                }
                $this->SetAlarmLevel($alarmLevel);
                break;

            case 2:  //Main Alarm
                $alarmLevel = 0; //Off
                if ($this->ReadPropertyBoolean('UsePostAlarm')) {
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
                if (!$this->ReadPropertyBoolean('UsePreAlarm')) {
                    break;
                }
                if ($alarmSiren && $alarmLevel > 1) {
                    break;
                }
                //Get values
                $duration = $this->ReadPropertyInteger('PreAlarmDuration');
                $acousticSignal = $this->ReadPropertyInteger('PreAlarmAcousticSignal');
                $opticalSignal = $this->ReadPropertyInteger('PreAlarmOpticalSignal');
                //Set values
                $this->SetValue('AlarmSiren', true);
                $this->SetValue('AlarmLevel', 1);
                $this->ExecuteSignaling($acousticSignal, $opticalSignal, 0, $duration);
                //Set next alarm level check
                $this->SetTimerInterval('CheckNextAlarmLevel', $duration * 1000);
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
                if (!$this->ReadPropertyBoolean('UseMainAlarm')) {
                    break;
                }
                //Get values
                $duration = $this->ReadPropertyInteger('MainAlarmDuration');
                $acousticSignal = $this->ReadPropertyInteger('MainAlarmAcousticSignal');
                $opticalSignal = $this->ReadPropertyInteger('MainAlarmOpticalSignal');
                //Set values
                $this->SetValue('AlarmSiren', true);
                $this->SetValue('AlarmLevel', 2);
                if ($acousticSignal >= 1 && $acousticSignal <= 9) {
                    $this->SetValue('SignallingAmount', $this->GetValue('SignallingAmount') + 1);
                }
                $this->ExecuteSignaling($acousticSignal, $opticalSignal, 0, $duration);
                //Set next alarm level check
                $this->SetTimerInterval('CheckNextAlarmLevel', $duration * 1000);
                //Log text
                $text = 'Der Hauptalarm wurde eingeschaltet.';
                $this->SendDebug(__FUNCTION__, $text, 0);
                $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', Alarmsirene, ' . $text . ' (ID ' . $this->InstanceID . ')';
                $this->UpdateAlarmProtocol($logText, 0);
                break;

            case 3: //Post alarm
                //Check conditions
                if (!$this->ReadPropertyBoolean('UsePostAlarm')) {
                    break;
                }
                //Get values
                $duration = $this->ReadPropertyInteger('PostAlarmDuration');
                $opticalSignal = $this->ReadPropertyInteger('PostAlarmOpticalSignal');
                //Set values
                $this->SetValue('AlarmSiren', true);
                $this->SetValue('AlarmLevel', 3);
                $this->ExecuteSignaling(0, $opticalSignal, 0, $duration);
                //Set next alarm level check
                $this->SetTimerInterval('CheckNextAlarmLevel', $duration * 1000);
                //Log text
                $text = 'Der Nachalarm wurde eingeschaltet.';
                $this->SendDebug(__FUNCTION__, $text, 0);
                $logText = date('d.m.Y, H:i:s') . ', ' . $this->ReadPropertyString('Location') . ', Alarmsirene, ' . $text . ' (ID ' . $this->InstanceID . ')';
                $this->UpdateAlarmProtocol($logText, 0);
                break;

            case 4: //Panic alarm
                //Check conditions
                if (!$this->ReadPropertyBoolean('UsePanicAlarm')) {
                    break;
                }
                //Get values
                $duration = $this->ReadPropertyInteger('PanicAlarmDuration');
                $acousticSignal = $this->ReadPropertyInteger('PanicAlarmAcousticSignal');
                $opticalSignal = $this->ReadPropertyInteger('PanicAlarmOpticalSignal');
                //Set values
                $this->SetValue('AlarmSiren', true);
                $this->SetValue('AlarmLevel', 4);
                if ($acousticSignal >= 1 && $acousticSignal <= 9) {
                    $this->SetValue('SignallingAmount', $this->GetValue('SignallingAmount') + 1);
                }
                $this->ExecuteSignaling($acousticSignal, $opticalSignal, 0, $duration);
                //Set next alarm level check
                $this->SetTimerInterval('CheckNextAlarmLevel', $duration * 1000);
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
                $this->SetTimerInterval('CheckNextAlarmLevel', 0);
                //Revert
                $this->SetValue('AlarmSiren', false);
                $this->SetValue('AlarmLevel', 0);
                //Turn alarm siren off
                $this->ExecuteSignaling();
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