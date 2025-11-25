import paho.mqtt.client as mqtt
import mysql.connector

# ------------- MQTT SETTINGS -------------
BROKER_URL = "broker.hivemq.com"
BROKER_PORT = 1883
TOPIC = "testtopic/temp/outTopic/Logan"   # ESP publishes here

# ------------- DATABASE SETTINGS -------------
HOST = "srv636.hstgr.io"                          # Hostinger MySQL hostname
USER = "u137220217_db_logancacy"                  # Your DB username
PASSWORD = "S@lty123"                              # Your DB password
DATABASE = "u137220217_logancacy"                 # Your DB name

# ------------- DATABASE FUNCTION -------------
def push_value_to_db(sensor_value):
    try:
        connection = mysql.connector.connect(
            host=HOST,
            user=USER,
            password=PASSWORD,
            database=DATABASE
        )

        if connection.is_connected():
            cursor = connection.cursor()
            insert_query = "INSERT INTO sensor_value (value) VALUES (%s)"
            cursor.execute(insert_query, (sensor_value,))
            connection.commit()
            print(f"[DB] Inserted value {sensor_value}")

    except mysql.connector.Error as err:
        print(f"[DB ERROR] {err}")

    finally:
        try:
            if connection.is_connected():
                cursor.close()
                connection.close()
        except:
            pass

# ------------- MQTT CALLBACKS -------------
def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print("[MQTT] Connected to HiveMQ")
        client.subscribe(TOPIC)
        print(f"[MQTT] Subscribed to {TOPIC}")
    else:
        print(f"[MQTT] Failed with code {rc}")

def on_message(client, userdata, msg):
    payload = msg.payload.decode()
    print(f"[MQTT] Message received: {payload}")

    try:
        value = float(payload)
        push_value_to_db(value)
    except ValueError:
        print(f"[WARN] Cannot convert '{payload}' to number")

# ------------- MAIN PROGRAM -------------
client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

print("[MQTT] Connecting to broker...")
client.connect(BROKER_URL, BROKER_PORT, 60)

print("[MQTT] Listening...")
client.loop_forever()
