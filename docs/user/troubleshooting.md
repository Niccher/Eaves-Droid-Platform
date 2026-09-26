# User Troubleshooting Guide

Common issues encountered when running or operating the Eaves Droid WebApp.

---

## 1. Port 9007 or 9306 Already in Use

**Symptom:**
`Error starting userland proxy: listen tcp4 0.0.0.0:9007: bind: address already in use`

**Solution:**
Identify what process is using port 9007 or 9306:
```bash
sudo lsof -i :9007
```
Either stop that service or edit `docker-compose.yml` to map a different external port:
```yaml
ports:
  - "9008:80"
```

---

## 2. MySQL Connection Refused / `Unable to connect to database`

**Symptom:**
Web application returns HTTP 500 or shows `mysqli::real_connect(): (HY000/2002): Connection refused`.

**Cause:**
The application started before MySQL completed initialization, or credentials do not match `.env`.

**Solution:**
1. Check MySQL container status:
   ```bash
   docker compose ps
   docker compose logs mysql
   ```
2. Wait until MySQL reaches `healthy` state.
3. Ensure `.env` specifies `database.default.hostname = mysql` when running inside Docker, or `127.0.0.1` when running native PHP outside Docker.

---

## 3. Directory `writable/` is Not Writable

**Symptom:**
`CodeIgniter\Exceptions\CriticalError: "writable/cache" path is not writable.`

**Solution:**
Ensure the web server user (`www-data`) owns the writable directory:
```bash
chmod -R 777 writable/
```
Or when inside the container:
```bash
docker compose exec eaves-droid chown -R www-data:www-data /var/www/html/writable
```

---

## 4. Mobile Client Cannot Reach Upload Endpoint

**Symptom:**
Android app reports network timeout when uploading forensic files to `http://10.0.2.2:9007/api/v1/files/upload`.

**Solution:**
1. Check that the web app is running and bound to `0.0.0.0:9007`.
2. For an Android Emulator, use `http://10.0.2.2:9007`.
3. For a Physical Android Device, use your machine's local LAN IP (e.g. `http://192.168.1.150:9007`), ensuring your workstation firewall allows inbound traffic on port 9007.
