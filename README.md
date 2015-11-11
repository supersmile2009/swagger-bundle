# swagger-bundle
Integration for draw/swagger into symfony 2 bundle

To use swagger with the api_key as the authorization token for lexik jwt authentication just enable the query_parameter:

```YAML
security:
    firewalls:
        api:
            pattern:   ^/api/
            stateless: true
            lexik_jwt:
                query_parameter:
                    enabled: true
                    name: api_key

```
