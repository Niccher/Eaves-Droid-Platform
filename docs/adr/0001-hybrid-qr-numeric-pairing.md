# ADR 0001: Hybrid QR & Numeric Device Pairing

## Status
Accepted

## Context & Problem Statement
Pairing a mobile Android collector device with the self-hosted WebApp backend must be fast, secure, and resilient against camera limitations or headless deployments where QR code scanning is inconvenient.

## Decision Drivers
* Rapid onboarding for non-technical users.
* High security against brute-force token generation.
* Zero external cloud dependencies.

## Considered Options
1. Traditional username/password login on the mobile app.
2. Camera-only QR code scanning.
3. Hybrid QR scan with 6-digit numeric fallback.

## Decision Outcome
Chosen Option 3 (Hybrid QR scan with 6-digit numeric fallback). The WebApp generates an ephemeral 6-digit PIN linked to a short-lived pairing session (10-minute TTL). The user can either scan the displayed QR code or enter the 6-digit code manually.

## Consequences
* **Positive:** Works seamlessly on low-end hardware without functional cameras.
* **Negative:** Requires stateful ephemeral session storage in `tbl_pairing_sessions`.
