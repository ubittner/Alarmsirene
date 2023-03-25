# Alarmsirene

[![Image](../imgs/logo-homematic-ip.png)](https://homematic-ip.com/de)

Zur Verwendung dieses Moduls als Privatperson, Einrichter oder Integrator wenden Sie sich bitte zunächst an den Autor.

Für dieses Modul besteht kein Anspruch auf Fehlerfreiheit, Weiterentwicklung, sonstige Unterstützung oder Support.  
Bevor das Modul installiert wird, sollte unbedingt ein Backup von IP-Symcon durchgeführt werden.  
Der Entwickler haftet nicht für eventuell auftretende Datenverluste oder sonstige Schäden.  
Der Nutzer stimmt den o.a. Bedingungen, sowie den Lizenzbedingungen ausdrücklich zu.


### Inhaltsverzeichnis

1. [Modulbeschreibung](#1-modulbeschreibung)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Schaubild](#3-schaubild)
4. [Auslöser](#4-auslöser)
5. [Externe Aktion](#5-externe-aktion)
6. [PHP-Befehlsreferenz](#6-php-befehlsreferenz)
   1. [Alarmsirene schalten](#61-alarmsirene-schalten)
   2. [Signalisierung auslösen](#62-alternative-ansteuerung)
   3. [Alternative Ansteuerung](#63-alternative-ansteuerung)

### 1. Modulbeschreibung

Dieses Modul integriert eine [Homematic IP](https://homematic-ip.com/de) Alarmsirene HmIP-ASIR, HmIP-ASIR-2, HmIP-ASIR-O in [IP-Symcon](https://www.symcon.de).

### 2. Voraussetzungen

- IP-Symcon ab Version 6.1
- HmIP-ASIR
- HmIP-ASIR-2
- HmIP-ASIR-O

Sollten mehrere Homematic Geräte geschaltet werden, so sollte zusätzlich das Modul Ablaufsteuerung genutzt werden.

### 3. Schaubild

```
                                   +----------------------+
            Auslöser <-------------+ Alarmsirene (Modul)  |<------------- externe Aktion
           +-----------------------+                      |
           |                       | Alarmsirene          |
           v                       |                      |
+---------------------+            | Alarmstufe           |
| Alarmierung (Modul) |            | Auslösungen          |
|                     |            | Rückstellung         |
| Alarmierung         |            |                      |
|                     |            | Quittungston         |
| Alarmstufe          |            +-------+--+-----------+
+---------------------+                    |  |
                                           |  |
                                           |  |    +---------------------------+
                                           |  +--->|  Ablaufsteuerung (Modul)  |
                                           |       +--------------+------------+
                                           |                      |
                                           |                      |
                                           v                      |
                                   +--------------------+         |
                                   |  Alarmsirene (HW)  |<--------+
                                   +--------------------+
```

### 4. Auslöser

Das Modul Alarmsirene reagiert auf verschiedene Auslöser.  
Als Auslöser kann auch das Modul Alarmierung genutzt werden.  

### 5. Externe Aktion

Das Modul Alarmsirene kann über eine externe Aktion geschaltet werden.  
Nachfolgendes Beispiel löst einen akustischen und optischen Alarm für 180 Sekunden aus.  

> ASIRHMIP_ToggleAlarmSiren(12345, true);

### 6. PHP-Befehlsreferenz

#### 6.1 Alarmsirene schalten

```
ASIRHMIP_ToggleAlarmSiren(integer INSTANCE_ID, boolean STATE);
```

Der Befehl liefert keinen Rückgabewert.

| Parameter     | Wert  | Bezeichnung    |
|---------------|-------|----------------|
| `INSTANCE_ID` |       | ID der Instanz |
| `STATE`       | false | Aus            |
|               | true  | An             |

Beispiel:
> ASIRHMIP_ToggleAlarmSiren(12345, false);

---

#### 6.2 Signalisierung auslösen

```
ASIRHMIP_ExecuteSignaling(integer INSTANCE_ID, integer ACOUSTIC_ALARM_SELECTION, integer OPTICAL_ALARM_SELECTION, integer DURATION_UNIT, integer DURATION_VALUE);
```

Der Befehl liefert keinen Rückgabewert.

| Parameter                  | Wert            | Bezeichnung                            | Beschreibung                                      |
|----------------------------|-----------------|----------------------------------------|---------------------------------------------------|
| `INSTANCE_ID`              |                 | ID der Instanz                         |                                                   |
|                            |                 |                                        |                                                   |
| `ACOUSTIC_ALARM_SELECTION` |                 | Akustische Signal                      |                                                   |
|                            | 0               | DISABLE_ACOUSTIC_SIGNAL                | Kein akustisches Signal                           |
|                            | 1               | FREQUENCY_RISING                       | Frequenz steigend                                 |
|                            | 2               | FREQUENCY_FALLING                      | Frequenz fallend                                  |
|                            | 3               | FREQUENCY_RISING_AND_FALLING           | Frequenz steigend/fallend                         |
|                            | 4               | FREQUENCY_ALTERNATING_LOW_HIGH         | Frequenz tief/hoch                                |
|                            | 5               | FREQUENCY_ALTERNATING_LOW_MID_HIGH     | Frequenz tief/mittel/hoch                         |
|                            | 6               | FREQUENCY_HIGHON_OFF                   | Frequenz hoch ein/aus                             |
|                            | 7               | FREQUENCY_HIGHON_LONGOFF               | Frequenz hoch ein, lang aus                       |
|                            | 8               | FREQUENCY_LOWON_OFF_HIGHON_OFF         | Frequenz tief ein/aus, hoch ein/aus               |
|                            | 9               | FREQUENCY_LOWON_LONGOFF_HIGHON_LONGOFF | Frequenz tief ein - lang aus, hoch ein - lang aus |
|                            | 10              | LOW_BATTERY                            | Batterie leer                                     |
|                            | 11              | DISARMED                               | Unscharf                                          |
|                            | 12              | INTERNALLY_ARMED                       | Intern scharf                                     |         
|                            | 13              | EXTERNALLY_ARMED                       | Extern scharf                                     |
|                            | 14              | DELAYED_INTERNALLY_ARMED               | Verzögert intern scharf                           |
|                            | 15              | DELAYED_EXTERNALLY_ARMED               | Verzögert extern scharf                           |
|                            | 16              | EVENT                                  | Alarm Ereignis                                    |
|                            | 17              | ERROR                                  | Fehler                                            |
|                            |                 |                                        |                                                   |
| `OPTICAL_ALARM_SELECTION`  |                 | Optische Signalisierung                |                                                   |
|                            | 0               | DISABLE_OPTICAL_SIGNAL                 | Kein optisches Signal                             | 
|                            | 1               | BLINKING_ALTERNATELY_REPEATING         | Abwechselndes langsames Blinken                   | 
|                            | 2               | BLINKING_BOTH_REPEATING                | Gleichzeitiges langsames Blinken                  | 
|                            | 3               | DOUBLE_FLASHING_REPEATING              | Gleichzeitiges schnelles Blinken                  | 
|                            | 4               | FLASHING_BOTH_REPEATING                | Gleichzeitiges kurzes Blinken                     |  
|                            | 5               | CONFIRMATION_SIGNAL_0 LONG_LONG        | Bestätigungssignal 0 - lang lang                  | 
|                            | 6               | CONFIRMATION_SIGNAL_1 LONG_SHORT       | Bestätigungssignal 1 - lang kurz                  | 
|                            | 7               | CONFIRMATION_SIGNAL_2 LONG_SHORT_SHORT | Bestätigungssignal 2 - lang kurz kurz             | 
|                            |                 |                                        |                                                   |
| `DURATION_UNIT`            |                 | Einheit Zeitdauer                      |                                                   |
|                            | 0               | SECONDS                                | Sekunden                                          |
|                            | 1               | MINUTES                                | Minuten                                           |
|                            | 2               | HOURS                                  | Stunden                                           |
|                            |                 |                                        |                                                   |
| `DURATION_VALUE`           |                 | Wert Zeitdauer                         |                                                   |
| 0 - n                      | DURATION_VALUE  | Wert                                   |                                                   |

Empfohlene Werte für die einzelnen Alarmstufen:

| Alarmstufe | Akustisches Signal | Optisches Signal |
|------------|--------------------|------------------|
| Voralarm   | 10                 | 3                |
| Hauptalarm | 3                  | 3                |
| Nachalarm  | 0                  | 1                |
| Panikalarm | 3                  | 3                |
| Unscharf   | 16                 | 2                |
| Scharf     | 17                 | 3                |

Nachfolgendes Beispiel löst einen akustischen und optischen Alarm für 60 Sekunden aus.

Beispiel:  
> ASIRHMIP_ExecuteSignaling(12345, 3, 2, 3, 60);

---

#### 6.3 Alternative Ansteuerung

Die Ansteuerung kann alternativ auch direkt an das Gerät erfolgen.

| Gerät       | Kanal |
|-------------|-------|
| HmIP-ASIR   | 3     |
| HmIP-ASIR-2 | 3     |
| HmIP-ASIR-0 | 3     |

```
boolean HM_WriteValueInteger(INSTANCE_ID, 'ACOUSTIC_ALARM_SELECTION', ACOUSTIC_ALARM_SELECTION);  
boolean HM_WriteValueInteger(INSTANCE_ID, 'OPTICAL_ALARM_SELECTION', OPTICAL_ALARM_SELECTION);  
boolean HM_WriteValueInteger(INSTANCE_ID, 'DURATION_UNIT', DURATION_UNIT);  
boolean HM_WriteValueInteger(INSTANCE_ID, 'DURATION_VALUE', DURATION_VALUE);
```

Konnte der jeweilige Befehl erfolgreich ausgeführt werden, liefert er als Ergebnis **TRUE**, andernfalls **FALSE**.  

| Parameter                  | Beschreibung                   |
|----------------------------|--------------------------------|
| `INSTANCE_ID`              | ID der Geräte-Instanz          |
| `ACOUSTIC_ALARM_SELECTION` | Akustischer Alarm (siehe oben) |
| `OPTICAL_ALARM_SELECTION`  | Optischer Alarm (siehe oben)   |
| `DURATION_UNIT`            | Einheit Zeitdauer (siehe oben) |
| `DURATION_VALUE`           | Wert Zeitdauer (siehe oben)    |


Die Werte für **ACOUSTIC_ALARM_SELECTION**, **OPTICAL_ALARM_SELECTION**, **DURATION_UNIT** und **DURATION_VALUE** entnehmen Sie bitte der entsprechenden Tabelle.

Beispiel:
> HM_WriteValueInteger(12345, 'ACOUSTIC_ALARM_SELECTION', 1);  
> HM_WriteValueInteger(12345, 'OPTICAL_ALARM_SELECTION', 2);  
> HM_WriteValueInteger(12345, 'DURATION_UNIT', 0);  
> HM_WriteValueInteger(12345, 'DURATION_VALUE', 180);

---