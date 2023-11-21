```
docker run --name chrome -it -p 9222:9222 --entrypoint /bin/bash femtopixel/google-chrome-headless

chromium --headless --disable-gpu --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --no-sandbox --remote-allow-origins=http://127.0.0.1:3334 --user-data-dir=/data --disable-v-shm-usage
```