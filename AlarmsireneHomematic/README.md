# Alarmsirene

[![Image](../imgs/logo-homematic.png)](https://homematic-ip.com/de)

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
   1. [Akustischer Alarm](#61-akustischer-alarm)
   2. [Optischer Alarm](#62-optischer-alarm)
   3. [Quittungston](#63-quittungston)
   4. [Alternative Ansteuerung](#64-alternative-ansteuerung)

### 1. Modulbeschreibung

Dieses Modul integriert eine [Homematic](https://www.eq-3.de/produkte/homematic.html) Alarmsirene HM-Sec-Sir-WM, HM-Sec-SFA-SM, HM-LC-Sw4-WM, HM-LC-Sw2-FM in [IP-Symcon](https://www.symcon.de).

### 2. Voraussetzungen

- IP-Symcon ab Version 6.1

| Gerät         | Beschreibung                             | Funktion          | Kanal   | Typ     | Parameter | Typ     | Verfügbar          | 
|---------------|------------------------------------------|-------------------|---------|---------|-----------|---------|--------------------|
| HM-Sec-Sir-WM | Homematic Innensirene                    | Akustischer Alarm | 3       | Instanz | STATE     | boolean | :white_check_mark: |
| HM-Sec-Sir-WM |                                          | Optischer Alarm   | -       | -       | -         | -       | :x:                |
| HM-Sec-Sir-WM |                                          | Quittungston      | 4       | Instanz | ARMSTATE  | integer | :white_check_mark: |
|               |                                          |                   |         |         |           |         |                    |
| HM-Sec-SFA-SM | Homematic Funk Sirenen-Blitz-Ansteuerung | Akustischer Alarm | 1       | Instanz | STATE     | boolean | :white_check_mark: |
| HM-Sec-SFA-SM |                                          | Optischer Alarm   | 2       | Instanz | STATE     | boolean | :white_check_mark: |
| HM-Sec-SFA-SM |                                          | Quittungston      | -       | -       | -         | -       | :x:                |
|               |                                          |                   |         |         |           |         |                    |
| HM-LC-Sw4-WM  | Homematic 4-fach Funk-Schaltaktor        | Akustischer Alarm | n (1-4) | Instanz | STATE     | boolean | :white_check_mark: |
| HM-LC-Sw4-WM  |                                          | Optischer Alarm   | n (1-4) | Instanz | STATE     | boolean | :white_check_mark: |
| HM-LC-Sw4-WM  |                                          | Quittungston      | -       | -       | -         | -       | :x:                |
|               |                                          |                   |         |         |           |         |                    |
| HM-LC-Sw2-FM  | Homematic 2-fach Funk-Schaltaktor        | Akustischer Alarm | n (1-2) | Instanz | STATE     | boolean | :white_check_mark: |
| HM-LC-Sw2-FM  |                                          | Optischer Alarm   | n (1-2) | Instanz | STATE     | boolean | :white_check_mark: |
| HM-LC-Sw2-FM  |                                          | Quittungston      | -       | -       | -         | -       | :x:                |

Sollten mehrere Homematic Geräte geschaltet werden, so sollte zusätzlich das Modul Ablaufsteuerung genutzt werden.

### 3. Schaubild

```
                                   +----------------------+
            Auslöser <-------------+ Alarmsirene (Modul)  |<------------- externe Aktion
           +-----------------------+                      |
           |                       | Akustischer Alarm    |
           |                       |                      |
           |                       | Optischer Alarm      |           
           v                       |                      |                     
+---------------------+            | Quittungston         |
| Alarmierung (Modul) |            +-------+--+-----------+
|                     |                    |  |
| Alarmierung         |                    |  |
|                     |                    |  |
| Alarmstufe          |                    |  |
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
Nachfolgendes Beispiel löst einen akustischen Alarm aus.  

> ASIRHM_ToggleAcousticAlarm(12345, true);

### 6. PHP-Befehlsreferenz

#### 6.1 Akustischer Alarm

```
boolean ASIRHM_ToggleAcousticAlarm(integer INSTANCE_ID, boolean STATE);
```

Der Befehl liefert keinen Rückgabewert.  

| Parameter     | Wert  | Bezeichnung    |
|---------------|-------|----------------|
| `INSTANCE_ID` |       | ID der Instanz |
| `STATE`       | false | Aus            |
|               | true  | An             |

Beispiel:  
> ASIRHM_ToggleAcousticAlarm(12345, false);

---

#### 6.2 Optischer Alarm

```
boolean ASIRHM_ToggleOpticalAlarm(integer INSTANCE_ID, boolean STATE);
```

Der Befehl liefert keinen Rückgabewert.  

| Parameter     | Wert  | Beschreibung   |
|---------------|-------|----------------|
| `INSTANCE_ID` |       | ID der Instanz |
| `STATE`       | false | Aus            |
|               | true  | An             |

Beispiel:
> ASIRHM_ToggleOpticalAlarm(12345, false);

---

#### 6.3 Quittungston

```
boolean ASIRHM_ExecuteToneAcknowledgement(integer INSTANCE_ID, integer VALUE);
```

Der Befehl liefert keinen Rückgabewert.  

| Parameter     | Wert | Beschreibung                         |
|---------------|------|--------------------------------------|
| `INSTANCE_ID` |      | ID der Instanz                       |                           
| `VALUE`       | 0    | Alarm Aus                            |
|               | 1    | Außensensoren scharf (intern scharf) |
|               | 2    | Alle Sensoren scharf (extern scharf) |
|               | 3    | Alarm blockiert                      |

Beispiel:
> ASIRHM_ExecuteToneAcknowledgement(12345, 2);

---

#### 6.4 Alternative Ansteuerung

Die Ansteuerung kann alternativ auch direkt an das Gerät erfolgen.

| Gerät         | Kanal | Beschreibung   | Parameter  | Typ      | Wert                                       |
|---------------|-------|----------------|------------|----------|--------------------------------------------|
| HM-Sec-Sir-WM | 3     | Panik Alarm    | STATE      | boolean  | false = Aus                                |
| HM-Sec-Sir-WM | 3     | Panik Alarm    | STATE      | boolean  | true = Ein                                 |
| HM-Sec-Sir-WM | 4     | Scharfschalten | ARMSTATE   | integer  | 0 = Alarm Aus                              |
| HM-Sec-Sir-WM | 4     | Scharfschalten | ARMSTATE   | integer  | 1 = Außensensoren scharf (intern scharf)   |
| HM-Sec-Sir-WM | 4     | Scharfschalten | ARMSTATE   | integer  | 2 = Alle Sensoren scharf (extern scharf)   |
| HM-Sec-Sir-WM | 4     | Scharfschalten | ARMSTATE   | integer  | 3 = Alarm blockiert                        |


```
boolean HM_WriteValueBoolean(integer INSTANCE_ID, string STATE, boolean VALUE);  
boolean HM_WriteValueInteger(integer INSTANCE_ID, string ARMSTATE, integer VALUE);  
```

Konnte der jeweilige Befehl erfolgreich ausgeführt werden, liefert er als Ergebnis **TRUE**, andernfalls **FALSE**.  

| Parameter        | Beschreibung          |
|------------------|-----------------------|
| `INSTANCE_ID`    | ID der Geräte-Instanz |
| `STATE`          | Alarm                 |
| `ARMSTATE`       | Quittungston          |
| `VALUE`          | Wert                  |

Die Werte für **STATE** und **ARMSTATE** entnehmen Sie bitte der entsprechenden Tabelle.

Beispiel Alarm (Ein):
> HM_WriteValueBoolean(12345, 'STATE', true);  

Beispiel Quittungston (Alle Sensoren scharf):
> HM_WriteValueInteger(98765, 'ARMSTATE', 2);

---