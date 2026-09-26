# Service Guide: Android Client

This guide details the native Android client application (`Niccher/Eaves-Droid-App`) that performs on-device forensic log extraction and secure payload transmission.

---

## 1. Overview & Stack

* **Repository:** [Niccher/Eaves-Droid-App](https://github.com/Niccher/Eaves-Droid-App)
* **Language:** Kotlin 1.9+ / Java 17
* **Framework:** Android SDK (API Level 26+ / Android 8.0 Oreo to Android 15), Jetpack Compose / Material Components, Retrofit 2, OkHttp 3, Coroutines & Flow, WorkManager.

---

## 2. Forensic Extractors (60+ Categories)

The app extracts on-device telemetry across multiple providers:
* **Communications:** SMS messages, MMS, call logs, contacts, calendar events, voicemail.
* **Telephony & Network:** Wi-Fi networks (SSID/BSSID), cell towers (CID/LAC), Bluetooth paired devices, SIM card telemetry, network statistics.
* **Device & Hardware:** Installed application packages, running processes, battery statistics, CPU/thermal sensors, storage layout, system settings.
* **Geospatial:** High-accuracy GPS fixes, network location triangulation, geofence status.

---

## 3. Cryptography & Upload Protocol

1. **AES-GCM Encryption:** All extracted JSON logs are serialized and encrypted on device with a user-supplied 256-bit AES-GCM cipher before hitting the network.
2. **Chunked Streaming Upload:** Large file lists and media archives are uploaded using zero-copy streaming `RequestBody` to prevent out-of-memory errors on low-spec devices.
3. **Emulator Routing:** In local testing, the app communicates with the host CodeIgniter server at `http://10.0.2.2:9007`.

---

## 4. Build & Gradle Commands

```bash
cd /home/niccher/AndroidStudioProjects/Eaves_Droid_App

# Build debug APK
./gradlew assembleDebug

# Run unit tests
./gradlew testDebugUnitTest
```
