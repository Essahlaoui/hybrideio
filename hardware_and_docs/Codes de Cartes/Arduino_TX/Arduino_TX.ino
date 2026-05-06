#include <RH_ASK.h>
#include <SPI.h>
#include "DHT.h"

/**
 * PROJET: HYBRIDE.IO - Node Émetteur RF433 (Arduino)
 * DESCRIPTION: Lit DHT22 + 2x LM35 et envoie via RF433
 * CORRECTIONS INCLUSES: Utilisation de dtostrf() pour la compatibilité des float sur Arduino AVR.
 */

// --- Configuration ---
const int DEVICE_ID = 17;  //Id carte 15
#define DHTPIN 2
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

// Initialisation du module RF (Par défaut, la broche DATA TX est la pin Digitale 12)
RH_ASK driver;

void setup() {
  Serial.begin(115200);
  dht.begin();

  if (!driver.init()) {
    Serial.println(F("RF Init Fail"));
  }
  Serial.println(F("Node TX Ready"));

  // RAPPEL MATÉRIEL : Si vous voyez "Node TX Ready" en boucle dans le moniteur,
  // ajoutez un condensateur (ex: 100µF) entre le 5V et le GND près de l'émetteur RF !
}

void loop() {
  // 1. Lecture des capteurs
  float h = dht.readHumidity() +3.77;     // DHT22 Humidi
  float t = dht.readTemperature() +0.20;  // DHT22 Temper

  // Lecture LM35 (Moyennage sur 50 lectures pour stabilité)
  float t_lm1 = readLM35(A0) - 0.23;  //**LM35 n°1** Internet
  float t_lm2 = readLM35(A1) - 0.78;  //**LM35 n°6** Externe

  // Vérification des erreurs du capteur DHT
  if (isnan(h) || isnan(t)) {
    Serial.println(F("Erreur de lecture du DHT22!"));
    delay(2000);  // Attendre un peu avant de réessayer
    return;       // On annule l'envoi si le capteur ne répond pas
  }

  // 2. Préparation du message (Correction AVR float avec dtostrf)
  char t_str[8], h_str[8], lm1_str[8], lm2_str[8];

  // Conversion des float en char array: dtostrf(valeur, largeur_min, precision, buffer)
  dtostrf(t, 4, 1, t_str);
  dtostrf(h, 4, 1, h_str);
  dtostrf(t_lm1, 4, 1, lm1_str);
  dtostrf(t_lm2, 4, 1, lm2_str);

  // Formatage final: I:ID, T:Temp, H:Hum, 1:LM1, 2:LM2
  char msg[64];
  snprintf(msg, sizeof(msg), "I:%d,T:%s,H:%s,1:%s,2:%s",
           DEVICE_ID, t_str, h_str, lm1_str, lm2_str);

  // 3. Envoi RF
  Serial.print(F("Sending: "));
  Serial.println(msg);

  driver.send((uint8_t *)msg, strlen(msg));
  driver.waitPacketSent();

  // Attente de 100 msecondes avant le prochain envoi
  delay(100);
}

// --- Fonctions Utilitaires ---

// Fonction pour lire et moyenner les valeurs du LM35
float readLM35(int pin) {
  long sum = 0;
  for (int i = 0; i < 50; i++) {
    sum += analogRead(pin);
    delay(2);  // Petite pause pour laisser l'ADC se stabiliser
  }
  // Formule: (ADC * (VCC_REEL / 1023.0)) * 100.0
  // NB: Si votre Arduino est alimenté en USB, la tension réelle est souvent de 4.7V à 4.8V.
  // Remplacez le "5.0" ci-dessous par la tension exacte mesurée au multimètre pour plus de précision.
  return ((sum / 50.0) * (5.0 / 1023.0)) * 100.0;
}