# User Troubleshooting Guide

Common startup and operational issues for the ML Eaves Droid backend.

---

## 1. `network hosts-shared-network not found`

**Symptom:**
Docker Compose exits with:
`network hosts-shared-network declared as external, but could not be found`

**Solution:**
Create the shared external bridge network before launching:
```bash
docker network create hosts-shared-network
```

---

## 2. Port 9071 Conflict

**Symptom:**
`bind: address already in use` for host port 9071.

**Solution:**
Inspect existing bindings:
```bash
sudo lsof -i :9071
```
Or override the published port in your `.env`:
```ini
ML_EAVES_DROID_API_PORT=9075
```

---

## 3. Database OperationalError / Connection Refused

**Symptom:**
`sqlalchemy.exc.OperationalError: (pymysql.err.OperationalError) (2003, "Can't connect to MySQL server on 'mysql'")`

**Solution:**
1. Ensure the `shared-mysql` container is running and healthy on `hosts-shared-network`.
2. Test network connectivity from inside the ML container:
   ```bash
   docker compose exec ml-eaves-droid nc -zv mysql 3306
   ```
