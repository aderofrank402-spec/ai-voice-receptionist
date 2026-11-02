# Engineering decisions

## Separate control and media planes

Laravel owns configuration and durable state; Node services own timing-sensitive SIP, RTP, STT, and TTS work. This avoids coupling web latency to audio handling and permits independent recovery.

## Use an explicit state machine

Voice interaction is asynchronous: callers speak during synthesis, providers close sockets, tools finish late, and callbacks arrive independently. Explicit states and watchdogs make transitions observable.

## Cancel by turn identity

Aborting a request does not guarantee its completion handler cannot run. Every user turn has an identifier; asynchronous work verifies it before changing history or completing follow-up behavior.

## Keep consequential actions behind Laravel

The model may propose an appointment, payment, or transfer, but durable actions cross a validated Laravel boundary that centralizes tenant ownership, authentication, persistence, provider adapters, and audit behavior.

## Version knowledge

Website crawls produce versioned content. Retrieval uses the active version so an incomplete refresh cannot silently mix old and new business facts.

## Fail closed internally

Missing configuration returns `503`; incorrect or absent credentials return `401`; comparisons are constant time. Private networking should reinforce application authentication.

## Preserve a simple development path

SQLite supports local web workflows. Vector retrieval degrades gracefully because PostgreSQL vector operations are deployment-specific; this limitation is documented rather than hidden.

## Publish evidence, not inferred success

Repository metrics, builds, tests, and code paths are engineering evidence. Production traffic, latency, adoption, and business outcomes require dated operational artifacts.
