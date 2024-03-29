<?php

/**
 * @project       Alarmsirene/AlarmsireneHomematicIP/helper/
 * @file          ASIRHMIP_ConfigurationForm.php
 * @author        Ulrich Bittner
 * @copyright     2023 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait ASIRHMIP_ConfigugrationForm
{
    /**
     * Reloads the configuration form.
     *
     * @return void
     */
    public function ReloadConfig(): void
    {
        $this->ReloadForm();
    }

    /**
     * Expands or collapses the expansion panels.
     *
     * @param bool $State
     * false =  collapse,
     * true =   expand
     *
     * @return void
     */
    public function ExpandExpansionPanels(bool $State): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $this->UpdateFormField('Panel' . $i, 'expanded', $State);
        }
    }

    /**
     * Modifies a configuration button.
     *
     * @param string $Field
     * @param string $Caption
     * @param int $ObjectID
     * @return void
     */
    public function ModifyButton(string $Field, string $Caption, int $ObjectID): void
    {
        $state = false;
        if ($ObjectID > 1 && @IPS_ObjectExists($ObjectID)) {
            $state = true;
        }
        $this->UpdateFormField($Field, 'caption', $Caption);
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $ObjectID);
    }

    public function ModifyActualVariableStatesConfigurationButton(string $Field, int $VariableID): void
    {
        $state = false;
        if ($VariableID > 1 && @IPS_ObjectExists($VariableID)) {
            $state = true;
        }
        $this->UpdateFormField($Field, 'caption', 'ID ' . $VariableID . ' Bearbeiten');
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $VariableID);
    }

    /**
     * Modifies a trigger list configuration button
     *
     * @param string $Field
     * @param string $Condition
     * @return void
     */
    public function ModifyTriggerListButton(string $Field, string $Condition): void
    {
        $id = 0;
        $state = false;
        //Get variable id
        $primaryCondition = json_decode($Condition, true);
        if (array_key_exists(0, $primaryCondition)) {
            if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $state = true;
                }
            }
        }
        $this->UpdateFormField($Field, 'caption', 'ID ' . $id . ' Bearbeiten');
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $id);
    }

    /**
     * Gets the configuration form.
     *
     * @return false|string
     * @throws Exception
     */
    public function GetConfigurationForm()
    {
        $form = [];

        ########## Elements

        //Configuration buttons
        $form['elements'][0] =
            [
                'type'  => 'RowLayout',
                'items' => [
                    [
                        'type'    => 'Button',
                        'caption' => 'Konfiguration ausklappen',
                        'onClick' => self::MODULE_PREFIX . '_ExpandExpansionPanels($id, true);'
                    ],
                    [
                        'type'    => 'Button',
                        'caption' => 'Konfiguration einklappen',
                        'onClick' => self::MODULE_PREFIX . '_ExpandExpansionPanels($id, false);'
                    ],
                    [
                        'type'    => 'Button',
                        'caption' => 'Konfiguration neu laden',
                        'onClick' => self::MODULE_PREFIX . '_ReloadConfig($id);'
                    ]
                ]
            ];
        //Info
        $library = IPS_GetLibrary(self::LIBRARY_GUID);
        $module = IPS_GetModule(self::MODULE_GUID);
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel1',
            'caption' => 'Info',
            'items'   => [
                [
                    'type'  => 'Image',
                    'image' => 'data:image/png;base64, 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'
                ],
                [
                    'type'    => 'Label',
                    'name'    => 'ModuleID',
                    'caption' => "ID:\t\t\t" . $this->InstanceID
                ],
                [
                    'type'    => 'Label',
                    'name'    => 'ModuleDesignation',
                    'caption' => "Modul:\t\tAlarmsirene Homematic IP"
                ],
                [
                    'type'    => 'Label',
                    'name'    => 'ModulePrefix',
                    'caption' => "Präfix:\t\t" . $module['Prefix']
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Version:\t\t" . $library['Version'] . '-' . $library['Build'] . ', ' . date('d.m.Y', $library['Date'])
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Entwickler:\t" . $library['Author']
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'Note',
                    'caption' => 'Notiz',
                    'width'   => '600px'
                ]
            ]
        ];

        ##### Element: Alarm siren

        //Alarm siren
        $deviceInstance = $this->ReadPropertyInteger('DeviceInstance');
        $enableDeviceInstanceButton = false;
        if ($deviceInstance > 1 && @IPS_ObjectExists($deviceInstance)) {
            $enableDeviceInstanceButton = true;
        }

        //Acoustic state
        $deviceStateAcousticAlarm = $this->ReadPropertyInteger('DeviceStateAcousticAlarm');
        $enableDeviceStateAcousticAlarmButton = false;
        if ($deviceStateAcousticAlarm > 1 && @IPS_ObjectExists($deviceStateAcousticAlarm)) {
            $enableDeviceStateAcousticAlarmButton = true;
        }

        //Optical state
        $deviceStateOpticalAlarm = $this->ReadPropertyInteger('DeviceStateOpticalAlarm');
        $enableDeviceStateOpticalAlarmButton = false;
        if ($deviceStateOpticalAlarm > 1 && @IPS_ObjectExists($deviceStateOpticalAlarm)) {
            $enableDeviceStateOpticalAlarmButton = true;
        }

        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel2',
            'caption' => 'Alarmsirene',
            'items'   => [
                [
                    'type'    => 'Select',
                    'name'    => 'DeviceType',
                    'caption' => 'Typ',
                    'options' => [
                        [
                            'caption' => 'Kein Gerät',
                            'value'   => 0
                        ],
                        [
                            'caption' => 'HmIP-ASIR, Kanal 3',
                            'value'   => 1
                        ],
                        [
                            'caption' => 'HmIP-ASIR-2, Kanal 3',
                            'value'   => 2
                        ],
                        [
                            'caption' => 'HmIP-ASIR-O, Kanal 3',
                            'value'   => 3
                        ]
                    ]
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'SelectInstance',
                            'name'     => 'DeviceInstance',
                            'caption'  => 'Instanz',
                            'width'    => '600px',
                            'onChange' => self::MODULE_PREFIX . '_ModifyButton($id, "DeviceInstanceConfigurationButton", "ID " . $DeviceInstance . " konfigurieren", $DeviceInstance);'
                        ],
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'DeviceInstanceConfigurationButton',
                            'caption'  => 'ID ' . $deviceInstance . ' konfigurieren',
                            'visible'  => $enableDeviceInstanceButton,
                            'objectID' => $deviceInstance
                        ]
                    ]
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'SelectVariable',
                            'name'     => 'DeviceStateAcousticAlarm',
                            'caption'  => 'Variable ACOUSTIC_ALARM_ACTIVE',
                            'width'    => '600px',
                            'onChange' => self::MODULE_PREFIX . '_ModifyButton($id, "DeviceStateAcousticAlarmConfigurationButton", "ID " . $DeviceStateAcousticAlarm . " bearbeiten", $DeviceStateAcousticAlarm);'
                        ],
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'DeviceStateAcousticAlarmConfigurationButton',
                            'caption'  => 'ID ' . $deviceStateAcousticAlarm . ' bearbeiten',
                            'visible'  => $enableDeviceStateAcousticAlarmButton,
                            'objectID' => $deviceStateAcousticAlarm
                        ]
                    ]
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'SelectVariable',
                            'name'     => 'DeviceStateOpticalAlarm',
                            'caption'  => 'Variable OPTICAL_ALARM_ACTIVE',
                            'width'    => '600px',
                            'onChange' => self::MODULE_PREFIX . '_ModifyButton($id, "DeviceStateOpticalAlarmConfigurationButton", "ID " . $DeviceStateOpticalAlarm . " aufrufen", $DeviceStateOpticalAlarm);'
                        ],
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'DeviceStateOpticalAlarmConfigurationButton',
                            'caption'  => 'ID ' . $deviceStateOpticalAlarm . ' bearbeiten',
                            'visible'  => $enableDeviceStateOpticalAlarmButton,
                            'objectID' => $deviceStateOpticalAlarm
                        ]
                    ]
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'SwitchingDelay',
                    'caption' => 'Schaltverzögerung',
                    'minimum' => 0,
                    'suffix'  => 'Millisekunden'
                ]
            ]
        ];

        ##### Element: Alarm levels

        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel3',
            'caption' => 'Alarmstufen',
            'items'   => [
                [
                    'type'    => 'Label',
                    'caption' => 'Voralarm',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UsePreAlarm',
                    'caption' => 'Voralarm'
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'PreAlarmDuration',
                    'caption' => 'Dauer',
                    'suffix'  => 'Sekunden'
                ],
                [
                    'type'    => 'Select',
                    'name'    => 'PreAlarmAcousticSignal',
                    'caption' => 'Akustisches Signal',
                    'options' => [
                        [
                            'caption' => '0 - Kein akustisches Signal',
                            'value'   => 0
                        ],
                        [
                            'caption' => '1 - Frequenz steigend',
                            'value'   => 1
                        ],
                        [
                            'caption' => '2 - Frequenz fallend',
                            'value'   => 2
                        ],
                        [
                            'caption' => '3 - Frequenz steigend/fallend',
                            'value'   => 3
                        ],
                        [
                            'caption' => '4 - Frequenz tief/hoch',
                            'value'   => 4
                        ],
                        [
                            'caption' => '5 - Frequenz tief/mittel/hoch',
                            'value'   => 5
                        ],
                        [
                            'caption' => '6 - Frequenz hoch ein/aus',
                            'value'   => 6
                        ],
                        [
                            'caption' => '7 - Frequenz hoch ein, lang aus',
                            'value'   => 7
                        ],
                        [
                            'caption' => '8 - Frequenz tief ein/aus, hoch ein/aus',
                            'value'   => 8
                        ],
                        [
                            'caption' => '9 - Frequenz tief ein - lang aus, hoch ein - lang aus',
                            'value'   => 9
                        ],
                        [
                            'caption' => '10 - Batterie leer',
                            'value'   => 10
                        ],
                        [
                            'caption' => '11 - Unscharf',
                            'value'   => 11
                        ],
                        [
                            'caption' => '12 - Intern scharf',
                            'value'   => 12
                        ],
                        [
                            'caption' => '13 - Extern scharf',
                            'value'   => 13
                        ],
                        [
                            'caption' => '14 - Verzögert intern scharf',
                            'value'   => 14
                        ],
                        [
                            'caption' => '15 - Verzögert extern scharf',
                            'value'   => 15
                        ],
                        [
                            'caption' => '16 - Alarm Ereignis',
                            'value'   => 16
                        ],
                        [
                            'caption' => '17 - Fehler',
                            'value'   => 17
                        ]
                    ]
                ],
                [
                    'type'    => 'Select',
                    'name'    => 'PreAlarmOpticalSignal',
                    'caption' => 'Optisches Signal',
                    'options' => [
                        [
                            'caption' => '0 - Kein optisches Signal',
                            'value'   => 0
                        ],
                        [
                            'caption' => '1 - Abwechselndes langsames Blinken',
                            'value'   => 1
                        ],
                        [
                            'caption' => '2 - Gleichzeitiges langsames Blinken',
                            'value'   => 2
                        ],
                        [
                            'caption' => '3 - Gleichzeitiges schnelles Blinken',
                            'value'   => 3
                        ],
                        [
                            'caption' => '4 - Gleichzeitiges kurzes Blinken',
                            'value'   => 4
                        ],
                        [
                            'caption' => '5 - Bestätigungssignal 0 - lang lang',
                            'value'   => 5
                        ],
                        [
                            'caption' => '6 - Bestätigungssignal 1 - lang kurz',
                            'value'   => 6
                        ],
                        [
                            'caption' => '7 - Bestätigungssignal 2 - lang kurz kurz',
                            'value'   => 7
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Hauptalarm',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseMainAlarm',
                    'caption' => 'Hauptalarm'
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'MainAlarmDuration',
                    'caption' => 'Dauer',
                    'suffix'  => 'Sekunden'
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'MaximumSignallingAmountAcousticAlarm',
                    'caption' => 'Maximale Auslösungen',
                    'minimum' => 0,
                    'suffix'  => 'Anzahl'
                ],
                [
                    'type'    => 'Select',
                    'name'    => 'MainAlarmAcousticSignal',
                    'caption' => 'Akustisches Signal',
                    'options' => [
                        [
                            'caption' => '0 - Kein akustisches Signal',
                            'value'   => 0
                        ],
                        [
                            'caption' => '1 - Frequenz steigend',
                            'value'   => 1
                        ],
                        [
                            'caption' => '2 - Frequenz fallend',
                            'value'   => 2
                        ],
                        [
                            'caption' => '3 - Frequenz steigend/fallend',
                            'value'   => 3
                        ],
                        [
                            'caption' => '4 - Frequenz tief/hoch',
                            'value'   => 4
                        ],
                        [
                            'caption' => '5 - Frequenz tief/mittel/hoch',
                            'value'   => 5
                        ],
                        [
                            'caption' => '6 - Frequenz hoch ein/aus',
                            'value'   => 6
                        ],
                        [
                            'caption' => '7 - Frequenz hoch ein, lang aus',
                            'value'   => 7
                        ],
                        [
                            'caption' => '8 - Frequenz tief ein/aus, hoch ein/aus',
                            'value'   => 8
                        ],
                        [
                            'caption' => '9 - Frequenz tief ein - lang aus, hoch ein - lang aus',
                            'value'   => 9
                        ],
                        [
                            'caption' => '10 - Batterie leer',
                            'value'   => 10
                        ],
                        [
                            'caption' => '11 - Unscharf',
                            'value'   => 11
                        ],
                        [
                            'caption' => '12 - Intern scharf',
                            'value'   => 12
                        ],
                        [
                            'caption' => '13 - Extern scharf',
                            'value'   => 13
                        ],
                        [
                            'caption' => '14 - Verzögert intern scharf',
                            'value'   => 14
                        ],
                        [
                            'caption' => '15 - Verzögert extern scharf',
                            'value'   => 15
                        ],
                        [
                            'caption' => '16 - Alarm Ereignis',
                            'value'   => 16
                        ],
                        [
                            'caption' => '17 - Fehler',
                            'value'   => 17
                        ]
                    ]
                ],
                [
                    'type'    => 'Select',
                    'name'    => 'MainAlarmOpticalSignal',
                    'caption' => 'Optisches Signal',
                    'options' => [
                        [
                            'caption' => '0 - Kein optisches Signal',
                            'value'   => 0
                        ],
                        [
                            'caption' => '1 - Abwechselndes langsames Blinken',
                            'value'   => 1
                        ],
                        [
                            'caption' => '2 - Gleichzeitiges langsames Blinken',
                            'value'   => 2
                        ],
                        [
                            'caption' => '3 - Gleichzeitiges schnelles Blinken',
                            'value'   => 3
                        ],
                        [
                            'caption' => '4 - Gleichzeitiges kurzes Blinken',
                            'value'   => 4
                        ],
                        [
                            'caption' => '5 - Bestätigungssignal 0 - lang lang',
                            'value'   => 5
                        ],
                        [
                            'caption' => '6 - Bestätigungssignal 1 - lang kurz',
                            'value'   => 6
                        ],
                        [
                            'caption' => '7 - Bestätigungssignal 2 - lang kurz kurz',
                            'value'   => 7
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Nachalarm',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UsePostAlarm',
                    'caption' => 'Nachalarm'
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'PostAlarmDuration',
                    'caption' => 'Dauer',
                    'minimum' => 0,
                    'maximum' => 1800,
                    'suffix'  => 'Sekunden'
                ],
                [
                    'type'    => 'Select',
                    'name'    => 'PostAlarmOpticalSignal',
                    'caption' => 'Optisches Signal',
                    'options' => [
                        [
                            'caption' => '0 - Kein optisches Signal',
                            'value'   => 0
                        ],
                        [
                            'caption' => '1 - Abwechselndes langsames Blinken',
                            'value'   => 1
                        ],
                        [
                            'caption' => '2 - Gleichzeitiges langsames Blinken',
                            'value'   => 2
                        ],
                        [
                            'caption' => '3 - Gleichzeitiges schnelles Blinken',
                            'value'   => 3
                        ],
                        [
                            'caption' => '4 - Gleichzeitiges kurzes Blinken',
                            'value'   => 4
                        ],
                        [
                            'caption' => '5 - Bestätigungssignal 0 - lang lang',
                            'value'   => 5
                        ],
                        [
                            'caption' => '6 - Bestätigungssignal 1 - lang kurz',
                            'value'   => 6
                        ],
                        [
                            'caption' => '7 - Bestätigungssignal 2 - lang kurz kurz',
                            'value'   => 7
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Panikalarm',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UsePanicAlarm',
                    'caption' => 'Panikalarm'
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'PanicAlarmDuration',
                    'caption' => 'Dauer',
                    'suffix'  => 'Sekunden'
                ],
                [
                    'type'    => 'Select',
                    'name'    => 'PanicAlarmAcousticSignal',
                    'caption' => 'Akustisches Signal',
                    'options' => [
                        [
                            'caption' => '0 - Kein akustisches Signal',
                            'value'   => 0
                        ],
                        [
                            'caption' => '1 - Frequenz steigend',
                            'value'   => 1
                        ],
                        [
                            'caption' => '2 - Frequenz fallend',
                            'value'   => 2
                        ],
                        [
                            'caption' => '3 - Frequenz steigend/fallend',
                            'value'   => 3
                        ],
                        [
                            'caption' => '4 - Frequenz tief/hoch',
                            'value'   => 4
                        ],
                        [
                            'caption' => '5 - Frequenz tief/mittel/hoch',
                            'value'   => 5
                        ],
                        [
                            'caption' => '6 - Frequenz hoch ein/aus',
                            'value'   => 6
                        ],
                        [
                            'caption' => '7 - Frequenz hoch ein, lang aus',
                            'value'   => 7
                        ],
                        [
                            'caption' => '8 - Frequenz tief ein/aus, hoch ein/aus',
                            'value'   => 8
                        ],
                        [
                            'caption' => '9 - Frequenz tief ein - lang aus, hoch ein - lang aus',
                            'value'   => 9
                        ],
                        [
                            'caption' => '10 - Batterie leer',
                            'value'   => 10
                        ],
                        [
                            'caption' => '11 - Unscharf',
                            'value'   => 11
                        ],
                        [
                            'caption' => '12 - Intern scharf',
                            'value'   => 12
                        ],
                        [
                            'caption' => '13 - Extern scharf',
                            'value'   => 13
                        ],
                        [
                            'caption' => '14 - Verzögert intern scharf',
                            'value'   => 14
                        ],
                        [
                            'caption' => '15 - Verzögert extern scharf',
                            'value'   => 15
                        ],
                        [
                            'caption' => '16 - Alarm Ereignis',
                            'value'   => 16
                        ],
                        [
                            'caption' => '17 - Fehler',
                            'value'   => 17
                        ]
                    ]
                ],
                [
                    'type'    => 'Select',
                    'name'    => 'PanicAlarmOpticalSignal',
                    'caption' => 'Optisches Signal',
                    'options' => [
                        [
                            'caption' => '0 - Kein optisches Signal',
                            'value'   => 0
                        ],
                        [
                            'caption' => '1 - Abwechselndes langsames Blinken',
                            'value'   => 1
                        ],
                        [
                            'caption' => '2 - Gleichzeitiges langsames Blinken',
                            'value'   => 2
                        ],
                        [
                            'caption' => '3 - Gleichzeitiges schnelles Blinken',
                            'value'   => 3
                        ],
                        [
                            'caption' => '4 - Gleichzeitiges kurzes Blinken',
                            'value'   => 4
                        ],
                        [
                            'caption' => '5 - Bestätigungssignal 0 - lang lang',
                            'value'   => 5
                        ],
                        [
                            'caption' => '6 - Bestätigungssignal 1 - lang kurz',
                            'value'   => 6
                        ],
                        [
                            'caption' => '7 - Bestätigungssignal 2 - lang kurz kurz',
                            'value'   => 7
                        ]
                    ]
                ]
            ]
        ];

        ##### Element: Trigger list

        $triggerListValues = [];
        $variables = json_decode($this->ReadPropertyString('TriggerList'), true);
        $amountRows = count($variables) + 1;
        if ($amountRows == 1) {
            $amountRows = 3;
        }
        $amountVariables = count($variables);
        foreach ($variables as $variable) {
            $conditions = true;
            //Primary condition
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $triggerID = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($triggerID <= 1 || !@IPS_ObjectExists($triggerID)) {
                            $conditions = false;
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
                                if ($id <= 1 || !@IPS_ObjectExists($id)) {
                                    $conditions = false;
                                }
                            }
                        }
                    }
                }
            }
            $rowColor = '#FFC0C0'; //red
            if ($conditions) {
                $rowColor = '#C0FFC0'; //light green
                if (!$variable['Use']) {
                    $rowColor = '#DFDFDF'; //grey
                }
            }
            $triggerListValues[] = ['rowColor' => $rowColor];
        }

        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel4',
            'caption' => 'Auslöser',
            'items'   => [
                [
                    'type'    => 'PopupButton',
                    'caption' => 'Aktueller Status',
                    'popup'   => [
                        'caption' => 'Aktueller Status',
                        'items'   => [
                            [
                                'type'     => 'List',
                                'name'     => 'ActualVariableStateList',
                                'caption'  => 'Variablen',
                                'add'      => false,
                                'rowCount' => 1,
                                'sort'     => [
                                    'column'    => 'ActualStatus',
                                    'direction' => 'ascending'
                                ],
                                'columns' => [
                                    [
                                        'name'    => 'ActualStatus',
                                        'caption' => 'Aktueller Status',
                                        'width'   => '250px',
                                        'save'    => false
                                    ],
                                    [
                                        'name'    => 'SensorID',
                                        'caption' => 'ID',
                                        'width'   => '80px',
                                        'onClick' => self::MODULE_PREFIX . '_ModifyActualVariableStatesConfigurationButton($id, "ActualVariableStateConfigurationButton", $ActualVariableStateList["SensorID"]);',
                                        'save'    => false
                                    ],
                                    [
                                        'name'    => 'Designation',
                                        'caption' => 'Bezeichnung',
                                        'width'   => '400px',
                                        'save'    => false
                                    ],
                                    [
                                        'name'    => 'SignalingMode',
                                        'caption' => 'Modus',
                                        'width'   => '250px',
                                        'save'    => false
                                    ],
                                    [
                                        'name'    => 'LastUpdate',
                                        'caption' => 'Letzte Aktualisierung',
                                        'width'   => '200px',
                                        'save'    => false
                                    ]
                                ]
                            ],
                            [
                                'type'     => 'OpenObjectButton',
                                'name'     => 'ActualVariableStateConfigurationButton',
                                'caption'  => 'Bearbeiten',
                                'visible'  => false,
                                'objectID' => 0
                            ]
                        ]
                    ],
                    'onClick' => self::MODULE_PREFIX . '_GetActualVariableStates($id);'
                ],
                [
                    'type'     => 'List',
                    'name'     => 'TriggerList',
                    'caption'  => 'Auslöser',
                    'rowCount' => $amountRows,
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "TriggerListConfigurationButton", $TriggerList["PrimaryCondition"]);',
                            'width'   => '400px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Mehrfachauslösung',
                            'name'    => 'UseMultipleAlerts',
                            'width'   => '200px',
                            'add'     => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Primäre Bedingung',
                            'name'    => 'PrimaryCondition',
                            'width'   => '1000px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'SelectCondition'
                            ]
                        ],
                        [
                            'caption' => 'Weitere Bedingungen',
                            'name'    => 'SecondaryCondition',
                            'width'   => '1000px',
                            'add'     => '',
                            'edit'    => [
                                'type'  => 'SelectCondition',
                                'multi' => true
                            ]
                        ],
                        [
                            'caption' => 'Modus',
                            'name'    => 'SignalingMode',
                            'width'   => '300px',
                            'add'     => 0,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Keine Funktion',
                                        'value'   => 0
                                    ],
                                    [
                                        'caption' => 'Alarmsirene Aus',
                                        'value'   => 1
                                    ],
                                    [
                                        'caption' => 'Alarmsirene An (Alarmstufen)',
                                        'value'   => 2
                                    ],
                                    [
                                        'caption' => 'Voralarm',
                                        'value'   => 3
                                    ],
                                    [
                                        'caption' => 'Hauptalarm',
                                        'value'   => 4
                                    ],
                                    [
                                        'caption' => 'Nachalarm',
                                        'value'   => 5
                                    ],
                                    [
                                        'caption' => 'Panikalarm',
                                        'value'   => 6
                                    ],
                                    [
                                        'caption' => 'Benutzerdefiniert',
                                        'value'   => 7
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Benutzerdefiniert: Akustisches Signal',
                            'name'    => 'AcousticSignal',
                            'width'   => '300px',
                            'add'     => 0,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => '0 - Kein akustisches Signal',
                                        'value'   => 0
                                    ],
                                    [
                                        'caption' => '1 - Frequenz steigend',
                                        'value'   => 1
                                    ],
                                    [
                                        'caption' => '2 - Frequenz fallend',
                                        'value'   => 2
                                    ],
                                    [
                                        'caption' => '3 - Frequenz steigend/fallend',
                                        'value'   => 3
                                    ],
                                    [
                                        'caption' => '4 - Frequenz tief/hoch',
                                        'value'   => 4
                                    ],
                                    [
                                        'caption' => '5 - Frequenz tief/mittel/hoch',
                                        'value'   => 5
                                    ],
                                    [
                                        'caption' => '6 - Frequenz hoch ein/aus',
                                        'value'   => 6
                                    ],
                                    [
                                        'caption' => '7 - Frequenz hoch ein, lang aus',
                                        'value'   => 7
                                    ],
                                    [
                                        'caption' => '8 - Frequenz tief ein/aus, hoch ein/aus',
                                        'value'   => 8
                                    ],
                                    [
                                        'caption' => '9 - Frequenz tief ein - lang aus, hoch ein - lang aus',
                                        'value'   => 9
                                    ],
                                    [
                                        'caption' => '10 - Batterie leer',
                                        'value'   => 10
                                    ],
                                    [
                                        'caption' => '11 - Unscharf',
                                        'value'   => 11
                                    ],
                                    [
                                        'caption' => '12 - Intern scharf',
                                        'value'   => 12
                                    ],
                                    [
                                        'caption' => '13 - Extern scharf',
                                        'value'   => 13
                                    ],
                                    [
                                        'caption' => '14 - Verzögert intern scharf',
                                        'value'   => 14
                                    ],
                                    [
                                        'caption' => '15 - Verzögert extern scharf',
                                        'value'   => 15
                                    ],
                                    [
                                        'caption' => '16 - Alarm Ereignis',
                                        'value'   => 16
                                    ],
                                    [
                                        'caption' => '17 - Fehler',
                                        'value'   => 17
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Benutzerdefiniert: Optisches Signal',
                            'name'    => 'OpticalSignal',
                            'width'   => '300px',
                            'add'     => 0,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => '0 - Kein optisches Signal',
                                        'value'   => 0
                                    ],
                                    [
                                        'caption' => '1 - Abwechselndes langsames Blinken',
                                        'value'   => 1
                                    ],
                                    [
                                        'caption' => '2 - Gleichzeitiges langsames Blinken',
                                        'value'   => 2
                                    ],
                                    [
                                        'caption' => '3 - Gleichzeitiges schnelles Blinken',
                                        'value'   => 3
                                    ],
                                    [
                                        'caption' => '4 - Gleichzeitiges kurzes Blinken',
                                        'value'   => 4
                                    ],
                                    [
                                        'caption' => '5 - Bestätigungssignal 0 - lang lang',
                                        'value'   => 5
                                    ],
                                    [
                                        'caption' => '6 - Bestätigungssignal 1 - lang kurz',
                                        'value'   => 6
                                    ],
                                    [
                                        'caption' => '7 - Bestätigungssignal 2 - lang kurz kurz',
                                        'value'   => 7
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Benutzerdefiniert: Einheit Zeitdauer',
                            'name'    => 'DurationUnit',
                            'width'   => '300px',
                            'add'     => 0,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => '0 - Sekunden',
                                        'value'   => 0
                                    ],
                                    [
                                        'caption' => '1 - Minuten',
                                        'value'   => 1
                                    ],
                                    [
                                        'caption' => '2 - Stunden',
                                        'value'   => 2
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Benutzerdefiniert: Wert Zeitdauer',
                            'name'    => 'DurationValue',
                            'width'   => '300px',
                            'add'     => 5,
                            'edit'    => [
                                'type'    => 'NumberSpinner',
                                'minimum' => 0
                            ]
                        ]
                    ],
                    'values' => $triggerListValues,
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Anzahl Auslöser: ' . $amountVariables
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'TriggerListConfigurationButton',
                    'caption'  => 'Bearbeiten',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        ##### Element: Command control

        $id = $this->ReadPropertyInteger('CommandControl');
        $enableButton = false;
        if ($id > 1 && @IPS_ObjectExists($id)) {
            $enableButton = true;
        }
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel5',
            'caption' => 'Ablaufsteuerung',
            'items'   => [
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'SelectModule',
                            'name'     => 'CommandControl',
                            'caption'  => 'Instanz',
                            'moduleID' => self::ABLAUFSTEUERUNG_MODULE_GUID,
                            'width'    => '600px',
                            'onChange' => self::MODULE_PREFIX . '_ModifyButton($id, "CommandControlConfigurationButton", "ID " . $CommandControl . " konfigurieren", $CommandControl);'
                        ],
                        [
                            'type'     => 'OpenObjectButton',
                            'caption'  => 'ID ' . $id . ' konfigurieren',
                            'name'     => 'CommandControlConfigurationButton',
                            'visible'  => $enableButton,
                            'objectID' => $id
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateCommandControlInstance($id);'
                        ]
                    ]
                ]
            ]
        ];

        ##### Element: Alarm protocol

        $id = $this->ReadPropertyInteger('AlarmProtocol');
        $enableButton = false;
        if ($id > 1 && @IPS_ObjectExists($id)) {
            $enableButton = true;
        }
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel6',
            'caption' => 'Alarmprotokoll',
            'items'   => [
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'SelectModule',
                            'name'     => 'AlarmProtocol',
                            'caption'  => 'Instanz',
                            'moduleID' => self::ALARMPROTOCOL_MODULE_GUID,
                            'width'    => '600px',
                            'onChange' => self::MODULE_PREFIX . '_ModifyButton($id, "AlarmProtocolConfigurationButton", "ID " . $AlarmProtocol . " konfigurieren", $AlarmProtocol);'
                        ],
                        [
                            'type'     => 'OpenObjectButton',
                            'caption'  => 'ID ' . $id . ' konfigurieren',
                            'name'     => 'AlarmProtocolConfigurationButton',
                            'visible'  => $enableButton,
                            'objectID' => $id
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateAlarmProtocolInstance($id);'
                        ]
                    ]
                ],
                [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'Location',
                    'caption' => 'Standortbezeichnung (z.B. Musterstraße 1)',
                    'width'   => '600px'
                ]
            ]
        ];

        ##### Element: Automatic deactivation

        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel7',
            'caption' => 'Deaktivierung',
            'items'   => [
                [
                    'type'    => 'CheckBox',
                    'name'    => 'UseAutomaticDeactivation',
                    'caption' => 'Automatische Deaktivierung'
                ],
                [
                    'type'    => 'SelectTime',
                    'name'    => 'AutomaticDeactivationStartTime',
                    'caption' => 'Startzeit'
                ],
                [
                    'type'    => 'SelectTime',
                    'name'    => 'AutomaticDeactivationEndTime',
                    'caption' => 'Endzeit'
                ]
            ]
        ];

        ##### Element: Visualisation

        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel8',
            'caption' => 'Visualisierung',
            'items'   => [
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableActive',
                    'caption' => 'Aktiv'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmSiren',
                    'caption' => 'Alarmsirene'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableAlarmLevel',
                    'caption' => 'Alarmstufe'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableSignalingAmount',
                    'caption' => 'Auslösungen'
                ],
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableResetSignalingAmount',
                    'caption' => 'Rückstellung'
                ]
            ]
        ];

        ########## Actions

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => 'Schaltelemente'
            ];

        //Test center
        $form['actions'][] =
            [
                'type' => 'TestCenter'
            ];

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => ' '
            ];

        //Registered references
        $registeredReferences = [];
        $references = $this->GetReferenceList();
        $amountReferences = count($references);
        if ($amountReferences == 0) {
            $amountReferences = 3;
        }
        foreach ($references as $reference) {
            $name = 'Objekt #' . $reference . ' existiert nicht';
            $location = '';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($reference)) {
                $name = IPS_GetName($reference);
                $location = IPS_GetLocation($reference);
                $rowColor = '#C0FFC0'; //light green
            }
            $registeredReferences[] = [
                'ObjectID'         => $reference,
                'Name'             => $name,
                'VariableLocation' => $location,
                'rowColor'         => $rowColor];
        }

        //Registered messages
        $registeredMessages = [];
        $messages = $this->GetMessageList();
        $amountMessages = count($messages);
        if ($amountMessages == 0) {
            $amountMessages = 3;
        }
        foreach ($messages as $id => $messageID) {
            $name = 'Objekt #' . $id . ' existiert nicht';
            $location = '';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($id)) {
                $name = IPS_GetName($id);
                $location = IPS_GetLocation($id);
                $rowColor = '#C0FFC0'; //light green
            }
            switch ($messageID) {
                case [10001]:
                    $messageDescription = 'IPS_KERNELSTARTED';
                    break;

                case [10603]:
                    $messageDescription = 'VM_UPDATE';
                    break;

                default:
                    $messageDescription = 'keine Bezeichnung';
            }
            $registeredMessages[] = [
                'ObjectID'           => $id,
                'Name'               => $name,
                'VariableLocation'   => $location,
                'MessageID'          => $messageID,
                'MessageDescription' => $messageDescription,
                'rowColor'           => $rowColor];
        }

        //Developer area
        $form['actions'][] = [
            'type'    => 'ExpansionPanel',
            'caption' => 'Entwicklerbereich',
            'items'   => [
                [
                    'type'    => 'Label',
                    'caption' => 'Registrierte Referenzen',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredReferences',
                    'rowCount' => $amountReferences,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredReferencesConfigurationButton", "ID " . $RegisteredReferences["ObjectID"] . " bearbeiten", $RegisteredReferences["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                        ],
                        [
                            'caption' => 'Objektbaum',
                            'name'    => 'VariableLocation',
                            'width'   => '700px'
                        ]
                    ],
                    'values' => $registeredReferences
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'RegisteredReferencesConfigurationButton',
                    'caption'  => 'Bearbeiten',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Registrierte Nachrichten',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredMessages',
                    'rowCount' => $amountMessages,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredMessagesConfigurationButton", "ID " . $RegisteredMessages["ObjectID"] . " bearbeiten", $RegisteredMessages["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                        ],
                        [
                            'caption' => 'Objektbaum',
                            'name'    => 'VariableLocation',
                            'width'   => '700px'
                        ],
                        [
                            'caption' => 'Nachrichten ID',
                            'name'    => 'MessageID',
                            'width'   => '150px'
                        ],
                        [
                            'caption' => 'Nachrichten Bezeichnung',
                            'name'    => 'MessageDescription',
                            'width'   => '250px'
                        ]
                    ],
                    'values' => $registeredMessages
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'RegisteredMessagesConfigurationButton',
                    'caption'  => 'Bearbeiten',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        //Dummy info message
        $form['actions'][] =
            [
                'type'    => 'PopupAlert',
                'name'    => 'InfoMessage',
                'visible' => false,
                'popup'   => [
                    'closeCaption' => 'OK',
                    'items'        => [
                        [
                            'type'    => 'Label',
                            'name'    => 'InfoMessageLabel',
                            'caption' => '',
                            'visible' => true
                        ]
                    ]
                ]
            ];

        ########## Status

        $form['status'][] = [
            'code'    => 101,
            'icon'    => 'active',
            'caption' => 'Alarmsirene Homematic IP wird erstellt',
        ];
        $form['status'][] = [
            'code'    => 102,
            'icon'    => 'active',
            'caption' => 'Alarmsirene Homematic IP ist aktiv',
        ];
        $form['status'][] = [
            'code'    => 103,
            'icon'    => 'active',
            'caption' => 'Alarmsirene Homematic IP wird gelöscht',
        ];
        $form['status'][] = [
            'code'    => 104,
            'icon'    => 'inactive',
            'caption' => 'Alarmsirene Homematic IP ist inaktiv',
        ];
        $form['status'][] = [
            'code'    => 200,
            'icon'    => 'inactive',
            'caption' => 'Es ist Fehler aufgetreten, weitere Informationen unter Meldungen, im Log oder Debug!',
        ];

        return json_encode($form);
    }
}