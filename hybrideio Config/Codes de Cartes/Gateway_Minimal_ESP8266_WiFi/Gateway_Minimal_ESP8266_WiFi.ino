#ifdef ESP32
  #include <Arduino.h>
  #include <WiFi.h>
  #include <HTTPClient.h>
  #undef attachInterrupt
  #undef detachInterrupt
  #define RF_RX_PIN 4  
  #define DHT_PIN   27 
#else
  #include <ESP8266WiFi.h>
  #include <ESP8266HTTPClient.h>
  #define RF_RX_PIN 4  
  #define DHT_PIN   2  
#endif

#include <RH_ASK.h>
#include <SPI.h>
#include "DHT.h"

#define DHTTYPE DHT22
DHT dht(DHT_PIN, DHTTYPE);
const int LOCAL_ID = 0; 
unsigned long lastLocalSend = -60000; 
const unsigned long localInterval = 60000; 

RH_ASK driver(2000, RF_RX_PIN); 

const char* ssid = "inwi Home 4G 0D180A";
const char* password = "34240121";
const char* server_url = "http://89.168.46.165/save.php";

void setup() {
    Serial.begin(115200);
    delay(1000);
    Serial.println(F("\n--- GATEWAY HYBRIDE.IO (Fix WiFi) ---"));
    
    dht.begin();
    
    // Initialisation Radio
    if (!driver.init()) Serial.println(F("Radio Fail"));
    else Serial.println(F("Radio Ready sur D4"));

    // Connexion WiFi initiale (avec attente)
    Serial.print(F("WiFi Connect..."));
    WiFi.begin(ssid, password);
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
        delay(500);
        Serial.print(".");
        attempts++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println(F(" OK!"));
    } else {
        Serial.println(F(" Timeout (Le WiFi se connectera en arrière-plan)"));
    }
}

void loop() {
    // 1. ECOUTE RADIO (Toujours prioritaire)
    uint8_t buf[64];
    uint8_t buflen = sizeof(buf);

    if (driver.recv(buf, &buflen)) {
        buf[buflen] = 0;
        String data = String((char*)buf);
        Serial.print(F("[RADIO] Recu : ")); Serial.println(data);
        sendToServer(data);
    }

    // 2. CAPTEUR LOCAL
    if (millis() - lastLocalSend >= localInterval) {
        lastLocalSend = millis();
        float h = dht.readHumidity() + 5.07;
        float t = dht.readTemperature();
        if (!isnan(h) && !isnan(t)) {
            String localMsg = "I:" + String(LOCAL_ID) + ",T:" + String(t) + ",H:" + String(h);
            Serial.println(F("[DHT] Envoi local..."));
            sendToServer(localMsg);
        }
    }
    
    // 3. MAINTENANCE WIFI (Sans bloquer)
    static unsigned long lastWiFiCheck = 0;
    if (millis() - lastWiFiCheck > 10000) {
        lastWiFiCheck = millis();
        if (WiFi.status() != WL_CONNECTED && WiFi.status() != WL_IDLE_STATUS) {
            Serial.println(F("[WIFI] Reconnexion..."));
            WiFi.disconnect();
            WiFi.begin(ssid, password);
        }
    }
}

void sendToServer(String data) {
    if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        String url = String(server_url) + "?data=" + data;
        url.replace(" ", "%20");
        
        http.begin(url);
        http.setTimeout(3000); 
        int httpCode = http.GET();
        Serial.printf("[HTTP] Code: %d\n", httpCode);
        http.end();
    }
}