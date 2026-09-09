# ADR 0002: Unified Forensic Feed Aggregation

## Status
Accepted

## Context & Problem Statement
The Android client collects over 60 categories of forensic events (SMS, calls, location points, app installs, network connections). Presenting these separately creates fragmented timelines.

## Decision Drivers
* Chronological forensic correlation across multi-source events.
* Low server-side aggregation overhead.
* Efficient pagination and real-time filtering.

## Considered Options
1. Querying 15+ separate SQL tables on every dashboard page load.
2. Building an asynchronous unified timeline projection table.
3. Client-side browser-based sorting and merging.

## Decision Outcome
Chosen Option 2. Ingestion parsers write to dedicated category tables and simultaneously project high-level timeline events into a unified chronological event index (`tbl_timeline_events`).

## Consequences
* **Positive:** Sub-50ms dashboard response times for cross-category event streams.
* **Negative:** Slight write amplification during high-throughput ingestion.
