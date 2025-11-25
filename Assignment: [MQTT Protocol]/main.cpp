#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <PubSubClient.h>

// ---- WiFi credentials ----
const char* ssid = "Cy's S24 Ultra";
const char* password = "pklq795@";

// ---- HiveMQ Broker ----
const char* mqtt_server = "broker.hivemq.com";
const int   mqtt_port   = 1883;

// ---- MQTT Client Setup ----
WiFiClient espClient;
PubSubClient client(espClient);

// ---- Topics ----
const char* outTopic = "testtopic/temp/outTopic/Logan";    
const char* inTopic  = "testtopic/temp/inTopic";           // NEW! For LED control

// ---- Pins ----
int potPin    = A0;
int switchPin = D2;
int ledPin    = D5;

bool lastSwitchState = HIGH;   // for detecting press/release
unsigned long lastMsg = 0;

// --------------------------------------------------
// WiFi Setup
// --------------------------------------------------
void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi connected");
  Serial.println(WiFi.localIP());
}

// --------------------------------------------------
// MQTT Callback (handles incoming messages)
// --------------------------------------------------
void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Message arrived on topic: ");
  Serial.println(topic);

  String msg;

  for (int i = 0; i < length; i++) {
    msg += (char)payload[i];
  }

  Serial.print("Message: ");
  Serial.println(msg);

  // --- LED control via MQTT ---
  if (msg == "1") {
    digitalWrite(ledPin, HIGH);
    Serial.println("LED TURNED ON via MQTT");
  }
  else if (msg == "0") {
    digitalWrite(ledPin, LOW);
    Serial.println("LED TURNED OFF via MQTT");
  }
}

// --------------------------------------------------
// MQTT Reconnect
// --------------------------------------------------
void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");

    String clientId = "ESP8266Client-Logan-";
    clientId += String(random(0xffff), HEX);

    if (client.connect(clientId.c_str())) {
      Serial.println("Connected to HiveMQ");

      // SUBSCRIBE HERE
      client.subscribe(inTopic);
      Serial.print("Subscribed to: ");
      Serial.println(inTopic);

    } else {
      Serial.print("Failed. rc=");
      Serial.print(client.state());
      delay(2000);
    }
  }
}

// --------------------------------------------------
// Setup
// --------------------------------------------------
void setup() {
  Serial.begin(115200);

  pinMode(potPin, INPUT);
  pinMode(switchPin, INPUT_PULLUP);
  pinMode(ledPin, OUTPUT);

  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

// --------------------------------------------------
// Main Loop
// --------------------------------------------------
void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  // -----------------------------------------
  // Publish potentiometer every 15 seconds
  // -----------------------------------------
  unsigned long now = millis();
  if (now - lastMsg > 15000) {
    lastMsg = now;

    int potValue = analogRead(potPin);

    char msg[10];
    sprintf(msg, "%d", potValue);

    Serial.print("Publishing Pot Value: ");
    Serial.println(msg);

    client.publish(outTopic, msg);
  }

  // -----------------------------------------
  // Detect switch press → publish 1 → wait → publish 0
  // -----------------------------------------
  int currentSwitch = digitalRead(switchPin);

  if (currentSwitch == LOW && lastSwitchState == HIGH) {
    // Button press detected
    Serial.println("Switch Pressed → Publishing 1");

    client.publish(outTopic, "1");

    delay(5000);

    Serial.println("Publishing 0 (switch released)");
    client.publish(outTopic, "0");
  }

  lastSwitchState = currentSwitch;
}
