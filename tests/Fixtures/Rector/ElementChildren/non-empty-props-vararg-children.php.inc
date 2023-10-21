<?php

use function StefanFisk\Vy\el;

el('div', ['foo' => 'bar'], 'foo', 'bar', 'baz');

?>
-----
<?php

use function StefanFisk\Vy\el;

el('div', ['foo' => 'bar'])('foo', 'bar', 'baz');

?>
