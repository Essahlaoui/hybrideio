# 📘 Dossier Technique Complet : Projet PFE Hybride.IO
**Système de Monitoring Environnemental Multicapteurs & IoT**  
*Edition 2026 - Rapport de Validation, Calibration et Architecture Cloud*

---

## 📑 Table des Matières
1.  [Présentation du Projet](#1-présentation-du-projet)
2.  [Capteurs & Instrumentation (DHT22 / LM35)](#2-capteurs-instrumentation)
3.  [PROTOCOLE DE CALIBRATION (Matrice logicielle)](#3-protocole-de-calibration)
4.  [SYSTÈME HYBRIDE SANS FIL (RF 433MHz)](#4-système-hybride-rf)
5.  [ARCHITECTURE SERVER-SIDE & DASHBOARD WEB](#5-architecture-web)
6.  [MODERNISATION FIRMWARE (ESP32 Multi-Tasking)](#6-firmware-esp32)
7.  [SÉCURITÉ DES DONNÉES (Redondance SD)](#7-securite-sd)
8.  [GUIDE D'INSTALLATION & RESSOURCES](#8-guide-ressources)

---

## 1. Présentation du Projet
Le projet **Hybride.IO** est un système industriel d'acquisition de données environnementales. La problématique centrale est la **fidélité de mesure** et la **résilience de transmission** : garantir que les données soient précises (calibration) et qu'elles arrivent à destination (architecture hybride RF/WiFi).

---

## 2. <a name="capteurs-instrumentation"></a>Capteurs & Instrumentation

### DHT22 (Numérique)
- **Précision** : ±2% RH / ±0.5°C.
- **Protocole** : 1-Wire (Trame de 40 bits avec Checksum).

### LM35 (Analogique)
- **Précision** : 10.0 mV/°C.
- **Optimisation Hardware** : Résistance de charge (10 kΩ) entre Vout et GND pour stabiliser le signal.
- **Filtrage** : Sur-échantillonnage de **50 lectures** moyennées pour éliminer le bruit électrique.

---

## 3. <a name="protocole-de-calibration"></a>PROTOCOLE DE CALIBRATION

### Méthodologie
Calcul des Offsets via la **Moyenne Arithmétique du Banc de Test** :
1.  Calcul de la Référence ($V_{Ref}$) à un instant $t$ stable.
2.  $Offset_i = V_{Ref} - Valeur_{brute_i}$.

### Matrice Finale (Extraits)
| Capteur | Offset Temp (°C) | Offset Hum (%) |
| :--- | :--- | :--- |
| **DHT22 n°1** | **0.00** | **+5.07** |
| **LM35 n°1** | **-0.78** | ✅ Calibré |
| **LM35 n°6** | **-0.23** | ✅ Calibré |

---

## 4. <a name="système-hybride-rf"></a>SYSTÈME HYBRIDE SANS FIL (RF 433MHz)

### Protocole Hybride.IO
Encapsulation des données dans une trame string compacte pour maximiser la portée :
`"I:ID,T:TEMP,H:HUM,1:LM1,2:LM2"`

**Stabilité** : Utilisation de la fonction `dtostrf()` pour garantir la transmission des chiffres après la virgule sur les architectures AVR (Arduino Uno).

---

## 5. <a name="architecture-web"></a>ARCHITECTURE SERVER-SIDE & DASHBOARD WEB

### Ingestion des Données (`save.php`)
- **Moteur Regex** : Analyseur de trames flexible capable de traiter des IDs alphanumériques.
- **Optimisation IP** : Communication forcée via IP directe (**89.168.46.165**) pour contourner les instabilités DNS.

### Dashboard Moderne
- **Design Glassmorphism** : Interface futuriste basée sur des flous d'arrière-plan et des bordures néon.
- **Générateur de Code** : Outil permettant de générer instantanément le firmware d'un node en y injectant automatiquement ses offsets de calibration.

---

## 6. <a name="firmware-esp32"></a>MODERNISATION FIRMWARE (ESP32 Multi-Tasking)

Pour éviter toute perte de données lors des envois WiFi (qui sont bloquants), la Gateway utilise une architecture **Multi-Tasking** sur ESP32 :
- **Cœur 0 (Tâche Radio)** : Écoute en temps réel du récepteur RF433.
- **Cœur 1 (Tâche Système)** : Gestion du WiFi, du capteur local et de l'envoi HTTP.
- **Communication** : Utilisation d'une **File d'attente (Queue)** pour stocker les messages reçus pendant que le WiFi est occupé.

---

## 7. <a name="securite-sd"></a>SÉCURITÉ DES DONNÉES (Redondance SD)

Mise en place d'un **Node Logger SD** indépendant :
- **Rôle** : Reçoit tous les messages RF et les enregistre dans un fichier `datalog.csv`.
- **Résilience** : Garantit la conservation des données même en cas de coupure internet ou de panne serveur.
- **Compatibilité SPI** : Déplacement du récepteur radio sur la **Pin 2** pour libérer le bus SPI de la carte SD.

---

## 8. <a name="guide-ressources"></a>GUIDE D'INSTALLATION & RESSOURCES

### Driver ESP32 (CP210x)
Si votre ordinateur ne reconnaît pas l'ESP32 lors du branchement USB, installez le driver suivant :
👉 [**Télécharger le Driver CP210x (Windows Universal)**](http://hybrideio.duckdns.org/assets/drivers/CP210x_ESP32_Driver.zip)

### Déploiement
1.  Configurer le WiFi dans `config.php` (Server) et dans le firmware (Node).
2.  Utiliser le **Générateur de Code** pour uploader le firmware calibré.
3.  Vérifier la réception en temps réel sur la page **Nodes**.

---
*Projet PFE Hybride.IO - Rapport de Développement Technique - 2026*
