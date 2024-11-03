#!/bin/bash

# Function to generate key and certificate with SANs
generate_cert() {
    local NAME=$1
    local ROOT_CA_NAME=$2
    local SAN_NAMES=$3

    # Generate key
    openssl genrsa -out "${NAME}-key.pem" 2048

    # Create a temporary OpenSSL config file for SANs
    cat > "${NAME}-openssl.cnf" <<EOF
[ req ]
default_bits       = 2048
distinguished_name = req_distinguished_name
req_extensions     = req_ext
prompt             = no

[ req_distinguished_name ]
CN = ${NAME}

[ req_ext ]
subjectAltName = ${SAN_NAMES}
EOF

    # Generate CSR using the temporary config file
    openssl req -new -key "${NAME}-key.pem" -out "${NAME}.csr" -config "${NAME}-openssl.cnf"

    # Generate certificate using the root CA
    openssl x509 -req -in "${NAME}.csr" -CA "${ROOT_CA_NAME}.pem" \
        -CAkey "${ROOT_CA_NAME}-key.pem" -CAcreateserial \
        -out "${NAME}.pem" -days 365 -sha256 -extfile "${NAME}-openssl.cnf" -extensions req_ext

    # Clean up CSR and temporary config file
    rm -f "${NAME}.csr" "${NAME}-openssl.cnf"
}

# Prompt for custom certificate names
read -p "Enter Root CA Certificate name (default: root-ca): " ROOT_CA_NAME
ROOT_CA_NAME=${ROOT_CA_NAME:-root-ca}

# Generate Root CA key and certificate
openssl genrsa -out "${ROOT_CA_NAME}-key.pem" 2048
openssl req -x509 -new -nodes -key "${ROOT_CA_NAME}-key.pem" -sha256 -days 365 \
    -out "${ROOT_CA_NAME}.pem" -subj "/CN=${ROOT_CA_NAME}"

# Array of service names and their respective SANs
declare -A SERVICES
SERVICES["node1"]="DNS:node1,DNS:opensearch-node1,DNS:ggscluster.com"
SERVICES["node2"]="DNS:node2,DNS:opensearch-node2,DNS:ggscluster.com"
SERVICES["admin"]="DNS:admin,DNS:ggscluster.com"
SERVICES["nginx"]="DNS:nginx,DNS:ggscluster.com"
SERVICES["apache"]="DNS:apache,DNS:ggscluster.com"
SERVICES["ls"]="DNS:ls,DNS:ggscluster.com"
SERVICES["opensearch-dashboards"]="DNS:opensearch-dashboards,DNS:ggscluster.com"

# Generate certificates for each service
for SERVICE in "${!SERVICES[@]}"; do
    echo "Generating certificate for ${SERVICE}..."
    generate_cert "$SERVICE" "$ROOT_CA_NAME" "${SERVICES[$SERVICE]}"
done

echo "Certificate generation complete."