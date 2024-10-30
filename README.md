# GGSCluster - Dockerized OpenSearch, Nginx, and Pi-hole

GGSCluster is a Docker Compose-based project to deploy a secure, self-hosted OpenSearch cluster with OpenSearch Dashboards, Nginx as a reverse proxy, and Pi-hole as a DNS resolver. This project enables secure HTTPS access to OpenSearch services and custom DNS resolution within a LAN environment.

It can be customized to use another domain.

## Features

- **OpenSearch Cluster**: A two-node OpenSearch cluster for indexing and searching data.
- **OpenSearch Dashboards**: A web-based GUI to interact with and visualize OpenSearch data.
- **Nginx**: A reverse proxy server securing OpenSearch and Dashboards with HTTPS.
- **Pi-hole**: A DNS resolver for custom local domain resolution within the LAN.

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
git clone https://github.com/yourusername/ggscluster.git
cd ggscluster
```

Generate SSL certificates for OpenSearch, Nginx, and OpenSearch Dashboards:

```bash
./certificates/generate_certificates.sh
```



This script generates a root CA, server certificates, and keys for each service. Customize the SERVICES variable within the script to add or modify service names and SANs.

Trust the Root CA:

Add the generated root CA certificate (root-ca.pem) to your system's trusted certificates.
For Ubuntu:

```bash
sudo cp /path/to/root-ca.pem /usr/local/share/ca-certificates/
sudo update-ca-certificates
```



## Configuration

Docker Compose File:

    Customize docker-compose.yml to specify service configurations, volume mappings, and ports.

    OpenSearch and Dashboards use port 9200 and 5601 respectively.

Nginx Configuration:

    Update nginx.conf to set up the reverse proxy for OpenSearch (https://ggscluster.com:9200) and Dashboards (https://ggscluster.com:5601).

    Ensure the SSL paths match the generated certificates:

    nginx

    ssl_certificate /etc/nginx/certs/nginx.pem;
    ssl_certificate_key /etc/nginx/certs/nginx-key.pem;
    ssl_trusted_certificate /etc/nginx/certs/root-ca.pem;

Pi-hole:

    Configure Pi-hole to resolve ggscluster.com to your LAN IP (e.g., 192.168.2.234) by adding a custom DNS entry in Pi-hole’s GUI or setting a static DNS entry in docker-compose.yml.

## Usage

Start the Cluster:

```bash
docker-compose up -d
```

Access OpenSearch Dashboards:

    Go to https://ggscluster.com:5601/app/home#/.

Pi-hole Admin Interface:

    Access the Pi-hole GUI at https://ggscluster.com/pihole/admin.

OpenSearch API:

    Interact with the OpenSearch API at https://ggscluster.com:9200.

## Security

This setup uses self-signed certificates. To avoid browser security warnings, add the generated root CA to your local trusted certificates. Additionally:

Nginx is configured to use TLSv1.2 and TLSv1.3 protocols with strong cipher suites.

OpenSearch Dashboards and API access are protected by HTTPS.

## Troubleshooting

Bad Gateway in Nginx: Ensure the proxy_pass URL in nginx.conf correctly references the Docker service names (opensearch-node1 and opensearch-dashboards).

Certificate Not Trusted: Verify the root CA is added to your system's trusted certificates and restart your browser.

Custom DNS Not Resolving: Verify Pi-hole is set as the DNS resolver on your router or client devices.

## License

This project is licensed under the MIT License.