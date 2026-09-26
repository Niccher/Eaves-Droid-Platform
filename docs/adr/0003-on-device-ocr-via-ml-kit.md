# ADR 0003: On-Device OCR via ML Kit

## Status
Accepted

## Context & Problem Statement
Extracting text and suspicious keywords from device screenshots and stored images can consume massive mobile network bandwidth if raw high-resolution bitmaps are transmitted over cellular connections.

## Decision Drivers
* Mobile data bandwidth preservation.
* End-to-end user privacy.
* Low server compute costs.

## Considered Options
1. Uploading uncompressed full-resolution screenshots to the backend for server OCR.
2. Performing on-device text recognition using Google ML Kit on Android before upload.
3. Completely ignoring image and screenshot content.

## Decision Outcome
Chosen Option 2 (On-Device OCR via Google ML Kit). The Android client runs local text recognition on targeted images, extracts structured text strings, and uploads the lightweight JSON text metadata.

## Consequences
* **Positive:** Reduces cellular upload size by >98% and protects sensitive image visuals.
* **Negative:** Modest increase in APK binary size due to embedded ML Kit models.
