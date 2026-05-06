# 🌡️ Projet PFE Slimane : Monitoring de Précision
Dépôt centralisé pour les bancs de tests de validation thermique et hygrométrique. Ce projet implémente une **stratégie de calibration logicielle par offsets** pour garantir la fidélité des mesures.

---

## ⚡ Nouveauté : Section Calibration
Le projet inclut désormais une procédure rigoureuse pour aligner les capteurs :
👉 **[Accéder au Protocole de Calibration](./Dossier_Technique_PFE.md#4-protocole-de-calibration)**

*Cette section détaille la méthodologie mathématique et la matrice finale des 12 offsets utilisés dans le code.*

---

## 📂 Documentation Unifiée
Toutes les informations techniques sont regroupées dans le document maître :

👉 **[Dossier_Technique_PFE.md](./Dossier_Technique_PFE.md)**

---

## 🔌 Câblage du Système Hybride RF
Pour le dossier `systeme_hybride_rf`, utilisez le schéma suivant :

| Composant | Pin Arduino |
| :--- | :--- |
| **RF433 DATA (TX - Émetteur)** | **D12** |
| **RF433 DATA (RX - Récepteur)** | **D11** |
| **DHT22 DATA** | **D2** |
| **LM35 n°1** | **A0** |
| **LM35 n°2** | **A1** |
| VCC / GND | 5V / GND |

---

## 🚀 Accès Rapide aux Codes
| Banc de Test | Type | Dossier Source |
| :--- | :--- | :--- |
| **Banc n°1 : DHT22** | Numérique | 📁 [test_5_dht22/](./test_5_dht22/test_5_dht22.ino) |
| **Banc n°2 : LM35** | Analogique | 📁 [test_6_lm35/](./test_6_lm35/test_6_lm35.ino) |
| **Système Hybride RF**| Mixte + Sans-fil| 📁 [systeme_hybride_rf/](./systeme_hybride_rf/systeme_hybride_rf.ino) |
| **Système Hybride RX**| Récepteur RF | 📁 [systeme_hybride_rx/](./systeme_hybride_rx/systeme_hybride_rx.ino) |
| **Diagnostic** | Unitaire | 📁 [test_dht22/](./test_dht22/) & [test_lm35/](./test_lm35/) |

---

## 💻 Instructions de Lancement
1.  **Matériel** : Connectez vos capteurs (Pins D2-D7 pour DHT22, A0-A5 pour LM35).
2.  **Logiciel** : Assurez-vous d'utiliser la matrice d'offsets définie dans le dossier technique.
3.  **Traceur Série** : Visualisation en temps réel à **9600 bauds**.

---
*Projet Académique Validé - Slimane - Edition 2026*
