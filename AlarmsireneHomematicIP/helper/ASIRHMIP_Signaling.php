<?php

/**
 * @project       Alarmsirene/AlarmsireneHomematicIP
 * @file          ASIRHMIP_Signaling.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

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
     * @return bool
     * @throws Exception
     */
    public function ToggleAlarmSiren(bool $State): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $statusText = 'Aus';
        if ($State) {
            $statusText = 'An';
        }
        $this->SendDebug(__FUNCTION__, 'Status: ' . $statusText, 0);
        //Off
        $acousticSignal = 0;
        $opticalSignal = 0;
        $durationUnit = 0;
        $durationValue = 5;
        //On
        if ($State) {
            $acousticSignal = (integer) $this->GetValue('AcousticSignal');
            $opticalSignal = (integer) $this->GetValue('OpticalSignal');
            $durationUnit = (integer) $this->GetValue('DurationUnit');
            $durationValue = (integer) $this->GetValue('DurationValue');
        }
        return $this->ExecuteSignaling($acousticSignal, $opticalSignal, $durationUnit, $durationValue);
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
     * @return bool
     * @throws Exception
     */
    public function ExecuteSignaling(int $AcousticSignal, int $OpticalSignal, int $DurationUnit, int $DurationValue): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $state = true;
        if ($AcousticSignal == 0 && $OpticalSignal == 0) {
            $state = false;
        }
        if ($state) {
            if ($this->CheckMaintenance()) {
                return false;
            }
        }
        //Check if signalling is on after 5 seconds
        if ($AcousticSignal != 0 || $OpticalSignal != 0) {
            $this->SetTimerInterval('CheckDeviceState', 5000);
        }
        $actualValue = $this->GetValue('AlarmSiren');
        $this->SendDebug(__FUNCTION__, 'Akustisches Signal: ' . $AcousticSignal, 0);
        $this->SendDebug(__FUNCTION__, 'Optisches Signal: ' . $OpticalSignal, 0);
        $this->SendDebug(__FUNCTION__, 'Einheit Zeitdauer: ' . $DurationUnit, 0);
        $this->SendDebug(__FUNCTION__, 'Wert Zeitdauer: ' . $DurationValue, 0);
        $result = false;
        $id = $this->ReadPropertyInteger('DeviceInstance');
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            $result = true;
            $this->SetValue('AlarmSiren', $state);
            switch ($this->ReadPropertyInteger('DeviceType')) {
                case 1: //HmIP-ASIR
                case 2: //HmIP-ASIR-2
                case 3: //HmIP-ASIR-O
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
                        $result = @IPS_RunScriptText($scriptText);
                    } else {
                        IPS_Sleep($this->ReadPropertyInteger('SwitchingDelay'));
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
                                $result = false;
                            }
                        }
                    }
                    if (!$result) {
                        //Revert
                        $this->SetValue('AlarmSiren', $actualValue);
                        $this->SendDebug(__FUNCTION__, 'Die Signalisierung konnte für die Alarmsirene ID ' . $id . ' nicht erfolgreich ausgeführt werden!', 0);
                        $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', die Signalisierung konnte für die Alarmsirene ID . ' . $id . ' nicht erfolgreich ausgeführt werden!', KL_ERROR);
                    }
                    break;

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
        $deviceStateAcousticAlarm = $this->ReadPropertyInteger('DeviceStateAcousticAlarm');
        $deviceStateOpticalAlarm = $this->ReadPropertyInteger('DeviceStateOpticalAlarm');
        if ($deviceStateAcousticAlarm > 1 && $deviceStateOpticalAlarm > 1) { //0 = main category, 1 = none
            $state = false;
            //Check whether the acoustic or optical signal is already switched on
            if (GetValueBoolean($deviceStateAcousticAlarm) || GetValueBoolean($deviceStateOpticalAlarm)) {
                $state = true;
            }
            $this->SetValue('AlarmSiren', $state);
        }
    }
}