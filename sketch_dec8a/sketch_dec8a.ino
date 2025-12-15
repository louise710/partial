#include <WiFi.h>
#include <PubSubClient.h>

// WiFi Configuration
const char* ssid = "YOUR_WIFI_NAME";
const char* password = "YOUR_WIFI_PASSWORD";

// MQTT Broker Configuration (XAMPP Local Server)
const char* mqtt_server = "YOUR_PC_IP_ADDRESS"; // Your computer's IP where XAMPP runs
const int mqtt_port = 1883;

// ESP32 Pin Configuration (30-pin ESP32 WROOM)
const int LIGHT1_PIN = 2;   // GPIO2 - Built-in LED
const int LIGHT2_PIN = 4;   // GPIO4
const int AC1_PIN = 5;      // GPIO5
const int AC2_PIN = 18;     // GPIO18

// Room Configuration - CHANGE THIS FOR EACH ESP32
String roomId = "3";  // Set to match your room number

WiFiClient espClient;
PubSubClient client(espClient);

// Function to connect to WiFi
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

  Serial.println("");
  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
}

// MQTT Message Callback
void callback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (int i = 0; i < length; i++) {
    message += (char)payload[i];
  }
  
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("]: ");
  Serial.println(message);

  // Check if message is for this room
  String prefix = "rm" + roomId;
  
  if (message.startsWith(prefix)) {
    // Extract device and command
    String device = message.substring(prefix.length(), prefix.length() + 3);
    String command = message.substring(prefix.length() + 3);
    
    Serial.print("Device: ");
    Serial.println(device);
    Serial.print("Command: ");
    Serial.println(command);
    
    // Control devices
    if (device == "L1") {
      digitalWrite(LIGHT1_PIN, command == "on" ? HIGH : LOW);
      publishStatus("L1", command);
    } 
    else if (device == "L2") {
      digitalWrite(LIGHT2_PIN, command == "on" ? HIGH : LOW);
      publishStatus("L2", command);
    }
    else if (device == "AC1") {
      digitalWrite(AC1_PIN, command == "on" ? HIGH : LOW);
      publishStatus("AC1", command);
    }
    else if (device == "AC2") {
      digitalWrite(AC2_PIN, command == "on" ? HIGH : LOW);
      publishStatus("AC2", command);
    }
  }
  
  // Handle status request
  if (message == "myStats") {
    publishAllStatus();
  }
}

// Publish device status
void publishStatus(String device, String status) {
  String topic = "main/dev/status";
  String message = "rm" + roomId + device + status;
  
  if (client.publish(topic.c_str(), message.c_str())) {
    Serial.print("Published: ");
    Serial.println(message);
  } else {
    Serial.println("Publish failed");
  }
}

// Publish all device statuses
void publishAllStatus() {
  String statusL1 = digitalRead(LIGHT1_PIN) == HIGH ? "on" : "off";
  String statusL2 = digitalRead(LIGHT2_PIN) == HIGH ? "on" : "off";
  String statusAC1 = digitalRead(AC1_PIN) == HIGH ? "on" : "off";
  String statusAC2 = digitalRead(AC2_PIN) == HIGH ? "on" : "off";
  
  publishStatus("L1", statusL1);
  delay(100);
  publishStatus("L2", statusL2);
  delay(100);
  publishStatus("AC1", statusAC1);
  delay(100);
  publishStatus("AC2", statusAC2);
}

// Reconnect to MQTT broker
void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    
    String clientId = "ESP32-Room-" + roomId;
    
    if (client.connect(clientId.c_str())) {
      Serial.println("connected");
      
      // Subscribe to topics
      client.subscribe("main/client/AC");
      client.subscribe("main/switch/lights");
      client.subscribe("main/web/AC");
      
      // Publish initial status
      publishAllStatus();
      
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  
  // Initialize pins as OUTPUT
  pinMode(LIGHT1_PIN, OUTPUT);
  pinMode(LIGHT2_PIN, OUTPUT);
  pinMode(AC1_PIN, OUTPUT);
  pinMode(AC2_PIN, OUTPUT);
  
  // Turn off all devices initially
  digitalWrite(LIGHT1_PIN, LOW);
  digitalWrite(LIGHT2_PIN, LOW);
  digitalWrite(AC1_PIN, LOW);
  digitalWrite(AC2_PIN, LOW);
  
  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();
}