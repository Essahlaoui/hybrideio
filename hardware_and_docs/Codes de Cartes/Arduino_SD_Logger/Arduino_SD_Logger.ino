#include <SPI.h>
#include <SD.h>
#include <RH_ASK.h>

/**
 * PROJET: HYBRIDE.IO - Data Logger SD de Secours
 * DESCRIPTION: Reçoit les données RF433 et les enregistre sur carte SD (format CSV)
 * MATÉRIEL: Arduino Uno, Module SD, Récepteur RF433
 */

// --- Configuration ---
const int RF_RX_PIN = 2;      
const char* filename = "datalog.csv";
int currentCS = 10; // Par défaut

// Initialisation Radio (2000 bps, RX: 2, TX: 3, PTT: 5)
// On libère les pins SPI (10, 11, 12, 13) pour la SD
RH_ASK driver(2000, RF_RX_PIN, 3, 5);

void setup() {
    Serial.begin(115200);
    while (!Serial) { ; } 

    Serial.println(F("\n--- INITIALISATION LOGGER SD HYBRIDE.IO ---"));

    // 1. Initialisation Carte SD avec détection de Pin CS
    Serial.print(F("SD Card... "));
    
    if (SD.begin(10)) {
        currentCS = 10;
        Serial.println(F("OK sur Pin 10!"));
    } else if (SD.begin(4)) {
        currentCS = 4;
        Serial.println(F("OK sur Pin 4!"));
    } else {
        Serial.println(F("ECHEC (Verifiez branchement FAT32 et Pins 10/4)"));
    }

    // Création de l'en-tête si le fichier est nouveau
    if (SD.exists(filename)) {
        Serial.println(F("Fichier datalog.csv detecte."));
    } else {
        File dataFile = SD.open(filename, FILE_WRITE);
        if (dataFile) {
            dataFile.println("Uptime(ms),Raw_Data");
            dataFile.close();
            Serial.println(F("Nouveau fichier datalog.csv cree."));
        }
    }

    // 2. Initialisation Radio
    if (!driver.init()) {
        Serial.println(F("Radio Init Fail"));
    } else {
        Serial.println(F("Radio Ready sur Pin 2"));
    }
    
    Serial.println(F("En attente de donnees..."));
    Serial.println(F("-------------------------------------------"));
}

void loop() {
    uint8_t buf[64];
    uint8_t buflen = sizeof(buf);

    if (driver.recv(buf, &buflen)) {
        buf[buflen] = 0; 
        String dataReceived = String((char*)buf);
        unsigned long now = millis();

        Serial.print(F("[REC] "));
        Serial.print(now);
        Serial.print(F(" ms | Data: "));
        Serial.println(dataReceived);

        saveToSD(now, dataReceived);
    }
}

void saveToSD(unsigned long time, String data) {
    File dataFile = SD.open(filename, FILE_WRITE);

    if (dataFile) {
        dataFile.print(time);
        dataFile.print(",");
        dataFile.println(data);
        dataFile.close();
        Serial.println(F(" -> Sauvegarde SD OK"));
    } else {
        Serial.println(F(" -> ERREUR SD"));
    }
}
