# wordpress-deployment
# WordPress Deployment with Nginx, MySQL, and GitHub Actions

## 📌 Overview
This document details the step-by-step process to deploy a WordPress website using the LEMP (Linux, Nginx, MySQL, PHP) stack with automated deployment via GitHub Actions.

---

## **1️⃣ Server Provisioning**

### **✅ Provisioning a VPS**
- A VPS was provisioned on AWS (`t2.micro` instance with Ubuntu 22.04)
- Configured firewall rules to allow only necessary ports: `80 (HTTP)`, `443 (HTTPS)`, `3306 (MYSQL)`, and `22 (SSH)`

### **✅ Creating a Deployment User**
```sh
sudo adduser deploy
sudo usermod -aG sudo deploy
```

### **✅ Setting Up SSH Access**
```sh
sudo mkdir -p /home/deploy/.ssh
sudo chmod 700 /home/deploy/.ssh
sudo cp ~/.ssh/authorized_keys /home/deploy/.ssh/
sudo chown -R deploy:deploy /home/deploy/.ssh
sudo chmod 600 /home/deploy/.ssh/authorized_keys
```

### **✅ Granting Limited Sudo Access**
```sh
sudo visudo
```
_Add the following line:_
```sh
deploy  ALL=(ALL) NOPASSWD: /bin/systemctl start nginx, /bin/systemctl stop nginx, /bin/systemctl restart nginx, /bin/systemctl status nginx, /usr/bin/apt-get install -y nginx, /bin/systemctl start mysql, /bin/systemctl stop mysql, /bin/systemctl restart mysql, /bin/systemctl status mysql, /usr/bin/apt-get install -y mysql-server, /usr/bin/mysql, /usr/bin/mysql_secure_installation, /bin/systemctl start php8.3-fpm, /bin/systemctl stop php8.3-fpm, /bin/systemctl restart php8.3-fpm, /bin/systemctl enable php8.3-fpm, /bin/systemctl status php8.3-fpm, /usr/bin/wget, /usr/bin/tar, /bin/mv, /bin/cp, /bin/chmod, /bin/chown, /bin/vi, /usr/bin/git, /usr/bin/systemctl
```

---

## **2️⃣ Installing the LEMP Stack**

### **✅ Install Nginx**
```sh
sudo apt update
sudo apt install -y nginx
```

### **✅ Install MySQL & Secure Database**
```sh
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### **✅ Install PHP & Required Extensions**
```sh
sudo apt install -y php-fpm php-mysql php-cli php-curl php-gd php-mbstring php-xml php-xmlrpc php-soap php-intl php-zip
```

---

## **3️⃣ Configuring Nginx for WordPress**

### **✅ Configure Nginx Virtual Host**
```sh
sudo nano /etc/nginx/sites-available/wordpress
```
_Add the following:_
```nginx
server {
    listen 80;
    server_name mywordpress.zapto.org;

    # Redirect all HTTP traffic to HTTPS
    return 301 https://$host$request_uri;
}
server {
        #listen 443;
        server_name mywordpress.zapto.org;
            root /var/www/html/wordpress;
            index index.php index.html index.htm;

        #ssl on;
        listen 443 ssl;
        ssl_certificate /etc/letsencrypt/live/mywordpress.zapto.org/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/mywordpress.zapto.org/privkey.pem;
        ssl_trusted_certificate /etc/letsencrypt/live/mywordpress.zapto.org/chain.pem;

        ssl_prefer_server_ciphers On;
        ssl_protocols TLSv1.2;
        ssl_ciphers ECDH+AESGCM:DH+AESGCM:ECDH+AES256:DH+AES256:ECDH+AES128:DH+AES:RSA+AESGCM:RSA+AES:!aNULL:!MD5:!DSS;

        gzip on;
        gzip_disable "msie6";
        gzip_vary on;
        gzip_proxied any;
        gzip_comp_level 6;
        gzip_buffers 16 8k;
        gzip_http_version 1.1;
        gzip_types application/javascript application/rss+xml application/vnd.ms-fontobject application/x-font application/x-font-opentype application/x-font-otf application/x-font-truetype application/x-font-ttf application/x-javascript application/xhtml+xml application/xml font/opentype font/otf font/ttf image/svg+xml image/x-icon text/css text/javascript text/plain text/xml;

        large_client_header_buffers 4 64k;
        client_max_body_size 10M;
        #large_client_header_buffers 4 32k;
        include /etc/nginx/letsencrypt/webroot.conf;


        access_log /var/log/nginx/wordpress_access.log;
        error_log /var/log/nginx/wordpress_error.log debug;

    # WordPress-specific rules
    location / {
        try_files $uri $uri/ /index.php?$args;
        expires 30d;
        add_header Cache-Control "public, max-age=2592000";
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;  # Adjust PHP version
        #fastcgi_pass unix:/run/php-fpm/www.sock; # For Unix socket
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|css|js|ico|webp|svg|ttf|woff|woff2|otf|eot|ttc|mp4|mp3)$ {
        expires max;
        log_not_found off;
    }
}
```
### **✅ Enable the Site & Restart Nginx**
```sh
sudo nginx-t
sudo systemctl restart nginx
```

---

## **4️⃣ Deploying WordPress & Setting Up GitHub Actions**

### **✅ Clone WordPress & Set Permissions**
```sh
cd /var/www/html/
sudo git clone https://github.com/WordPress/WordPress.git wordpress
sudo chown -R www-data:www-data /var/www/html/wordpress
```

### **✅ Push Nginx Config to GitHub**
```sh
sudo cp -rp /etc/nginx /var/www/html/wordpress/
sudo chown -R deploy:deploy /var/www/html/wordpress/nginx
cd /var/www/html/wordpress
git add nginx-config/
git commit -m "Added Nginx configuration files"
git push origin main
```

---

## **5️⃣ Setting Up GitHub Actions for Auto Deployment**

### **✅ Define GitHub Secrets**
Go to **GitHub → Repository Settings → Secrets and Variables → Actions** and add:
- `SERVER_IP` → `your-server-ip`
- `SSH_USER` → `deploy`
- `SSH_PRIVATE_KEY` → *(Paste your SSH private key)*

### **✅ Create GitHub Actions Workflow**
```sh
mkdir -p .github/workflows
nano .github/workflows/deploy.yml
```
_Add the following:_
```yaml
name: Deploy WordPress

on:
  push:
    branches:
      - main
  workflow_dispatch:

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout Repository
        uses: actions/checkout@v3
      - name: Deploy via SSH
        uses: appleboy/ssh-action@master
        with:
          host: "${{ secrets.SERVER_IP }}"
          username: "${{ secrets.SSH_USER }}"
          key: "${{ secrets.SSH_PRIVATE_KEY }}"
          script: |
            echo "Connected to server successfully!"
            cd /var/www/html/wordpress
            git pull origin main
            sudo chown -R www-data:www-data /var/www/html/wordpress
            sudo systemctl restart nginx php8.3-fpm
```
### **✅ Push GitHub Actions File**
```sh
git add .github/workflows/deploy.yml
git commit -m "Added GitHub Actions for WordPress deployment"
git push origin main
```

---

## **🎯 Final Steps**
- ✅ Ensure **GitHub Actions runs automatically** when code is pushed.
- ✅ Verify deployment by checking `https://mywordpress.zapto.org/`
- ✅ Check logs via **GitHub → Actions** to debug errors.

