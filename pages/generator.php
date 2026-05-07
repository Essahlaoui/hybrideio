<?php
session_start();
require_once '../includes/config.php';
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

$generated_code = "";
$type = isset($_POST['type']) ? $_POST['type'] : 'arduino_tx';
$device_name = isset($_POST['device_name']) ? $_POST['device_name'] : 'NODE_109';
$frequency = isset($_POST['frequency']) ? (int)$_POST['frequency'] : 60; 
$dht_id = isset($_POST['dht_id']) ? $_POST['dht_id'] : '1';
$lm1_id = isset($_POST['lm1_id']) ? $_POST['lm1_id'] : '6';
$lm2_id = isset($_POST['lm2_id']) ? $_POST['lm2_id'] : '1';

// Matrices de Calibration (PFE 2026)
$offsets_dht = [
    '0' => ['t' => 0.0, 'h' => 0.0, 'name' => 'Aucun'],
    '1' => ['t' => 0.00, 'h' => 5.07, 'name' => 'DHT22 n°1'],
    '2' => ['t' => -0.65, 'h' => -1.13, 'name' => 'DHT22 n°2'],
    '3' => ['t' => 0.20, 'h' => 3.77, 'name' => 'DHT22 n°3'],
    '4' => ['t' => -0.05, 'h' => -5.83, 'name' => 'DHT22 n°4'],
    '5' => ['t' => 0.45, 'h' => 3.77, 'name' => 'DHT22 n°5'],
    '6' => ['t' => 0.05, 'h' => -5.63, 'name' => 'DHT22 n°6']
];

$offsets_lm = [
    '0' => ['v' => 0.0, 'name' => 'Aucun'],
    '1' => ['v' => -0.78, 'name' => 'LM35 n°1'],
    '2' => ['v' => -1.26, 'name' => 'LM35 n°2'],
    '3' => ['v' => 0.48, 'name' => 'LM35 n°3'],
    '4' => ['v' => 0.32, 'name' => 'LM35 n°4'],
    '5' => ['v' => 1.25, 'name' => 'LM35 n°5'],
    '6' => ['v' => -0.23, 'name' => 'LM35 n°6 (Batch 2)'],
    '7' => ['v' => -1.01, 'name' => 'LM35 n°7 (Batch 2)'],
    '8' => ['v' => 1.93, 'name' => 'LM35 n°8 (Batch 2)'],
    '9' => ['v' => -1.53, 'name' => 'LM35 n°9 (Batch 2)'],
    '10' => ['v' => 1.20, 'name' => 'LM35 n°10 (Batch 2)']
];

