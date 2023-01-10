<?php

/**
 * @project       Alarmsirene/Alarmsirene
 * @file          ASIR_Signaling.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait ASIR_Signaling
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
        $result = true;
        $id = $this->ReadPropertyInteger('AcousticAlarm');
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            $actualValue = $this->GetValue('AcousticAlarm');
            $this->SetValue('AcousticAlarm', $State);
            IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayAcousticAlarm'));
            $commandControl = $this->ReadPropertyInteger('CommandControl');
            if ($commandControl > 1 && @IPS_ObjectExists($commandControl)) { //0 = main category, 1 = none
                $commands = [];
                $commands[] = '@RequestAction(' . $id . ', ' . $value . ');';
                $this->SendDebug(__FUNCTION__, 'Befehl: ' . json_encode(json_encode($commands)), 0);
                $scriptText = self::ABLAUFSTEUERUNG_MODULE_PREFIX . '_ExecuteCommands(' . $commandControl . ', ' . json_encode(json_encode($commands)) . ');';
                $this->SendDebug(__FUNCTION__, 'Ablaufsteuerung: ' . $scriptText, 0);
                $result = @IPS_RunScriptText($scriptText);
            } else {
                @RequestAction($id, $State);
            }
            if (!$result) {
                //Revert
                $this->SetValue('AcousticAlarm', $actualValue);
                $this->SendDebug(__FUNCTION__, 'Der akustische Alarm konnte für die Alarmsirene ID ' . $id . ' nicht erfolgreich geschaltet werden!', 0);
                $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der akustische Alarm konnte für die Alarmsirene ID . ' . $id . ' nicht erfolgreich geschaltet werden!', KL_ERROR);
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
        $result = true;
        $id = $this->ReadPropertyInteger('OpticalAlarm');
        if ($id > 1 && @IPS_ObjectExists($id)) { //0 = main category, 1 = none
            $actualValue = $this->GetValue('OpticalAlarm');
            $this->SetValue('OpticalAlarm', $State);
            IPS_Sleep($this->ReadPropertyInteger('SwitchingDelayOpticalAlarm'));
            $commandControl = $this->ReadPropertyInteger('CommandControl');
            if ($commandControl > 1 && @IPS_ObjectExists($commandControl)) { //0 = main category, 1 = none
                $commands = [];
                $commands[] = '@RequestAction(' . $id . ', ' . $value . ');';
                $this->SendDebug(__FUNCTION__, 'Befehl: ' . json_encode(json_encode($commands)), 0);
                $scriptText = self::ABLAUFSTEUERUNG_MODULE_PREFIX . '_ExecuteCommands(' . $commandControl . ', ' . json_encode(json_encode($commands)) . ');';
                $this->SendDebug(__FUNCTION__, 'Ablaufsteuerung: ' . $scriptText, 0);
                $result = @IPS_RunScriptText($scriptText);
            } else {
                @RequestAction($id, $State);
            }
            if (!$result) {
                //Revert
                $this->SetValue('OpticalAlarm', $actualValue);
                $this->SendDebug(__FUNCTION__, 'Der optische Alarm konnte für die Alarmsirene ID ' . $id . ' nicht erfolgreich geschaltet werden!', 0);
                $this->LogMessage('ID ' . $this->InstanceID . ', ' . __FUNCTION__ . ', der optische Alarm konnte für die Alarmsirene ID . ' . $id . ' nicht erfolgreich geschaltet werden!', KL_ERROR);
            }
        }
        $this->SetTimerInterval('CheckDeviceState', 5000);
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
        $acousticAlarm = $this->ReadPropertyInteger('AcousticAlarm');
        if ($acousticAlarm > 1 && @IPS_ObjectExists($acousticAlarm)) { //0 = main category, 1 = none
            $this->SetValue('AcousticAlarm', GetValue($acousticAlarm));
        }
        $opticalAlarm = $this->ReadPropertyInteger('OpticalAlarm');
        if ($opticalAlarm > 1 && @IPS_ObjectExists($opticalAlarm)) { //0 = main category, 1 = none
            $this->SetValue('OpticalAlarm', GetValue($opticalAlarm));
        }
    }
}