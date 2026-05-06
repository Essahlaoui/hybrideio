# 🌍 Hybride.IO - Système de Monitoring IoT Intelligent

**Projet de Fin d'Études (PFE) 2026**  
*Système hybride d'acquisition de données environnementales avec calibration logicielle et dashboard temps réel.*

---

## 🚀 Présentation
**Hybride.IO** est une plateforme IoT complète conçue pour surveiller des environnements critiques (serres, salles serveurs, laboratoires). Le projet résout deux problématiques majeures :
1.  **La précision des mesures** : Grâce à une matrice de calibration par offsets intégrée au générateur de firmware.
2.  **La résilience de transmission** : Utilisation d'un protocole hybride RF 433MHz pour la portée et WiFi/Ethernet pour la connectivité cloud.

---

## 🛠️ Architecture Technique

### 🛰️ Hardware (Nodes & Gateway)
- **Nodes Émetteurs** : Arduino Uno / Nano + DHT22 + LM35 + Émetteur RF433.
- **Gateway (Passerelle)** : ESP32 (Multi-tasking) ou Arduino Ethernet Shield.
- **Logger SD** : Système de secours autonome pour enregistrement hors-ligne sur carte SD.

### 💻 Software (Stack Web)
- **Backend** : PHP 8 / MySQL (API d'ingestion ultra-rapide).
- **Frontend** : Dashboard moderne en Glassmorphism (HTML/CSS/JS).
- **Générateur** : Outil web de génération de firmware `.ino` avec offsets de calibration injectés.

---

## 📂 Structure du Dépôt
- `/` : Racine du projet web (Dashboard, API, CSS).
- `/pages/` : Pages du dashboard (Nodes, Graphiques, Générateur).
- `/includes/` : Configuration et logique backend.
- `/hardware_and_docs/` : 
  - `Codes de Cartes/` : Firmwares Arduino & ESP32.
  - `Config Server/` : Dossier Technique PFE et configurations serveur.
  - `assets/drivers/` : Pilotes USB pour les cartes ESP32.

---

## ⚙️ Installation Rapide

1.  **Serveur Web** : 
    - Déployez le dossier racine sur un serveur PHP/MySQL (XAMPP ou Linux).
    - Importez la base de données via `init_gateway.sql`.
    - Configurez `includes/config.php`.
2.  **Matériel** :
    - Utilisez le **Générateur de Code** sur le dashboard pour créer votre firmware.
    - Flashez vos cartes Arduino/ESP32 via l'IDE Arduino.
    - Installez le driver CP210x si nécessaire (disponible dans `/assets/drivers/`).

---

## 📊 Calibration (Indispensable PFE)
Le système intègre une méthode scientifique de calibration :
1.  Calcul de la moyenne d'un banc de test.
2.  Calcul de l'écart type (Offset) pour chaque capteur.
3.  Correction logicielle automatique dans le firmware généré.

---

## ✍️ Auteur
**Slimane Essahlaoui**  
*Étudiant en Ingénierie / PFE 2026*

---
*Projet développé pour la validation du Diplôme de Fin d'Études.*
