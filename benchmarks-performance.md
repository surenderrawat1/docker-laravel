# Laravel Octane vs Node.js Benchmark Results

## Environment

### Hardware / OS

- Windows 11
- WSL2 Ubuntu
- Docker Desktop
- Docker WSL Integration Enabled

---

## Laravel Stack

- PHP 8.5
- Laravel 12
- Laravel Octane
- FrankenPHP
- Redis
- MySQL
- Docker

---

## Node.js Stack

- Node.js
- Fastify
- Redis
- MySQL
- Docker

---

# Benchmark Tool

Used:

```bash
wrk
```

Example:

```bash
wrk -t4 -c100 -d30s http://localhost:8000/api/plain-ok
```

---

# Benchmark Parameters

| Parameter | Value |
|---|---|
| Threads | 4 |
| Connections | 100 |
| Duration | 30 seconds |

---

# Laravel Benchmarks

## Plain Text Route

- Requests/sec: 1395.60
- Avg Latency: 71.61ms

## JSON Route

- Requests/sec: 602.45
- Avg Latency: 194.66ms

## Products API

- Requests/sec: 512.43
- Avg Latency: 220.51ms

## Redis Cached API

- Requests/sec: 566.56
- Avg Latency: 217.00ms

## Large Payload API

- Requests/sec: 18.07
- Avg Latency: 1.10s

---

# Node.js Benchmarks

## Plain Text Route

- Requests/sec: 9242.33
- Avg Latency: 14.09ms

## Products API

- Requests/sec: 3084.85
- Avg Latency: 35.50ms

## Redis Cached API

- Requests/sec: 7027.58
- Avg Latency: 16.19ms

---

# Final Comparison

| Test | Laravel Octane | Node.js |
|---|---|---|
| Plain Text | ~1396 RPS | ~9242 RPS |
| Products API | ~512 RPS | ~3085 RPS |
| Redis API | ~566 RPS | ~7028 RPS |

---

# Key Learnings

- WSL2 significantly improved Laravel performance
- Payload size heavily affects throughput
- Redis reduces database bottlenecks
- Framework abstraction has measurable runtime cost
- Infrastructure configuration matters

---

# Conclusion

Node.js achieved significantly higher throughput for lightweight API workloads.

Laravel Octane greatly improved Laravel performance and remains excellent for business applications and developer productivity.
