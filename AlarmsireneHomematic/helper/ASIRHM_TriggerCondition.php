<?php

/**
 * @project       Alarmsirene/AlarmsireneHomematic
 * @file          ASIRHM_TriggerCondition.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait ASIRHM_TriggerCondition
{
    /**
     * Checks the trigger conditions.
     *
     * @param int $SenderID
     * @param bool $ValueChanged
     * false =  same value
     * true =   new value
     *
     * @throws Exception
     */
    public function CheckTriggerConditions(int $SenderID, bool $ValueChanged): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SendDebug(__FUNCTION__, 'Sender: ' . $SenderID, 0);
        $valueChangedText = 'nicht ';
        if ($ValueChanged) {
            $valueChangedText = '';
        }
        $this->SendDebug(__FUNCTION__, 'Der Wert hat sich ' . $valueChangedText . 'geändert', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        $variables = json_decode($this->ReadPropertyString('TriggerList'), true);
        foreach ($variables as $key => $variable) {
            if (!$variable['Use']) {
                continue;
            }
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($SenderID == $id) {
                            $this->SendDebug(__FUNCTION__, 'Listenschlüssel: ' . $key, 0);
                            if (!$variable['UseMultipleAlerts'] && !$ValueChanged) {
                                $this->SendDebug(__FUNCTION__, 'Abbruch, die Mehrfachauslösung ist nicht aktiviert!', 0);
                                continue;
                            }
                            $execute = true;
                            //Check primary condition
                            if (!IPS_IsConditionPassing($variable['PrimaryCondition'])) {
                                $execute = false;
                            }
                            //Check secondary condition
                            if (!IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                                $execute = false;
                            }
                            if (!$execute) {
                                $this->SendDebug(__FUNCTION__, 'Abbruch, die Bedingungen wurden nicht erfüllt!', 0);
                            } else {
                                $this->SendDebug(__FUNCTION__, 'Die Bedingungen wurden erfüllt.', 0);
                                switch ($variable['SignalingMode']) {
                                    case 0: //not used
                                        break;

                                    case 1: //alarm siren off
                                        $this->ToggleAlarmSiren(false);
                                        break;

                                    case 2: //alarm siren on (alarm level)
                                        $this->ToggleAlarmSiren(true);
                                        break;

                                    case 3: //pre alarm
                                        $this->SetAlarmLevel(1);
                                        break;

                                    case 4: //main alarm
                                        $this->SetAlarmLevel(2);
                                        break;

                                    case 5: //post alarm
                                        $this->SetAlarmLevel(3);
                                        break;

                                    case 6: //panic alarm
                                        $this->SetAlarmLevel(4);
                                        break;

                                    case 7: //tone acknowledgement - alarm off
                                        $this->ExecuteToneAcknowledgement(0);
                                        break;

                                    case 8: //tone acknowledgement - internally armed
                                        $this->ExecuteToneAcknowledgement(1);
                                        break;

                                    case 9: //tone acknowledgement - externally armed
                                        $this->ExecuteToneAcknowledgement(2);
                                        break;

                                    case 10: //tone acknowledgement - alarm locked
                                        $this->ExecuteToneAcknowledgement(3);
                                        break;

                                }
                            }
                        }
                    }
                }
            }
        }
    }
}