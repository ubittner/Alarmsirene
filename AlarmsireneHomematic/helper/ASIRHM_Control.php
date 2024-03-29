<?php

/**
 * @project       Alarmsirene/AlarmsireneHomematic/helper/
 * @file          ASIRHM_Control.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait ASIRHM_Control
{
    #################### Public

    /**
     * Starts the automatic deactivation.
     */
    public function StartAutomaticDeactivation(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        //Turn the alarm siren off
        $this->SetAlarmLevel();
        $this->SetValue('Active', false);
        $this->SetAutomaticDeactivationTimer();
    }

    /**
     * Stops the automatic deactivation.
     */
    public function StopAutomaticDeactivation(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SetValue('Active', true);
        $this->SetAutomaticDeactivationTimer();
    }

    #################### Private

    private function SetAutomaticDeactivationTimer(): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $use = $this->ReadPropertyBoolean('UseAutomaticDeactivation');
        //Start
        $milliseconds = 0;
        if ($use) {
            $milliseconds = $this->GetInterval('AutomaticDeactivationStartTime');
        }
        $this->SetTimerInterval('StartAutomaticDeactivation', $milliseconds);
        //End
        $milliseconds = 0;
        if ($use) {
            $milliseconds = $this->GetInterval('AutomaticDeactivationEndTime');
        }
        $this->SetTimerInterval('StopAutomaticDeactivation', $milliseconds);
    }

    /**
     * Gets the interval for a timer.
     *
     * @param string $TimerName
     * @return int
     * @throws Exception
     */
    private function GetInterval(string $TimerName): int
    {
        $timer = json_decode($this->ReadPropertyString($TimerName));
        $now = time();
        $hour = $timer->hour;
        $minute = $timer->minute;
        $second = $timer->second;
        $definedTime = $hour . ':' . $minute . ':' . $second;
        if (time() >= strtotime($definedTime)) {
            $timestamp = mktime($hour, $minute, $second, (int) date('n'), (int) date('j') + 1, (int) date('Y'));
        } else {
            $timestamp = mktime($hour, $minute, $second, (int) date('n'), (int) date('j'), (int) date('Y'));
        }
        return ($timestamp - $now) * 1000;
    }

    /**
     * Checks the status of the automatic deactivation timer.
     *
     * @return bool
     * @throws Exception
     */
    private function CheckAutomaticDeactivationTimer(): bool
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if (!$this->ReadPropertyBoolean('UseAutomaticDeactivation')) {
            return false;
        }
        $start = $this->GetTimerInterval('StartAutomaticDeactivation');
        $stop = $this->GetTimerInterval('StopAutomaticDeactivation');
        if ($start > $stop) {
            //Turn alarm siren off
            $this->SetAlarmLevel();
            //Deactivation timer is active, must be toggled to inactive
            $this->SetValue('Active', false);
            return true;
        } else {
            //Deactivation timer is inactive, must be toggled to active
            $this->SetValue('Active', true);
            return false;
        }
    }
}