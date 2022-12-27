# Alarmsirene

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

### 1. Modulbeschreibung

Dieses Modul schaltet eine Variable als Alarmsirene in [IP-Symcon](https://www.symcon.de).

### 2. Voraussetzungen

- IP-Symcon ab Version 6.1

Sollten mehrere Variablen geschaltet werden, so sollte zusätzlich das Modul Ablaufsteuerung genutzt werden.

### 3. Schaubild

```
                                   +----------------------+
            Auslöser <-------------+ Alarmsirene (Modul)  |<------------- externe Aktion
           +-----------------------+                      |
           |                       | Akustischer Alarm    |
           v                       |                      |
+---------------------+            | Optischer Alarm      |           
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

Konnte der Befehl erfolgreich ausgeführt werden, liefert er als Ergebnis **TRUE**, andernfalls **FALSE**.

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

Konnte der Befehl erfolgreich ausgeführt werden, liefert er als Ergebnis **TRUE**, andernfalls **FALSE**.

| Parameter     | Wert  | Beschreibung   |
|---------------|-------|----------------|
| `INSTANCE_ID` |       | ID der Instanz |
| `STATE`       | false | Aus            |
|               | true  | An             |

Beispiel:
> ASIRHM_ToggleOpticalAlarm(12345, false);

---