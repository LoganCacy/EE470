#include "sensor_functions.h"

void setup() {
  Serial.begin(115200);
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  pinMode(TILT_PIN, INPUT_PULLUP);

  Serial.println("\n✅ WiFi Connected");
  Serial.print("📡 MAC Address: ");
  Serial.println(WiFi.macAddress());
  
  WiFi.begin(ssid, password);
  Serial.print("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n✅ WiFi Connected");

  init_dht();          // start sensor
  select_timezone();   // 🕒 prompt user for zone before first reading
}

void loop() {
  check_switch();   // Detect which switch triggered a node
  delay(200);       // simple debounce
}
