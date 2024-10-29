#!/bin/bash

# Start OpenSearch in the background
/usr/share/opensearch/opensearch-docker-entrypoint.sh &

# Wait for OpenSearch to be ready, then apply the security configurations
(
  until curl -s -k https://localhost:9200/_cat/health -u admin:admin --cert /usr/share/opensearch/config/admin.pem --key /usr/share/opensearch/config/admin-key.pem | grep -q 'green'; do
    echo "Waiting for OpenSearch to be ready..."
    sleep 5
  done

  # Run securityadmin.sh to apply the configuration
  /usr/share/opensearch/plugins/opensearch-security/tools/securityadmin.sh \
    -cd /usr/share/opensearch/plugins/opensearch-security/securityconfig \
    -icl -key /usr/share/opensearch/config/admin-key.pem \
    -cert /usr/share/opensearch/config/admin.pem \
    -cacert /usr/share/opensearch/config/root-ca.pem \
    -nhnv

  echo "Security configurations applied successfully."
) &

# Wait for OpenSearch to be ready
wait -n

# After OpenSearch starts, continue to show logs
wait