# Turkish Name Parser
This is a simple parser that parses Turkish names and tries to validate them. Characters, tags and numbers are stripped and what remains is evaluated. Names with single letters, with no vowels are considered invalid and are stripped from the result. Parser then tries to order names and determine first name, middle name and the last name. 

### Installation

`php composer require raicem/turkish-name-parser`

### Usage

```php
use Raicem\NameParser;

$parser = new NameParser;
$name = $parser->parse('Ahmet Yılmaz');

// test weather the name was valid
$name->isValid();

// get the result as array
$name->asArray();

// get the result as string;
$name->asString();

// toString allows you to get the string as well
echo $name

// you can see whichs parts are invalid
$name->getInvalidChunks();
```