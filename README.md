# mathrix-education/openapi-processor

![version]
![license]
![php-version]

[version]: https://img.shields.io/packagist/v/mathrix-education/openapi-processor?style=flat-square
[license]: https://img.shields.io/packagist/l/mathrix-education/openapi-processor?style=flat-square
[php-version]: https://img.shields.io/packagist/php-v/mathrix-education/openapi-processor?style=flat-square

This is a small processor to split the OpenAPI specification in the multiple pieces.

Usage:
```bash
vendor/bin/openapi-processor \
    --srcDir={sources directory} \
    [--output={output file}]
```

By default, the output file is stored in the same folder as the source files.