if (isset($_POST['generate'])) {
    $cur_dht = $offsets_dht[$dht_id];
    $cur_lm1 = $offsets_lm[$lm1_id];
    $cur_lm2 = $offsets_lm[$lm2_id];

    if ($type == 'arduino_rx') {
        $generated_code = "#include <RH_ASK.h>\n#include <SPI.h>\n#include <Ethernet.h>\n\n/**\n * GÉNÉRÉ PAR HYBRIDE.IO - Gateway RX Ethernet (Stable)\n * ID Gateway : 0 (GATEWAY)\n */\n\nRH_ASK driver(2000, 2, 3, 5); \nbyte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };\nconst char* server_ip = \"89.168.46.165\";\nEthernetClient client;\n\nvoid setup() {\n    Serial.begin(115200);\n    Ethernet.init(10); \n    if (Ethernet.begin(mac) == 0) Serial.println(F(\"Eth Fail\"));\n    if (!driver.init()) Serial.println(F(\"RF Fail\"));\n    Serial.println(F(\"Gateway Ready sur 89.168.46.165\"));\n}\n\nvoid loop() {\n    uint8_t buf[64];\n    uint8_t buflen = sizeof(buf);\n    if (driver.recv(buf, &buflen)) {\n        buf[buf] = 0;\n        sendToServer((char*)buf);\n    }\n}\n\nvoid sendToServer(char* data) {\n    if (client.connect(server_ip, 80)) {\n        String url = \"/save.php?data=\" + String(data);\n        url.replace(\" \", \"%20\");\n        \n        client.print(\"GET \" + url + \" HTTP/1.1\\r\\n\");\n        client.print(\"Host: 89.168.46.165\\r\\n\");\n        client.println(\"Connection: close\\r\\n\\r\\n\");\n        client.stop();\n        Serial.println(\"Sent OK\");\n    }\n}";
    } elseif ($type == 'esp_rx') {
        $generated_code = "#include <RH_ASK.h>\n#ifdef ESP32\n  #include <WiFi.h>\n  #include <HTTPClient.h>\n  #define RF_RX_PIN 4\n#else\n  #include <ESP8266WiFi.h>\n  #include <ESP8266HTTPClient.h>\n  #define RF_RX_PIN 4\n#endif\n\n/**\n * GÉNÉRÉ PAR HYBRIDE.IO - Gateway RX WiFi (Ultra-Stable)\n * Support : ESP32 / ESP8266\n * ID Gateway : 0\n */\n\nRH_ASK driver(2000, RF_RX_PIN); \nconst char* ssid = \"VOTRE_WIFI\";\nconst char* password = \"MOT_DE_PASSE\";\nconst char* server_url = \"http://89.168.46.165/save.php\";\n\nvoid setup() {\n    Serial.begin(115200);\n    WiFi.begin(ssid, password);\n    while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print(\".\"); }\n    if (!driver.init()) Serial.println(\"RF Fail\");\n    Serial.println(\"Gateway WiFi Ready\");\n}\n\nvoid loop() {\n    uint8_t buf[64];\n    uint8_t buflen = sizeof(buf);\n    if (driver.recv(buf, &buflen)) {\n        buf[buflen] = 0;\n        sendToServer(String((char*)buf));\n    }\n    if (WiFi.status() != WL_CONNECTED) { WiFi.begin(ssid, password); delay(5000); }\n}\n\nvoid sendToServer(String data) {\n    HTTPClient http;\n    data.replace(\" \", \"%20\");\n    String url = String(server_url) + \"?data=\" + data;\n    http.begin(url);\n    http.setTimeout(3000);\n    int code = http.GET();\n    Serial.printf(\"HTTP: %d\\n\", code);\n    http.end();\n}";
    } elseif ($type == 'arduino_tx') {
        $generated_code = "#include <RH_ASK.h>\n#include <SPI.h> \n#include \"DHT.h\"\n\n/**\n * GÉNÉRÉ PAR HYBRIDE.IO - Node Émetteur RF433\n * Device ID: $device_name\n */\n\n#define DHTPIN 2\n#define DHTTYPE DHT22\nDHT dht(DHTPIN, DHTTYPE);\nRH_ASK driver; \n\nvoid setup() {\n    Serial.begin(115200);\n    dht.begin();\n    randomSeed(analogRead(A5));\n    driver.init();\n}\n\nvoid loop() {\n    float h = dht.readHumidity() + (" . $cur_dht['h'] . ");\n    float t = dht.readTemperature() + (" . $cur_dht['t'] . ");\n    float t_lm1 = readLM35(A0) + (" . $cur_lm1['v'] . ");\n    float t_lm2 = readLM35(A1) + (" . $cur_lm2['v'] . ");\n\n    if (isnan(h) || isnan(t)) return;\n\n    char t_s[8], h_s[8], l1_s[8], l2_s[8];\n    dtostrf(t, 4, 1, t_s);\n    dtostrf(h, 4, 1, h_s);\n    dtostrf(t_lm1, 4, 1, l1_s);\n    dtostrf(t_lm2, 4, 1, l2_s);\n\n    char msg[64];\n    snprintf(msg, sizeof(msg), \"I:$device_name,T:%s,H:%s,1:%s,2:%s\", t_s, h_s, l1_s, l2_s);\n    \n    driver.send((uint8_t *)msg, strlen(msg));\n    driver.waitPacketSent();\n\n    // Logique Anti-collision : Base 2s + Jitter + Offset par ID (converti en int)\n    int offset = atoi(preg_replace('/[^0-9]/', '', '$device_name')) % 10; \n    delay(2000 + (offset * 300) + random(0, 1000)); \n}\n\nfloat readLM35(int pin) {\n    long sum = 0;\n    for(int i=0; i < 50; i++) { sum += analogRead(pin); delay(2); }\n    return ((sum / 50.0) * (5.0 / 1023.0)) * 100.0;\n}";
    } elseif ($type == 'arduino_sd') {
        $generated_code = "#include <SPI.h>\n#include <SD.h>\n#include <RH_ASK.h>\n\n/**\n * GÉNÉRÉ PAR HYBRIDE.IO - Data Logger SD + RF433\n */\n\nconst int RF_RX_PIN = 2;      \nconst char* filename = \"datalog.csv\";\nRH_ASK driver(2000, RF_RX_PIN, 3, 5);\n\nvoid setup() {\n    Serial.begin(115200);\n    if (!SD.begin(10) && !SD.begin(4)) Serial.println(F(\"SD Fail\"));\n    if (!driver.init()) Serial.println(F(\"Radio Fail\"));\n    if (!SD.exists(filename)) {\n        File f = SD.open(filename, FILE_WRITE);\n        f.println(\"Uptime(ms),Data\");\n        f.close();\n    }\n}\n\nvoid loop() {\n    uint8_t buf[64];\n    uint8_t buflen = sizeof(buf);\n    if (driver.recv(buf, &buflen)) {\n        buf[buflen] = 0;\n        File f = SD.open(filename, FILE_WRITE);\n        if (f) {\n            f.print(millis()); f.print(\",\"); f.println((char*)buf);\n            f.close();\n        }\n        Serial.println((char*)buf);\n    }\n}";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurateur PFE - Hybride IO</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --card-bg: rgba(13, 17, 23, 0.7);
            --accent-glow: rgba(74, 222, 128, 0.15);
        }

        .hero-section {
            padding: 40px 0;
            margin-bottom: 20px;
        }

        .generator-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 32px;
            border: 1px solid var(--border);
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, var(--border), transparent);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 10px;
            margin-left: 5px;
            text-transform: uppercase;
        }

        .custom-select, .custom-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 15px 20px;
            color: #fff;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .custom-select:focus, .custom-input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(74, 222, 128, 0.05);
            box-shadow: 0 0 20px var(--accent-glow);
        }

        .btn-premium {
            background: var(--accent);
            color: #000;
            width: 100%;
            border: none;
            padding: 20px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin-top: 10px;
        }

        .btn-premium:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(74, 222, 128, 0.3);
            filter: brightness(1.1);
        }

        .code-output {
            margin-top: 50px;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .code-container {
            background: #0d1117;
            border-radius: 24px;
            border: 1px solid var(--border);
            overflow: hidden;
            position: relative;
        }

        .code-toolbar {
            background: rgba(255,255,255,0.02);
            padding: 15px 25px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lang-tag {
            font-size: 0.7rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            background: rgba(255,255,255,0.05);
            padding: 4px 10px;
            border-radius: 6px;
        }

        .copy-pill {
            background: rgba(74, 222, 128, 0.1);
            color: var(--accent);
            border: 1px solid rgba(74, 222, 128, 0.2);
            padding: 8px 18px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .copy-pill:hover {
            background: var(--accent);
            color: #000;
        }

        pre {
            padding: 30px;
            margin: 0;
            color: #e2e8f0;
            font-family: 'Fira Code', 'Consolas', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
            line-height: 1.7;
        }

        .keyword { color: #ff7b72; }
        .function { color: #d2a8ff; }
        .string { color: #a5d6ff; }
        .comment { color: #8b949e; font-style: italic; }

    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <div class="hero-section">
            <h1 style="margin-bottom: 10px;">Générateur de Firmware</h1>
            <p style="color: #94a3b8; font-size: 1.1rem;">Configurez et déployez vos nodes avec les calibrations PFE officielles.</p>
        </div>

        <div class="generator-card">
            <form method="POST">
                <div class="section-title">Configuration Système</div>
                <div class="form-grid">
                    <div class="input-wrapper">
                        <label>Architecture Node</label>
                        <select name="type" class="custom-select">
                             <option value="arduino_tx" <?php if($type=='arduino_tx') echo 'selected'; ?>>Node Arduino (RF433 + Capteurs)</option>
                             <option value="esp_rx" <?php if($type=='esp_rx') echo 'selected'; ?>>Gateway WiFi (ESP32 / ESP8266)</option>
                             <option value="arduino_rx" <?php if($type=='arduino_rx') echo 'selected'; ?>>Gateway Ethernet (Uno + Shield)</option>
                             <option value="arduino_sd" <?php if($type=='arduino_sd') echo 'selected'; ?>>Node Logger (RF433 -> Carte SD)</option>
                        </select>
                    </div>
                    <div class="input-wrapper">
                        <label>Identifiant (ID)</label>
                        <input type="text" name="device_name" class="custom-input" placeholder="Ex: NODE_109" value="<?php echo htmlspecialchars($device_name); ?>">
                    </div>
                    <div class="input-wrapper">
                        <label>Fréquence d'envoi (s)</label>
                        <input type="number" name="frequency" class="custom-input" value="<?php echo $frequency; ?>" min="1">
                    </div>
                </div>

                <div class="section-title">Matrices de Calibration (Offisielle PFE)</div>
                <div class="form-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="input-wrapper">
                        <label>Série Capteur DHT22</label>
                        <select name="dht_id" class="custom-select">
                            <?php foreach($offsets_dht as $id => $data): ?>
                                <option value="<?php echo $id; ?>" <?php if($dht_id==$id) echo 'selected'; ?>><?php echo $data['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-wrapper">
                        <label>Série LM35 (A0)</label>
                        <select name="lm1_id" class="custom-select">
                            <?php foreach($offsets_lm as $id => $data): ?>
                                <option value="<?php echo $id; ?>" <?php if($lm1_id==$id) echo 'selected'; ?>><?php echo $data['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-wrapper">
                        <label>Série LM35 (A1)</label>
                        <select name="lm2_id" class="custom-select">
                            <?php foreach($offsets_lm as $id => $data): ?>
                                <option value="<?php echo $id; ?>" <?php if($lm2_id==$id) echo 'selected'; ?>><?php echo $data['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" name="generate" class="btn-premium">
                    Générer le Code Calibré
                </button>
            </form>

            <!-- Nouvelle Section Ressources -->
            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--border); display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <a href="../assets/drivers/CP210x_ESP32_Driver.zip" class="resource-card" style="text-decoration: none;">
                    <div style="background: rgba(74, 222, 128, 0.05); padding: 20px; border-radius: 20px; border: 1px dashed rgba(74, 222, 128, 0.3); transition: all 0.3s;">
                        <span style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--accent); text-transform: uppercase; margin-bottom: 5px;">Pilote USB</span>
                        <h4 style="margin: 0; color: #fff; font-size: 0.9rem;">Driver CP210x (ESP32)</h4>
                        <p style="margin: 5px 0 0; color: #64748b; font-size: 0.75rem;">Nécessaire pour l'upload USB sur Windows.</p>
                    </div>
                </a>
                <a href="../Dossier_Technique_PFE.md" target="_blank" class="resource-card" style="text-decoration: none;">
                    <div style="background: rgba(255, 255, 255, 0.02); padding: 20px; border-radius: 20px; border: 1px dashed var(--border); transition: all 0.3s;">
                        <span style="display: block; font-size: 0.7rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px;">Documentation</span>
                        <h4 style="margin: 0; color: #fff; font-size: 0.9rem;">Dossier Technique PFE</h4>
                        <p style="margin: 5px 0 0; color: #64748b; font-size: 0.75rem;">Consulter les méthodes de calibration.</p>
                    </div>
                </a>
            </div>
        </div>

        <?php if ($generated_code): ?>
        <div class="code-output">
            <div class="code-container">
                <div class="code-toolbar">
                    <div class="lang-tag">Arduino Sketch (.ino)</div>
                    <button class="copy-pill" onclick="copyCode()" id="copyBtn">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 4v8a2 2 0 002 2h8M16 20H8a2 2 0 01-2-2V8a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2z"/></svg>
                        Copier le Code
                    </button>
                </div>
                <pre id="code-block"><?php echo htmlspecialchars($generated_code); ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function copyCode() {
            const code = document.getElementById('code-block').innerText;
            const btn = document.getElementById('copyBtn');
            
            navigator.clipboard.writeText(code).then(() => {
                const originalText = btn.innerHTML;
                btn.style.background = "#4ade80";
                btn.style.color = "#000";
                btn.innerHTML = "Copié !";
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = "";
                    btn.style.color = "";
                }, 2000);
            });
        }
    </script>
</body>
</html>
