# OAS Tools

![Travis (.org)](https://img.shields.io/travis/danballance/oas-tools.svg)

OAS Tools is a PHP library for working with Open API Specifications in both version 2 
and version 3 formats.

The purpose of the library is to provide a more convenient interface for 
working with Open API specifications than manipulating arrays generated 
directly from the JSON or YAML files themselves. Over time the scope of the
library will be improved to cover more of the schema and additional common 
use cases.

The current implementation provides a few initial features, including the 
ability to:

* easily load version 2 and 3 schemas from a file or network
location and in JSON or YAML format
* query for lists of operations, operation ids and paths without having 
to parse the whole schema yourself
* query for schemas/definitions, optionally resolving all references

## Installation

Use the package manager [composer](https://getcomposer.org/) to install foobar.

```bash
composer require danballance/oas-tools 
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)