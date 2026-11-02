# Security policy

## Supported version

Security fixes are applied to the latest commit on `main`.

## Reporting

Report suspected vulnerabilities privately through the contact information on the [repository owner's GitHub profile](https://github.com/aderofrank402-spec). Do not open a public issue containing exploit instructions, credentials, caller data, recordings, transcripts, or production endpoints.

Include the affected component, reproduction conditions, impact, and any safe mitigation. Allow reasonable time for investigation before public disclosure.

## Sensitive data

This repository must never contain live API keys, SIP credentials, OAuth tokens, caller information, recordings, transcripts, production databases, logs, or backups. If any such value is committed, revoke it first; deleting it from the latest commit is insufficient because Git history and clones may retain it.
