# 📑 Rapport de Réalisation : Projet PFE Slimane
**Sujet : Banc de Test et de Calibration Multicapteurs Haute Précision**  
**Auteur : Slimane**  
**Date : 14 Avril 2026**

---

## 📖 Introduction
Le but de ce projet de Fin d'Études (PFE) est de concevoir un banc de mesure environnementale rigoureux permettant de comparer et de calibrer plusieurs capteurs de température et d'humidité. La précision et la répétabilité des mesures sont les piliers de cette étude.

---

## 🛠️ Étape 1 : Choix Technologique et Banc DHT22
Initialement basé sur le capteur DHT11, le projet a migré vers le **DHT22 (AM2302)** pour sa résolution accrue et sa plage de mesure étendue.
- **Réalisation** : Mise en place d'un banc de **6 capteurs numériques**.
- **Protocole** : Utilisation du bus 1-Wire et intégration des bibliothèques Adafruit.
- **Optimisation** : Ajout de résistances de tirage (Pull-up) pour garantir l'intégrité du signal 1-Wire.

---

## 📉 Étape 2 : Analyse et Calibration Digitale
Suite à la capture de données via *CoolTerm*, une déviation de **11% d'humidité** et de **0.9°C** a été constatée sur les unités brutes.
- **Méthodologie** : Calcul d'une moyenne de référence globale et détermination d'une **matrice d'offsets**.
- **Résultat** : Injection de 12 offsets (6T / 6H) dans le code Arduino, réduisant l'écart entre les capteurs à moins de **0.1°C**.

---

## ⚡ Étape 3 : Banc Analogique LM35
Pour diversifier l'étude, un second banc basé sur le capteur analogique **LM35** a été implémenté.
- **Hardware** : Installation de 5 capteurs sur les ports ADC (A0-A4).
- **Optimisation du Bruit** : 
    - Ajout de résistances de charge de **2 kΩ à 10 kΩ** pour stabiliser le signal face aux parasites.
    - Implémentation logicielle d'un algorithme de **Sur-échantillonnage (Oversampling)** sur 25 lectures.
- **Calibration** : Analyse des logs analogiques et création d'une matrice corrective spécifique au LM35.

## 📂 Étape 4 : Consolidation et Documentation
Afin de rendre le projet exploitable pour un mémoire académique, tous les supports ont été regroupés.
- **Dossier Technique Master** : Création d'un document complet ([Dossier_Technique_PFE.md](./Dossier_Technique_PFE.md)) regroupant spécifications, calculs et schémas.
- **Codes Sources** : Nettoyage et commentaire intégral de tous les fichiers `.ino` en langue française pour une meilleure lisibilité.

---

## 📡 Étape 5 : Transmission Sans Fil (RF 433MHz)
La phase finale du projet a consisté à rendre le système communicant sans liaison filaire série vers l'ordinateur.
- **Hardware** : Intégration d'un module émetteur FS1000A et d'un **récepteur RF433**.
- **Brochage** : 
    - Émetteur : DATA sur la **broche 12**.
    - Récepteur : DATA sur la **broche 11**.
- **Logiciel** : Création d'un programme de réception dédié (`systeme_hybride_rx.ino`) pour décoder les trames en temps réel.
- **Défis** : Gestion de la portée et formatage des chaînes de caractères pour ne pas saturer le tampon RF.
- **Système Complet** : 1x DHT22 (Pin 2), 2x LM35 (A0, A1) et l'émetteur RF433.

---

## 🏁 Conclusion
Le système est désormais opérationnel et scientifiquement validé. 
- **Précision Numérique** : ~0.1°C (après calibration).
- **Précision Analogique** : ~0.5°C (avec filtrage ADC).

Le banc de test permet désormais de fournir des données fiables pour n'importe quelle application de surveillance climatique de précision.

---
*Fin du Rapport de Projet - PFE 2026*
