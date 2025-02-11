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
deploy  ALL=(ALL) NOPASSWD: /bin/systemctl restart nginx, /bin/systemctl restart php-fpm, /bin/systemctl restart mysql, /usr/bin/git pull
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
    server_name yourdomain.com;
    root /var/www/html/wordpress;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```
### **✅ Enable the Site & Restart Nginx**
```sh
sudo ln -s /etc/nginx/sites-available/wordpress /etc/nginx/sites-enabled/
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
mkdir -p /var/www/html/wordpress/nginx-config
sudo cp -rp /etc/nginx /var/www/html/wordpress/nginx-config
sudo chown -R deploy:deploy /var/www/html/wordpress/nginx-config
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
- ✅ Verify deployment by checking `http://yourdomain.com`
- ✅ Check logs via **GitHub → Actions** to debug errors.

