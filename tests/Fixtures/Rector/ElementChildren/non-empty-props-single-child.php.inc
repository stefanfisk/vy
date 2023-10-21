<?php

use function StefanFisk\Vy\el;

el('div', ['foo' => 'bar'], 'foo');

?>
-----
<?php

use function StefanFisk\Vy\el;

el('div', ['foo' => 'bar'])('foo');

?>
