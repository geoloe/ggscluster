# GGSCluster - Dockerized OpenSearch (Dashboards) with Nginx, Pi-hole, Apache, and Logstash

GGSCluster is a Docker Compose-based project to deploy a secure, self-hosted OpenSearch cluster with OpenSearch Dashboards, Nginx as a reverse proxy, and Pi-hole as a DNS resolver. This project enables secure HTTPS access to OpenSearch services and custom DNS resolution within a LAN environment.

Logstash is an optional tool for ingesting data into the cluster. It is already configured.

This setup can be customized to use another domain. Just adjust the Nginx reverse proxy configuration, the CNs in the Cluster/Certificates, and your Router/Switch and Pi-hole DNS configuration.

## Features

- **OpenSearch Cluster**: A two-node OpenSearch cluster for indexing and searching data.
- **OpenSearch Dashboards**: A web-based GUI to interact with and visualize OpenSearch data.
- **Nginx**: A reverse proxy server securing OpenSearch and Dashboards with HTTPS.
- **Pi-hole**: A DNS resolver for custom local domain resolution within the LAN.
- **Apache**: An optional file server for serving files under a custom domain.
- **Logstash**: An optional data ingest tool that can be used for adding data to the cluster.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Security](#security)
- [Troubleshooting](#troubleshooting)

## Prerequisites

- **Docker** and **Docker Compose** installed on your system.
- **OpenSSL** for generating certificates.
- **Linux environment** (instructions may vary for other OS).

## Installation

1. Clone this repository:
    ```bash
    git clone https://github.com/geoloe/ggscluster.git
    cd ggscluster-main
    ```

2. Generate SSL certificates for OpenSearch, Nginx, OpenSearch Dashboards, and Apache:
    ```bash
    ./certificates/generate_certificates.sh
    ```

   This script generates a root CA, server certificates, and keys for each service. Customize the `SERVICES` variable within the script to add or modify service names and SANs.

3. Trust the Root CA:

   Add the generated root CA certificate (`root-ca.pem`) to your system's trusted certificates.
   For Ubuntu:
    ```bash
    sudo cp /path/to/root-ca.pem /usr/local/share/ca-certificates/
    sudo update-ca-certificates
    ```

## Configuration

### docker-compose.yml

- **Docker Compose File**: Customize `docker-compose.yml` to specify service configurations, volume mappings, and ports.

- **OpenSearch and Dashboards**: OpenSearch uses port 9200, while Nginx serves Dashboards over port 443.

### Nginx Configuration

- Update `nginx.conf` to set up the reverse proxy for OpenSearch (`https://ggscluster.com:9200`) and Dashboards (`https://ggscluster.com`).

- Ensure the SSL paths match the generated certificates:
    ```nginx
    ssl_certificate /etc/nginx/certs/nginx.pem;
    ssl_certificate_key /etc/nginx/certs/nginx-key.pem;
    ssl_trusted_certificate /etc/nginx/certs/root-ca.pem;
    ```

### Pi-hole

- Configure Pi-hole to resolve `ggscluster.com` to your LAN IP (e.g., 192.168.2.234) by adding a custom DNS entry in Pi-hole’s GUI or setting a static DNS entry in `docker-compose.yml`.

### Logstash

- Configure Logstash to fetch and filter data via the logstash folder. You can create your data ingest pipelines. Please remember that the credentials can be created via the security tool from OpenSearch.

## Usage

1. Start the main applications:
```bash
docker-compose -f docker-compose.yml up -d
```

Start the Data Ingest Tool (optional)
```bash
docker-compose -f docker-compose-logstash.yml up -d
```

After build you might get an error that the port 53 is already in use.
In that case disable systemd-resolved.service for pihole to run properly:

```bash
echo "nameserver 127.0.0.1" | sudo tee /etc/resolv.conf
sudo systemctl stop systemd-resolved
sudo systemctl disable systemd-resolved
```

And then:

```bash
docker-compose -f docker-compose.yml restart
```

If you need the service again you can undo with:
```bash
echo "nameserver 8.8.8.8" | sudo tee /etc/resolv.conf
sudo systemctl enable systemd-resolved
sudo systemctl start systemd-resolved
```

Before navigating to the sites. Import your previously created root-ca.pem to your broweser (might be needed)

Access OpenSearch Dashboards:

    Go to https://ggscluster.com.

Pi-hole Admin Interface:

    Access the Pi-hole GUI at https://ggscluster.com/admin.

File Server:

    Use your files under https://ggscluster.com/files

## Security

This setup uses self-signed certificates. To avoid browser security warnings, add the generated root CA to your local trusted certificates. Additionally:

Nginx is configured to use TLSv1.2 and TLSv1.3 protocols with strong cipher suites.

OpenSearch Dashboards and API access are protected by HTTPS.

To generate bcrypt passwords for the clusters internal users you can use:

```
python3 -c 'import bcrypt; print(bcrypt.hashpw(b"securePasswordTest123!", bcrypt.gensalt(rounds=12)).decode())'
```

`internal_users.yml`:

```
# Define the hashing algorithm (bcrypt)
_hash: &hash
  hash: "bcrypt"   # Explicitly declare bcrypt as the hashing method
```

To generate hashed passwords for mysql to login to your file server use the hash_creator.php and write your password to be hashed here:

```
<?php
session_start();

// Check if user is logged in and has the required role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], [1, 2])) {
    header('Location: unauthorized.php'); // Redirect to a custom page
    exit();
}

// Define the plain text password
$password = 'yourpassword123';

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Output the hashed password
echo "Hashed Password: " . $hashedPassword;
?>
```

Then navigate to https://ggscluster.com/files/hash_creator.php

You will see your hashed password. This is only readable for logged in users with admin permissions

## Opensearch Dashboards Plugins

You can adapt the opensearch-dashboards container service to use:

```
    command: >
      bash -c "
      if bin/opensearch-dashboards-plugin list | grep -q securityDashboards; then
        bin/opensearch-dashboards-plugin remove securityDashboards;
      fi &&
      bin/opensearch-dashboards-plugin install securityDashboards &&
      if bin/opensearch-dashboards-plugin list | grep -q canvasReportDataAnalyzer; then
        bin/opensearch-dashboards-plugin remove canvasReportDataAnalyzer;
      fi &&
      bin/opensearch-dashboards-plugin install https://github.com/geoloe/opensearch-dashboards-plugin-canvas-reports-data-analyzer/releases/download/3.0.0/canvasReportDataAnalyzer-3.0.0.zip &&
      bin/opensearch-dashboards
      "
```

Official plugins available can be fetched from: https://docs.opensearch.org/docs/latest/install-and-configure/install-dashboards/plugins/

You can use my custom opensearch dashboards plugin to generate PDF reports and use a user friendly Query Workbench for more information see here:

https://github.com/geoloe/opensearch-dashboards-plugin-canvas-reports-data-analyzer/tree/2.x

Fore more plugins add:

      if bin/opensearch-dashboards-plugin list | grep -q yourPlugin; then
        bin/opensearch-dashboards-plugin remove yourPlugin;
      fi &&
      bin/opensearch-dashboards-plugin install yourPlugin &&

## Troubleshooting

Bad Gateway in Nginx: Ensure the proxy_pass URL in nginx.conf correctly references the Docker service names (opensearch-node1 and opensearch-dashboards).

Certificate Not Trusted: Verify the root CA is added to your system's trusted certificates and restart your browser.

Custom DNS Not Resolving: Verify Pi-hole is set as the DNS resolver on your router or client devices.

If you get 

`WARN[0000] Found orphan containers ([opensearch-node2 opensearch-dashboards opensearch-node1 pihole opensearch-nginx]) for this project. If you removed or renamed this service in your compose file, you can run this command with the --remove-orphans flag to clean it up.`

when using both docker-compose.yml. Ignore it, as both docker-compose files use the same network.

## License

This project is licensed under the [MIT License](./LICENSE).