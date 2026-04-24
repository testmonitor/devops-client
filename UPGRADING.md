# Upgrade Guide

## Upgrading To 3.0 From 2.x

### Retrieving Work Items

PR: https://github.com/testmonitor/devops-client/pull/56

The `workItems()` method now returns a `LengthAwarePaginatedResponse` instead of a plain array. Update any code that expects an array to use the paginated response object instead, for example:

```php
// Before (2.x)
$items = $client->workItems($project);

// After (3.0)
$response = $client->workItems($project);
$items = $response->items;
```
